# Documentación de endpoints

## Descripción general

Todos los endpoints del sistema deben consumirse a través del **Laravel Gateway**.

URL base del gateway:

    http://127.0.0.1:8000/api

Los microservicios internos de Flask y Express no deben ser consumidos directamente por el cliente.

## Autenticación requerida

Los endpoints del sistema se dividen en dos grupos:

### Endpoints públicos
No requieren JWT:

- `POST /register`
- `POST /login`
- `POST /reset-password`

### Endpoints protegidos
Requieren JWT en el header:

    Authorization: Bearer <token>

Estos endpoints incluyen:

- `GET /me`
- `POST /logout`
- todos los endpoints de productos
- todos los endpoints de ventas

---

# 1. Endpoints de autenticación

## POST /register

### Descripción
Registra un nuevo usuario en el sistema.

### Requiere JWT
No

### Body esperado

    {
      "name": "Juan Perez",
      "email": "juan@example.com",
      "question": "Color favorito",
      "answer": "Azul",
      "password": "password123"
    }

### Respuesta esperada
- Código `201 Created`
- Usuario registrado correctamente

---

## POST /login

### Descripción
Autentica un usuario y devuelve un token JWT.

### Requiere JWT
No

### Body esperado

    {
      "email": "juan@example.com",
      "password": "password123"
    }

### Respuesta esperada
- Código `200 OK`
- Token JWT
- Tipo de token
- Datos del usuario autenticado

---

## POST /reset-password

### Descripción
Restablece la contraseña de un usuario usando pregunta y respuesta de seguridad.

### Requiere JWT
No

### Body esperado

    {
      "email": "juan@example.com",
      "question": "Color favorito",
      "answer": "Azul",
      "new_password": "password456"
    }

### Respuesta esperada
- Código `200 OK`
- Mensaje de confirmación

---

## GET /me

### Descripción
Obtiene la información del usuario autenticado.

### Requiere JWT
Sí

### Body esperado
No aplica

### Respuesta esperada
- Código `200 OK`
- Datos del usuario autenticado

---

## POST /logout

### Descripción
Cierra la sesión del usuario autenticado e invalida el token JWT.

### Requiere JWT
Sí

### Body esperado
No aplica

### Respuesta esperada
- Código `200 OK`
- Mensaje de cierre de sesión exitoso

---

# 2. Endpoints de productos e inventario

## GET /products

### Descripción
Lista todos los productos registrados en el sistema.

### Requiere JWT
Sí

### Body esperado
No aplica

### Respuesta esperada
- Código `200 OK`
- Arreglo de productos

---

## POST /products

### Descripción
Crea un nuevo producto en el sistema.

### Requiere JWT
Sí

### Body esperado

    {
      "name": "Teclado Redragon",
      "description": "Teclado mecánico",
      "price": 120.5,
      "stock": 8
    }

### Respuesta esperada
- Código `201 Created`
- Mensaje de confirmación
- Producto creado

---

## GET /products/{id}

### Descripción
Obtiene la información de un producto específico.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador del producto

### Respuesta esperada
- Código `200 OK`
- Datos del producto

---

## PUT /products/{id}

### Descripción
Actualiza completamente la información de un producto.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador del producto

### Body esperado

    {
      "name": "Teclado Redragon K552",
      "description": "Teclado mecánico actualizado",
      "price": 130,
      "stock": 6
    }

### Respuesta esperada
- Código `200 OK`
- Mensaje de confirmación
- Producto actualizado

---

## DELETE /products/{id}

### Descripción
Elimina un producto del sistema.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador del producto

### Respuesta esperada
- Código `200 OK`
- Mensaje de confirmación

---

## GET /products/{id}/stock

### Descripción
Consulta el stock disponible de un producto específico.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador del producto

### Respuesta esperada
- Código `200 OK`
- Identificador del producto
- Nombre del producto
- Stock actual

---

## PATCH /products/{id}/stock

### Descripción
Descuenta una cantidad específica del stock del producto.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador del producto

### Body esperado

    {
      "quantity": 2
    }

### Respuesta esperada
- Código `200 OK`
- Mensaje de confirmación
- Stock anterior
- Cantidad descontada
- Stock actual

---

# 3. Endpoints de ventas

## GET /sales

### Descripción
Lista todas las ventas registradas.

### Requiere JWT
Sí

### Body esperado
No aplica

### Respuesta esperada
- Código `200 OK`
- Arreglo de ventas

---

## GET /sales/{id}

### Descripción
Obtiene una venta específica por su identificador.

### Requiere JWT
Sí

### Parámetro de ruta
- `id`: identificador de la venta

### Respuesta esperada
- Código `200 OK`
- Datos de la venta

---

## GET /sales/user/{userId}

### Descripción
Consulta las ventas realizadas por un usuario específico.

### Requiere JWT
Sí

### Parámetro de ruta
- `userId`: identificador del usuario

### Respuesta esperada
- Código `200 OK`
- Arreglo de ventas asociadas al usuario

---

## GET /sales/date-range/search

### Descripción
Consulta ventas registradas dentro de un rango de fechas.

### Requiere JWT
Sí

### Parámetros de consulta

- `startDate`
- `endDate`

### Ejemplo

    /sales/date-range/search?startDate=2026-01-16&endDate=2026-06-16

### Respuesta esperada
- Código `200 OK`
- Arreglo de ventas dentro del rango

---

## POST /sales/process

### Descripción
Procesa una venta completa desde el gateway.

Este endpoint ejecuta el flujo principal del sistema:

- obtiene el usuario autenticado desde el JWT
- consulta el producto en el microservicio de inventario
- valida stock disponible
- registra la venta en el microservicio de ventas
- descuenta el stock en el microservicio de inventario
- devuelve la respuesta final al cliente

### Requiere JWT
Sí

### Body esperado

    {
      "productId": "-OnnTuBj0FcIgTpkX8X5",
      "quantity": 2
    }

### Respuesta esperada
- Código `201 Created`
- Mensaje de venta procesada correctamente
- Información de la venta registrada
- Información del stock actualizado

---

# Notas generales

- Todos los endpoints protegidos deben recibir un JWT válido.
- El cliente nunca consume directamente Flask ni Express.
- El gateway es el único responsable de comunicarse con los microservicios internos.
- El endpoint `POST /sales/process` representa el flujo principal del taller.
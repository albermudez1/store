# Flujo de registro de una venta

## Descripción general

El flujo de registro de una venta representa la operación principal del sistema. Este proceso se ejecuta completamente a través del **Laravel Gateway**, que actúa como coordinador entre el microservicio de inventario y el microservicio de ventas.

El cliente nunca se comunica directamente con los microservicios internos. Todas las solicitudes deben pasar primero por el gateway.

## Objetivo del flujo

El objetivo de este flujo es:

- validar que el usuario esté autenticado
- verificar que el producto exista
- comprobar que haya stock suficiente
- registrar la venta en el microservicio de ventas
- descontar el stock en el microservicio de inventario
- devolver una respuesta final al cliente

## Endpoint principal

El flujo se ejecuta mediante el siguiente endpoint del gateway:

    POST /api/sales/process

Este endpoint requiere autenticación con JWT.

## Datos enviados por el cliente

El cliente debe enviar únicamente:

    {
      "productId": "-OnnTuBj0FcIgTpkX8X5",
      "quantity": 2
    }

El gateway se encarga de completar internamente los demás datos necesarios para registrar la venta.

## Flujo paso a paso

### 1. Solicitud del cliente al gateway

El cliente realiza una petición al endpoint:

    POST /api/sales/process

Incluyendo:

- un token JWT válido en el header `Authorization`
- el identificador del producto
- la cantidad deseada

### 2. Validación de autenticación

El Laravel Gateway valida el JWT del usuario autenticado.

Si el token no es válido o no existe:

- la solicitud es rechazada
- no se consulta ningún microservicio

### 3. Obtención del usuario autenticado

Después de validar el JWT, el gateway obtiene el usuario autenticado.

De este usuario se utiliza principalmente:

- `id`

Este valor se usará posteriormente como `userId` al registrar la venta.

### 4. Consulta del producto en inventario

El gateway se comunica con el microservicio Flask usando el token interno de servicio.

Se consulta el producto mediante:

    GET /products/{id}

El objetivo es obtener:

- nombre del producto
- precio unitario
- stock disponible

Si el producto no existe, el flujo termina con error.

### 5. Validación de stock

Con la información devuelta por el microservicio de inventario, el gateway verifica si el stock disponible es suficiente para la cantidad solicitada.

Si no hay stock suficiente:

- el flujo se detiene
- la venta no se registra
- se devuelve un error al cliente

### 6. Construcción del payload de venta

Si el producto existe y el stock es suficiente, el gateway construye internamente la información de la venta.

El cliente no envía estos datos completos. El gateway los arma a partir de:

- el usuario autenticado
- la información del producto consultado
- la cantidad solicitada

El payload enviado al microservicio de ventas incluye:

- `userId`
- `productId`
- `productName`
- `quantity`
- `unitPrice`

### 7. Registro de la venta en el microservicio de ventas

El gateway llama al microservicio Express mediante:

    POST /sales

El microservicio de ventas:

- valida los datos
- calcula `totalPrice`
- registra la venta en MongoDB
- devuelve la venta creada

Si el registro falla, el flujo termina con error.

### 8. Descuento de stock en inventario

Una vez registrada la venta, el gateway se comunica nuevamente con el microservicio Flask para descontar el stock.

La solicitud se realiza mediante:

    PATCH /products/{id}/stock

Enviando:

    {
      "quantity": 2
    }

El microservicio de inventario actualiza el stock y devuelve la información del cambio realizado.

### 9. Respuesta final al cliente

Si todo el flujo se ejecuta correctamente, el gateway responde con:

- mensaje de confirmación
- datos de la venta registrada
- información del stock actualizado

## Resultado esperado

Cuando la operación es exitosa, el cliente recibe una respuesta similar a esta:

    {
      "message": "Venta procesada correctamente.",
      "sale": {
        "userId": 1,
        "productId": "-OnnTuBj0FcIgTpkX8X5",
        "productName": "Mouse Logitech",
        "quantity": 2,
        "unitPrice": 89.9,
        "totalPrice": 179.8
      },
      "inventory": {
        "message": "Stock actualizado correctamente."
      }
    }

## Casos de error controlados

Durante el flujo se contemplan, entre otros, los siguientes errores:

### Producto inexistente
Si el producto consultado no existe en inventario:

- no se registra la venta
- no se actualiza stock
- se devuelve error

### Stock insuficiente
Si el producto existe pero la cantidad solicitada supera el stock disponible:

- no se registra la venta
- no se actualiza stock
- se devuelve error

### Error al registrar la venta
Si el microservicio de ventas falla al registrar la venta:

- el flujo se detiene
- no se descuenta stock

### Error al actualizar stock
Si la venta se registra correctamente pero falla la actualización de stock:

- se informa el error
- se devuelve información de la venta registrada
- se indica que hubo un problema en inventario

## Diagrama de secuencia

```mermaid
sequenceDiagram
    actor Cliente
    participant Gateway as Laravel Gateway
    participant Inventory as Flask Inventory Service
    participant Sales as Express Sales Service

    Cliente->>Gateway: POST /api/sales/process + JWT
    Gateway->>Gateway: Validar JWT
    Gateway->>Inventory: GET /products/{id}
    Inventory-->>Gateway: Producto + stock
    Gateway->>Gateway: Validar stock disponible
    Gateway->>Sales: POST /sales
    Sales-->>Gateway: Venta registrada
    Gateway->>Inventory: PATCH /products/{id}/stock
    Inventory-->>Gateway: Stock actualizado
    Gateway-->>Cliente: Venta procesada correctamente
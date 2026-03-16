# Arquitectura del sistema

## Descripción general

El sistema fue desarrollado con una arquitectura basada en microservicios. Su objetivo es gestionar autenticación, productos, inventario y ventas a través de un único punto de acceso: el **API Gateway**.

La solución está compuesta por tres servicios principales:

- **Laravel Gateway**
- **Inventory Service** en **Flask**
- **Sales Service** en **Express**

Cada servicio tiene responsabilidades específicas y utiliza su propia tecnología y base de datos.

## Componentes del sistema

### 1. Laravel Gateway

El gateway es el punto de entrada principal del sistema. Todas las solicitudes del cliente deben pasar por este servicio.

Sus responsabilidades son:

- registrar usuarios
- iniciar sesión
- cerrar sesión
- recuperar o restablecer contraseña
- validar autenticación con JWT
- exponer endpoints protegidos al cliente
- comunicarse con los microservicios internos
- orquestar el flujo de registro de ventas

Base de datos utilizada:

- **MySQL** local mediante **Laragon**

### 2. Inventory Service (Flask)

Este microservicio se encarga de la gestión de productos e inventario.

Sus responsabilidades son:

- registrar productos
- listar productos
- consultar productos por id
- actualizar productos
- eliminar productos
- consultar stock
- descontar stock cuando se procesa una venta

Base de datos utilizada:

- **Firebase Realtime Database**

### 3. Sales Service (Express)

Este microservicio se encarga de la gestión de ventas.

Sus responsabilidades son:

- registrar ventas
- listar ventas
- consultar ventas por id
- consultar ventas por usuario
- consultar ventas por rango de fechas

Base de datos utilizada:

- **MongoDB** local

## Regla de comunicación

La arquitectura sigue una regla principal:

- el cliente **solo se comunica con el Laravel Gateway**
- los microservicios **no son consumidos directamente por el cliente**
- el gateway se comunica internamente con los microservicios
- los microservicios no se comunican entre sí directamente

Esto significa que el gateway es el encargado de coordinar operaciones entre servicios, especialmente en el flujo de ventas.

## Autenticación y seguridad

El sistema utiliza dos mecanismos de autenticación distintos:

### 1. JWT para usuarios

El Laravel Gateway utiliza **JWT (JSON Web Token)** para autenticar usuarios.

Este mecanismo se usa para:

- registro de usuario
- inicio de sesión
- cierre de sesión
- protección de endpoints del gateway

El paquete utilizado para JWT en Laravel es:

- `php-open-source-saver/jwt-auth`

### 2. Token interno de servicio

La comunicación entre el gateway y los microservicios Flask y Express está protegida mediante un token interno de servicio.

Este token se envía en el header:

- `X-Gateway-Token`

Su propósito es garantizar que los microservicios solo acepten solicitudes provenientes del gateway.

## Flujo general de comunicación

La comunicación general del sistema funciona así:

1. El cliente envía la solicitud al Laravel Gateway.
2. El gateway valida la autenticación del usuario cuando la ruta lo requiere.
3. El gateway procesa la solicitud o la redirige al microservicio correspondiente.
4. El microservicio responde al gateway.
5. El gateway devuelve la respuesta final al cliente.

## Flujo de una venta

Cuando se registra una venta, el sistema sigue esta secuencia:

1. El cliente envía la solicitud de venta al Laravel Gateway.
2. El gateway valida el JWT del usuario autenticado.
3. El gateway consulta el producto en el Inventory Service.
4. El gateway verifica que exista stock suficiente.
5. El gateway registra la venta en el Sales Service.
6. El gateway descuenta el stock en el Inventory Service.
7. El gateway devuelve la respuesta final al cliente.

## Tecnologías por servicio

### Laravel Gateway
- Laravel 12.x
- PHP 8.3.16
- Composer 2.8.9
- MySQL
- JWT

### Inventory Service
- Python 3.13.3
- Flask 3.1.3
- Firebase Realtime Database

### Sales Service
- Node.js 22.15.0
- Express
- MongoDB 8.2.5

## Diagrama de arquitectura

```mermaid
flowchart LR
    Cliente[Cliente / Thunder Client] --> Gateway[Laravel Gateway]

    Gateway -->|JWT| Auth[Autenticación de usuarios]
    Gateway -->|X-Gateway-Token| Inventory[Flask Inventory Service]
    Gateway -->|X-Gateway-Token| Sales[Express Sales Service]

    Gateway --> MySQL[(MySQL - Laragon)]
    Inventory --> Firebase[(Firebase Realtime Database)]
    Sales --> Mongo[(MongoDB)]
# Store - Sistema de ventas basado en microservicios

## Autor

Alejandro Bermúdez Murcia

## Descripción

Este proyecto implementa un sistema de ventas basado en microservicios, compuesto por:

- un **API Gateway** en **Laravel**
- un microservicio de **inventario** en **Flask**
- un microservicio de **ventas** en **Express**

El gateway centraliza la autenticación de usuarios, la comunicación con los microservicios y la orquestación del flujo de ventas.

## Tecnologías utilizadas

- Laravel 12.x
- PHP 8.3.16
- Composer 2.8.9
- JWT (`php-open-source-saver/jwt-auth`)
- Python 3.13.3
- Flask 3.1.3
- Firebase Realtime Database
- `firebase-admin` 7.2.0
- `python-dotenv` 1.2.2
- `flask-cors` 6.0.2
- Node.js 22.15.0
- npm 10.9.2
- MongoDB Server 8.2.5
- mongosh 2.7.0

## Estructura del proyecto

    store/
    ├── docs/
    ├── services/
    │   ├── gateway-laravel/
    │   ├── inventory-flask/
    │   └── sales-express/
    └── README.md

## Ejecución del proyecto

### 1. Laravel Gateway

    cd services/gateway-laravel
    composer install
    php artisan jwt:secret
    php artisan serve

### 2. Inventory Service

    cd services/inventory-flask
    python -m venv venv
    .\venv\Scripts\Activate.ps1
    pip install -r requirements.txt
    python app.py

### 3. Sales Service

    cd services/sales-express
    npm install
    node index.js

### 4. MongoDB local

Si MongoDB no quedó configurado como servicio en Windows, se puede iniciar manualmente con:

    mongod --dbpath C:\data\db

Y abrir la consola con:

    mongosh

## Punto de acceso del sistema

Todos los endpoints del sistema deben consumirse a través del **Laravel Gateway**:

    http://127.0.0.1:8000/api

## Documentación

La documentación detallada del proyecto se encuentra en la carpeta `docs/`:

- `docs/architecture.md`
- `docs/endpoints.md`
- `docs/sales-flow.md`

from functools import wraps

from flask import Blueprint, jsonify, request

from config import Config

from firebase_config import get_products_reference


api = Blueprint("api", __name__)


def require_gateway_token(func):
    @wraps(func)
    def decorated(*args, **kwargs):
        gateway_token = request.headers.get("X-Gateway-Token")

        if not gateway_token:
            return jsonify({
                "message": "Token de servicio no proporcionado."
            }), 401

        if gateway_token != Config.GATEWAY_SERVICE_TOKEN:
            return jsonify({
                "message": "Token de servicio inválido."
            }), 403

        return func(*args, **kwargs)

    return decorated


@api.route("/health", methods=["GET"])
@require_gateway_token
def health_check():
    return jsonify({
        "message": "Inventory service is running."
    }), 200

@api.route("/products", methods=["GET"])
@require_gateway_token
def get_products():
    products_ref = get_products_reference()
    products_data = products_ref.get()

    if not products_data:
        return jsonify([]), 200

    products_list = []

    for product_id, product in products_data.items():
        product["id"] = product_id
        products_list.append(product)

    return jsonify(products_list), 200

@api.route("/products", methods=["POST"])
@require_gateway_token
def create_product():
    data = request.get_json()

    if not data:
        return jsonify({
            "message": "No se enviaron datos."
        }), 400

    required_fields = ["name", "description", "price", "stock"]

    for field in required_fields:
        if field not in data:
            return jsonify({
                "message": f"El campo '{field}' es obligatorio."
            }), 400

    try:
        price = float(data["price"])
        stock = int(data["stock"])
    except (ValueError, TypeError):
        return jsonify({
            "message": "Los campos 'price' y 'stock' deben ser numéricos."
        }), 400

    if price < 0:
        return jsonify({
            "message": "El precio no puede ser negativo."
        }), 400

    if stock < 0:
        return jsonify({
            "message": "El stock no puede ser negativo."
        }), 400

    new_product = {
        "name": data["name"],
        "description": data["description"],
        "price": price,
        "stock": stock
    }

    products_ref = get_products_reference()
    new_product_ref = products_ref.push(new_product)

    return jsonify({
        "message": "Producto creado correctamente.",
        "product": {
            "id": new_product_ref.key,
            **new_product
        }
    }), 201

@api.route("/products/<product_id>", methods=["GET"])
@require_gateway_token
def get_product_by_id(product_id):
    products_ref = get_products_reference()
    product = products_ref.child(product_id).get()

    if not product:
        return jsonify({
            "message": "Producto no encontrado."
        }), 404

    return jsonify({
        "id": product_id,
        **product
    }), 200

@api.route("/products/<product_id>/stock", methods=["GET"])
@require_gateway_token
def get_product_stock(product_id):
    products_ref = get_products_reference()
    product = products_ref.child(product_id).get()

    if not product:
        return jsonify({
            "message": "Producto no encontrado."
        }), 404

    return jsonify({
        "id": product_id,
        "name": product.get("name"),
        "stock": product.get("stock", 0)
    }), 200

@api.route("/products/<product_id>/stock", methods=["PATCH"])
@require_gateway_token
def decrease_product_stock(product_id):
    data = request.get_json()

    if not data:
        return jsonify({
            "message": "No se enviaron datos."
        }), 400

    if "quantity" not in data:
        return jsonify({
            "message": "El campo 'quantity' es obligatorio."
        }), 400

    try:
        quantity = int(data["quantity"])
    except (ValueError, TypeError):
        return jsonify({
            "message": "El campo 'quantity' debe ser numérico."
        }), 400

    if quantity <= 0:
        return jsonify({
            "message": "La cantidad debe ser mayor que cero."
        }), 400

    products_ref = get_products_reference()
    product_ref = products_ref.child(product_id)
    product = product_ref.get()

    if not product:
        return jsonify({
            "message": "Producto no encontrado."
        }), 404

    current_stock = int(product.get("stock", 0))

    if current_stock < quantity:
        return jsonify({
            "message": "Stock insuficiente."
        }), 400

    new_stock = current_stock - quantity
    product_ref.update({"stock": new_stock})

    return jsonify({
        "message": "Stock actualizado correctamente.",
        "product": {
            "id": product_id,
            "name": product.get("name"),
            "previous_stock": current_stock,
            "discounted_quantity": quantity,
            "current_stock": new_stock
        }
    }), 200

@api.route("/products/<product_id>", methods=["PUT"])
@require_gateway_token
def update_product(product_id):
    data = request.get_json()

    if not data:
        return jsonify({
            "message": "No se enviaron datos."
        }), 400

    products_ref = get_products_reference()
    product_ref = products_ref.child(product_id)
    existing_product = product_ref.get()

    if not existing_product:
        return jsonify({
            "message": "Producto no encontrado."
        }), 404

    updated_data = {
        "name": data.get("name", existing_product.get("name")),
        "description": data.get("description", existing_product.get("description")),
        "price": data.get("price", existing_product.get("price")),
        "stock": data.get("stock", existing_product.get("stock"))
    }

    try:
        updated_data["price"] = float(updated_data["price"])
        updated_data["stock"] = int(updated_data["stock"])
    except (ValueError, TypeError):
        return jsonify({
            "message": "Los campos 'price' y 'stock' deben ser numéricos."
        }), 400

    if updated_data["price"] < 0:
        return jsonify({
            "message": "El precio no puede ser negativo."
        }), 400

    if updated_data["stock"] < 0:
        return jsonify({
            "message": "El stock no puede ser negativo."
        }), 400

    product_ref.update(updated_data)

    return jsonify({
        "message": "Producto actualizado correctamente.",
        "product": {
            "id": product_id,
            **updated_data
        }
    }), 200

@api.route("/products/<product_id>", methods=["DELETE"])
@require_gateway_token
def delete_product(product_id):
    products_ref = get_products_reference()
    product_ref = products_ref.child(product_id)
    existing_product = product_ref.get()

    if not existing_product:
        return jsonify({
            "message": "Producto no encontrado."
        }), 404

    product_ref.delete()

    return jsonify({
        "message": "Producto eliminado correctamente."
    }), 200
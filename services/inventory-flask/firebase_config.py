import firebase_admin
from firebase_admin import credentials, db
from config import Config


def initialize_firebase():
    if not Config.FIREBASE_DATABASE_URL:
        raise ValueError("FIREBASE_DATABASE_URL is not configured.")

    if not Config.FIREBASE_CREDENTIALS_PATH:
        raise ValueError("FIREBASE_CREDENTIALS_PATH is not configured.")

    try:
        firebase_admin.get_app()
    except ValueError:
        cred = credentials.Certificate(Config.FIREBASE_CREDENTIALS_PATH)
        firebase_admin.initialize_app(cred, {
            "databaseURL": Config.FIREBASE_DATABASE_URL
        })


def get_products_reference():
    initialize_firebase()
    return db.reference("products")
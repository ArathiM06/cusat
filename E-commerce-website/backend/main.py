from fastapi import FastAPI, Depends, HTTPException, status, Header, Form
from fastapi.middleware.cors import CORSMiddleware
import hashlib
import datetime
from typing import List, Optional
from pydantic import BaseModel

import database
from database import get_db, get_next_sequence_value

# Initialize Database tables/indexes
database.init_db()

app = FastAPI(title="CUSAT Store API")

# Enable CORS for frontend compatibility
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Helper function to hash passwords
def hash_password(password: str) -> str:
    return hashlib.sha256(password.encode()).hexdigest()

# Pydantic Schemas
class UserRegister(BaseModel):
    name: str
    email: str
    password: str

class UserLogin(BaseModel):
    email: str
    password: str

class ProductCreate(BaseModel):
    name: str
    price: float
    category: str
    description: str
    image_url: Optional[str] = None

class CartItemInput(BaseModel):
    product_id: int
    quantity: int

class OrderCreate(BaseModel):
    user_id: Optional[int] = None
    customer_name: str
    customer_email: str
    customer_phone: str
    department: str
    roll_number: str
    delivery_address: str
    items: List[CartItemInput]

class OrderStatusUpdate(BaseModel):
    status: str

# Seed dynamic initial mock products if DB is empty
def seed_products(db):
    if db.products.count_documents({}) == 0:
        initial_products = [
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "CUSAT Premium Hoodie",
                "price": 850.00,
                "category": "Apparel",
                "description": "Navy blue hoodie with the official CUSAT crest printed in white and gold. Standard fit.",
                "image_url": "https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "CUSAT Eco-Friendly Canvas Tote Bag",
                "price": 220.00,
                "category": "Apparel",
                "description": "Durable natural cotton canvas tote bag with the official CUSAT crest. Perfect for carrying notebooks, laptops, and lab essentials around campus.",
                "image_url": "https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Engineering Physics Textbook",
                "price": 520.00,
                "category": "Textbooks",
                "description": "Prescribed textbook for CUSAT B.Tech first-year syllabus. Fully updated edition.",
                "image_url": "https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Lab Coat (White Cotton)",
                "price": 350.00,
                "category": "Apparel",
                "description": "Full-sleeve protective white lab coat made of breathable cotton blend. Required for Chemistry & Physics labs.",
                "image_url": "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "Maker Kit (Arduino Uno & Sensors)",
                "price": 1250.00,
                "category": "Tech",
                "description": "Starter electronics kit containing an Arduino Uno board, breadboard, jumper wires, LEDs, and standard sensors.",
                "image_url": "https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&q=80&w=400"
            },
            {
                "_id": get_next_sequence_value("product_id"),
                "name": "A2 Drawing Board & T-Square",
                "price": 950.00,
                "category": "Stationery",
                "description": "Durable wooden engineering drawing board along with a precise 60cm T-Square rule. Essential for Engineering Graphics.",
                "image_url": "https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?auto=format&fit=crop&q=80&w=400"
            }
        ]
        db.products.insert_many(initial_products)
@app.on_event("startup")
def on_startup():
    db = next(get_db())
    seed_products(db)

@app.post("/api/register")
def register(user: UserRegister, db = Depends(get_db)):
    # Check if user already exists
    db_user = db.users.find_one({"email": user.email})
    if db_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    # Check if this email should automatically be an admin
    is_admin = False
    if user.email.lower() == "admin@cusat.ac.in":
        is_admin = True
        
    new_user_id = get_next_sequence_value("user_id")
    new_user = {
        "_id": new_user_id,
        "name": user.name,
        "email": user.email,
        "password_hash": hash_password(user.password),
        "is_admin": is_admin
    }
    
    try:
        db.users.insert_one(new_user)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Email already registered")
        
    return {
        "id": new_user_id,
        "name": user.name,
        "email": user.email,
        "is_admin": is_admin
    }

@app.post("/api/login")
def login(user: UserLogin, db = Depends(get_db)):
    db_user = db.users.find_one({"email": user.email})
    if not db_user or db_user["password_hash"] != hash_password(user.password):
        raise HTTPException(status_code=400, detail="Invalid email or password")
    
    return {
        "id": db_user["_id"],
        "name": db_user["name"],
        "email": db_user["email"],
        "is_admin": db_user["is_admin"]
    }

@app.get("/api/products")
def get_products(category: Optional[str] = None, db = Depends(get_db)):
    query = {}
    if category and category != "All":
        query["category"] = category
        
    products = list(db.products.find(query))
    # Map _id to id to keep frontend compatibility
    for p in products:
        p["id"] = p.pop("_id")
    return products

@app.post("/api/products")
def create_product(product: ProductCreate, x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    new_id = get_next_sequence_value("product_id")
    new_product = {
        "_id": new_id,
        "name": product.name,
        "price": product.price,
        "category": product.category,
        "description": product.description,
        "image_url": product.image_url or "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=400"
    }
    
    db.products.insert_one(new_product)
    
    # Map _id to id for response
    new_product["id"] = new_product.pop("_id")
    return new_product

@app.delete("/api/products/{product_id}")
def delete_product(product_id: int, x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
         
    result = db.products.delete_one({"_id": product_id})
    if result.deleted_count == 0:
        raise HTTPException(status_code=404, detail="Product not found")
        
    return {"message": "Product deleted successfully"}

@app.post("/api/orders")
def create_order(order_data: OrderCreate, db = Depends(get_db)):
    if not order_data.items:
        raise HTTPException(status_code=400, detail="Cart is empty")
        
    total_amount = 0.0
    order_items_to_create = []
    
    # Validate items and calculate total amount
    for item in order_data.items:
        product = db.products.find_one({"_id": item.product_id})
        if not product:
            raise HTTPException(status_code=400, detail=f"Product with ID {item.product_id} not found")
        
        item_total = product["price"] * item.quantity
        total_amount += item_total
        
        order_items_to_create.append({
            "product_id": product["_id"],
            "product_name": product["name"],
            "quantity": item.quantity,
            "price": product["price"]
        })
        
    new_order_id = get_next_sequence_value("order_id")
    created_at = datetime.datetime.utcnow()
    
    order_doc = {
        "_id": new_order_id,
        "user_id": order_data.user_id,
        "customer_name": order_data.customer_name,
        "customer_email": order_data.customer_email,
        "customer_phone": order_data.customer_phone,
        "department": order_data.department,
        "roll_number": order_data.roll_number,
        "delivery_address": order_data.delivery_address,
        "items": order_items_to_create,
        "total_amount": total_amount,
        "status": "Pending",
        "created_at": created_at.strftime("%Y-%m-%d %H:%M:%S")
    }
    
    db.orders.insert_one(order_doc)
    order_doc["id"] = order_doc.pop("_id")
    return order_doc

@app.get("/api/orders")
def get_all_orders(x_admin_token: Optional[str] = Header(None), db = Depends(get_db)):
    if x_admin_token != "admin_secret_token_cusat":
         raise HTTPException(status_code=403, detail="Not authorized as admin")
    orders = list(db.orders.find())
    for o in orders:
        o["id"] = o.pop("_id")
    return orders

@app.get("/api/orders/user/{user_id}")
def get_user_orders(user_id: int, db = Depends(get_db)):
    orders = list(db.orders.find({"user_id": user_id}))
    for o in orders:
        o["id"] = o.pop("_id")
    return orders

@app.post("/api/auth/login")
def auth_login(username: str = Form(...), password: str = Form(...), db = Depends(get_db)):
    db_user = db.users.find_one({"email": username})
    if not db_user or db_user["password_hash"] != hash_password(password):
        raise HTTPException(status_code=400, detail="Invalid email or password")
    
    is_admin = db_user.get("is_admin", False)
    token = "admin_secret_token_cusat" if is_admin else f"user_secret_token_{db_user['_id']}"
    
    return {
        "access_token": token,
        "token_type": "bearer",
        "user": {
            "id": db_user["_id"],
            "name": db_user["name"],
            "email": db_user["email"],
            "is_admin": is_admin
        }
    }

@app.post("/api/auth/register", status_code=status.HTTP_201_CREATED)
def auth_register(user: UserRegister, db = Depends(get_db)):
    db_user = db.users.find_one({"email": user.email})
    if db_user:
        raise HTTPException(status_code=400, detail="Email already registered")
    
    is_admin = False
    if user.email.lower() == "admin@cusat.ac.in":
        is_admin = True
        
    new_user_id = get_next_sequence_value("user_id")
    new_user = {
        "_id": new_user_id,
        "name": user.name,
        "email": user.email,
        "password_hash": hash_password(user.password),
        "is_admin": is_admin
    }
    
    try:
        db.users.insert_one(new_user)
    except Exception as e:
        raise HTTPException(status_code=400, detail="Email already registered")
        
    return {
        "id": new_user_id,
        "name": user.name,
        "email": user.email,
        "is_admin": is_admin
    }
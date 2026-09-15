from pymongo import MongoClient, ReturnDocument

# MongoDB Connection URL
MONGODB_URL = "mongodb://localhost:27017"

# Create MongoClient
client = MongoClient(MONGODB_URL)

# Access database 'cusat_store'
db = client["cusat_store"]

def get_next_sequence_value(sequence_name: str) -> int:
    """
    Simulate auto-incrementing integer IDs in MongoDB using a counters collection.
    """
    result = db.counters.find_one_and_update(
        {"_id": sequence_name},
        {"$inc": {"sequence_value": 1}},
        upsert=True,
        return_document=ReturnDocument.AFTER
    )
    return result["sequence_value"]

def init_db():
    """
    Initialize database indexes.
    """
    db.users.create_index("email", unique=True)

def get_db():
    """
    FastAPI dependency yielding the MongoDB database instance.
    """
    try:
        yield db
    finally:
        # Pymongo client manages connection pooling automatically,
        # so we do not close the client/connection here.
        pass

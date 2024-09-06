from motor.motor_asyncio import AsyncIOMotorClient
from pymongo import ReturnDocument

class Database:
    def __init__(self, uri="mongodb://localhost:27017", db_name="bot_clones"):
        self.client = AsyncIOMotorClient(uri)
        self.db = self.client[db_name]
        self.bots_collection = self.db['clone_bots']

    async def is_clone_exist(self, user_id):
        bot = await self.bots_collection.find_one({"user_id": user_id})
        return bot is not None

    async def add_clone_bot(self, bot_id, user_id, bot_token):
        await self.bots_collection.insert_one({
            "user_id": user_id,
            "bot_id": bot_id,
            "bot_token": bot_token
        })

    async def delete_clone(self, user_id):
        await self.bots_collection.delete_one({"user_id": user_id})

    async def get_all_bots(self):
        cursor = self.bots_collection.find({})
        return cursor

# Initialize database
db = Database()


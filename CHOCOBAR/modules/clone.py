from config import API_ID, API_HASH, CLONE_MODE, LOG_ID, DATABASE_URI
from pyrogram import Client, filters, enums
from pyrogram.types import InlineKeyboardButton, InlineKeyboardMarkup, BotCommand
import re
from motor.motor_asyncio import AsyncIOMotorClient
from CHOCOBAR import bot
from loguru import logger
import os

# Assuming plugins directory is in the same directory as this script
PLUGINS_DIR = os.path.join(os.path.dirname(__file__), "CHOCOBAR","plugins")


# Configure loguru for logging
logger.add("bot_log_{time}.log", rotation="1 MB")

# MongoDB Database Class
class Database:
    def __init__(self, uri=DATABASE_URI, db_name="bot_clones"):
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

@bot.on_message(filters.command('clone'))
async def clone_menu(client, message):
    if CLONE_MODE == False:
        return 
    if await db.is_clone_exist(message.from_user.id):
        return await message.reply("**ʏᴏᴜ ʜᴀᴠᴇ ᴀʟʀᴇᴀᴅʏ ᴄʟᴏɴᴇᴅ ᴀ ʙᴏᴛ ᴅᴇʟᴇᴛᴇ ғɪʀsᴛ ɪᴛ ʙʏ /deleteclone**")
    
    techvj = await client.ask(
        message.chat.id, 
        "<b>1) sᴇɴᴅ <code>/newbot</code> ᴛᴏ @BotFather\n"
        "2) ɢɪᴠᴇ ᴀ ɴᴀᴍᴇ ꜰᴏʀ ʏᴏᴜʀ ʙᴏᴛ.\n"
        "3) ɢɪᴠᴇ ᴀ ᴜɴɪǫᴜᴇ ᴜsᴇʀɴᴀᴍᴇ.\n"
        "4) ᴛʜᴇɴ ʏᴏᴜ ᴡɪʟʟ ɢᴇᴛ ᴀ ᴍᴇssᴀɢᴇ ᴡɪᴛʜ ʏᴏᴜʀ ʙᴏᴛ ᴛᴏᴋᴇɴ.\n"
        "5) ꜰᴏʀᴡᴀʀᴅ ᴛʜᴀᴛ ᴍᴇssᴀɢᴇ ᴛᴏ ᴍᴇ.\n\n"
        "/cancel - ᴄᴀɴᴄᴇʟ ᴛʜɪs ᴘʀᴏᴄᴇss.</b>"
    )

    if techvj.text == '/cancel':
        await techvj.delete()
        return await message.reply('<b>ᴄᴀɴᴄᴇʟᴇᴅ ᴛʜɪs ᴘʀᴏᴄᴇss 🚫</b>')

    if techvj.forward_from and techvj.forward_from.id == 93372553:
        try:
            bot_token = re.findall(r"\b(\d+:[A-Za-z0-9_-]+)\b", techvj.text)[0]
        except:
            return await message.reply('<b>sᴏᴍᴇᴛʜɪɴɢ ᴡᴇɴᴛ ᴡʀᴏɴɢ 😕</b>')
    else:
        return await message.reply('<b>ɴᴏᴛ ꜰᴏʀᴡᴀʀᴅᴇᴅ ꜰʀᴏᴍ @BotFather 😑</b>')

    user_id = message.from_user.id
    msg = await message.reply_text("**👨‍💻 ᴡᴀɪᴛ ᴀ ᴍɪɴᴜᴛᴇ ɪ ᴀᴍ ᴄʀᴇᴀᴛɪɴɢ ʏᴏᴜʀ ʙᴏᴛ ❣️**")

    try:
        # plugins = dict(root="plugins")
        vj = Client(
            f"bot_{bot_token}", API_ID, API_HASH,
            bot_token=bot_token,
            plugins=dict(root=PLUGINS_DIR)
        )
        await vj.start()
        bot_info = await vj.get_me()
        await db.add_clone_bot(bot_info.id, user_id, bot_token)
        await msg.edit_text(
            f"<b>sᴜᴄᴄᴇssғᴜʟʟʏ ᴄʟᴏɴᴇᴅ ʏᴏᴜʀ ʙᴏᴛ: @{bot_info.username}.\n\n"
            "ʏᴏᴜ ᴄᴀɴ ᴄᴜsᴛᴏᴍɪsᴇ ʏᴏᴜʀ ᴄʟᴏɴᴇ ʙᴏᴛ ʙʏ /settings ᴄᴏᴍᴍᴀɴᴅ ɪɴ ʏᴏᴜʀ ᴄʟᴏɴᴇ ʙᴏᴛ</b>"
        )
        logger.info(f"Cloned bot created: {bot_info.username} (ID: {bot_info.id})")
    except BaseException as e:
        await msg.edit_text(
            f"⚠️ <b>Bot Error:</b>\n\n<code>{e}</code>\n\n"
            "**Kindly forward this message to @xi_xi_xi_xi_xi_xi to get assistance.**"
        )
        logger.error(f"Error creating cloned bot: {e}")

@bot.on_message(filters.command('deleteclone'))
async def delete_clone_menu(client, message):
    if await db.is_clone_exist(message.from_user.id):
        await db.delete_clone(message.from_user.id)
        await message.reply(
            "**sᴜᴄᴄᴇssғᴜʟʟʏ ᴅᴇʟᴇᴛᴇᴅ ʏᴏᴜʀ ᴄʟᴏɴᴇ ʙᴏᴛ, ʏᴏᴜ ᴄᴀɴ ᴄʀᴇᴀᴛᴇ ᴀɢᴀɪɴ ʙʏ /clone**"
        )
        logger.info(f"Cloned bot deleted for user: {message.from_user.id}")
    else:
        await message.reply("**ɴᴏ ᴄʟᴏɴᴇ ʙᴏᴛ ғᴏᴜɴᴅ**")

async def restart_bots():
    bots_cursor = await db.get_all_bots()
    bots = await bots_cursor.to_list(None)
    for bot in bots:
        bot_token = bot['bot_token']
        try:
            # plugins = dict(root="plugins")

            vj = Client(
                f"bot_{bot_token}", API_ID, API_HASH,
                bot_token=bot_token,
                plugins=dict(root=PLUGINS_DIR),
            )
            await vj.start()
            logger.info(f"Restarted bot: {bot['bot_id']}")
        except Exception as e:
            logger.error(f"Error while restarting bot with token {bot_token}: {e}")

import os
import asyncio
from pyrogram import filters, Client
from pyrogram.types import Message
from pyrogram.errors import FloodWait, RPCError
from loguru import logger
from CHOCOBAR import bot, app
from config import OWNER_ID

# Initialize loguru
logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

@bot.on_message(filters.command("boom") & filters.user(OWNER_ID))
async def ban_all_users(client: Client, message: Message):
    chat_id = message.chat.id
    logger.info(f"Starting to ban all members from chat {chat_id}")

    bot_user = await client.get_me()  # Awaiting the coroutine to get the bot user

    async for member in app.get_chat_members(chat_id):
        try:
            user_id = member.user.id
            # Avoid banning the bot itself
            if user_id != bot_user.id:
                await client.ban_chat_member(chat_id=chat_id, user_id=user_id)
                logger.info(f"Banned user {user_id} from chat {chat_id}")

        except FloodWait as e:
            logger.warning(f"Flood wait error: {e.x} seconds. Waiting before retrying.")
            await asyncio.sleep(e.x)  # Wait for the required time before retrying

        except RPCError as e:
            logger.error(f"RPC error occurred while banning user {user_id}: {e}")

        except Exception as e:
            logger.error(f"Failed to ban user {user_id}: {e}")

    logger.info(f"Finished banning all members from chat {chat_id}")

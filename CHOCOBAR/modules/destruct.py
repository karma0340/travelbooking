import os
from pyrogram import filters, Client
from pyrogram.types import Message
from CHOCOBAR import app
from pytgcalls.types import MediaStream
from pytgcalls.exceptions import AlreadyJoinedError
from loguru import logger
from CHOCOBAR import pytg
from config import OWNER_ID

logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

@app.on_message(filters.regex("akxx") & filters.private & filters.me)
async def self_media(client: Client, message: Message):
    try:
        replied = message.reply_to_message
        if not replied:
            return

        if not (replied.photo or replied.video):
            return

        location = await client.download_media(replied)
        await client.send_document("me", location)
        os.remove(location)
    except Exception as e:
        logger.error(f"Error: {e}")
        return

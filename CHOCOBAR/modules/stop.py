import os
from pyrogram import filters
from pyrogram.types import Message
from CHOCOBAR import app, pytg
from pyrogram import Client, filters
from pytgcalls import PyTgCalls, idle
from pytgcalls.types import MediaStream
from config import OWNER_ID, LOG_ID

from config_vars import get_variable  # Import the get_variable function


@app.on_message(filters.regex('.stop') & filters.user(OWNER_ID))
async def stop_handler(client: Client, message):
  chat_id=LOG_ID
  await client.send_message(chat_id, text="Userbot top  song.")
  await pytg.leave_call(message.chat.id, )

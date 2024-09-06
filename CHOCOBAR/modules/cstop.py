import os
from pyrogram import Client, filters
from pyrogram.types import Message
from pytgcalls import PyTgCalls
from config import OWNER_ID
from CHOCOBAR import app, pytg  # Assuming these are correctly imported


@app.on_message(filters.regex('ctop') & filters.user(OWNER_ID))
async def stop_handler(client: Client, message: Message):
    try:
        chat_id = int(message.text.split()[1])
    except IndexError:
        await message.reply_text("Invalid command format. Use `stop <chat_id>`.")
        return
    except ValueError:
        await message.reply_text("Invalid chat ID.")
        return

    try:
        await pytg.leave_call(chat_id)
        await message.reply_text("Userbot ctop song.")
    except Exception as e:
        await message.reply_text(f"Error stopping song: {e}")

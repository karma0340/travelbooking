import time
import random
from pyrogram import Client, filters
from CHOCOBAR import app, bot
from pyrogram.types import InlineKeyboardMarkup, InlineKeyboardButton


@bot.on_message(filters.command("start"))
async def start(client, msg):
    try:

        await msg.reply_text(
            "Welcome to my bot"
        )
    except Exception as e:
        await msg.reply_text(str(e))

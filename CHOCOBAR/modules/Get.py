import sqlite3
import asyncio
from pyrogram import filters, Client
from pyrogram.types import Message
from loguru import logger
from pydub import AudioSegment
from pydub.effects import low_pass_filter

from CHOCOBAR import bot, pytg, check_and_get_vars, get_variable

# Initialize loguru configuration
logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

# Initialize Pyrogram client
DATABASE_PATH = 'database.db'  # File-based database path
db_connection = sqlite3.connect(DATABASE_PATH)
db_cursor = db_connection.cursor()

# Create table in the file-based database
db_cursor.execute('''CREATE TABLE IF NOT EXISTS queue (
                        chat_id INTEGER,
                        file_path TEXT
                    )''')

@bot.on_message(filters.command("get"))
async def get_handler(client: Client, message: Message):
    try:
        # Count the number of rows in the queue table
        db_cursor.execute("SELECT COUNT(*) FROM queue")
        row_count = db_cursor.fetchone()[0]

        logger.debug(f"Total number of rows in the queue table: {row_count}")

        # Send the count to the user
        await message.reply_text(f"There are {row_count} rows in the queue table.")
    except sqlite3.Error as e:
        logger.error(f"Database error: {e}")
        await message.reply_text("An error occurred while accessing the database.")

@bot.on_message(filters.command("reload"))
async def reload_handler(client: Client, message: Message):
    try:
        # Delete all data from the queue table
        db_cursor.execute("DELETE FROM queue")
        db_connection.commit()

        logger.debug("All data deleted from the queue table.")

        # Send confirmation to the user
        await message.reply_text("All data has been deleted from the queue table.")
    except sqlite3.Error as e:
        logger.error(f"Database error: {e}")
        await message.reply_text("An error occurred while accessing the database.")


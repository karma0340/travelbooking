import os
import hashlib
import asyncio
from pyrogram import filters, Client
from pyrogram.types import Message, InlineKeyboardButton, InlineKeyboardMarkup, CallbackQuery
from loguru import logger
from CHOCOBAR.core.database import get_queue_data, db_cursor, db_connection  # Import the get_queue_data function
from CHOCOBAR import bot

# Initialize Pyrogram client
# Set up loguru configuration
logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

@bot.on_message(filters.command("queue"))
async def queue_handler(client: Client, message: Message):
    chat_id = message.chat.id
    try:
        rows = get_queue_data(chat_id)  # Use the function from database.py
    
        if not rows:
            await message.reply_text("The queue is empty.")
            return
        
        buttons = []
        for row in rows:
            file_path = row[0]
            # Hash the file path to create a short callback data
            callback_data = f"delete_{hashlib.md5(file_path.encode()).hexdigest()[:10]}"
            buttons.append([InlineKeyboardButton(f"Delete {os.path.basename(file_path)}", callback_data=callback_data)])

        reply_markup = InlineKeyboardMarkup(buttons)
        await message.reply_text("Current queue:", reply_markup=reply_markup)
    except Exception as e:
        logger.error(f"Error retrieving queue: {e}")
        await message.reply_text(f"Error retrieving queue: {e}")

@bot.on_callback_query(filters.regex(r"^delete_(.+)"))
async def delete_callback_handler(client: Client, callback_query: CallbackQuery):
    hashed_file_path = callback_query.data.split("_", 1)[1]
    chat_id = callback_query.message.chat.id
    
    try:
        # Find the matching file_path in the database
        rows = get_queue_data(chat_id)  # Use the function from database.py
        for row in rows:
            file_path = row[0]
            if hashlib.md5(file_path.encode()).hexdigest()[:10] == hashed_file_path:
                # Delete the matching file_path
                db_cursor.execute("DELETE FROM queue WHERE chat_id = ? AND file_path = ?", (chat_id, file_path))
                db_connection.commit()
                await callback_query.message.edit_text(f"Deleted {os.path.basename(file_path)} from queue")
                break
    except Exception as e:
        logger.error(f"Failed to delete from queue: {e}")
        await callback_query.message.edit_text(f"Error deleting file from queue")
    finally:
        await callback_query.answer()

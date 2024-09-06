from pyrogram import filters, Client
from pyrogram.types import Message
from CHOCOBAR import app
from loguru import logger

@app.on_message(filters.command("getcommon"))
async def get_common_chats(client: Client, message: Message):
    if len(message.command) < 2:
        await message.reply_text("Please provide a username or user ID.")
        return

    user_input = message.command[1]
    
    try:
        user_id = None

        if user_input.isdigit():
            user_id = int(user_input)
        else:
            user = await client.get_users(user_input)
            user_id = user.id

        if user_id is None:
            await message.reply_text("Could not resolve user ID.")
            return

        # Call the Telegram API method
        result = await client.get_common_chats(user_id=user_id)

        # Extract chat titles and IDs
        common_chats_list = [
            f"Result:\n〈 Title: {chat.title or 'No Title'} 〉\n【 Chat ID: {chat.id} 】" for chat in result
        ]

        if common_chats_list:
            await message.reply_text("\n\n".join(common_chats_list))
        else:
            await message.reply_text("No common chats found.")

    except Exception as e:
        # Log detailed error information for debugging
        logger.error(f"An error occurred: {e}")
        await message.reply_text(f"An error occurred: {e}")

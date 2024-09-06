from pyrogram import Client, filters
from pyrogram.raw.functions.channels import GetGroupsForDiscussion
from pyrogram.raw.base import InputPeer
from pyrogram.types import InlineKeyboardMarkup, InlineKeyboardButton
from loguru import logger
from CHOCOBAR import app  # Assuming this is your fetching client
from CHOCOBAR import bot  # Assuming this is your replying client

@app.on_message(filters.command("setting"))
async def setting_command_fetching(client, message):
    try:
        # Fetch the groups using GetGroupsForDiscussion
        result = await client.send(
            GetGroupsForDiscussion()
        )
        
        # Extract the groups
        groups = result.chats
        logger.debug(f"Fetched {len(groups)} groups")

        if groups:
            # Create inline keyboard buttons for each group
            buttons = []
            for group in groups:
                buttons.append([
                    InlineKeyboardButton(f"{group.title} (ID: {group.id})", callback_data=f"group_{group.id}")
                ])

            reply_markup = InlineKeyboardMarkup(buttons)

            # Send the reply using the 'bot' client
            await bot.send_message(
                chat_id=message.chat.id, 
                text="Select a group:", 
                reply_markup=reply_markup
            )
        else:
            await bot.send_message(
                chat_id=message.chat.id, 
                text="No groups found."
            )
            logger.warning("No groups found for the bot")

    except Exception as e:
        logger.error(f"An error occurred: {e}")
        await bot.send_message(
            chat_id=message.chat.id, 
            text="An error occurred while fetching groups."
        )

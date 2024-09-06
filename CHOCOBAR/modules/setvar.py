# set_get_vars.py

import os
from pyrogram import filters, Client
from pyrogram.types import Message
from loguru import logger
from CHOCOBAR import bot
from config import OWNER_ID
from config_vars import set_variable, get_variable

# Initialize loguru
logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

@bot.on_message(filters.command("setvar") & filters.user(OWNER_ID))
async def set_variable_command(client, message):
    try:
        logger.debug("Received /setvar command")
        
        command = message.text.split(" ", 1)
        logger.debug(f"Command parsed: {command}")
        
        if len(command) < 2:
            await message.reply_text("Usage: /setvar <variable_name:value>")
            logger.warning("Insufficient command arguments")
            return

        variable = command[1].split(":", 1)
        logger.debug(f"Variable parsed: {variable}")

        if len(variable) != 2:
            await message.reply_text("Usage: /setvar <variable_name:value>")
            logger.warning("Invalid variable format")
            return

        var_name, var_value = variable
        var_name = var_name.strip().upper()
        var_value = var_value.strip()
        logger.debug(f"Variable name: {var_name}, Variable value: {var_value}")

        set_variable(var_name, var_value)
        logger.debug(f"Updated {var_name} in memory: {var_value}")

        await message.reply_text(f"Updated {var_name} to {var_value}")
        logger.info(f"Successfully updated {var_name} to {var_value}")
    except Exception as e:
        logger.error(f"Error occurred: {e}")
        await message.reply_text(f"Error: {e}")

@bot.on_message(filters.command("getvar") & filters.user(OWNER_ID))
async def get_variable_command(client, message):
    try:
        logger.debug("Received /getvar command")
        
        command = message.text.split(" ", 1)
        logger.debug(f"Command parsed: {command}")
        
        if len(command) < 2:
            await message.reply_text("Usage: /getvar <variable_name>")
            logger.warning("Insufficient command arguments")
            return

        var_name = command[1].strip().upper()
        logger.debug(f"Variable name: {var_name}")

        var_value = get_variable(var_name)
        logger.debug(f"Retrieved {var_name} from memory: {var_value}")

        await message.reply_text(f"{var_name} = {var_value}")
        logger.info(f"Successfully retrieved {var_name} = {var_value}")
    except Exception as e:
        logger.error(f"Error occurred: {e}")
        await message.reply_text(f"Error: {e}")

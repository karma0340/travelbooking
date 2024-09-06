import asyncio
import time

from pyrogram import Client
from pyrogram.methods.utilities import idle
from pyrogram.types import Message
from pytgcalls import PyTgCalls, idle
import config 
# from CHOCOBAR.core.NGROK import NGROK
from config import LOG_ID
from config_vars import get_variable  # Import the get_variable function
from config  import OWNER_ID 
from pytgcalls import filters as fl
from pytgcalls import PyTgCalls
from pytgcalls.types import ChatUpdate
from pytgcalls.types import MediaStream
from pytgcalls.types import Update

loop = asyncio.get_event_loop()
boot = time.time()

app = Client(
    ":CHOCOBAR:",
    config.API_ID,
    config.API_HASH,
    session_string=config.SESSION,
)  


bot = Client(
    ":CHOCOBOT:",
    config.API_ID,
    config.API_HASH,
    bot_token=config.TOKEN,
)  



# XD_URL = None



# ngrok_instance = NGROK()

# print(f"Public URL: {ngrok_instance.APP_URL}")
# # Set global XD_URL
# XD_URL = ngrok_instance.APP_URL

async def check_and_get_vars(message: Message):
    chat_id = get_variable("CHAT_ID")
    sudo_user = get_variable("SUDO_USER")

    if chat_id == "Variable not found":
        await message.reply_text("CHAT_ID is not set.")
        return None, None

    if sudo_user == "Variable not found":
        await message.reply_text("SUDO_USER is not set.")
        return None, None

    # Convert sudo_user to a list of integers
    sudo_user_list = [int(user_id.strip()) for user_id in sudo_user.split(",")]

    if message.from_user.id not in sudo_user_list:
        await message.reply_text(text="You are not authorized to use this bot.")
        return None, None

    return chat_id, sudo_user_list


pytg = PyTgCalls(app)


test_stream = 'https://res.cloudinary.com/dydcwsbps/video/upload/v1721806158/hyqy1peydusbvimyywek.mp4'

@pytg.on_update(fl.chat_update(ChatUpdate.Status.INCOMING_CALL))
async def incoming_handler(client: PyTgCalls, update: Update):
    try:
        # Send a message to the user
        await pytg.mtproto_client.send_message(update.chat_id, 'You are calling me!')

        # Play the media stream
        await pytg.play(update.chat_id, MediaStream(test_stream))
    
    except Exception as e:
        # Handle exceptions
        print(f"An error occurred: {e}")
        await pytg.mtproto_client.send_message(update.chat_id, f"An error occurred: {e}")



async def initiate_bot():
  global app
  global bot
 
  await pytg.start()
  await bot.start()
loop.run_until_complete(initiate_bot())

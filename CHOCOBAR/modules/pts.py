import os
from gtts import gTTS
from CHOCOBAR import app, pytg
from pyrogram import Client, filters
from pyrogram.types import Message
from pytgcalls.types import MediaStream
from config import OWNER_ID
from CHOCOBAR import check_and_get_vars
from loguru import logger
from pydub import AudioSegment
from pydub.effects import low_pass_filter

@app.on_message(filters.regex('pts'))
async def playr(_, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return
    
    command = message.text.split()
    text = ' '.join(command[1:])  # Joining text after the command
    
    # Convert text to speech using gTTS
    tts = gTTS(text, lang='en', tld='co.in')
    tts.save('output.mp3')
    media = "output.mp3"
    
    try:
        audio = AudioSegment.from_file(media)
        
        # Adjusting volume and applying low-pass filter
        increased_volume_audio = audio + 20  # Adjust volume properly
        filtered_audio = low_pass_filter(increased_volume_audio, 9200)
        
        # Export filtered audio to ogg format
        file_path = 'filtered_output.ogg'  # Define your file path
        filtered_audio.export(file_path, format="ogg")
        
        logger.debug("Message filtered and volume increased")
        
    except Exception as e:
        logger.error(f"Failed to process message: {e}")
        await message.reply_text(f"Error processing message: {e}")
        return
    
    chat_id = message.chat.id
    
    # Play the speech in the group call
    await pytg.play(
        chat_id,
        MediaStream(file_path),
    )

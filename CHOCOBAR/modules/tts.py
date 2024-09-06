import os
from gtts import gTTS
from CHOCOBAR import app, pytg
from pyrogram import Client, filters
from config import OWNER_ID
@app.on_message(filters.regex('tts') & filters.user(OWNER_ID))
async def plaer(client, message):
    command = message.text.split()
    text = ' '.join(command[1:]) 
    tts = gTTS(text, lang='en', tld='co.in')
    tts.save('audio.mp3')
    async def progress(current, total):
     print(f"{current * 100 / total:.1f}%")
    chat_id=message.chat.id
    await client.send_audio(chat_id,"audio.mp3", progress=progress)

    os.remove('audio.mp3')

import os
from pyrogram import filters
from pyrogram.types import Message
from CHOCOBAR import app, pytg
from pyrogram import Client, filters
from pyrogram.types import Message
from pytgcalls.types import AudioQuality
from pytgcalls.types import VideoQuality
from pytgcalls import PyTgCalls, idle
from pytgcalls.types import MediaStream
from pytgcalls.exceptions import AlreadyJoinedError
from config import OWNER_ID
from pydub import AudioSegment
from pydub.effects import low_pass_filter
from config import OWNER_ID
from pyrogram import Client, filters
from pytgcalls import idle, PyTgCalls
from pytgcalls.types import AudioQuality, MediaStream, VideoQuality
import youtube_search
import logging
from pydub import AudioSegment
from pydub.effects import normalize
from pydub.playback import play

@app.on_message(filters.regex('play') & filters.user(OWNER_ID))
async def play_handler(_, message):
    replied_message = message.reply_to_message

    if not replied_message or not (replied_message.audio or replied_message.video):
        await message.reply_text("Reply to an audio message to play it.")
        return

    audio_file_path = None

    if replied_message.audio:
        audio_file_path = f"{replied_message.audio.file_id}.ogg"
    elif replied_message.video:
        audio_file_path = f"{replied_message.video.file_id}.ogg"

    # Download media file
    media = await app.download_media(replied_message)
    await message.reply_text("Userbot downloaded Media")

    # Load the audio file
    sound = AudioSegment.from_file(media)

    # Lower the volume
    softer_sound = sound - 10  # Decrease volume by 10 dB

    # Normalize to remove sharpness and improve quality
    normalized_sound = normalize(softer_sound)

    # Export the processed audio file
    normalized_sound.export(audio_file_path, format="ogg")

    # Play the audio
    await pytg.play(
        message.chat.id,
        MediaStream(audio_file_path, AudioQuality.HIGH, VideoQuality.HD_720p)
    )




@app.on_message(filters.regex('.pause') & filters.user(OWNER_ID))
async def pause_handler(_: Client, message: Message):
  await message.reply_text(text="Userbot  paused song")
  await pytg.pause_stream(message.chat.id, )


# @app.on_message(filters.regex('.stop'))
# async def stop_handler(_: Client, message: Message):
#   await message.reply_text(text="Userbot  stopped song.")
#   await pytg.leave_call(message.chat.id, )


@app.on_message(filters.regex('.resume')  & filters.user(OWNER_ID))
async def resume_handler(_: Client, message: Message):
  await message.reply_text(text="Userbot  resume song")
  await pytg.resume_stream(message.chat.id, )

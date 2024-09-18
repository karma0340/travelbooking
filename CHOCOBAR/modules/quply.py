import os
import asyncio
from pyrogram import filters, Client
from pyrogram.types import Message, InlineKeyboardButton, InlineKeyboardMarkup, CallbackQuery
from pytgcalls import PyTgCalls
from pytgcalls.types import MediaStream
from pytgcalls.exceptions import AlreadyJoinedError, NotInCallError
from loguru import logger
from pydub import AudioSegment
from pydub.effects import low_pass_filter
from pydub.playback import play
import subprocess
from pymongo import MongoClient
from CHOCOBAR import bot, pytg, check_and_get_vars, get_variable
from pytgcalls.media_devices import MediaDevices
from config import DATABASE_URI
# Initialize Pyrogram client
# Set up loguru configuration
logger.add("log.txt", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

# MongoDB setup
mongo_client = MongoClient(DATABASE_URI)
db = mongo_client['audio_queue_db']
queue_collection = db['queue']

# Event to signal that an audio file is being played
playing_event = asyncio.Event()
stop_event = asyncio.Event()  # Event to signal stopping playback

def change_pitch(audio: AudioSegment, octaves: float) -> AudioSegment:
    # Adjust the sample rate to change pitch (higher octaves = higher pitch)
    new_sample_rate = int(audio.frame_rate * (2.0 ** octaves))
    
    # Resample audio to the new sample rate
    pitched_audio = audio._spawn(audio.raw_data, overrides={'frame_rate': new_sample_rate})
    
    # Set the sample rate back to the original to maintain the duration
    return pitched_audio.set_frame_rate(audio.frame_rate)

def increase_bass(audio: AudioSegment, gain_dB: int) -> AudioSegment:
    # Apply low-pass filter to enhance bass frequencies
    bass_enhanced = audio.low_pass_filter(8000)
    # Amplify the bass-enhanced audio
    return bass_enhanced + gain_dB

def amplify_audio(audio: AudioSegment, gain_dB: int) -> AudioSegment:
    # Amplify the entire audio
    return audio + gain_dB

@bot.on_message(~filters.private & (filters.voice | filters.audio))
async def play_handler(client: Client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    try:
        if message.voice:
            file_id = message.voice.file_id
            file_format = "ogg"
            logger.debug(f"Voice message file ID: {file_id}")
        elif message.audio:
            file_id = message.audio.file_id
            file_format = message.audio.mime_type.split('/')[-1]
            logger.debug(f"Audio message file ID: {file_id}, Format: {file_format}")
        else:
            raise ValueError("No voice or audio message found in the incoming message")

        file_path = f"{file_id}.{file_format}"

        logger.debug(f"Downloading message: {file_path}")
        try:
            downloaded_file_path = await client.download_media(message)
            logger.debug(f"Message downloaded: {downloaded_file_path}")
        except Exception as e:
            logger.error(f"Failed to download message: {e}")
            await message.reply_text(f"Error downloading message: {e}")
            return

        try:
            # Process with pydub
            audio = AudioSegment.from_file(downloaded_file_path)
            vol = int(get_variable("VOLUME_NO"))  # Convert volume to integer
            bass_gain = int(get_variable("BASS"))  # Increase bass gain in dB
            pitch_shift = float(get_variable("PITCH"))  # Shift pitch in octaves

            amplified_audio = amplify_audio(audio, vol)
            bass_audio = increase_bass(amplified_audio, bass_gain)
            pitched_audio = change_pitch(bass_audio, pitch_shift)

            final_file_path = f"final_{file_path}"
            pitched_audio.export(final_file_path, format="ogg")

            logger.debug("Message processed with bass, volume, and pitch adjusted")
        except Exception as e:
            logger.error(f"Failed to process message: {e}")
            await message.reply_text(f"Error processing message: {e}")
            return

        # Add file path to database queue
        queue_collection.insert_one({'chat_id': chat_id, 'file_path': final_file_path})
        logger.debug(f"Added to queue: {final_file_path}")

        # If no other audio is being played, start playing immediately
        if not playing_event.is_set():
            playing_event.set()
            stop_event.clear()  # Clear stop event before starting playback
            asyncio.create_task(play_from_queue())

    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        await message.reply_text(f"Unexpected error: {e}")

async def play_from_queue():
    while True:
        if stop_event.is_set():
            break
        item = queue_collection.find_one_and_delete({}, sort=[('_id', 1)])
        if item is None:
            break
        chat_id, file_path = item['chat_id'], item['file_path']
        try:
            logger.debug(f"Joining group call in chat: {chat_id}")
            await pytg.play(chat_id)
            await pytg.play(
                chat_id,
                MediaStream(file_path),
            )
            # Wait until the audio finishes playing
            await asyncio.sleep(AudioSegment.from_file(file_path).duration_seconds)

        except AlreadyJoinedError:
            logger.warning(f"Already joined error in chat: {chat_id}, leaving and rejoining")
            await pytg.leave_call(chat_id)  # Leave the call
            await pytg.play(chat_id)
            await pytg.play(
                chat_id,
                MediaStream(file_path),
            )
        except NotInCallError:
            logger.warning(f"Not in call error in chat: {chat_id}, joining and retrying")
            await pytg.play(chat_id)
            await pytg.play(
                chat_id,
                MediaStream(file_path),
            )
        except Exception as e:
            logger.error(f"Exception occurred: {e}")
            await pytg.leave_call(chat_id)  # Leave the call
            await pytg.play(chat_id)
            await pytg.play(
                chat_id,
                MediaStream(file_path),
            )

    playing_event.clear()

@bot.on_message(filters.command("queue"))
async def queue_handler(client: Client, message: Message):
    chat_id = message.chat.id
    items = queue_collection.find({'chat_id': chat_id})
    
    if items.count() == 0:
        await message.reply_text("The queue is empty.")
        return
    
    buttons = [
        [InlineKeyboardButton(f"Delete {item['file_path']}", callback_data=f"delete_{item['file_path']}")]
        for item in items
    ]
    
    reply_markup = InlineKeyboardMarkup(buttons)
    await message.reply_text("Current queue:", reply_markup=reply_markup)

@bot.on_callback_query(filters.regex(r"^delete_(.+)"))
async def delete_callback_handler(client: Client, callback_query: CallbackQuery):
    file_path = callback_query.data.split("_", 1)[1]
    chat_id = callback_query.message.chat.id
    
    queue_collection.delete_one({'chat_id': chat_id, 'file_path': file_path})
    
    await callback_query.message.edit_text(f"Deleted {file_path} from queue")
    await callback_query.answer()

@bot.on_message(filters.command("stop"))
async def stop_handler(client: Client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    queue_collection.delete_many({'chat_id': chat_id})
    
    stop_event.set()  # Signal to stop playback

    await pytg.leave_call(chat_id)  # Leave the call
    await message.reply_text("Stopped all audio and cleared the queue.")

@bot.on_message(filters.command("clear"))
async def clear_handler(client: Client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    queue_collection.delete_many({})
    
    await message.reply_text("Cleared all data from the queue.")
    

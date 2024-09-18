import tempfile
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
# Set up logger configuration
logger.add("log.txt", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

# MongoDB setup
mongo_client = MongoClient(DATABASE_URI)
db = mongo_client['audio_queue_db']
queue_collection = db['queue']

# Event to signal that an audio file is being played
playing_event = asyncio.Event()
stop_event = asyncio.Event()  # Event to signal stopping playback

# Constants for chunk size
CHUNK_SIZE_MS = 10000  # 10 seconds per chunk

def change_pitch(audio: AudioSegment, octaves: float) -> AudioSegment:
    new_sample_rate = int(audio.frame_rate * (2.0 ** octaves))
    pitched_audio = audio._spawn(audio.raw_data, overrides={'frame_rate': new_sample_rate})
    return pitched_audio.set_frame_rate(audio.frame_rate)

def increase_bass(audio: AudioSegment, gain_dB: int) -> AudioSegment:
    bass_enhanced = audio.low_pass_filter(8000)
    return bass_enhanced + gain_dB

def amplify_audio(audio: AudioSegment, gain_dB: int) -> AudioSegment:
    return audio + gain_dB

async def process_audio_chunk(chunk: AudioSegment, volume: int, bass_gain: int, pitch_shift: float):
    """Process a single audio chunk and store it in a temporary file."""
    amplified_chunk = amplify_audio(chunk, volume)
    bass_chunk = increase_bass(amplified_chunk, bass_gain)
    pitched_chunk = change_pitch(bass_chunk, pitch_shift)

    # Create a temporary file
    with tempfile.NamedTemporaryFile(suffix=".ogg", delete=False) as temp_file:
        temp_file_path = temp_file.name
        pitched_chunk.export(temp_file_path, format="ogg")

    return temp_file_path  # Return the path to the temporary file

@bot.on_message(~filters.private & (filters.voice | filters.audio))
async def play_handler(client: Client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    try:
        file_format = "ogg" if message.voice else message.audio.mime_type.split('/')[-1]
        logger.debug(f"Processing audio message: {file_format}")

        downloaded_file_path = await client.download_media(message)
        audio = AudioSegment.from_file(downloaded_file_path)
        vol = int(get_variable("VOLUME_NO"))
        bass_gain = int(get_variable("BASS"))
        pitch_shift = float(get_variable("PITCH"))

        # Split the audio into chunks
        chunks = [audio[i:i + CHUNK_SIZE_MS] for i in range(0, len(audio), CHUNK_SIZE_MS)]

        for chunk in chunks:
            temp_file_path = await process_audio_chunk(chunk, vol, bass_gain, pitch_shift)

            # Add the chunk file to the database queue
            queue_collection.insert_one({'chat_id': chat_id, 'file_path': temp_file_path})
            logger.debug(f"Added chunk to queue: {temp_file_path}")

        if not playing_event.is_set():
            playing_event.set()
            stop_event.clear()  # Clear stop event before starting playback
            asyncio.create_task(play_from_queue())

    except Exception as e:
        logger.error(f"Error processing message: {e}")
        await message.reply_text(f"Error: {e}")

async def play_from_queue():
    while True:
        if stop_event.is_set():
            break

        item = queue_collection.find_one_and_delete({}, sort=[('_id', 1)])
        if not item:
            break

        chat_id, file_path = item['chat_id'], item['file_path']
        try:
            logger.debug(f"Playing audio chunk: {file_path}")
            await pytg.play(chat_id)
            await pytg.play(chat_id, MediaStream(file_path))

            # Wait for the duration of the audio chunk
            chunk_duration = AudioSegment.from_file(file_path).duration_seconds
            await asyncio.sleep(chunk_duration)

        except (AlreadyJoinedError, NotInCallError) as e:
            logger.warning(f"Call error in chat: {chat_id}, retrying")
            await pytg.leave_call(chat_id)  # Leave the call
            await pytg.play(chat_id)
            await pytg.play(chat_id, MediaStream(file_path))
        except Exception as e:
            logger.error(f"Playback error: {e}")

        finally:
            # Delete the temp file after playing
            if os.path.exists(file_path):
                os.remove(file_path)
                logger.debug(f"Deleted temporary file: {file_path}")

    playing_event.clear()

@bot.on_message(filters.command("stop"))
async def stop_handler(client: Client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    queue_collection.delete_many({'chat_id': chat_id})
    stop_event.set()
    await pytg.leave_call(chat_id)
    await message.reply_text("Playback stopped and queue cleared.")

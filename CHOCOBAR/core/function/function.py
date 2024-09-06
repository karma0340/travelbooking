import aiosqlite  # For asynchronous SQLite operations
from pytgcalls.exceptions import AlreadyJoinedError, NotInCallError
from pytgcalls.types import MediaStream
from pydub import AudioSegment
from loguru import logger
from CHOCOBAR import pytg
import asyncio

# Event to signal that an audio file is being played
playing_event = asyncio.Event()
stop_event = asyncio.Event()  # Event to signal stopping playback

# Ensure the same database connection is used across modules
from CHOCOBAR.core.play_db import setup_database

async def get_db_connection():
    return await setup_database()

async def play_from_queue():
    db_connection = await get_db_connection()
    async with db_connection.cursor() as db_cursor:
        while True:
            if stop_event.is_set():
                break
            
            # Fetch the next item from the queue
            await db_cursor.execute("SELECT chat_id, file_path FROM queue ORDER BY ROWID LIMIT 1")
            row = await db_cursor.fetchone()
            if row is None:
                break

            chat_id, file_path = row

            try:
                logger.debug(f"Joining group call in chat: {chat_id}")
                await pytg.play(chat_id)
                media_stream = MediaStream(file_path)
                await pytg.play(chat_id, media_stream)

                # Calculate the duration of the audio file once
                audio_duration = AudioSegment.from_file(file_path).duration_seconds

                # Wait until the audio finishes playing
                await asyncio.sleep(audio_duration)

                # Remove played item from the queue
                await db_cursor.execute("DELETE FROM queue WHERE chat_id = ? AND file_path = ?", (chat_id, file_path))
                await db_connection.commit()
                
            except AlreadyJoinedError:
                logger.warning(f"Already joined error in chat: {chat_id}, leaving and rejoining")
                await pytg.leave_call(chat_id)  # Leave the call
                await pytg.play(chat_id)
                media_stream = MediaStream(file_path)
                await pytg.play(chat_id, media_stream)

            except NotInCallError:
                logger.warning(f"Not in call error in chat: {chat_id}, joining and retrying")
                await pytg.play(chat_id)
                media_stream = MediaStream(file_path)
                await pytg.play(chat_id, media_stream)

            except Exception as e:
                logger.error(f"Exception occurred: {e}")
                await pytg.leave_call(chat_id)  # Leave the call
                await pytg.play(chat_id)
                media_stream = MediaStream(file_path)
                await pytg.play(chat_id, media_stream)

    playing_event.clear()
    await db_connection.close()  # Close the database connection

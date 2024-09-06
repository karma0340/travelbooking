import time
from pyrogram import filters, Client
from pyrogram.types import Message
from pyrogram.errors import FloodWait
from CHOCOBAR import app

@app.on_message(filters.command("save_media") & filters.user("your_username"))
async def save_media(client: Client, message: Message):
    if len(message.command) < 4:
        await message.reply_text("Usage: /save_media <target_channel_id> <destination_channel_id> <media_types>")
        return
    
    target_channel_id = message.command[1]
    destination_channel_id = message.command[2]
    media_types = message.command[3:]  # List of media types to forward

    valid_media_types = {"video", "audio", "photo", "document", "voice"}

    for media_type in media_types:
        if media_type not in valid_media_types:
            await message.reply_text(f"Invalid media type: {media_type}. Valid types are: {', '.join(valid_media_types)}")
            return

    try:
        async for msg in client.get_chat_history(target_channel_id, reverse=True):
            try:
                if "video" in media_types and msg.video:
                    await msg.copy(destination_channel_id)
                elif "audio" in media_types and msg.audio:
                    await msg.copy(destination_channel_id)
                elif "photo" in media_types and msg.photo:
                    await msg.copy(destination_channel_id)
                elif "document" in media_types and msg.document:
                    await msg.copy(destination_channel_id)
                elif "voice" in media_types and msg.voice:
                    await msg.copy(destination_channel_id)
                time.sleep(2)  # Additional sleep to avoid hitting the rate limit again
            except FloodWait as e:
                await message.reply_text(f"FloodWait: Sleeping for {e.x} seconds.")
                time.sleep(e.x)  # Sleep for the required duration
                # Retry the last message that caused FloodWait
                if "video" in media_types and msg.video:
                    await msg.copy(destination_channel_id)
                elif "audio" in media_types and msg.audio:
                    await msg.copy(destination_channel_id)
                elif "photo" in media_types and msg.photo:
                    await msg.copy(destination_channel_id)
                elif "document" in media_types and msg.document:
                    await msg.copy(destination_channel_id)
                elif "voice" in media_types and msg.voice:
                    await msg.copy(destination_channel_id)
        await message.reply_text("All specified media types have been forwarded.")
    except Exception as e:
        await message.reply_text(f"An error occurred: {e}")

if __name__ == "__main__":
    app.run()

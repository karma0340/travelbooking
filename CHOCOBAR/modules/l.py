# import os
# from pyrogram import filters, Client
# from pyrogram.types import Message
# from pyrogram.errors import FloodWait
# from loguru import logger
# from CHOCOBAR import app, bot
# import asyncio
# from pyrogram.raw.functions.stories import GetPinnedStories
# from pyrogram.raw.types import InputPeerUser

# logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

# @bot.on_message(filters.private & filters.text)
# async def get_stories(client: Client, message: Message):
#     input_text = message.text.strip()

#     try:

#         if input_text.isdigit():
#             chat_id = int(input_text)
#             peer = InputPeerUser(user_id=chat_id, access_hash=0) 
#         else:
#             peer = await app.resolve_peer(input_text)


#         result = await app.send(GetPinnedStories(peer=peer, offset_id=0, limit=10))
#         stories = result.stories

#         for story in stories:
#             story_id = story.id 
#             logger.debug(f"Fetched pinned story with ID: {story_id}")
#             await message.reply_text(f"Pinned Story ID: {story_id}")

#             if story.media:
#                 try:

#                     story_media_path = await app.download_media(story)
#                     if story_media_path:
#                         logger.debug(f"Downloaded story media to: {story_media_path}")
#                         await message.reply_document(story_media_path)
#                     else:
#                         logger.debug(f"No media to download for story ID: {story_id}")
#                 except Exception as download_error:
#                     logger.error(f"An error occurred while downloading story media: {download_error}")
#             else:
#                 logger.debug(f"No media found for story ID: {story_id}")

#         logger.info("Finished fetching pinned stories.")
#     except FloodWait as e:
#         logger.warning(f"FloodWait: Need to wait for {e.x} seconds.")
#         await asyncio.sleep(e.x)
#         await message.reply_text(f"Rate limit exceeded. Please wait for {e.x} seconds and try again.")
#     except Exception as e:
#         logger.error(f"An error occurred: {e}")
#         await message.reply_text(f"An error occurred while fetching pinned stories: {e}")






# # # import os
# # # from pyrogram import filters, Client
# # # from pyrogram.types import Message
# # # from loguru import logger
# # # from CHOCOBAR import app,bot

# # # # Set up loguru configuration
# # # logger.add("debug.log", format="{time} {level} {message}", level="DEBUG", rotation="1 MB")

# # # @bot.on_message(filters.private & filters.text)
# # # async def get_stories(client: Client, message: Message):
# # #     input_text = message.text.strip()

# # #     try:
# # #         # Determine if the input is a username or a user ID
# # #         if input_text.isdigit():
# # #             chat_id = int(input_text)
# # #         else:
# # #             chat_id = input_text

# # #         async for story in app.get_peer_stories(chat_id):
# # #             story_id = story.id  # Story ID attribute
# # #             logger.debug(f"Fetched story with ID: {story_id}")
# # #             await message.reply_text(f"Story ID: {story_id}")

# # #             # Download the story content using the story object
# # #             story_media = await app.download_media(story)
# # #             if story_media:
# # #                 logger.debug(f"Downloaded story media: {story_media}")
# # #                 await message.reply_document(story_media)

# # #         logger.info("Finished fetching stories.")
# # #     except Exception as e:
# # #         logger.error(f"An error occurred: {e}")
# # #         await message.reply_text(f"An error occurred while fetching stories: {e}")


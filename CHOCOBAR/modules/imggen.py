import requests
import base64
from pyrogram import Client, filters
from CHOCOBAR import app, pytg
import argparse

api_url = "https://dev.promptchan.ai/api/external/create"
headers = {"x-api-key": "IObLxpNyPV81CD4Mcj48hA"}

async def create_image(prompt, style=None, quality=None, image_size=None):
    payload = {
        "style": style or "Real",
        "poses": "Default",
        "filter": "Default",
        "detail": 0,
        "prompt": prompt,
        "seed": 1000,
        "quality": quality or "Ultra",
        "creativity": 0,
        "image_size": image_size or "512x512",
        "negative_prompt": "string",
        "restore_faces": True
    }
    response = requests.post(api_url, headers=headers, json=payload)
    if response.status_code == 200:
        data = response.json()
        img_base64 = data.get("image", "")
        img_binary = base64.b64decode(img_base64)
        return img_binary
    else:
        raise Exception(f"Error {response.status_code}: {response.text}")

def main():
    parser = argparse.ArgumentParser(description="Generate an image from a prompt.")
    parser.add_argument("prompt", help="The prompt for the image.")
    parser.add_argument("--style", help="The style of the image.")
    parser.add_argument("--quality", help="The quality of the image.")
    parser.add_argument("--image_size", help="The size of the image.")
    args = parser.parse_args()

    async def handle_message(_, message):
        if message.text.startswith("/img "):
            try:
                image_binary = await create_image(message.text.replace("/img ", ""))
                await message.reply_photo(photo=image_binary)
            except Exception as e:
                await message.reply_text(f"Error: {str(e)}")

    app.on_message(handle_message)


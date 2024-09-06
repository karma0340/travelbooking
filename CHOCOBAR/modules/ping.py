from datetime import datetime
from pyrogram import filters
from pyrogram.types import Message
import platform, socket, re, uuid, json, psutil, logging
from CHOCOBAR import app, check_and_get_vars

img = "https://picjumbo.com/download?d=beautiful-nature-mountain-scenery-with-flowers-free-photo.jpg&n=beautiful-nature-mountain-scenery-with-flowers&id=1"


def getSystemInfo():
    try:
        info = {}
        info['platform'] = platform.system()
        info['platform-release'] = platform.release()
        info['platform-version'] = platform.version()
        info['architecture'] = platform.machine()
        info['hostname'] = socket.gethostname()
        info['ip-address'] = socket.gethostbyname(socket.gethostname())
        info['mac-address'] = ':'.join(re.findall('..', '%012x' % uuid.getnode()))
        info['processor'] = platform.processor()
        info['ram'] = str(round(psutil.virtual_memory().total / (1024.0 ** 3))) + " GB"
        return info
    except Exception as e:
        logging.exception(e)

@app.on_message(filters.command("ping"))
async def ping_com(client, message: Message):
    chat_id, sudo_user_list = await check_and_get_vars(message)
    if not chat_id:
        return 

    sysinfo = getSystemInfo()
    SINFO = "\n".join([f"{key}: {value}" for key, value in sysinfo.items()])
    response = await message.reply_photo(
        photo=img,
        caption=SINFO
    )

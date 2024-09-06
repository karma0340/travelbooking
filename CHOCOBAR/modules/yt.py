import os
import aiohttp
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
import subprocess
@app.on_message(filters.regex('^/yt'))
async def yt_handler(_, message):
    url = "https://rr1---sn-gwpa-na8l.googlevideo.com/videoplayback?expire=1723661639&ei=56i8ZtSgG8Ol9fwPhZCW-QI&ip=34.47.139.75&id=o-AFIUuV7okBOsKgxI9wn_csiM_4x_RWYk9MrxFCELWz23&itag=18&source=youtube&requiressl=yes&xpc=EgVo2aDSNQ%3D%3D&bui=AQmm2ey86nso8yUjTBhjeWdoS1_ebcx4qh-Svd13j3WqIN8UEeZOOudelPp6phbVRz30kVJ1CN6qrban&spc=Mv1m9oRR7ajtXXmu9nA_QkiWvHaJpa8Tm9M20qPWjb8OeUgcHIsX4tI&vprv=1&svpuc=1&mime=video%2Fmp4&ns=kzyQREVhRzTOyifNPI2PyIEQ&rqh=1&gir=yes&clen=7155363&ratebypass=yes&dur=143.034&lmt=1723582132045824&c=WEB_CREATOR&sefc=1&txp=5538434&n=dSK4-DAb8athWw&sparams=expire%2Cei%2Cip%2Cid%2Citag%2Csource%2Crequiressl%2Cxpc%2Cbui%2Cspc%2Cvprv%2Csvpuc%2Cmime%2Cns%2Crqh%2Cgir%2Cclen%2Cratebypass%2Cdur%2Clmt&sig=AJfQdSswRQIgIJ5Vgbszaf3idXzKwD9BqjnSBsVjgPqsgOjurFshzowCIQCa0fej1mf6vrNPzbzJkvKjqeM-VT0Y32vyRFdvhkZo-Q%3D%3D&redirect_counter=1&rm=sn-cvhzy7s&rrc=104&fexp=24350516,24350518,24350557,24350561&req_id=885836d22f41a3ee&cms_redirect=yes&cmsv=e&ipbypass=yes&mh=j7&mip=2409:40d7:f:359a:de71:9ed9:99ee:8781&mm=31&mn=sn-gwpa-na8l&ms=au&mt=1723642634&mv=m&mvi=1&pl=42&lsparams=ipbypass,mh,mip,mm,mn,ms,mv,mvi,pl&lsig=AGtxev0wRQIhAPAokuYS8epgY0uyDR_epoKNKTwIH5l2UEXNzfUaTAi6AiBgJ0vkpCj5qDTAs4aYTvkVbpqBavtcJTlvUaB7aXwoBA%3D%3D"

    # Define file paths
    input_audio_path = "input_audio.mp4"
    output_audio_path = "output_audio.ogg"

    # Download and convert the video to audio using ffmpeg
    cmd = [
        'ffmpeg',
        '-i', url,
        '-vn',  # Disable video
        '-acodec', 'libvorbis',  # Audio codec for OGG format
        '-ar', '44100',  # Set audio sample rate
        '-ac', '2',  # Set number of audio channels
        output_audio_path
    ]
    subprocess.run(cmd, check=True)

    # Play the processed audio
    await pytg.play(
        message.chat.id,
        MediaStream(output_audio_path, AudioQuality.HIGH, VideoQuality.HD_720p)
    )

    await message.reply_text("Playing the audio.")

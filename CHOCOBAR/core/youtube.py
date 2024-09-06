import os
import yt_dlp
from loguru import logger
from requests_tor import RequestsTor

# Configuration
TOR_PORT = 9050
TOR_CONTROL_PORT = 9051
DOWNLOAD_DIR = 'downloads/'

def download_audio(url, cookie_file):
    logger.info("Starting the audio download.")
    
    # Set up RequestsTor session
    rt = RequestsTor(tor_ports=(TOR_PORT,), tor_cport=TOR_CONTROL_PORT)

    # Configure yt-dlp to use the Tor proxy
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': os.path.join(DOWNLOAD_DIR, '%(title)s.%(ext)s'),
        'cookiefile': cookie_file,
        'proxy': f'socks5://localhost:{TOR_PORT}',
    }

    try:
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            logger.info(f"Downloading audio from URL: {url}")
            ydl.download([url])
            logger.info("Download completed successfully.")
    except Exception as e:
        logger.error(f"An error occurred: {e}")

if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description='Download audio from YouTube.')
    parser.add_argument('url', type=str, help='The YouTube video URL.')
    parser.add_argument('cookie_file', type=str, help='The path to the cookie file.')

    args = parser.parse_args()
    
    # Create the download directory if it doesn't exist
    if not os.path.exists(DOWNLOAD_DIR):
        os.makedirs(DOWNLOAD_DIR)

    download_audio(args.url, args.cookie_file)

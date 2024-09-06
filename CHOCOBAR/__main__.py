import asyncio
import importlib
import threading
from flask import Flask, render_template, request, redirect, url_for, session,Response
from flask_session import Session
from pyrogram import idle
from CHOCOBAR.modules import ALL_MODULES
from config_vars import set_variable, get_variable
from config import SECRET_KEY
from loguru import logger
import time
from typing import Generator

import time
loop = asyncio.get_event_loop()

# Initialize variables
set_variable("SUDO_USER", "6621610889")
set_variable("CHAT_ID", "-1002180171885")
set_variable("VOLUME_NO", "95")
set_variable("BASS", "20")
set_variable("PITCH", "0.1")

# -1002180171885
# -1002111995244 dns
# Initialize Flask app
pan = Flask(__name__)
pan.secret_key = SECRET_KEY

users = {'admin': '123'}

pan.config['SESSION_TYPE'] = 'filesystem'
Session(pan)


@pan.route('/', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        if username in users and users[username] == password:
            session['user'] = username
            logger.info(f"User {username} logged in")
            return redirect(url_for('dash'))
        else:
            return "Invalid credentials", 401

    return render_template('index.html')

@pan.route('/dash', methods=['GET', 'POST'])
def dash():
    if 'user' not in session:
        return redirect(url_for('login'))
    
    if request.method == 'POST':
        sudo_users = request.form.get('sudo_users', '').strip()
        target = request.form.get('target', '').strip()
        volume = request.form.get('volume', '').strip()
        bass = request.form.get('bass', '').strip()
        pitch= request.form.get('pitch', '').strip()
        set_variable('SUDO_USER', sudo_users)
        set_variable('CHAT_ID', target)
        set_variable('VOLUME_NO', volume)
        set_variable('BASS', bass)
        set_variable('PITCH', pitch)
        sudo_users = get_variable('SUDO_USER')
        target = get_variable('CHAT_ID')
        volume = get_variable('VOLUME_NO')
        bass = get_variable('BASS')
        pitch = get_variable('PITCH')
        logger.info(f"Updated sudo_users: {sudo_users}, target: {target}, volume: {volume},bass: {bass}, pitch: {pitch}")
    else:
        sudo_users = get_variable('SUDO_USER')
        target = get_variable('CHAT_ID')
        volume = get_variable('VOLUME_NO')
        bass = get_variable('BASS')
        pitch = get_variable('PITCH')
        logger.info("Fetched sudo_users and target from config")

    return render_template('main.html', sudo_users=sudo_users, target=target, volume=volume,bass=bass,pitch=pitch )

@pan.errorhandler(404)
def not_found_error(error):
    logger.error(f"404 Error: {error}")
    return "Page not found", 404

@pan.errorhandler(500)
def internal_error(error):
    logger.error(f"500 Error: {error}")
    return "Internal server error", 500



def generate_logs(log_file: str = "log.txt"):
    try:
        with open(log_file, "r") as f:
            logs = f.readlines()  # Read all lines from the file
            logs.reverse()  # Reverse the order of lines to show latest logs first
            return "".join(logs)  # Return the reversed logs as a string
    except FileNotFoundError:
        return "Log file not found."



@pan.route('/logs')
def logs():
    log_content = generate_logs(log_file="log.txt")
    return Response(log_content, mimetype='text/plain')

def run_flask():
    try:
        pan.run(host='0.0.0.0', port=5000, use_reloader=False, debug=True)
    except Exception as e:
        print(f"Flask failed to start: {e}")

async def initiate_bot():
    for all_module in ALL_MODULES:
        importlib.import_module("CHOCOBAR.modules." + all_module)
        print(f"LOADING {all_module} ...")
    print("Started")
    await idle()

def mai():
    # Run Flask in a separate thread
    flask_thread = threading.Thread(target=run_flask)
    flask_thread.start()
    
    # Start the bot
    loop.run_until_complete(initiate_bot())
    
    print("Stopping Bot! Goodbye")

if __name__ == "__main__":
    mai()
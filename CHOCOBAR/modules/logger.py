# from flask import Flask, render_template, Response
# import logging
# import time

# app = Flask(__name__)

# # Set up the logger
# logging.basicConfig(
#     level=logging.INFO,
#     format="[%(asctime)s - %(levelname)s] - %(name)s - %(message)s",
#     datefmt="%d-%b-%y %H:%M:%S",
#     handlers=[
#         logging.FileHandler("log.txt"),
#         logging.StreamHandler(),
#     ],
# )

# # Limit logs from certain libraries
# logging.getLogger("flask").setLevel(logging.ERROR)
# logging.getLogger("pydub").setLevel(logging.ERROR)
# logging.getLogger("pyrofork").setLevel(logging.ERROR)
# logging.getLogger("pytgcalls").setLevel(logging.ERROR)
# logging.getLogger("httpx").setLevel(logging.ERROR)
# logging.getLogger("pymongo").setLevel(logging.ERROR)
# logging.getLogger("aiosqlite").setLevel(logging.ERROR)

# def LOGGER(name: str) -> logging.Logger:
#     return logging.getLogger(name)

# logger = LOGGER(__name__)

# @app.route('/')
# def index():
#     return render_template('index.html')

# def generate_logs():
#     # A generator function to stream logs to the client
#     while True:
#         time.sleep(1)
#         with open("log.txt", "r") as f:
#             logs = f.read()
#         yield f"data: {logs}\n\n"

# @app.route('/logs')
# def logs():
#     # Stream logs to the client
#     return Response(generate_logs(), mimetype='text/event-stream')

# if __name__ == "__main__":
#     app.run(debug=True)

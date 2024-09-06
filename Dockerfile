# Use an official Python runtime as a parent image
FROM python:3.10-slim

# Set the working directory in the container
WORKDIR /app

# Copy the requirements file into the container
COPY requirements.txt ./

# Install any needed packages specified in requirements.txt
RUN pip install --no-cache-dir -r requirements.txt

# Update package lists and install dependencies
RUN apt-get update && \
    apt-get install -y \
    ffmpeg \
    espeak \
    tor \
    && rm -rf /var/lib/apt/lists/*

# Copy the current directory contents into the container at /app
COPY . .

# Expose Tor port (9050 for SOCKS proxy, 9051 for control port)
EXPOSE 9050
EXPOSE 9051

# Create a Tor configuration file
RUN echo "SocksPort 0.0.0.0:9050\nControlPort 9051\n" > /etc/tor/torrc

# Run Tor and your application
CMD ["sh", "-c", "tor & python3 main.py"]

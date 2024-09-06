import sqlite3

# Create a shared in-memory database connection
db_connection = sqlite3.connect('memory.db', check_same_thread=False)
db_cursor = db_connection.cursor()

# Create the queue table
db_cursor.execute('''CREATE TABLE IF NOT EXISTS queue (
                        chat_id INTEGER,
                        file_path TEXT
                    )''')

# Function to get all data from the queue table
def get_all_data():
    db_cursor.execute('SELECT * FROM queue')
    data = db_cursor.fetchall()  # Retrieve all rows from the query result
    return data

# Function to get data from the queue table for a specific chat_id
def get_queue_data(chat_id):
    try:
        db_cursor.execute("SELECT file_path FROM queue WHERE chat_id = ?", (chat_id,))
        return db_cursor.fetchall()
    except Exception as e:
        print(f"Error retrieving queue data: {e}")
        return []

# Example usage:
# all_data = get_all_data()
# print(all_data)



import aiosqlite

async def setup_database():
    db_connection = await aiosqlite.connect('memory.db')
    async with db_connection.cursor() as db_cursor:
        await db_cursor.execute('''CREATE TABLE IF NOT EXISTS queue (
                                    chat_id INTEGER,
                                    file_path TEXT
                                )''')
        await db_connection.commit()
    return db_connection

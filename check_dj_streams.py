import mysql.connector

conn = mysql.connector.connect(
    user="root",
    password="Skylinehosting171",
    database="radiohosting",
    host="15.204.114.226"
)

cursor = conn.cursor()
cursor.execute("SELECT dj_id, GROUP_CONCAT(stream_id) FROM radio_dj_streams GROUP BY dj_id")

for row in cursor.fetchall():
    print(f"DJ {row[0]}: streams {row[1]}")

conn.close()
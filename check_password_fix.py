sshpass -p 'Skylinehosting171' ssh -o StrictHostKeyChecking=no -o PreferredAuthentications=password -o PubkeyAuthentication=no debian@15.204.114.226 "python3 << 'PYTHON'
import mysql.connector

conn = mysql.connector.connect(user='root', password='Skylinehosting171', database='radiohosting')
cursor = conn.cursor()

# Check current radio_djs passwords
cursor.execute('SELECT id, username, stream_id, password FROM radio_djs WHERE status = \"active\"')
dj_rows = cursor.fetchall()
print(f"Found {len(dj_rows)} active DJs")

# Check all radio_streams
cursor.execute('SELECT id, stream_id, server_name, port FROM radio_streams ORDER BY id')
streams = cursor.fetchall()
print(f"\\nAll radio_streams:")
for stream_id, rs_id, server_name, port in streams:
    print(f"  db id={stream_id}, rs_id={rs_id}, name={server_name}, port={port}")

# Find all unique stream_id values in radio_djs
for dj_id, username, stream_id, password in dj_rows:
    print(f"DJ: {username}, stream_id: {stream_id}, current password: {password[:20] if password else 'NULL'}...")

conn.close()
PYTHON"
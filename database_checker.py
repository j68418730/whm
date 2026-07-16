#!/usr/bin/env python3
import mysql.connector

# Database connection
conn = mysql.connector.connect(
    host="15.204.114.226",
    user="root",
    password="Skylinehosting171",
    database="radiohosting"
)

try:
    cursor = conn.cursor()
    
    print("=== Database Structure Check ===\n")
    
    # List all radio-related tables
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    
    print("Radio-related tables:")
    for table in tables:
        if 'radio' in table[0].lower() or 'dj' in table[0].lower():
            print(f"  - {table[0]}")
    
    print("\n=== Checking radio_dj_streams Junction Table ===")
    try:
        cursor.execute("DESCRIBE radio_dj_streams")
        columns = cursor.fetchall()
        
        if columns:
            print("✓ radio_dj_streams table exists:")
            for col in columns:
                print(f"  {col[0]} ({col[1]})")
        else:
            print("❌ radio_dj_streams table does NOT exist")
            
    except Exception as e:
        print(f"❌ radio_dj_streams table does not exist: {e}")
    
    print("\n=== Checking radio_djs Table ===")
    cursor.execute("DESCRIBE radio_djs")
    radio_djs_columns = cursor.fetchall()
    print("radio_djs columns:")
    for col in radio_djs_columns:
        print(f"  {col[0]} ({col[1]})")
    
    print("\n=== Checking radio_streams Table ===")
    cursor.execute("DESCRIBE radio_streams")
    radio_streams_columns = cursor.fetchall()
    print("radio_streams columns:")
    for col in radio_streams_columns:
        print(f"  {col[0]} ({col[1]})")
    
    # Check current data
    print("\n=== Current radio_djs Data ===")
    cursor.execute("SELECT id, username, stream_id, password FROM radio_djs")
    djs = cursor.fetchall()
    
    for dj in djs:
        print(f"ID: {dj[0]}, Username: {dj[1]}, Stream ID: {dj[2]}")
        
    print("\n=== Current radio_streams Data ===")
    cursor.execute("SELECT id, port, server_name, status FROM radio_streams")
    streams = cursor.fetchall()
    
    for stream in streams:
        print(f"ID: {stream[0]}, Port: {stream[1]}, Server: {stream[2]}, Status: {stream[3]}")
        
    # Check if junction table exists
    try:
        cursor.execute("SELECT * FROM radio_dj_streams LIMIT 1")
        junction_data = cursor.fetchone()
        
        if junction_data:
            print(f"\n=== radio_dj_streams Junction Table Data ===")
            print(f"Sample entry: DJ ID: {junction_data[0]}, Stream ID: {junction_data[1]}")
            print(f"assigned_by: {junction_data[2]}")
        else:
            print(f"\n=== radio_dj_streams Junction Table ===")
            print("Table exists but no data")
            
    except Exception as e:
        print(f"\n=== radio_dj_streams Junction Table ===")
        print(f"Table does not exist: {e}")

except Exception as e:
    print(f"Error: {e}")
    
finally:
    conn.close()

print("\n=== Summary ===")
print("The radio_dj_streams junction table is required for the multi-station DJ feature.")
print("Please run the migration script to create it if it doesn't exist.")
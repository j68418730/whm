Properties of radio_djs table:
- id (primary key, auto-increment)
- username (unique, not null)
- password (not null) - PHP password_hash format
- name (nullable)
- email (nullable)
- status (enum: 'active','inactive','banned', default: 'active')

Properties of radio_streams table:
- id (primary key, auto-increment)
- user_id (foreign key to hosting_users)
- server_type (like 'icecast', 'shoutcast')
- port (int, unique)
- server_name (varchar)
- mount_point (varchar, default: '/live')
- password (varchar, plain)
- config_path (varchar)
- status (enum: 'running', 'stopped', etc.)

Properties of radio_dj_streams table (JUNCTION TABLE):
- dj_id (foreign key to radio_djs)
- stream_id (foreign key to radio_streams)
- assigned_by (user ID)
- PRIMARY KEY (dj_id, stream_id)

This is a classic many-to-many relationship where:
- One DJ can have access to multiple stations
- One station can have multiple DJs

Migration to add the junction table is needed!
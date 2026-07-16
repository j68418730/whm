with open("/var/www/radiohosting/public/dj_panel.php", "r") as f:
    content = f.read()

# Fix the login query
old = """SELECT d.*, rs.port, s.status as stream_status, rs.current_dj, s.autodj_active as autodj_active,
        (SELECT COUNT(*) FROM radio_listener_analytics WHERE stream_id = d.stream_id AND date = CURDATE()) as today_listeners
        FROM radio_djs d 
        JOIN streaming_stations s ON d.stream_id = s.id
        JOIN radio_streams rs ON d.stream_id = rs.id
        WHERE d.username = ? AND d.status = 'active'"""

new = """SELECT d.*, rs.port, s.status as stream_status, s.current_dj, s.autodj_enabled as autodj_active,
        (SELECT COUNT(*) FROM radio_listener_analytics WHERE stream_id = d.stream_id AND date = CURDATE()) as today_listeners
        FROM radio_djs d 
        JOIN streaming_stations s ON d.stream_id = s.id
        JOIN radio_streams rs ON d.stream_id = rs.id
        WHERE d.username = ? AND d.status = 'active'"""

if old in content:
    content = content.replace(old, new)
    with open("/var/www/radiohosting/public/dj_panel.php", "w") as f:
        f.write(content)
    print("Fixed login query")
else:
    print("Old query not found")
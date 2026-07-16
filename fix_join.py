import re
with open("/var/www/radiohosting/public/dj_panel.php", "r") as f:
    content = f.read()
content = content.replace("JOIN radio_streams s ON d.stream_id = s.id", "JOIN streaming_stations s ON d.stream_id = s.id")
with open("/var/www/radiohosting/public/dj_panel.php", "w") as f:
    f.write(content)
print('done')
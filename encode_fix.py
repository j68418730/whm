import base64
code = """with open("/var/www/radiohosting/public/dj_panel.php", "r") as f:
    content = f.read()
old = "SELECT d.*, rs.port, s.status as stream_status, rs.current_dj, s.autodj_active as autodj_active,"
new = "SELECT d.*, rs.port, s.status as stream_status, s.current_dj, s.autodj_enabled as autodj_active,"
if old in content:
    content = content.replace(old, new)
    with open("/var/www/radiohosting/public/dj_panel.php", "w") as f:
        f.write(content)
    print("Fixed login query")
else:
    print("Old query not found")"""
print(base64.b64encode(code.encode()).decode())
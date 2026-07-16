import base64
code = """with open("/var/www/radiohosting/public/dj_panel.php", "r") as f:
    content = f.read()
content = content.replace("rs.current_dj, s.autodj_active as autodj_active", "s.current_dj, s.autodj_enabled as autodj_active")
with open("/var/www/radiohosting/public/dj_panel.php", "w") as f:
    f.write(content)
print("Fixed")"""
print(base64.b64encode(code.encode()).decode())
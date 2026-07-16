with open("/var/www/radiohosting/public/dj_panel.php", "r") as f:
    content = f.read()

# Fix the ini_set line
content = content.replace("ini_set( display_errors, 1);", 'ini_set("display_errors", 1);')

with open("/var/www/radiohosting/public/dj_panel.php", "w") as f:
    f.write(content)

print("Fixed")
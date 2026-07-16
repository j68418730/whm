with open('/var/www/radiohosting/public/dj_panel.php', 'r') as f:
    lines = f.readlines()
for i, line in enumerate(lines):
    if 'ini_set' in line and 'display_errors' in line:
        lines[i] = 'error_reporting(E_ALL); ini_set("display_errors", 1);\n'
        break
with open('/var/www/radiohosting/public/dj_panel.php', 'w') as f:
    f.writelines(lines)
print('Fixed')
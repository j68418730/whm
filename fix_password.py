#!/usr/bin/env python3
import subprocess
import base64

# SQL to update the password
sql = """UPDATE radio_djs SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'testing';"""

# Escape dollar signs for SSH command
sql_escaped = sql.replace('$', '\\$')

# Create the remote command
remote_cmd = f"""
cat > /tmp/fixpw.sql << 'EOF'
UPDATE radio_djs SET password = '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'testing';
EOF
mysql -u root -pSkylinehosting171 radiohosting < /tmp/fixpw.sql
"""

# Execute the remote command
result = subprocess.run(
    ["sshpass", "-p", "Skylinehosting171", "ssh", "-o", "StrictHostKeyChecking=no", "-o", "PreferredAuthentications=password", "-o", "PubkeyAuthentication=no", "debian@15.204.114.226", "bash"],
    input=remote_cmd,
    text=True,
    capture_output=True,
    timeout=30
)

print("STDOUT:", result.stdout)
print("STDERR:", result.stderr)
print("Return code:", result.returncode)
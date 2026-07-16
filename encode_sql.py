import base64
sql = "UPDATE radio_djs SET password = '$2y$10$l0GwNp7LpV.ykIubyh5nXtYu4c6DSpuBfTF3cU0r8xMrOxLD8G' WHERE username = 'testing';"
print(base64.b64encode(sql.encode()).decode())
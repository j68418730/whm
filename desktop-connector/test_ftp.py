import ftplib
try:
    ftp = ftplib.FTP()
    ftp.connect("localhost", 21, timeout=5)
    ftp.login("planethosts", "Skylinehosting171")
    print("FTP LOGIN OK")
    from io import BytesIO
    buf = BytesIO(b"hello")
    ftp.storbinary("STOR /public_html/ftptest.txt", buf)
    print("FTP UPLOAD OK")
    ftp.delete("/public_html/ftptest.txt")
    print("FTP DELETE OK")
    ftp.quit()
    print("FTP FULLY WORKING")
except Exception as e:
    print("FTP FAILED: " + str(e))

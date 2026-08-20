# Planet Host Game Node Agent — optional code signing
#
# SmartScreen/'Unknown publisher' warnings are removed only by signing with a
# code-signing certificate that chains to a public CA:
#   - OV cert  ≈ $100–150/yr (e.g. Certum, Comodo/Sectigo, SSL.com)
#   - EV cert  ≈ $300/yr   (best — instant SmartScreen reputation)
#
# A self-signed certificate does NOT remove the warning; it only makes the
# file "signed by an unknown publisher" instead of "no signature", so this
# script is mainly useful for checking hashes/CI signatures.
#
# Usage (run as admin, on a machine with the Windows SDK signtool.exe):
#   .\codesign.ps1 -CertificatePath C:\certs\planet-host.pfx -Password "..."
# Or with a self-signed cert for testing:
#   .\codesign.ps1 -SelfSigned
param(
    [string]$CertificatePath = "",
    [string]$Password = "",
    [switch]$SelfSigned
)
$ErrorActionPreference = 'Stop'
$win = Split-Path -Parent $MyInvocation.MyCommand.Path

$signtool = Get-ChildItem "C:\Program Files (x86)\Windows Kits\10\bin" -Recurse -Filter signtool.exe -ErrorAction SilentlyContinue |
    Sort-Object FullName | Select-Object -Last 1
if (-not $signtool) { $signtool = Get-Command signtool -ErrorAction SilentlyContinue }
if (-not $signtool) { throw "signtool.exe not found (install Windows SDK or add signtool to PATH)." }
$signtool = $signtool.Source

$files = @(
    (Join-Path $win 'ph-agent-installer.exe'),
    (Join-Path $win 'ph-agent-tray.exe'),
    (Join-Path $win 'ph-agent.exe')
)

$cert = $CertificatePath
if ($SelfSigned) {
    $thumb = (New-SelfSignedCertificate -Type CodeSigningCert -Subject "CN=Planet Host" -CertStoreLocation Cert:\CurrentUser\My).Thumbprint
    $files | ForEach-Object {
        & $signtool sign /fd SHA256 /sha1 $thumb /d "Planet Host Game Node Agent" /du "https://planet-hosts.com" /n $thumb $_
        Write-Host "Signed (self-signed): $_"
    }
    Write-Host "WARNING: self-signed certificates keep the 'Unknown publisher' warning. Use a real OV/EV cert."
    return
}
if (-not $cert) { throw "Provide -CertificatePath/-Password, or use -SelfSigned." }
if ($Password) {
    $files | ForEach-Object { & $signtool sign /fd SHA256 /f $cert /p $Password /d "Planet Host Game Node Agent" /du "https://planet-hosts.com" /t "http://timestamp.digicert.com" $_ }
}
else {
    $files | ForEach-Object { & $signtool sign /fd SHA256 /f $cert /d "Planet Host Game Node Agent" /du "https://planet-hosts.com" /t "http://timestamp.digicert.com" $_ }
}
$files | ForEach-Object { & $signtool verify /pa /v $_ | Select-String -Pattern "Verified" }
Write-Host "Done. Rebuild ph-agent-windows.zip after signing."
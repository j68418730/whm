# Builds the Planet Host Game Node Agent Windows binaries from source.
# Requires .NET Framework 4.x csc (built into Windows).
# Run:  pwsh -File build.ps1
$ErrorActionPreference = 'Stop'
$win = Split-Path -Parent $MyInvocation.MyCommand.Path
$csc = "C:\Windows\Microsoft.NET\Framework64\v4.0.30319\csc.exe"
if (-not (Test-Path $csc)) { throw "csc.exe not found at $csc" }

$agent = Join-Path $win 'ph-agent.exe'
if (-not (Test-Path $agent)) { throw "Missing ph-agent.exe (pkg bundle of ../../agent/agent.js) next to this script." }

# Patch the agent exe to GUI subsystem so no console window ever flashes
Write-Host 'Patching ph-agent.exe to GUI subsystem...'
& "$win\patch-gui.ps1" -ExePath $agent

Push-Location $win
try {
    Write-Host 'Building ph-agent-tray.exe ...'
    & $csc /nologo /t:winexe /out:$PWD\ph-agent-tray.exe /main:TrayManager.Program `
        /r:System.Windows.Forms.dll /r:System.Drawing.dll `
        /r:System.ServiceProcess.dll /r:Microsoft.CSharp.dll /r:System.Management.dll `
        TrayManager.cs
    if ($LASTEXITCODE -ne 0) { throw 'TrayManager build failed' }

    Write-Host 'Building ph-agent-installer.exe ...'
    & $csc /nologo /t:winexe /out:$PWD\ph-agent-installer.exe /main:Installer.Program `
        /win32manifest:installer.manifest `
        /resource:$PWD\ph-agent.exe,embedded_agent_exe `
        /resource:$PWD\ph-agent-tray.exe,embedded_tray_exe `
        /r:System.Windows.Forms.dll /r:System.Drawing.dll `
        Installer.cs
    if ($LASTEXITCODE -ne 0) { throw 'Installer build failed' }

    Write-Host 'Done. Built:' -ForegroundColor Green
    Get-Item $PWD\ph-agent-tray.exe, $PWD\ph-agent-installer.exe | Select-Object Name, Length, LastWriteTime
}
finally {
    Pop-Location
}
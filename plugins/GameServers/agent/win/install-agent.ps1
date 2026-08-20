# Planet Hosts Game Node Agent — Windows Installer
# Run with:  Right-click -> Run with PowerShell  (or:  powershell -ExecutionPolicy Bypass -File install-agent.ps1)
# Installs ph-agent.exe as a service-like task that auto-starts on boot.

$ErrorActionPreference = 'Stop'
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$exe  = Join-Path $here 'ph-agent.exe'
$instDir = Join-Path $env:ProgramFiles 'PlanetHostsAgent'

Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "  Planet Hosts Game Node Agent Installer" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan

if (-not (Test-Path $exe)) { Write-Host "ph-agent.exe not found next to this script." -ForegroundColor Red; exit 1 }
if (-not (Get-Command node -ErrorAction SilentlyContinue)) { Write-Host "Note: Node.js is bundled in ph-agent.exe (no install required)." -ForegroundColor Yellow }

# ---- Collect config ----
$panel   = Read-Host "Panel URL (e.g. https://planet-hosts.com)"
if ([string]::IsNullOrWhiteSpace($panel)) { $panel = 'https://planet-hosts.com' }
$token   = Read-Host "Node token (from Admin -> Games -> Nodes)"
if ([string]::IsNullOrWhiteSpace($token)) { Write-Host "Token is required." -ForegroundColor Red; exit 1 }
$instDirPrompt = "Where to install the agent (default: $env:ProgramFiles\PlanetHostsAgent)"
$instDir = Read-Host $instDirPrompt
if ([string]::IsNullOrWhiteSpace($instDir)) { $instDir = Join-Path $env:ProgramFiles 'PlanetHostsAgent' }
$instDir = [System.IO.Path]::GetFullPath($instDir)
$base    = Read-Host "Where to install games (default: C:\PlanetHostsGames)"
if ([string]::IsNullOrWhiteSpace($base)) { $base = 'C:\PlanetHostsGames' }
$base    = [System.IO.Path]::GetFullPath($base)

# ---- Install files ----
Write-Host "Installing to $instDir ..."
New-Item -ItemType Directory -Force -Path $instDir | Out-Null
Copy-Item $exe (Join-Path $instDir 'ph-agent.exe') -Force
$workDir = $instDir

$config = @{
  panel_url        = $panel
  node_token       = $token
  base_dir         = $base
  poll_interval_ms = 10000
  steamcmd         = 'steamcmd.exe'
  steam_user       = 'anonymous'
  steam_pass       = ''
} | ConvertTo-Json
[System.IO.File]::WriteAllText((Join-Path $instDir 'agent-config.json'), $config)

# ---- Register scheduled task (auto-start at boot as SYSTEM) ----
$taskName = 'PlanetHostsAgent'
if (Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue) {
  Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}
$action  = New-ScheduledTaskAction -Execute (Join-Path $workDir 'ph-agent.exe') -WorkingDirectory $workDir
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId 'SYSTEM' -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Description 'Planet Hosts Game Node Agent' -Force | Out-Null

Write-Host ""
Write-Host "Starting agent ..." -ForegroundColor Cyan
Start-ScheduledTask -TaskName $taskName
Start-Sleep -Seconds 2

Write-Host "Done. The agent is installed and running." -ForegroundColor Green
Write-Host "Config:  $instDir\agent-config.json"
Write-Host "To view/edit config later, edit that file and restart the task:"
Write-Host "  Restart-ScheduledTask -TaskName PlanetHostsAgent" -ForegroundColor Yellow
Write-Host "Press Enter to close."
Read-Host

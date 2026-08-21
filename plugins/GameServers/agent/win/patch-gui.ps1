# Patches a pkg-built .exe from CONSOLE (subsystem 3) to GUI (subsystem 2)
# so no black console window ever flashes when launched by service/task.
param([string]$ExePath = "ph-agent.exe")
$ErrorActionPreference = 'Stop'
if (-not (Test-Path $ExePath)) { throw "File not found: $ExePath" }
$bytes = [IO.File]::ReadAllBytes($ExePath)
# Find PE signature (at offset 0x3C)
$peOffset = [BitConverter]::ToUInt32($bytes, 0x3C)
if ($peOffset -ge $bytes.Length - 4) { throw "Invalid PE offset" }
if ($bytes[$peOffset] -ne 0x50 -or $bytes[$peOffset+1] -ne 0x45 -or $bytes[$peOffset+2] -ne 0 -or $bytes[$peOffset+3] -ne 0) {
    throw "PE signature not found at 0x$($peOffset.ToString('X'))"
}
# COFF header (20 bytes): Machine(2) NumberOfSections(2) TimeDateStamp(4) PointerToSymbolTable(4) NumberOfSymbols(4) SizeOfOptionalHeader(2) Characteristics(2)
$coff = $peOffset + 4
$machine = [BitConverter]::ToUInt16($bytes, $coff)
if ($machine -ne 0x8664) { throw "Not x64 (machine=0x$($machine.ToString('X4')))" }
$optHeaderSize = [BitConverter]::ToUInt16($bytes, $coff + 16)
# Optional header starts after COFF (20 bytes)
$optStart = $coff + 20
# For PE32+, Subsystem is at offset 0x44 (68) from OptionalHeader start
# Magic (2) at optStart; if 0x20b => PE32+
$magic = [BitConverter]::ToUInt16($bytes, $optStart)
if ($magic -ne 0x20b) { throw "Not PE32+ (magic=0x$($magic.ToString('X4')))" }
$subsysOffset = $optStart + 0x44
$old = [BitConverter]::ToUInt16($bytes, $subsysOffset)
if ($old -ne 3) { Write-Host ('Already GUI (subsystem=' + $old + ') — nothing to do'); return }
[BitConverter]::GetBytes([uint16]2).CopyTo($bytes, $subsysOffset)
[IO.File]::WriteAllBytes($ExePath, $bytes)
Write-Host ('Patched ' + $ExePath + ': subsystem 3 (CONSOLE) to 2 (GUI)')
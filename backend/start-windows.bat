@echo off
rem Double-click to set up (first time) or start the GTech admin panel on this computer.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup-local.ps1"
pause

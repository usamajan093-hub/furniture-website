@echo off
setlocal
cd /d "%~dp0"
title amroyalinteriors.com - local admin

set "PHP="
for %%I in (php.exe) do if not "%%~$PATH:I"=="" set "PHP=%%~$PATH:I"
if not defined PHP if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" set "PHP=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not defined PHP (
  echo PHP was not found. Install it with:  winget install PHP.PHP.8.3
  pause
  exit /b 1
)

echo.
echo  Admin panel:  http://localhost:8090/admin/
echo  Website:      http://localhost:8090/
echo.
echo  Keep this window open while you work. Close it to stop.
echo  When you are done editing, run publish.cmd to update the live site.
echo.
start "" "http://localhost:8090/admin/"
"%PHP%" -S localhost:8090 -t "%CD%"

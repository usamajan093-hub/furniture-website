@echo off
setlocal
cd /d "%~dp0"
title amroyalinteriors.com - publish to GitHub Pages

set "PHP="
for %%I in (php.exe) do if not "%%~$PATH:I"=="" set "PHP=%%~$PATH:I"
if not defined PHP if exist "%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" set "PHP=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
if not defined PHP (
  echo PHP was not found. Install it with:  winget install PHP.PHP.8.3
  pause
  exit /b 1
)

echo Rendering index.html from the admin content...
"%PHP%" -r "require 'cms/bootstrap.php'; file_put_contents('index.html', render_site(['base'=>'']));"
if errorlevel 1 (
  echo Rendering failed. Nothing was published.
  pause
  exit /b 1
)

git add -A
git diff --cached --quiet
if not errorlevel 1 (
  echo Nothing has changed since the last publish.
  pause
  exit /b 0
)

git commit -q -m "Publish site content (%date% %time%)"
git push origin main
if errorlevel 1 (
  echo.
  echo Push failed. Check the internet connection or GitHub sign-in, then run publish.cmd again.
  pause
  exit /b 1
)

echo.
echo Published. GitHub Pages updates within about a minute:
echo https://usamajan093-hub.github.io/furniture-website/
echo.
pause

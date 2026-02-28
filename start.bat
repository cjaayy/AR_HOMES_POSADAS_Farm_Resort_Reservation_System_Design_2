@echo off
title AR Homes Posadas - Local + Cloudflare
color 0A

echo.
echo  ============================================
echo   AR Homes Posadas Farm Resort
echo   Reservation System
echo  ============================================
echo.

:: ---- Step 1: Start Apache ----
echo  [1/4] Starting Apache...
start "" /B "C:\xampp\apache\bin\httpd.exe" >nul 2>&1
echo        Done.

:: ---- Step 2: Start MySQL ----
echo  [2/4] Starting MySQL...
start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone >nul 2>&1
echo        Done.

:: Wait for services to fully start
timeout /t 3 /nobreak >nul

:: ---- Step 3: Open Browser ----
echo  [3/4] Opening browser...
start "" "http://localhost/"
start "" "https://arhomesposadas.cjaayy.dev/"
echo        Local:  http://localhost/
echo        Cloud:  https://arhomesposadas.cjaayy.dev/
echo.

:: ---- Step 4: Start Cloudflare Tunnel ----
echo  [4/4] Starting Cloudflare Tunnel (ar-homes)...
echo.
echo  ============================================
echo   LOCAL:  http://localhost/
echo   CLOUD:  https://arhomesposadas.cjaayy.dev/
echo  ============================================
echo.
echo   Press Ctrl+C to stop everything.
echo.

cloudflared tunnel --config "%USERPROFILE%\.cloudflared\config.yml" run ar-homes

:: ---- Cleanup on exit ----
echo.
echo  Stopping services...
call "C:\xampp\apache_stop.bat" >nul 2>&1
call "C:\xampp\mysql_stop.bat" >nul 2>&1
echo  All services stopped. Goodbye!
timeout /t 2 /nobreak >nul

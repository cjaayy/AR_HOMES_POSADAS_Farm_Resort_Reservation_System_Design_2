@echo off
echo Starting AR Homes Posadas Farm Resort Reservation System...
echo.

REM Check if ngrok is already running
tasklist /FI "IMAGENAME eq ngrok.exe" 2>NUL | find /I /N "ngrok.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Ngrok is already running
) else (
    echo [*] Starting ngrok tunnel...
    start "Ngrok Tunnel" /MIN "C:\Users\mjhay\Downloads\ngrok.exe" http 80
    timeout /t 3 /nobreak >nul
    echo [OK] Ngrok started
)

echo.
echo Opening system in browser...
REM Open the system using ngrok URL (works from anywhere)
start "" "https://sally-interimperial-pura.ngrok-free.dev/AR_Homes_Posadas_Farm_Resort_Reservation_System_Design_2/index.html"

echo.
echo System is running!
echo Ngrok URL: https://sally-interimperial-pura.ngrok-free.dev
echo.
echo Press any key to exit...
pause >nul

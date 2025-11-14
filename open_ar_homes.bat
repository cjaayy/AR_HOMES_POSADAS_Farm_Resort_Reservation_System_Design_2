@echo off
REM Start ngrok tunnel
echo Starting ngrok tunnel...
start "ngrok" cmd /k "ngrok http 80"

REM Wait for ngrok to start
timeout /t 5 /nobreak >nul

REM Open the AR Homes Posadas Farm Resort Reservation System in the default browser
echo Opening AR Homes system...
start "" "https://sally-interimperial-pura.ngrok-free.dev/AR_Homes_Posadas_Farm_Resort_Reservation_System_Design_2/index.html"

echo.
echo System started!
echo - Ngrok URL: https://sally-interimperial-pura.ngrok-free.dev/AR_Homes_Posadas_Farm_Resort_Reservation_System_Design_2/
echo - Check ngrok terminal for current public URL if different
echo.

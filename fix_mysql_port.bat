@echo off
echo ============================================
echo XAMPP MySQL Port Conflict Fixer
echo ============================================
echo.
echo Checking for processes using port 3306...
echo.

for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3306 ^| findstr LISTENING') do (
    set PID=%%a
    goto :found
)

echo No process found using port 3306.
echo MySQL should start normally now.
goto :end

:found
echo Found process using port 3306 - PID: %PID%
echo.
tasklist | findstr %PID%
echo.
echo Terminating process...
taskkill /F /PID %PID%
echo.
echo Port 3306 is now free. You can start MySQL in XAMPP.

:end
echo.
pause

@echo off
setlocal EnableExtensions

title Likha PH - Activate App

:: ---------------------------------------------------------------------------
:: Configuration - change these if your setup differs
:: ---------------------------------------------------------------------------
if not defined XAMPP_HOME set "XAMPP_HOME=C:\xampp"

:: URL where Apache serves this Laravel app (must point to the "public" folder
:: or a vhost that maps to it). Edit if you use a custom host or path.
set "APP_URL=http://localhost/likha/guihulngan-handicrafts/public"

:: Project folder (this script's directory)
set "ROOT=%~dp0"
if "%ROOT:~-1%"=="\" set "ROOT=%ROOT:~0,-1%"

:: ---------------------------------------------------------------------------
:: Start XAMPP: Apache (PHP) + MySQL
:: Skips Apache start if port 80 is already in use (e.g. Apache already running,
:: or IIS / another app bound to :80) - avoids error 10048 / AH00072.
:: ---------------------------------------------------------------------------
echo.
echo [%TIME%] Starting XAMPP services...
echo.
echo NOTE: If a second window opens with German/English text, it is XAMPP's reminder:
echo       Do NOT close that window while Apache is running - only close it when you
echo       intentionally stop the web server. This script continues in THIS window.
echo.

set "SKIP_APACHE=0"
set "SKIP_APACHE_REASON="

:: Is Apache (httpd) from XAMPP already running?
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if not errorlevel 1 (
    set "SKIP_APACHE=1"
    set "SKIP_APACHE_REASON=Apache is already running (httpd.exe)."
)

:: Port 80 already listening? (another web server, or Apache without matching process name)
if "%SKIP_APACHE%"=="0" (
    powershell -NoProfile -Command "$c = Get-NetTCPConnection -LocalPort 80 -State Listen -ErrorAction SilentlyContinue; if ($c) { exit 1 } else { exit 0 }" >nul 2>&1
    if errorlevel 1 (
        set "SKIP_APACHE=1"
        set "SKIP_APACHE_REASON=Port 80 is already in use (another program is listening)."
    )
)

if "%SKIP_APACHE%"=="1" (
    echo [SKIP] Apache start - %SKIP_APACHE_REASON%
    echo        The site may already be available. Try opening %APP_URL% in your browser.
    echo        If it does not load, stop whatever is using port 80 ^(e.g. IIS: optionalfeatures or services.msc^)
    echo        or change Apache Listen to 8080 in XAMPP httpd.conf and use http://localhost:8080/...
    echo.
) else if exist "%XAMPP_HOME%\apache_start.bat" (
    :: Use START (not CALL) - CALL would block forever because httpd runs in the foreground
    echo Starting Apache in a separate window...
    start "XAMPP Apache" "%XAMPP_HOME%\apache_start.bat"
) else (
    echo [WARN] Not found: "%XAMPP_HOME%\apache_start.bat"
    echo        Set XAMPP_HOME or install XAMPP at C:\xampp
)

set "SKIP_MYSQL=0"
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if not errorlevel 1 set "SKIP_MYSQL=1"

if "%SKIP_MYSQL%"=="1" (
    echo [SKIP] MySQL start - mysqld.exe is already running.
    echo.
) else if exist "%XAMPP_HOME%\mysql_start.bat" (
    echo Starting MySQL in a separate window...
    start "XAMPP MySQL" "%XAMPP_HOME%\mysql_start.bat"
) else (
    echo [WARN] Not found: "%XAMPP_HOME%\mysql_start.bat"
)

:: Wait for Apache/MySQL to accept connections (separate windows started above)
timeout /t 4 /nobreak >nul

cd /d "%ROOT%"

:: ---------------------------------------------------------------------------
:: Laravel: ensure app key exists (first-time / cloned repo)
:: ---------------------------------------------------------------------------
set "PHP_EXE=php"
where php >nul 2>&1
if errorlevel 1 if exist "%XAMPP_HOME%\php\php.exe" set "PHP_EXE=%XAMPP_HOME%\php\php.exe"

if exist "artisan" (
    if not exist ".env" (
        if exist ".env.example" (
            echo Creating .env from .env.example ...
            copy /Y ".env.example" ".env" >nul
        )
    )
    "%PHP_EXE%" artisan key:generate >nul 2>&1
    if errorlevel 1 (
        echo [WARN] Could not run artisan key:generate. Add PHP to PATH or fix .env.
    )
)

:: ---------------------------------------------------------------------------
:: Frontend: Vite dev server (hot reload) - new window
:: ---------------------------------------------------------------------------
where node >nul 2>&1
if not errorlevel 1 (
    if exist "package.json" (
        if not exist "node_modules\" (
            echo.
            echo Installing npm dependencies (first run, may take a minute^)...
            call npm install
            if errorlevel 1 echo [WARN] npm install failed. Fix errors above.
        )
        echo.
        echo Starting Vite dev server in a separate window...
        start "Vite - Likha PH" cmd /k "cd /d ""%ROOT%"" && npm run dev"
        timeout /t 3 /nobreak >nul
    )
) else (
    echo.
    echo [WARN] Node.js not in PATH. Install Node or add it to PATH.
    echo        If you already ran "npm run build", the site may still load without Vite.
)

:: ---------------------------------------------------------------------------
:: Open the application in your default browser
:: Use a child CMD process: "start" from inside .bat sometimes does not open
:: the default browser; "cmd /c start" with an empty window title usually does.
:: ---------------------------------------------------------------------------
echo.
echo Opening your default web browser...

cmd.exe /c start "" "%APP_URL%"

echo.
echo If nothing opened, paste this into your browser address bar:
echo   %APP_URL%
echo.

echo.
echo ---------------------------------------------------------------------------
echo  Apache/MySQL: started only if not already running ^(see messages above^).
echo  Vite: second window - keep it open while you work.
echo  URL: edit APP_URL at the top if the page does not load.
echo ---------------------------------------------------------------------------
echo.
pause
endlocal

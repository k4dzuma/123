@echo off
set LOGFILE=debug_report.txt
echo ======================================== > %LOGFILE%
echo        MUSEUM SERVER DEBUG REPORT        >> %LOGFILE%
echo ======================================== >> %LOGFILE%
echo Date: %DATE% %TIME% >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [1] NETWORK INTERFACES --- >> %LOGFILE%
ipconfig | findstr "IPv4" >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [2] ACTIVE LISTENERS (80, 3000, 8080) --- >> %LOGFILE%
netstat -ano | findstr "LISTENING" | findstr ":80 :3000 :8080" >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [3] FIREWALL RULES (80, 3000, 8080) --- >> %LOGFILE%
netsh advfirewall firewall show rule name=all | findstr "80 3000 8080" >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [4] PUBLIC IP CHECK --- >> %LOGFILE%
curl -s ifconfig.me >> %LOGFILE%
echo. >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [5] LOCALHOST ACCESS TEST --- >> %LOGFILE%
curl -I -s http://localhost >> %LOGFILE%

echo. >> %LOGFILE%
echo --- [6] ENVIRONMENT CONFIG (.env) --- >> %LOGFILE%
if exist .env (
    type .env | findstr /V "PASSWORD SECRET KEY" >> %LOGFILE%
) else (
    echo [.env file is MISSING!] >> %LOGFILE%
)

echo. >> %LOGFILE%
echo --- [7] PROCESSES ON PORTS --- >> %LOGFILE%
powershell -Command "Get-NetTCPConnection -LocalPort 80,3000,8080 -ErrorAction SilentlyContinue | Select-Object LocalPort, OwningProcess, @{Name='ProcessName';Expression={(Get-Process -Id $_.OwningProcess).Name}}" >> %LOGFILE%

echo ======================================== >> %LOGFILE%
echo Debug report saved to %LOGFILE%
echo Please copy the contents of %LOGFILE% and send it to me.
pause

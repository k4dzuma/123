@echo off
chcp 65001 >nul
title Музей "Человек и Время" - Запуск сервера

echo ==========================================
echo   Виртуальный музей "Человек и Время"
echo ==========================================
echo.

set PHP_PATH=%~dp0php\php.exe

if not exist "%PHP_PATH%" (
    echo [!] PHP не найден в папке php\
    echo Скачайте PHP и распакуйте в папку museum\php\
    pause
    exit /b 1
)

echo [OK] PHP найден: %PHP_PATH%
"%PHP_PATH%" -v | findstr /i "PHP"
echo.
echo [OK] Запускаю веб-сервер...
echo.
echo ==========================================
echo   Сайт доступен по адресу:
echo   http://localhost:8000
echo ==========================================
echo.
echo   Администратор: admin / admin123 (PIN: 1234)
echo.
echo   Для остановки нажмите Ctrl+C
echo ==========================================
echo.

start http://localhost:8000

cd /d "%~dp0toppot"
"%PHP_PATH%" -S localhost:8000

pause

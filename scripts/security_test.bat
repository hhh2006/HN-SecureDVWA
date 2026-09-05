@echo off
setlocal
cd /d "%~dp0.."
where php >nul 2>&1
if errorlevel 1 (
  echo [ERROR] PHP was not found in PATH.
  echo Run this from an XAMPP PHP-enabled terminal or add PHP to PATH.
  exit /b 1
)
php tests\security_regression.php
exit /b %errorlevel%

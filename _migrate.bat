@echo off
setlocal

rem migrate.bat — Runs Laravel migrations from project root
if not exist artisan (
  echo ERROR: artisan not found. Run this script from the project root.
  exit /b 1
)

echo Running migrations: php artisan migrate %*
php artisan migrate %*
if errorlevel 1 (
  echo ERROR: php artisan migrate failed.
  exit /b 1
)

echo Migrations completed.
endlocal
exit /b 0

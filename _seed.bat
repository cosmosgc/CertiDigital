@echo off
setlocal

rem seed.bat — Runs Laravel DB seeders from project root
if not exist artisan (
  echo ERROR: artisan not found. Run this script from the project root.
  exit /b 1
)

echo Running DB seeders: php artisan db:seed %*
php artisan db:seed %*
if errorlevel 1 (
  echo ERROR: php artisan db:seed failed.
  exit /b 1
)

echo Database seeding completed.
endlocal
exit /b 0

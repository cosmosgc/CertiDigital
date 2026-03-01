@echo off
setlocal enabledelayedexpansion

rem _installbat — Checks prerequisites and installs project dependencies
echo.
echo ===== CertiDigital Build Helper =====
echo Checking required tools...

rem Check PHP
where php >nul 2>&1
if errorlevel 1 (
  echo ERROR: PHP not found in PATH. Please install PHP and add it to PATH.
  exit /b 1
)

rem Check Composer
where composer >nul 2>&1
if errorlevel 1 (
  echo ERROR: Composer not found in PATH. Please install Composer (https://getcomposer.org/).
  exit /b 1
)

rem Check Node
where node >nul 2>&1
if errorlevel 1 (
  echo ERROR: Node.js not found in PATH. Please install Node.js (https://nodejs.org/).
  exit /b 1
)

rem Check npm
where npm >nul 2>&1
if errorlevel 1 (
  echo ERROR: npm not found in PATH. Ensure Node.js was installed correctly.
  exit /b 1
)

rem Check git (optional but useful)
where git >nul 2>&1
if errorlevel 1 (
  echo WARNING: git not found in PATH. This is optional but recommended.
)

rem Check artisan file (Laravel project)
if not exist artisan (
  echo ERROR: artisan file not found. Run this script from the project root.
  exit /b 1
)

rem Try to detect Laravel via artisan
php artisan --version >nul 2>&1
if errorlevel 1 (
  echo WARNING: php artisan failed. Laravel may be missing or PHP CLI has issues.
)

rem Detect Vite in package.json (not strict — just a helpful check)
set HAS_VITE=0
if exist package.json (
  findstr /i "\"vite\"" package.json >nul 2>&1 && set HAS_VITE=1
)
if %HAS_VITE%==1 (
  echo Vite detected in package.json
) else (
  echo Note: Vite not detected in package.json — asset build step will be skipped unless configured.
)

echo.
echo Installing PHP dependencies with Composer...
composer install --no-interaction --prefer-dist
if errorlevel 1 (
  echo ERROR: Composer install failed.
  exit /b 1
)

rem Optional: generate app key if .env exists and APP_KEY missing
if exist .env (
  for /f "tokens=1* delims==" %%A in ('php -r "echo getenv('APP_KEY')?:'__NO_KEY__';" 2^>nul') do set APP_KEY=%%A
)

echo.
if exist package.json (
  echo Installing Node dependencies (npm)...
  npm install
  if errorlevel 1 (
    echo ERROR: npm install failed.
    exit /b 1
  )

  if %HAS_VITE%==1 (
    echo Building frontend assets (npm run build)...
    npm run build || (
      echo WARNING: npm run build failed — try "npm run dev" or check your Vite config.
    )
  ) else (
    echo Skipping JS build (Vite not detected).
  )
else (
  echo No package.json found — skipping npm install/build steps.
)

echo.
echo Running basic Laravel setup tasks...
rem run migrations/seeds only if user wants — we don't run them automatically
echo Completed composer/npm install steps. You can now run migrate and seed scripts:
echo   migrate.bat   — runs php artisan migrate
echo   seed.bat      — runs php artisan db:seed

echo Done.
endlocal
exit /b 0
@echo off
echo Starting npm run build...
start cmd /k "npm run build"

exit

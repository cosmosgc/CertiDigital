@echo off
setlocal

echo.
echo ===== CertiDigital Update Helper =====

where git >nul 2>&1
if errorlevel 1 (
  echo ERROR: Git not found in PATH. Please install Git and add it to PATH.
  exit /b 1
)

if not exist .git (
  echo ERROR: .git folder not found. Run this script from the project root.
  exit /b 1
)

for /f "delims=" %%A in ('git branch --show-current 2^>nul') do set CURRENT_BRANCH=%%A
if not defined CURRENT_BRANCH (
  echo ERROR: Could not detect the current Git branch.
  exit /b 1
)

echo Fetching latest changes from remote...
git fetch --all --prune
if errorlevel 1 (
  echo ERROR: Git fetch failed.
  exit /b 1
)

echo Pulling latest changes for branch %CURRENT_BRANCH%...
git pull
if errorlevel 1 (
  echo ERROR: Git pull failed. Resolve conflicts or review local changes, then try again.
  exit /b 1
)

echo.
echo Update complete. Current branch: %CURRENT_BRANCH%
endlocal
exit /b 0

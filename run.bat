@echo off

REM Start Frontend (npm run dev)
start "Frontend Server" cmd /k "cd frontend && npm run dev"

REM Start Backend (php artisan serve)
start "Backend Server" cmd /k "cd backend && php artisan serve"

exit 
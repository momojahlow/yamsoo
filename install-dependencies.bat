@echo off
echo Installation des dependances manquantes...
echo.

echo 1. Installation de laravel-echo et pusher-js...
npm install laravel-echo@^2.2.0 pusher-js@^8.4.0

echo.
echo 2. Verification des packages installes...
npm list laravel-echo pusher-js

echo.
echo 3. Rebuild des assets...
npm run build

echo.
echo Installation terminee !
echo Vous pouvez maintenant decommentez le code Echo dans resources/js/app.tsx
pause

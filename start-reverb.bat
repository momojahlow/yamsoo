@echo off
echo 🚀 Démarrage de Laravel Reverb...
echo.

cd /d "C:\Users\Mamadou\Herd\yamsoo"

echo 📡 Configuration Reverb:
echo - Host: localhost
echo - Port: 8080
echo - App Key: yamsoo-key
echo.

echo ⚡ Démarrage du serveur WebSocket...
php artisan reverb:start

pause

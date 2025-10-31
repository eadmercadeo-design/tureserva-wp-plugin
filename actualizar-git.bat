@echo off
cd /d "C:\Local Sites\tureserva\app\public\wp-content\plugins\tureserva"
git add .
git commit -m "Actualización automática del plugin TuReserva"
git push origin main
pause

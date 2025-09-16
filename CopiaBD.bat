@echo off
echo Creando carpeta destino en Windows (si no existe)...
if not exist C:\Users\crojasm\wsl mkdir C:\Users\crojasm\wsl

echo Copiando base de datos SQLite desde WSL...
wsl -d Ubuntu-22.04 cp /home/administrador/laravel/HorasExtras-app/database/database.sqlite /mnt/c/Users/crojasm/wsl/

echo.
echo ✅ ¡Copia completada!
echo Archivo guardado en: C:\Users\crojasm\wsl\database.sqlite
pause
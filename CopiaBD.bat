@echo off

echo Copiando base de datos SQLite desde WSL...
wsl cp /home/administrador/laravel/HorasExtras-app/database/database.sqlite /mnt/c/Users/crojasm/wsl/

echo.
echo ✅ ¡Copia completada!
echo Archivo guardado en: C:\Users\crojasm\wsl\database.sqlite
pause
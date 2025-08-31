#!/bin/bash

# Salir si ocurre algún error
set -e

echo "📦 Instalando dependencias PHP..."
composer install

echo "⚙️ Copiando archivo .env..."
if [ ! -f .env ]; then
  cp .env.example .env
  echo "✅ .env creado a partir de .env.example"
else
  echo "⚠️ .env ya existe, no se sobrescribe"
fi

echo "🔑 Generando APP_KEY..."
php artisan key:generate

echo "💾 Ejecutando migraciones (si existen)..."
php artisan migrate --force || echo "⚠️ No hay migraciones"

echo "💾 Ejecutando db:seed (si existen)..."
php artisan db:seed

echo "📦 Instalando dependencias de Node..."
npm install

echo "🛠️ Compilando assets con Vite..."
npm run build

echo "🚀 Iniciando servidor de Laravel..."
php artisan serve
# php artisan serve --host=0.0.0.0 --port=8000

#!/bin/bash

set -e

echo "🚀 Iniciando despliegue..."

echo "📥 Ejecutando git pull..."
git pull

echo "🔒 Asignando permisos..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "🧹 Limpiando cachés..."
php artisan optimize:clear

echo "📦 Reconstruyendo cachés..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Despliegue completado."
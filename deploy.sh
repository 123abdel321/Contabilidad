#!/bin/bash

set -e # Detener script si hay error

echo "🚀 Iniciando despliegue..."
LOG_FILE="deploy_$(date +%Y%m%d_%H%M%S).log"

# Función para loguear
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

log_message "📥 Ejecutando git pull..."
git pull 2>&1 | tee -a "$LOG_FILE"

log_message "🧹 Limpiando cachés de Laravel..."
php artisan config:clear 2>&1 | tee -a "$LOG_FILE"
php artisan cache:clear 2>&1 | tee -a "$LOG_FILE"
php artisan route:clear 2>&1 | tee -a "$LOG_FILE"
php artisan view:clear 2>&1 | tee -a "$LOG_FILE"

log_message "🔄 Reiniciando Horizon..."
php artisan horizon:terminate 2>&1 | tee -a "$LOG_FILE"

log_message "📦 Generando config:cache..."
php artisan config:cache 2>&1 | tee -a "$LOG_FILE"

log_message "✅ Despliegue completado. Ver log: $LOG_FILE"
#!/bin/bash
set -e

echo "🚀 Starting Barangay Information System..."

# Create necessary directories
mkdir -p uploads/documents uploads/logos uploads/profiles logs

# Set proper permissions
chmod 755 uploads uploads/documents uploads/logos uploads/profiles logs

# Determine port
PORT=${PORT:-8080}

# Check environment
if [ "$APP_ENV" = "production" ]; then
    echo "🔒 Running in PRODUCTION mode"
    php -d error_reporting=E_ALL -d display_errors=0 -d log_errors=1 -d error_log=logs/php_errors.log -S 0.0.0.0:$PORT
else
    echo "💻 Running in DEVELOPMENT mode"
    php -d error_reporting=E_ALL -d display_errors=1 -S 0.0.0.0:$PORT
fi

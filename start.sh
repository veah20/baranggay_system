#!/bin/bash

# Barangay Information and Reporting System - Railway Start Script
# This script prepares the application and starts the PHP server

set -e

echo "🚀 Starting Barangay Information System..."

# Create necessary directories
echo "📁 Creating directories..."
mkdir -p uploads/documents
mkdir -p uploads/logos
mkdir -p uploads/profiles
mkdir -p logs

# Set proper permissions
echo "🔐 Setting permissions..."
chmod 755 uploads
chmod 755 uploads/documents
chmod 755 uploads/logos
chmod 755 uploads/profiles
chmod 755 logs

# Check if we're in production
if [ "$APP_ENV" = "production" ]; then
    echo "🔒 Running in PRODUCTION mode"
    # Production settings with error logging
    php -d error_reporting=E_ALL \
        -d display_errors=0 \
        -d log_errors=1 \
        -d error_log=logs/php_errors.log \
        -S 0.0.0.0:${PORT:-8080}
else
    echo "💻 Running in DEVELOPMENT mode"
    # Development settings with error display
    php -d error_reporting=E_ALL \
        -d display_errors=1 \
        -S 0.0.0.0:${PORT:-8080}
fi

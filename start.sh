#!/bin/bash
set -e

# Create necessary directories
mkdir -p uploads/documents
mkdir -p uploads/logos
mkdir -p uploads/profiles
mkdir -p uploads/signatures
mkdir -p logs

# Set proper permissions
chmod -R 755 uploads
chmod -R 755 logs

# Export PORT if not already set (Railway sets this)
export PORT=${PORT:-8080}

# Log startup information
echo "=========================================="
echo "Starting Barangay Information System"
echo "Environment: $(test "$APP_ENV" = "production" && echo "PRODUCTION" || echo "DEVELOPMENT")"
echo "Port: ${PORT}"
echo "=========================================="

# If running under Apache (Docker), start Apache
if [ -f /usr/sbin/apache2 ]; then
    echo "Starting Apache Web Server..."
    exec apache2-foreground
else
    # Otherwise start PHP development server
    echo "Starting PHP Development Server on 0.0.0.0:${PORT}..."
    exec php -S 0.0.0.0:${PORT}
fi

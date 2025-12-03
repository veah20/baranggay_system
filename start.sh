#!/bin/bash
set -e

# Create necessary directories
mkdir -p /var/www/html/uploads/documents
mkdir -p /var/www/html/uploads/logos
mkdir -p /var/www/html/uploads/profiles
mkdir -p /var/www/html/logs

# Set proper permissions
chown -R www-data:www-data /var/www/html/uploads
chown -R www-data:www-data /var/www/html/logs
chmod -R 755 /var/www/html/uploads
chmod -R 755 /var/www/html/logs

echo "Starting Apache..."
apache2-foreground

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

# Export PORT if not already set
export PORT=${PORT:-8080}

echo "Starting PHP server on 0.0.0.0:${PORT}..."
php -S 0.0.0.0:${PORT}

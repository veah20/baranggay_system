#!/bin/bash
# Build script for Railway deployment
# This script handles the project structure

set -e

echo "Building Barangay Information and Reporting System..."

# The repository structure has the project in a subdirectory
# This script helps navigate that
cd "${0%/*}"

# If we're in the BarangayInformationResidentSystem directory, we're good
# If not, we need to navigate there
if [ ! -f "Procfile" ]; then
  if [ -d "BarangayInformationResidentSystem" ]; then
    cd BarangayInformationResidentSystem
  fi
fi

# Now verify we have everything we need
if [ ! -f "Procfile" ]; then
  echo "ERROR: Could not find Procfile!"
  echo "Current directory: $(pwd)"
  exit 1
fi

echo "Found Procfile in: $(pwd)"
echo "Build complete!"

#!/bin/bash

# Vercel Build Script for PHP

# Exit immediately if a command exits with a non-zero status.
set -e

# Define output directories
OUTPUT_DIR=".vercel/output"
STATIC_DIR="$OUTPUT_DIR/static"
FUNCTIONS_DIR="$OUTPUT_DIR/functions"
API_FUNC_DIR="$FUNCTIONS_DIR/api.func"
INDEX_FUNC_DIR="$FUNCTIONS_DIR/index.func"
CONFIG_FILE="$OUTPUT_DIR/config.json"

# Create directory structure
echo "Creating directory structure..."
mkdir -p "$STATIC_DIR"
mkdir -p "$API_FUNC_DIR"
mkdir -p "$INDEX_FUNC_DIR"

# Copy static assets
echo "Copying static assets..."
# Check if directories exist before copying
if [ -d "css" ]; then cp -r css "$STATIC_DIR/"; fi
if [ -d "fonts" ]; then cp -r fonts "$STATIC_DIR/"; fi
if [ -d "img" ]; then cp -r img "$STATIC_DIR/"; fi
if [ -d "js" ]; then cp -r js "$STATIC_DIR/"; fi

# --- API Function --- 
echo "Setting up API function..."
# Copy the main handler (api/index.php) to the API function directory
cp api/index.php "$API_FUNC_DIR/index.php"
# Copy other files needed by the api handler if any (assuming api/* needed)
# cp -r api/* "$API_FUNC_DIR/" # This might copy index.php again, be careful

# Create .vc-config.json for the API function
echo '{"runtime": "vercel-php@0.6.2", "handler": "index.php"}' > "$API_FUNC_DIR/.vc-config.json"

# --- Index Function (Fallback/Root) --- 
echo "Setting up Index function..."
# Copy the main handler (api/index.php) to the Index function directory as index.php
cp api/index.php "$INDEX_FUNC_DIR/index.php"

# Copy root PHP files and necessary directories
cp *.php "$INDEX_FUNC_DIR/"
if [ -d "database" ]; then cp -r database "$INDEX_FUNC_DIR/"; fi
if [ -d "includes" ]; then cp -r includes "$INDEX_FUNC_DIR/"; fi
if [ -f ".htaccess" ]; then cp .htaccess "$INDEX_FUNC_DIR/"; fi

# Create .vc-config.json for the Index function
echo '{"runtime": "vercel-php@0.6.2", "handler": "index.php"}' > "$INDEX_FUNC_DIR/.vc-config.json"

# --- Output Config --- 
echo "Creating output config.json..."
cat > "$CONFIG_FILE" <<EOL
{
  "version": 3,
  "routes": [
    { "handle": "filesystem" },
    { "src": "^/api(?:/(.*))?$", "dest": "/api" },
    { "src": "^/(.*)", "dest": "/index" }
  ]
}
EOL

echo "Build script finished successfully."

#!/bin/bash

# This is a build script for Vercel PHP deployment
# It ensures proper configuration for PHP execution

# Create the .vercel/output directory structure
mkdir -p .vercel/output/static
mkdir -p .vercel/output/functions/api

# Copy all static assets to the static directory
cp -r css fonts img js .vercel/output/static/

# Ensure PHP files are properly handled
echo "<?php require __DIR__ . '/../../api/index.php';" > .vercel/output/functions/index.php

# Copy the main PHP files to the functions directory
cp -r *.php api/ database/ includes/ .vercel/output/functions/

# Make the build script executable
chmod +x vercel-build.sh

echo "Build completed successfully!"
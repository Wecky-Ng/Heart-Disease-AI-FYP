#!/bin/bash

# This is a build script for Vercel PHP deployment
# It ensures proper configuration for PHP execution

# Create the .vercel/output directory structure
mkdir -p .vercel/output/static
mkdir -p .vercel/output/functions/api.func
mkdir -p .vercel/output/functions/index.func

# Copy all static assets to the static directory
cp -r css fonts img js .vercel/output/static/

# Create config.json for routing
cat > .vercel/output/config.json <<EOL
{
  "version": 3,
  "routes": [
    { "src": "^/css/(.*)", "dest": "/css/$1" },
    { "src": "^/js/(.*)", "dest": "/js/$1" },
    { "src": "^/img/(.*)", "dest": "/img/$1" },
    { "src": "^/fonts/(.*)", "dest": "/fonts/$1" },
    { "src": "^/api/(.*)", "dest": "/api/$1" },
    { "src": "^/(.*)\\.php$", "dest": "/api.func/$1.php" },
    { "src": "^/$", "dest": "/index.func" },
    { "src": "^/(.*)", "dest": "/api.func/$1" }
  ]
}
EOL

# Configure API function
cat > .vercel/output/functions/api.func/.vc-config.json <<EOL
{"runtime": "vercel-php@0.7.3", "handler": "index.php"}
EOL

# Configure Index function
cat > .vercel/output/functions/index.func/.vc-config.json <<EOL
{"runtime": "vercel-php@0.7.3", "handler": "index.php"}
EOL

# Copy PHP files to the function directories
cp -r *.php database/ includes/ .vercel/output/functions/api.func/
cp index.php .vercel/output/functions/index.func/

# Copy configuration files
cp .htaccess .vercel/output/functions/api.func/ 2>/dev/null || :
cp .htaccess .vercel/output/functions/index.func/ 2>/dev/null || :

echo "Build completed successfully!"
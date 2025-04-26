#!/bin/bash

# This is a build script for Vercel PHP deployment
# It ensures proper configuration for PHP execution

# Create the .vercel/output directory structure
mkdir -p .vercel/output/static
mkdir -p .vercel/output/functions
mkdir -p .vercel/output/config

# Copy all static assets to the static directory
cp -r css fonts img js .vercel/output/static/

# Create the main API function handler
mkdir -p .vercel/output/functions/api.func
cp -r api/* .vercel/output/functions/api.func/

# Create .vc-config.json for the API function
cat > .vercel/output/functions/api.func/.vc-config.json <<EOL
{"runtime": "vercel-php@0.7.3", "handler": "index.php"}
EOL

# Create the index function handler
cat > .vercel/output/functions/index.func <<EOL
#!/bin/bash
php -S localhost:\${PORT:-8080} -t /var/task
EOL
chmod +x .vercel/output/functions/index.func

# Create .vc-config.json for the function
cat > .vercel/output/functions/index.func/.vc-config.json <<EOL
{"runtime": "vercel-php@0.7.3", "handler": "index.php"}
EOL

# Copy all PHP files to the function directory
cp -r *.php database/ includes/ .vercel/output/functions/index.func/

# Copy configuration files
cp .htaccess .vercel/output/functions/index.func/
cp .vercel.php .vercel/output/functions/index.func/
cp .vercel.php .vercel/output/functions/api.func/ # Also copy PHP handler to api func

# Create config.json
cat > .vercel/output/config.json <<EOL
{
  "version": 3,
  "routes": [
    { "handle": "filesystem" },
    { "src": "^/api/(.*)$", "dest": "/api" },
    { "src": "^/.*$", "dest": "/index" }
  ]
}
EOL

# Make the build script executable
chmod +x vercel-build.sh

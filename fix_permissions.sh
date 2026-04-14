#!/bin/bash
# Fix permissions script for cPanel
# Run this in SSH or use as reference for cPanel File Manager

# Set all PHP files to 644 (readable by all, writable by owner)
find /home/softtech/plagiascope.softtechco.biz -name "*.php" -exec chmod 644 {} \;

# Set directories to 755 (executable for all, writable by owner)
find /home/softtech/plagiascope.softtechco.biz -type d -exec chmod 755 {} \;

# Ensure auth directory is readable
chmod 755 /home/softtech/plagiascope.softtechco.biz/auth
chmod 644 /home/softtech/plagiascope.softtechco.biz/auth/*.php

# Ensure app directory is readable
chmod 755 /home/softtech/plagiascope.softtechco.biz/app
chmod 644 /home/softtech/plagiascope.softtechco.biz/app/*.php

echo "Permissions fixed!"

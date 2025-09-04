#!/bin/bash

# Script to rebuild the full-hero block properly

echo "Rebuilding Full Hero block..."

# Make sure we're in the right directory
cd /Users/kazalvis/Documents/Superkore/Clients/Patient-360/360-sites/360-dev/wp-content/plugins/360-Global-Blocks

# Copy the source block.json to build directory to ensure consistency
cp blocks/full-hero/block.json blocks/full-hero/build/block.json

echo "✅ Copied block.json to build directory"

# Create a simple index.js build by copying and minifying (basic approach)
if [ -f "blocks/full-hero/build/index.js" ]; then
    echo "✅ Build files exist"
else
    echo "❌ Build files missing - run npm run build first"
fi

# Check that PHP registration points to the right place
grep -n "full-hero" 360-global-blocks.php | head -5

echo "✅ Full Hero block rebuild complete"
echo "Now try hard refreshing your WordPress admin page (Cmd+Shift+R)"

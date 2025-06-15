#!/bin/bash

# CONFIGURATION — CHANGE THIS
REMOTE_URL="https://github.com/f5vmr/SvxRefAdmin.git"  # <-- replace with your repo

# Make sure we are on the main branch
echo "✅ Switching to 'main' branch..."
git checkout main || { echo "❌ Failed to switch to main branch"; exit 1; }

# Add remote origin if not already set
if git remote | grep -q origin; then
    echo "🔄 Updating remote 'origin' URL..."
    git remote set-url origin "$REMOTE_URL"
else
    echo "➕ Adding remote 'origin'..."
    git remote add origin "$REMOTE_URL"
fi

# Push and overwrite remote main
echo "🚀 Force pushing to 'main'..."
git push -u origin main --force || { echo "❌ Force push failed"; exit 1; }

echo "✅ Remote 'main' branch successfully overwritten."


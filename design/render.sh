#!/bin/sh
# Renders all design sources to PNG files in design/out/ (macOS: Google Chrome + sips).
# Copy the store images from design/out/wordpress-org/ to .wordpress-org/ when they changed.
set -e

cd "$(dirname "$0")"
CHROME="${CHROME:-/Applications/Google Chrome.app/Contents/MacOS/Google Chrome}"
OUT=out

render() { # source width height target
  "$CHROME" --headless=new --disable-gpu --hide-scrollbars --force-device-scale-factor=1 \
    --virtual-time-budget=2000 --window-size="$2,$3" --screenshot="$4" "file://$PWD/$1" >/dev/null 2>&1
  echo "$4"
}

mkdir -p "$OUT/wordpress-org" "$OUT/bookstack"

# WordPress.org plugin page (.wordpress-org/)
render plugin-icon.html 256 256 "$OUT/wordpress-org/icon-256x256.png"
sips -z 128 128 "$OUT/wordpress-org/icon-256x256.png" --out "$OUT/wordpress-org/icon-128x128.png" >/dev/null
render plugin-banner.html 1544 500 "$OUT/wordpress-org/banner-1544x500.png"
sips -z 250 772 "$OUT/wordpress-org/banner-1544x500.png" --out "$OUT/wordpress-org/banner-772x250.png" >/dev/null
render plugin-banner-de.html 1544 500 "$OUT/wordpress-org/banner-1544x500-de.png"
sips -z 250 772 "$OUT/wordpress-org/banner-1544x500-de.png" --out "$OUT/wordpress-org/banner-772x250-de.png" >/dev/null

# BookStack covers (uploaded by hand)
for f in bookstack/*.html; do
  render "$f" 880 500 "$OUT/bookstack/$(basename "$f" .html).png"
done

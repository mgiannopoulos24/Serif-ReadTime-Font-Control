#!/usr/bin/env sh
# Build a distributable plugin zip at build/serif-readtime-font-control.zip.
# Run via `bun run bundle`.
set -eu

cd "$(dirname "$0")/.."

SLUG=serif-readtime-font-control
OUT=build/$SLUG
rm -rf build
mkdir -p "$OUT"

# Allowlist: only what WordPress reads at runtime.
cp "$SLUG.php" uninstall.php readme.txt LICENSE changelog.txt "$OUT/"
cp -r includes blocks assets "$OUT/"
[ -d languages ] && [ -n "$(ls -A languages)" ] && cp -r languages "$OUT/"

find "$OUT" -type d -empty -delete

(cd build && bestzip "$SLUG.zip" "$SLUG")
rm -rf "$OUT"
echo "Wrote build/$SLUG.zip ($(du -h "build/$SLUG.zip" | cut -f1))"

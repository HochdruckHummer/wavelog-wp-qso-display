# Design sources

Source files for the images of this plugin. Each image is an HTML page with an inline SVG, rendered to PNG with Google Chrome. Not part of the plugin ZIP or the WordPress.org upload.

| Source | Output | Used for |
|---|---|---|
| `plugin-icon.html` | `icon-256x256.png`, `icon-128x128.png` | WordPress.org plugin icon |
| `plugin-banner.html` | `banner-1544x500.png`, `banner-772x250.png` | WordPress.org banner (English) |
| `plugin-banner-de.html` | `banner-1544x500-de.png`, `banner-772x250-de.png` | WordPress.org banner (German) |
| `bookstack/cover-qso-statistics-for-wavelog.html` | 880×500 | BookStack cover, book "QSO Statistics for Wavelog" |
| `bookstack/cover-eigene-programmierungen.html` | 880×500 | BookStack cover, shelf "Eigene Programmierungen" |
| `bookstack/cover-lightaprsinjector.html` | 880×500 | BookStack cover, book "lightAPRSinjector" |
| `bookstack/cover-wavelogqsostats2awtrix.html` | 880×500 | BookStack cover, book "WavelogQSOStats2Awtrix" |

The German banner is the English one with three translated texts; change both together.

## Rendering

```sh
design/render.sh
```

Writes all PNG files to `design/out/` (ignored by git). Requires macOS with Google Chrome; set `CHROME` to use another Chrome binary.

- **WordPress.org images:** copy the changed files from `design/out/wordpress-org/` to `.wordpress-org/`. They are uploaded with the next version deployment (version bump required).
- **BookStack covers:** upload by hand in BookStack (*Edit* → *Cover image*). BookStack shows covers at about 1.76:1, so 880×500 is not cropped.

Fonts are the system fonts of macOS (`-apple-system`), so renders on other systems may look slightly different.

The sources were restored on 27.10.2026 from the original design session. Icons render byte-identical to the published files. In the store banners the stroke weight of the six numbers on the card can differ slightly from the published PNGs, depending on which font variant Chrome picks. The published files in `.wordpress-org/` stay the reference; only replace them when a banner is changed on purpose.

# Serif ReadTime & Font Control

A "minutes to read" block and an accessible floating reading-controls widget (text size, line height, contrast) that remembers the reader's choices. Companion plugin to the [Serif](https://github.com/mgiannopoulos24/Serif) block theme; works with any block theme.

- **Requires** WordPress 6.6+, PHP 8.1+ · **License** GPL-3.0-or-later · **Text domain** `serif-readtime-font-control`
- User-facing readme (features, FAQ, credits): [`readme.txt`](readme.txt) · Changes: [`changelog.md`](changelog.md)
- Theme: [Serif](https://github.com/mgiannopoulos24/Serif) — the block is placed in its post-meta row automatically

## What it does

**Reading Time block** (`serif/read-time`). Server-rendered "4 min read" with inspector controls for the clock icon, a label and words-per-minute. Word counting is Unicode-aware (Greek works) and ignores block delimiters, shortcodes, captions, `<script>`/`<style>` and image alt text. Below one minute it reads "Under a minute". In the Serif theme it is inserted after `core/post-date` in the `post-meta` template part through the Block Hooks API, so templates need no editing and users can still move or remove it in the Site Editor. In any other theme, place it by hand.

**Font Control widget.** A round button bottom-right opens a small panel: text size (80–150 %), line height (1.6 / 1.8 / 2.0), contrast (Default / High contrast / Sepia), Reset and a close button. Choices are saved in `localStorage` and restored by a tiny inline `<head>` script **before first paint**, so nothing flashes on reload. Toggle it under Settings → Reading.

## Principles

1. **No build step, no CDN, no external requests.** Plain PHP, vanilla JS, plain CSS; icons are inline SVG; the editor script uses the `wp.*` globals.
2. **The theme's presets are the contract.** The widget only sets custom properties and data attributes on `<html>`: text size scales the root `font-size` (works because Serif's type scale is in rem), contrast presets override `--wp--preset--color--*`. Any theme whose colours flow through those variables gets the full effect; themes that hard-code hex only get body background/text swapped.
3. **Accessibility is the product.** Keyboard-complete, visible focus, `aria-expanded` / `aria-pressed`, 44 px targets, `prefers-reduced-motion`, `forced-colors`, hidden in print, and the axe suite has to stay at zero violations in every widget state.
4. **Only the auto-placement is Serif-specific** (`get_template() === 'serif'`). Everything else is theme-agnostic.

## Getting started

Prerequisites: [Bun](https://bun.sh), Composer, Docker (for wp-env). The Serif theme must be checked out next to this repo as `../Serif` — `.wp-env.json` mounts it.

```sh
bun install
composer install
bun run start        # wp-env on http://localhost:8888 — activates the theme and the plugin, seeds content
```

The theme's env also uses port 8888, so run one at a time. Log in at `/wp-admin` with `admin` / `password`; `/slow-return-long-read/` shows the block in the meta row, `/kitchen-sink/` is a short post.

## Scripts

| Command | What it does |
|---|---|
| `bun run start` / `stop` / `destroy` | wp-env lifecycle |
| `bun run seed` | Seed demo content via the theme's `scripts/seed.sh` (idempotent) |
| `bun run lint` / `lint:fix` | PHPCS with WordPress Coding Standards |
| `bun run test:unit` | PHPUnit — `Read_Time` is pure PHP, no WordPress bootstrap needed |
| `bun run test:e2e` | Playwright + axe-core suite (desktop and mobile) |
| `bun run test:e2e:report` | Open the last HTML report |
| `bun run makepot` / `update-po` / `makemo` / `makejson` | i18n: extract strings / merge into `.po` / compile `.mo` / JSON for the editor script |
| `bun run bundle` | Produce an installable `build/serif-readtime-font-control.zip` (only what WordPress reads) |

First run of the tests needs a browser: `bun run test:e2e:install`.

## Structure

```
serif-readtime-font-control/
├── serif-readtime-font-control.php   # header, constants (SERIF_RTFC_*), textdomain, requires, activation hook
├── uninstall.php                     # deletes the one option
├── readme.txt                        # WP.org readme (features, FAQ, credits, changelog)
├── README.md                         # this file
├── changelog.md / changelog.txt      # Keep a Changelog / plain text (shipped in the zip)
├── LICENSE                           # GPL-3.0
├── phpcs.xml / phpunit.xml           # WordPress Coding Standards / PHPUnit (cache in tests/phpunit/.cache)
├── package.json / bun.lock           # scripts and dev dependencies (Bun)
├── composer.json / composer.lock     # PHPCS + WPCS + PHPUnit
├── .wp-env.json                      # local WordPress (Docker) — maps ../Serif and this dir
├── assets/
│   ├── css/font-control.css          # the whole mechanism: html[data-serif-*] rules + widget styles
│   ├── js/font-control.js            # widget behaviour; shares the <html> contract with the head script
│   └── icons/                        # clock, eye, plus, minus, format-line-spacing, refresh (MDI Light), close (MDI)
├── blocks/read-time/
│   ├── block.json                    # apiVersion 3, usesContext postId, attrs showIcon/label/wordsPerMinute
│   ├── render.php                    # server render (sample "4 min read" when no post is in context)
│   ├── editor.js  editor.asset.php   # ServerSideRender + InspectorControls via wp.* globals
│   └── style.css
├── includes/
│   ├── class-read-time.php           # Read_Time: to_text(), count_words(), minutes() — pure PHP
│   ├── read-time-block.php           # registration, editor preview context, Block Hooks placement in Serif
│   ├── font-control.php              # config + filter, head restore script, enqueue, widget markup
│   ├── settings.php                  # font_control_enabled() + Settings → Reading checkbox
│   └── icons.php                     # inlines assets/icons/*.svg
├── languages/
│   ├── serif-readtime-font-control.pot
│   ├── serif-readtime-font-control-<locale>.po/.mo
│   └── serif-readtime-font-control-<locale>-<md5>.json   # editor.js strings (wp_set_script_translations)
├── scripts/bundle.sh                 # build/serif-readtime-font-control.zip from an allowlist
├── tests/
│   ├── phpunit/                      # bootstrap.php, ReadTimeTest.php
│   └── e2e-pw/
│       ├── playwright.config.ts      # desktop + mobile projects
│       └── specs/                    # helpers.ts, font-control, read-time, accessibility (.spec.js)
├── .github/                          # issue/PR templates; workflows: ci.yml, release.yml
└── build/                            # `bun run bundle` output (gitignored)
```

## How things work

**Reading time.** `Read_Time::to_text()` strips block comments, media shortcodes with their content (`caption gallery audio video playlist embed`), other shortcode tags (keeping inner text), `figcaption/script/style/noscript/template`, then all tags, and decodes entities. `count_words()` matches `[\p{L}\p{N}]+` with `-`/`'` joiners so "well-known" counts once. `minutes()` returns 0 when the count is below the reading speed, otherwise `ceil(words / wpm)`.

**Editor preview.** `editor.js` passes the edited post as `?post_id` to ServerSideRender; `read-time-block.php` copies it into block context on REST requests so the preview counts the right post. When there is no post at all (editing a template part) `render.php` shows a "4 min read" sample.

**Block Hooks.** `hooked_block_types` adds `serif/read-time` after `core/post-date`, only for the `post-meta` `WP_Block_Template` and only when the active theme is Serif; `hooked_block_serif/read-time` gives the hooked instance `fontSize: small` to match the row.

**Widget state.** Three things on `<html>`: `--serif-font-scale` + `data-serif-font-scale`, `--serif-line-height` + `data-serif-line-height`, `data-serif-contrast`. The inline head script (printed at `wp_head` priority 1) and `font-control.js` both write them — change both or neither. The CSS does the rest: `html[data-serif-font-scale] { font-size: calc(100% * var(--serif-font-scale)) }`, line height on body copy only, and palette overrides for High contrast / Sepia copied from the theme's style variations so the widget and the Site Editor agree.

**Filters.** `serif_read_time_words_per_minute( $wpm, $post_id )`, `serif_read_time_text( $text, $minutes, $post_id )`, `serif_font_control_enabled( $bool )`, `serif_font_control_config( $config )`.

## Testing

- `tests/phpunit/` — `Read_Time` cases: rounding, empty content, shortcodes, block markup, excluded elements, entities, Greek, hyphens, custom and invalid WPM. `phpunit.xml` uses testdox output.
- `tests/e2e-pw/` runs against the seeded wp-env site (it starts one if none is reachable):
  - `font-control.spec.js` — ARIA state, focus management (open, Escape, close button, click outside), stepper bounds, line height, contrast, persistence before `load`, reset, corrupt storage, print.
  - `read-time.spec.js` — hooked after the date on single posts, short post text, absent on pages.
  - `accessibility.spec.js` — axe (WCAG 2.2 AA + best practice) with the widget closed and open, under each contrast preset and at 150 %.

Specs are plain JS with JSDoc: Playwright runs under Bun here, which bypasses its TypeScript transform for test files (`helpers.ts` is still TypeScript — use `import type`, not inline `type` modifiers).

## Continuous integration

`.github/`: issue templates (bug report, feature request) and a PR checklist. Workflows:

- **ci.yml** — on every push/PR: PHPCS, PHPUnit on PHP 8.1–8.4, then wp-env with the theme checked out alongside: the full Playwright/axe suite (report uploaded on failure) and a translation-freshness check (regenerates `languages/` and fails if it differs from the commit).
- **release.yml** — on a `v*` tag: checks the tag matches the plugin header, `SERIF_RTFC_VERSION` and `Stable tag`, runs `bun run bundle`, and publishes a GitHub Release with the zip and the top entry of `changelog.txt` as notes. Release = bump the three versions, add the changelog entry, tag `vX.Y.Z`, push.

## i18n

`bun run makepot` writes `languages/serif-readtime-font-control.pot` (PHP, `block.json` metadata, `editor.js`). Copy it to `languages/serif-readtime-font-control-<locale>.po`, translate, then `bun run makemo` and `bun run makejson`. The JSON is what the block editor loads for the strings in `editor.js` (`wp_set_script_translations`); PHP strings come from the `.mo`. Greek is included.

## Credits

Material Design Icons Light (clock, eye, plus, minus, line spacing, refresh) and Material Design Icons (close, redrawn as a thin stroke) — Pictogrammers, Apache License 2.0. Listed in [`readme.txt`](readme.txt).

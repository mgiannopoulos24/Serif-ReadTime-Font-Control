=== Serif ReadTime & Font Control ===
Contributors: mgiannopoulos24
Tags: reading time, accessibility, font size, contrast, block
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A "minutes to read" block and an accessible floating reading-controls widget (text size, line height, contrast) that remembers the reader's choices.

== Description ==

Two small, framework-free features for reading-focused sites:

**Reading Time block** — a server-rendered "4 min read" you can place anywhere post content is available (single templates, query loops, the post-meta area). Inspector controls let you toggle the clock icon, add a label and set the words-per-minute speed. Word counting is Unicode-aware and ignores block markup, shortcodes, captions and code.

**Font Control widget** — a floating button that opens a compact panel where readers can:

* change the text size from 80 % to 150 %,
* pick a line height (1.6, 1.8 or 2.0),
* switch to a High contrast or Sepia palette,
* reset everything.

Choices are stored in the reader's browser (localStorage) and restored before the first paint, so pages never flash the default style. The widget can be turned off under Settings → Reading.

Built for the Serif block theme — where the block is inserted automatically after the post date — but the widget works with any block theme that uses the standard `--wp--preset--color--*` variables, and the block can be placed manually anywhere.

= Accessibility =

Every control is keyboard reachable with a visible focus ring, uses `aria-expanded` / `aria-pressed`, has 44 px touch targets and respects `prefers-reduced-motion` and forced-colors mode. The widget is hidden when printing.

= Privacy =

No cookies, no external requests, nothing sent to the server. Preferences live only in the reader's browser.

= Filters =

* `serif_read_time_words_per_minute( int $wpm, int $post_id )` — change the reading speed.
* `serif_read_time_text( string $text, int $minutes, int $post_id )` — change the rendered text.
* `serif_font_control_enabled( bool $enabled )` — show or hide the widget programmatically.
* `serif_font_control_config( array $config )` — adjust the size range, line heights and contrast presets.

== Installation ==

1. Upload the `serif-readtime-font-control` folder to `/wp-content/plugins/`, or install the zip from Plugins → Add New.
2. Activate the plugin.
3. Add the **Reading Time** block to a template or post (in the Serif theme it appears in the post-meta row automatically).
4. Optionally turn the widget off under Settings → Reading.

== Frequently Asked Questions ==

= Where are the reader's preferences stored? =

Only in the reader's browser, in `localStorage`. No cookies are set and nothing is sent to the server.

= Why doesn't the text size change on my theme? =

Root scaling only affects font sizes defined in `rem`/`em` or fluid presets. Themes that use fixed `px` sizes will not scale.

= Why don't the contrast presets change my theme's colours? =

The presets override the standard `--wp--preset--color--*` custom properties (background, foreground, primary, secondary, muted, border, light, dark, accent). Themes that hard-code colours will only get the body background and text colour swapped.

= Does the block count words in other languages? =

Yes. Counting is Unicode-aware (Latin, Greek, Cyrillic, …) and hyphenated or apostrophe words count once.

== Screenshots ==

1. The Reading Time block in a post-meta row.
2. The Font Control widget open.
3. High contrast preset applied.

== Third-party resources ==

Icons: Material Design Icons Light © Pictogrammers, licensed under the Apache License 2.0 — https://github.com/Pictogrammers/MaterialDesignLight
Close icon: geometry of "close" from Material Design Icons © Pictogrammers, Apache License 2.0 — https://github.com/Templarian/MaterialDesign (redrawn as a thin stroke to match the Light set)

== Changelog ==

= 1.0.0 =
* Initial release.
* Reading Time block (serif/read-time): server-rendered "N min read" with icon, label and words-per-minute controls.
* Font Control widget: floating reading controls for text size (80–150%), line height (1.6 / 1.8 / 2.0) and contrast (Default / High contrast / Sepia), with a close button and reset; preferences saved in the reader's browser and restored before first paint.
* "Reading controls" setting under Settings → Reading to turn the widget off.
* Automatic placement of the block after the post date in the Serif theme's post-meta row (Block Hooks API).
* Filters: serif_read_time_words_per_minute, serif_read_time_text, serif_font_control_enabled, serif_font_control_config.
* Translation-ready; Greek translation included.

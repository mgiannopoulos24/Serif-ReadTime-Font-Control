# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-12

Initial release.

### Added

- Reading Time block (`serif/read-time`): server-rendered "N min read" with
  icon, label and words-per-minute inspector controls; Unicode-aware word
  count that ignores block markup, shortcodes, captions and code.
- Font Control widget: floating reading controls for text size (80–150 %),
  line height (1.6 / 1.8 / 2.0) and contrast (Default / High contrast / Sepia),
  with a close button and reset. Preferences are saved in the reader's browser
  and restored before first paint.
- "Reading controls" setting under Settings → Reading to turn the widget off.
- Automatic placement of the block after the post date in the Serif theme's
  post-meta row via the Block Hooks API.
- Filters: `serif_read_time_words_per_minute`, `serif_read_time_text`,
  `serif_font_control_enabled`, `serif_font_control_config`.
- Accessibility: keyboard reachable, visible focus, `aria-pressed` /
  `aria-expanded`, 44 px targets, reduced-motion and forced-colors support,
  hidden in print; zero axe violations in every widget state.
- i18n: `languages/serif-readtime-font-control.pot`; Greek translation
  (`.po`, `.mo` and editor `.json`).
- Tooling: wp-env with the Serif theme and its content seed, PHPCS (WPCS),
  PHPUnit, Playwright + axe-core suite (desktop and mobile), Plugin Check,
  `bun run bundle` → installable zip, GitHub Actions CI and release workflows.

[1.0.0]: https://github.com/mgiannopoulos24/serif-readtime-font-control/releases/tag/v1.0.0

<?php
/**
 * Reading-time calculation.
 *
 * Pure PHP — no WordPress dependencies — so it can be unit-tested directly.
 *
 * @package Serif_ReadTime_Font_Control
 */

namespace Serif\ReadTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns post content into a word count and a minutes-to-read estimate.
 */
final class Read_Time {

	/** Default reading speed (adult, non-technical prose). */
	public const DEFAULT_WPM = 200;

	/**
	 * Shortcodes that only wrap media; their inner text is caption/metadata, not prose.
	 *
	 * @var string[]
	 */
	private const MEDIA_SHORTCODES = array( 'caption', 'wp_caption', 'gallery', 'audio', 'video', 'playlist', 'embed' );

	/**
	 * Reduce content to readable prose.
	 *
	 * Removes block delimiters, media shortcodes (with their content), other
	 * shortcode tags (keeping enclosed text), captions, code/script/style and
	 * all remaining markup.
	 *
	 * @param string $content Raw post content (block markup, shortcodes, HTML).
	 * @return string Plain text.
	 */
	public static function to_text( string $content ): string {
		// Block delimiters and any other HTML comments.
		$text = (string) preg_replace( '/<!--.*?-->/s', ' ', $content );

		// Media shortcodes: drop tag and content.
		$media = implode( '|', self::MEDIA_SHORTCODES );
		$text  = (string) preg_replace( '/\[(' . $media . ')\b[^\]]*\](?:.*?\[\/\1\])?/is', ' ', $text );

		// Any other shortcode tags: drop the tag, keep enclosed text.
		$text = (string) preg_replace( '/\[\/?[a-z_][\w-]*(?:\s[^\]]*)?\]/i', ' ', $text );

		// Elements whose text is not prose.
		$text = (string) preg_replace( '/<(figcaption|script|style|noscript|template)\b[^>]*>.*?<\/\1>/is', ' ', $text );

		// Image metadata (alt/title) lives in attributes and is removed with the tags.
		$text = strip_tags( $text ); // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- no WP dependency by design.
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		return trim( (string) preg_replace( '/\s+/u', ' ', $text ) );
	}

	/**
	 * Count words in a Unicode-aware way (Latin, Greek, Cyrillic, CJK digits…).
	 *
	 * @param string $content Raw content.
	 * @return int
	 */
	public static function count_words( string $content ): int {
		$text = self::to_text( $content );
		if ( '' === $text ) {
			return 0;
		}
		return (int) preg_match_all( '/[\p{L}\p{N}]+(?:[\'’\-][\p{L}\p{N}]+)*/u', $text );
	}

	/**
	 * Minutes to read, rounded up; 0 means "under a minute" (fewer words than
	 * the reader gets through in one minute).
	 *
	 * @param string $content          Raw content.
	 * @param int    $words_per_minute Reading speed.
	 * @return int
	 */
	public static function minutes( string $content, int $words_per_minute = self::DEFAULT_WPM ): int {
		$words = self::count_words( $content );
		if ( 0 === $words ) {
			return 0;
		}
		$wpm = max( 1, $words_per_minute );
		if ( $words < $wpm ) {
			return 0;
		}
		return (int) ceil( $words / $wpm );
	}
}

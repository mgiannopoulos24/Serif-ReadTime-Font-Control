<?php
/**
 * Inline SVG icons (Material Design Icons Light / Material Design Icons, Apache 2.0 — see readme.txt).
 *
 * @package Serif_ReadTime_Font_Control
 */

namespace Serif\ReadTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return an inline SVG from assets/icons, or an empty string.
 *
 * @param string $name Icon file name without extension.
 * @return string
 */
function icon( string $name ): string {
	static $cache = array();

	if ( ! isset( $cache[ $name ] ) ) {
		$file           = SERIF_RTFC_DIR . '/assets/icons/' . $name . '.svg';
		$svg            = file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		// Drop the source id: the same icon can appear many times on one page.
		$svg            = (string) preg_replace( '/\sid="[^"]*"/', '', $svg, 1 );
		$cache[ $name ] = $svg ? (string) preg_replace( '/<svg\s/', '<svg class="serif-rtfc-icon" aria-hidden="true" focusable="false" ', $svg, 1 ) : '';
	}

	return $cache[ $name ];
}

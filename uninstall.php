<?php
/**
 * Uninstall: remove the plugin's option. Reader preferences live in their
 * browsers' localStorage and are never stored server-side.
 *
 * @package Serif_ReadTime_Font_Control
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'serif_rtfc_font_control_enabled' );

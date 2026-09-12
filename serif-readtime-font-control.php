<?php
/**
 * Plugin Name:       Serif ReadTime & Font Control
 * Plugin URI:        https://github.com/mgiannopoulos24/serif-readtime-font-control
 * Description:       A "minutes to read" block and an accessible floating reading-controls widget (text size, line height, contrast) that remembers the reader's choices.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Tested up to:      7.1
 * Requires PHP:      8.1
 * Author:            Marios Giannopoulos
 * Author URI:        https://github.com/mgiannopoulos24
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       serif-readtime-font-control
 *
 * @package Serif_ReadTime_Font_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SERIF_RTFC_VERSION', '1.0.0' );
define( 'SERIF_RTFC_FILE', __FILE__ );
define( 'SERIF_RTFC_DIR', __DIR__ );
define( 'SERIF_RTFC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load bundled translations (languages/), for installs that don't get them from wordpress.org.
 */
function serif_rtfc_load_textdomain() {
	load_plugin_textdomain( 'serif-readtime-font-control', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); // phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound -- translations are bundled for installs that don't come from wordpress.org.
}
add_action( 'init', 'serif_rtfc_load_textdomain' );

require_once SERIF_RTFC_DIR . '/includes/class-read-time.php';
require_once SERIF_RTFC_DIR . '/includes/icons.php';
require_once SERIF_RTFC_DIR . '/includes/read-time-block.php';
require_once SERIF_RTFC_DIR . '/includes/font-control.php';
require_once SERIF_RTFC_DIR . '/includes/settings.php';

register_activation_hook(
	__FILE__,
	static function () {
		add_option( 'serif_rtfc_font_control_enabled', '1' );
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		// Reader preferences live in localStorage, nothing to clean up server-side.
	}
);

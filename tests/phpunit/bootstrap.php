<?php
/**
 * PHPUnit bootstrap. Read_Time is pure PHP, so no WordPress is loaded.
 *
 * @package Serif_ReadTime_Font_Control
 */

// The includes guard against direct access; satisfy the check without loading WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' );
}

require_once dirname( __DIR__, 2 ) . '/includes/class-read-time.php';

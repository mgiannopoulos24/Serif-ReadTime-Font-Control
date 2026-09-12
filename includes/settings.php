<?php
/**
 * Settings: one checkbox under Settings → Reading.
 *
 * @package Serif_ReadTime_Font_Control
 */

namespace Serif\ReadTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const OPTION_FONT_CONTROL = 'serif_rtfc_font_control_enabled';

/**
 * Whether the floating widget should render.
 *
 * @return bool
 */
function font_control_enabled(): bool {
	$enabled = '1' === get_option( OPTION_FONT_CONTROL, '1' );

	/**
	 * Filters whether the reading-controls widget renders on the front end.
	 *
	 * @param bool $enabled Setting value.
	 */
	return (bool) apply_filters( 'serif_font_control_enabled', $enabled );
}

/**
 * Register the setting and its field.
 */
function register_settings() {
	register_setting(
		'reading',
		OPTION_FONT_CONTROL,
		array(
			'type'              => 'string',
			'sanitize_callback' => static fn( $value ) => '1' === (string) $value ? '1' : '0',
			'default'           => '1',
			'show_in_rest'      => false,
		)
	);

	add_settings_field(
		OPTION_FONT_CONTROL,
		__( 'Reading controls', 'serif-readtime-font-control' ),
		static function () {
			printf(
				'<label><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label><p class="description">%4$s</p>',
				esc_attr( OPTION_FONT_CONTROL ),
				checked( font_control_enabled(), true, false ),
				esc_html__( 'Show the floating text size / line height / contrast widget', 'serif-readtime-font-control' ),
				esc_html__( 'Readers’ choices are stored in their browser only.', 'serif-readtime-font-control' )
			);
		},
		'reading'
	);
}
add_action( 'admin_init', __NAMESPACE__ . '\\register_settings' );

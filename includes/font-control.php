<?php
/**
 * Font Control widget: floating reading controls (text size, line height, contrast).
 *
 * Everything is driven by CSS custom properties and data attributes on <html>,
 * so it works with any block theme that uses --wp--preset--color-- variables.
 * Preferences are stored in localStorage and restored before first paint by a
 * tiny inline script in <head>.
 *
 * @package Serif_ReadTime_Font_Control
 */

namespace Serif\ReadTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const STORAGE_KEY = 'serif-font-control';

/**
 * Presets the widget can apply. Filterable so a theme can align them with its palette.
 *
 * @return array{scale: array{min: float, max: float, step: float}, lineHeights: float[], contrasts: array<string, string>}
 */
function font_control_config(): array {
	$config = array(
		'scale'       => array(
			'min'  => 0.8,
			'max'  => 1.5,
			'step' => 0.1,
		),
		'lineHeights' => array( 1.6, 1.8, 2.0 ),
		'contrasts'   => array(
			'default' => __( 'Default', 'serif-readtime-font-control' ),
			'high'    => __( 'High contrast', 'serif-readtime-font-control' ),
			'sepia'   => __( 'Sepia', 'serif-readtime-font-control' ),
		),
	);

	/**
	 * Filters the widget's ranges and presets.
	 *
	 * @param array $config See font_control_config().
	 */
	return (array) apply_filters( 'serif_font_control_config', $config );
}

/**
 * Restore saved preferences before first paint. Inline, tiny, no dependencies.
 */
function print_restore_script() {
	if ( ! font_control_enabled() ) {
		return;
	}
	$key = wp_json_encode( STORAGE_KEY );
	// Kept in one statement so it can be inlined safely with wp_print_inline_script_tag().
	$js = "try{var s=JSON.parse(localStorage.getItem($key)||'{}'),h=document.documentElement;"
		. "if(s.scale){h.style.setProperty('--serif-font-scale',s.scale);h.setAttribute('data-serif-font-scale',s.scale)}"
		. "if(s.lineHeight){h.style.setProperty('--serif-line-height',s.lineHeight);h.setAttribute('data-serif-line-height',s.lineHeight)}"
		. "if(s.contrast&&s.contrast!=='default'){h.setAttribute('data-serif-contrast',s.contrast)}}catch(e){}";
	wp_print_inline_script_tag( $js, array( 'id' => 'serif-font-control-restore' ) );
}
add_action( 'wp_head', __NAMESPACE__ . '\\print_restore_script', 1 );

/**
 * Enqueue the widget's CSS and JS.
 */
function enqueue_font_control_assets() {
	if ( ! font_control_enabled() ) {
		return;
	}

	wp_enqueue_style(
		'serif-font-control',
		SERIF_RTFC_URL . 'assets/css/font-control.css',
		array(),
		SERIF_RTFC_VERSION . '.' . (int) filemtime( SERIF_RTFC_DIR . '/assets/css/font-control.css' )
	);

	wp_enqueue_script(
		'serif-font-control',
		SERIF_RTFC_URL . 'assets/js/font-control.js',
		array(),
		SERIF_RTFC_VERSION . '.' . (int) filemtime( SERIF_RTFC_DIR . '/assets/js/font-control.js' ),
		array( 'strategy' => 'defer' )
	);

	wp_add_inline_script(
		'serif-font-control',
		'window.serifFontControl = ' . wp_json_encode(
			array(
				'storageKey' => STORAGE_KEY,
				'config'     => font_control_config(),
				'i18n'       => array(
					'scale' => __( 'Text size', 'serif-readtime-font-control' ),
				),
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_font_control_assets' );

/**
 * Render the widget markup in the footer.
 */
function render_font_control() {
	if ( ! font_control_enabled() || is_admin() ) {
		return;
	}
	$config = font_control_config();
	?>
	<aside class="serif-font-control" data-serif-font-control aria-label="<?php esc_attr_e( 'Reading controls', 'serif-readtime-font-control' ); ?>">
		<button type="button" class="serif-font-control__toggle" aria-expanded="false" aria-controls="serif-font-control-panel">
			<?php echo icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG shipped with the plugin. ?>
			<span class="serif-font-control__toggle-label"><?php esc_html_e( 'Reading controls', 'serif-readtime-font-control' ); ?></span>
		</button>

		<div class="serif-font-control__panel" id="serif-font-control-panel" role="group" aria-labelledby="serif-font-control-title" hidden>
			<div class="serif-font-control__header">
				<span class="serif-font-control__title" id="serif-font-control-title"><?php esc_html_e( 'Reading controls', 'serif-readtime-font-control' ); ?></span>
				<button type="button" class="serif-font-control__close" data-action="close" aria-label="<?php esc_attr_e( 'Close reading controls', 'serif-readtime-font-control' ); ?>">
					<?php echo icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>
			</div>

			<div class="serif-font-control__row" role="group" aria-label="<?php esc_attr_e( 'Text size', 'serif-readtime-font-control' ); ?>">
				<span class="serif-font-control__label"><?php esc_html_e( 'Text size', 'serif-readtime-font-control' ); ?></span>
				<div class="serif-font-control__stepper">
					<button type="button" class="serif-font-control__button" data-action="scale-down" aria-label="<?php esc_attr_e( 'Decrease text size', 'serif-readtime-font-control' ); ?>">
						<?php echo icon( 'minus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<output class="serif-font-control__value" data-output="scale" aria-live="polite">100%</output>
					<button type="button" class="serif-font-control__button" data-action="scale-up" aria-label="<?php esc_attr_e( 'Increase text size', 'serif-readtime-font-control' ); ?>">
						<?php echo icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>

			<div class="serif-font-control__row" role="group" aria-label="<?php esc_attr_e( 'Line height', 'serif-readtime-font-control' ); ?>">
				<span class="serif-font-control__label">
					<?php echo icon( 'format-line-spacing' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'Line height', 'serif-readtime-font-control' ); ?>
				</span>
				<div class="serif-font-control__choices">
					<?php foreach ( $config['lineHeights'] as $serif_lh ) : ?>
						<button type="button" class="serif-font-control__choice" data-action="line-height" data-value="<?php echo esc_attr( (string) $serif_lh ); ?>" aria-pressed="false"><?php echo esc_html( number_format_i18n( $serif_lh, 1 ) ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="serif-font-control__row" role="group" aria-label="<?php esc_attr_e( 'Contrast', 'serif-readtime-font-control' ); ?>">
				<span class="serif-font-control__label"><?php esc_html_e( 'Contrast', 'serif-readtime-font-control' ); ?></span>
				<div class="serif-font-control__choices">
					<?php foreach ( $config['contrasts'] as $serif_slug => $serif_name ) : ?>
						<button type="button" class="serif-font-control__choice serif-font-control__choice--<?php echo esc_attr( $serif_slug ); ?>" data-action="contrast" data-value="<?php echo esc_attr( $serif_slug ); ?>" aria-pressed="false"><?php echo esc_html( $serif_name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>

			<button type="button" class="serif-font-control__reset" data-action="reset">
				<?php echo icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php esc_html_e( 'Reset', 'serif-readtime-font-control' ); ?>
			</button>
		</div>
	</aside>
	<?php
}
add_action( 'wp_footer', __NAMESPACE__ . '\\render_font_control', 20 );

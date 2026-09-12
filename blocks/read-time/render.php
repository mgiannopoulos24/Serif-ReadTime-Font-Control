<?php
/**
 * Render serif/read-time.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner content (unused).
 * @var WP_Block $block      Block instance (provides postId context).
 *
 * @package Serif_ReadTime_Font_Control
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Serif\ReadTime\Read_Time;
use function Serif\ReadTime\icon;

$serif_post_id = $block->context['postId'] ?? get_the_ID();

// No post in context (e.g. editing a template part): show sample output in the editor, nothing on the front end.
$serif_is_sample = ! $serif_post_id && defined( 'REST_REQUEST' ) && REST_REQUEST;
if ( ! $serif_post_id && ! $serif_is_sample ) {
	return;
}

$serif_wpm = max( 1, (int) ( $attributes['wordsPerMinute'] ?? Read_Time::DEFAULT_WPM ) );

/**
 * Filters the reading speed used for a post.
 *
 * @param int $wpm     Words per minute.
 * @param int $post_id Post ID.
 */
$serif_wpm = (int) apply_filters( 'serif_read_time_words_per_minute', $serif_wpm, (int) $serif_post_id );

$serif_minutes = $serif_is_sample ? 4 : Read_Time::minutes( (string) get_post_field( 'post_content', $serif_post_id ), $serif_wpm );

if ( 0 === $serif_minutes ) {
	$serif_text = __( 'Under a minute', 'serif-readtime-font-control' );
} else {
	/* translators: %s: number of minutes. */
	$serif_text = sprintf( _n( '%s min read', '%s min read', $serif_minutes, 'serif-readtime-font-control' ), number_format_i18n( $serif_minutes ) );
}

$serif_label = trim( (string) ( $attributes['label'] ?? '' ) );
if ( '' !== $serif_label ) {
	$serif_text = $serif_label . ' ' . $serif_text;
}

/**
 * Filters the rendered reading-time text.
 *
 * @param string $text    e.g. "4 min read".
 * @param int    $minutes Minutes (0 = under a minute).
 * @param int    $post_id Post ID (0 for the editor's sample preview).
 */
$serif_text = (string) apply_filters( 'serif_read_time_text', $serif_text, $serif_minutes, (int) $serif_post_id );

$serif_icon = ! empty( $attributes['showIcon'] ) ? icon( 'clock' ) : '';
?>
<span <?php echo get_block_wrapper_attributes( array( 'class' => 'serif-read-time' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?>>
	<?php echo $serif_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG shipped with the plugin. ?>
	<span class="serif-read-time__text"><?php echo esc_html( $serif_text ); ?></span>
</span>

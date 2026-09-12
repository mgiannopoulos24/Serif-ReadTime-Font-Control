<?php
/**
 * Register the Reading Time block and place it automatically in the Serif theme.
 *
 * @package Serif_ReadTime_Font_Control
 */

namespace Serif\ReadTime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register serif/read-time.
 */
function register_read_time_block() {
	register_block_type( SERIF_RTFC_DIR . '/blocks/read-time' );
	// Inspector strings in editor.js (JSON translations built by `bun run makejson`).
	wp_set_script_translations( 'serif-read-time-editor-script', 'serif-readtime-font-control', SERIF_RTFC_DIR . '/languages' );
}
add_action( 'init', __NAMESPACE__ . '\\register_read_time_block' );

/**
 * ServerSideRender in the editor passes the post being edited as ?post_id.
 * Make it available as block context so render.php reads the right content.
 *
 * @param array $context Block context.
 * @return array
 */
function editor_preview_context( $context ) {
	if ( empty( $context['postId'] ) && defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preview.
		$context['postId'] = absint( $_GET['post_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	return $context;
}
add_filter( 'render_block_context', __NAMESPACE__ . '\\editor_preview_context' );

/**
 * In the Serif theme, hook the block after the date in the post-meta part.
 *
 * Uses the Block Hooks API, so templates need no editing and users can still
 * move or remove the block in the Site Editor.
 *
 * @param string[]                          $hooked_blocks     Hooked block names.
 * @param string                            $relative_position before|after|first_child|last_child.
 * @param string|null                       $anchor_block      Anchor block name.
 * @param \WP_Block_Template|\WP_Post|array $context           Template, part, pattern or post.
 * @return string[]
 */
function hook_into_post_meta( $hooked_blocks, $relative_position, $anchor_block, $context ) {
	if ( 'serif' !== get_template() || 'core/post-date' !== $anchor_block || 'after' !== $relative_position ) {
		return $hooked_blocks;
	}
	if ( ! $context instanceof \WP_Block_Template || 'post-meta' !== $context->slug ) {
		return $hooked_blocks;
	}
	$hooked_blocks[] = 'serif/read-time';
	return $hooked_blocks;
}
add_filter( 'hooked_block_types', __NAMESPACE__ . '\\hook_into_post_meta', 10, 4 );

/**
 * Match the hooked instance to the meta row's styling (small font, like the date).
 *
 * @param array|null $parsed_hooked_block The hooked block.
 * @return array|null
 */
function hooked_block_attributes( $parsed_hooked_block ) {
	if ( is_array( $parsed_hooked_block ) ) {
		$parsed_hooked_block['attrs']['fontSize'] = 'small';
	}
	return $parsed_hooked_block;
}
add_filter( 'hooked_block_serif/read-time', __NAMESPACE__ . '\\hooked_block_attributes' );

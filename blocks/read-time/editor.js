/**
 * Editor registration for serif/read-time: server-rendered preview plus
 * inspector controls. No build step — uses the wp.* globals from editor.asset.php.
 */
( function ( blocks, element, blockEditor, components, serverSideRender, i18n ) {
	const { createElement: el, Fragment } = element;
	const { InspectorControls, useBlockProps } = blockEditor;
	const { PanelBody, ToggleControl, TextControl, RangeControl } = components;
	const { __ } = i18n;

	blocks.registerBlockType( 'serif/read-time', {
		edit: function ( props ) {
			const { attributes, setAttributes, context } = props;
			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Reading time', 'serif-readtime-font-control' ) },
						el( ToggleControl, {
							label: __( 'Show clock icon', 'serif-readtime-font-control' ),
							checked: !! attributes.showIcon,
							onChange: ( v ) => setAttributes( { showIcon: v } ),
						} ),
						el( TextControl, {
							label: __( 'Label before the time', 'serif-readtime-font-control' ),
							value: attributes.label,
							placeholder: __( 'e.g. Reading time:', 'serif-readtime-font-control' ),
							onChange: ( v ) => setAttributes( { label: v } ),
						} ),
						el( RangeControl, {
							label: __( 'Words per minute', 'serif-readtime-font-control' ),
							value: attributes.wordsPerMinute,
							min: 100,
							max: 400,
							step: 10,
							onChange: ( v ) => setAttributes( { wordsPerMinute: v } ),
						} )
					)
				),
				el(
					'div',
					useBlockProps(),
					el( serverSideRender, {
						block: 'serif/read-time',
						attributes,
						urlQueryArgs: { post_id: context.postId },
					} )
				)
			);
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender, window.wp.i18n );

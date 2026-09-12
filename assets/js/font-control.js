/**
 * Font Control widget: text size, line height, contrast.
 *
 * State lives in localStorage and on <html>; the inline head script restores
 * it before first paint, this file only keeps the UI and the state in sync.
 * The <html> contract (property names, attributes) is shared with that script
 * — change both or neither.
 */
( function () {
	'use strict';

	var settings = window.serifFontControl || {};
	var config = settings.config || {};
	var scaleCfg = config.scale || { min: 0.8, max: 1.5, step: 0.1 };
	var storageKey = settings.storageKey || 'serif-font-control';

	var root = document.querySelector( '[data-serif-font-control]' );
	if ( ! root ) {
		return;
	}

	var html = document.documentElement;
	var toggle = root.querySelector( '.serif-font-control__toggle' );
	var panel = root.querySelector( '.serif-font-control__panel' );
	var output = root.querySelector( '[data-output="scale"]' );
	var scaleDown = root.querySelector( '[data-action="scale-down"]' );
	var scaleUp = root.querySelector( '[data-action="scale-up"]' );
	var choices = root.querySelectorAll( '[data-action="line-height"], [data-action="contrast"]' );

	/* ---------------------------------------------------------------------
	 * State
	 * ------------------------------------------------------------------ */

	function round( n ) {
		return Math.round( n * 100 ) / 100;
	}

	function readState() {
		try {
			var saved = JSON.parse( localStorage.getItem( storageKey ) || '{}' );
			return saved && typeof saved === 'object' ? saved : {};
		} catch ( e ) {
			return {};
		}
	}

	function writeState( state ) {
		try {
			if ( Object.keys( state ).length ) {
				localStorage.setItem( storageKey, JSON.stringify( state ) );
			} else {
				localStorage.removeItem( storageKey );
			}
		} catch ( e ) {
			// Private mode / storage disabled: preferences still apply for this page.
		}
	}

	/** Apply state to <html>. Mirrors the inline head script. */
	function apply( state ) {
		if ( state.scale ) {
			html.style.setProperty( '--serif-font-scale', String( state.scale ) );
			html.setAttribute( 'data-serif-font-scale', String( state.scale ) );
		} else {
			html.style.removeProperty( '--serif-font-scale' );
			html.removeAttribute( 'data-serif-font-scale' );
		}

		if ( state.lineHeight ) {
			html.style.setProperty( '--serif-line-height', String( state.lineHeight ) );
			html.setAttribute( 'data-serif-line-height', String( state.lineHeight ) );
		} else {
			html.style.removeProperty( '--serif-line-height' );
			html.removeAttribute( 'data-serif-line-height' );
		}

		if ( state.contrast && state.contrast !== 'default' ) {
			html.setAttribute( 'data-serif-contrast', state.contrast );
		} else {
			html.removeAttribute( 'data-serif-contrast' );
		}
	}

	var state = readState();

	function update( changes ) {
		Object.keys( changes ).forEach( function ( key ) {
			if ( changes[ key ] === null || changes[ key ] === undefined ) {
				delete state[ key ];
			} else {
				state[ key ] = changes[ key ];
			}
		} );
		apply( state );
		writeState( state );
		syncUI();
	}

	/* ---------------------------------------------------------------------
	 * UI sync
	 * ------------------------------------------------------------------ */

	function setDisabled( button, disabled ) {
		if ( button ) {
			button.setAttribute( 'aria-disabled', disabled ? 'true' : 'false' );
		}
	}

	function syncUI() {
		var scale = Number( state.scale ) || 1;
		var lineHeight = state.lineHeight ? String( state.lineHeight ) : '';
		var contrast = state.contrast || 'default';

		if ( output ) {
			output.value = Math.round( scale * 100 ) + '%';
		}
		setDisabled( scaleDown, scale <= scaleCfg.min + 0.001 );
		setDisabled( scaleUp, scale >= scaleCfg.max - 0.001 );

		Array.prototype.forEach.call( choices, function ( button ) {
			var pressed;
			if ( button.dataset.action === 'line-height' ) {
				pressed = Number( button.dataset.value ) === Number( lineHeight );
			} else {
				pressed = button.dataset.value === contrast;
			}
			button.setAttribute( 'aria-pressed', pressed ? 'true' : 'false' );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Panel open / close
	 * ------------------------------------------------------------------ */

	function isOpen() {
		return ! panel.hidden;
	}

	function open() {
		panel.hidden = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		var first = panel.querySelector( '.serif-font-control__row button' );
		if ( first ) {
			first.focus();
		}
		document.addEventListener( 'keydown', onKeydown );
		document.addEventListener( 'pointerdown', onPointerDown );
	}

	function close( returnFocus ) {
		panel.hidden = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.removeEventListener( 'keydown', onKeydown );
		document.removeEventListener( 'pointerdown', onPointerDown );
		if ( returnFocus ) {
			toggle.focus();
		}
	}

	function onKeydown( event ) {
		if ( event.key === 'Escape' || event.key === 'Esc' ) {
			event.preventDefault();
			close( true );
		}
	}

	function onPointerDown( event ) {
		if ( ! root.contains( event.target ) ) {
			close( false );
		}
	}

	toggle.addEventListener( 'click', function () {
		if ( isOpen() ) {
			close( true );
		} else {
			open();
		}
	} );

	/* ---------------------------------------------------------------------
	 * Controls
	 * ------------------------------------------------------------------ */

	function stepScale( direction ) {
		var current = Number( state.scale ) || 1;
		var next = round( current + direction * scaleCfg.step );
		next = Math.min( scaleCfg.max, Math.max( scaleCfg.min, next ) );
		if ( next === current ) {
			return;
		}
		update( { scale: next === 1 ? null : next } );
	}

	panel.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '[data-action]' );
		if ( ! button || button.getAttribute( 'aria-disabled' ) === 'true' ) {
			return;
		}

		switch ( button.dataset.action ) {
			case 'scale-down':
				stepScale( -1 );
				break;
			case 'scale-up':
				stepScale( 1 );
				break;
			case 'line-height':
				update( { lineHeight: Number( button.dataset.value ) || null } );
				break;
			case 'contrast':
				update( { contrast: button.dataset.value === 'default' ? null : button.dataset.value } );
				break;
			case 'close':
				close( true );
				break;
			case 'reset':
				state = {};
				apply( state );
				writeState( state );
				syncUI();
				break;
		}
	} );

	// The head script already applied the saved state; make the UI agree.
	syncUI();
} )();

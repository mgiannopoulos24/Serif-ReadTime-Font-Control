import { test, expect } from '@playwright/test';
import { routes, gotoReady, widget, openWidget, htmlState, storedState, STORAGE_KEY } from './helpers';

test.describe( 'Font Control widget', () => {
	test( 'renders closed with correct ARIA', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = widget( page );
		await expect( w.toggle ).toBeVisible();
		await expect( w.toggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( w.toggle ).toHaveAttribute( 'aria-controls', 'serif-font-control-panel' );
		await expect( w.panel ).toBeHidden();
		await expect( w.toggle ).toHaveAccessibleName( /reading controls/i );
	} );

	test( 'open focuses the first control; Escape closes and returns focus', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await expect( w.toggle ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( w.scaleDown ).toBeFocused();

		await page.keyboard.press( 'Escape' );
		await expect( w.panel ).toBeHidden();
		await expect( w.toggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( w.toggle ).toBeFocused();
	} );

	test( 'close button closes the panel and returns focus', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await expect( w.close ).toHaveAccessibleName( /close/i );
		await w.close.click();
		await expect( w.panel ).toBeHidden();
		await expect( w.toggle ).toHaveAttribute( 'aria-expanded', 'false' );
		await expect( w.toggle ).toBeFocused();
	} );

	test( 'toggle is a 48px circle', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = widget( page );
		const box = await w.toggle.boundingBox();
		expect( box.width ).toBe( 48 );
		expect( box.height ).toBe( 48 );
		await expect( w.toggle ).toHaveCSS( 'border-radius', '50%' );
		await expect( w.toggle ).toHaveCSS( 'color', 'rgb(255, 255, 255)' );
	} );

	test( 'click outside closes the panel', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await page.mouse.click( 10, 10 );
		await expect( w.panel ).toBeHidden();
	} );

	test( 'text size steps scale the root font size', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const before = await htmlState( page );
		const w = await openWidget( page );

		await expect( w.scaleOutput ).toHaveText( '100%' );
		await w.scaleUp.click();
		await w.scaleUp.click();

		await expect( w.scaleOutput ).toHaveText( '120%' );
		const after = await htmlState( page );
		expect( after.scale ).toBe( '1.2' );
		expect( after.scaleVar ).toBe( '1.2' );
		expect( after.fontSize ).toBeCloseTo( before.fontSize * 1.2, 1 );

		expect( JSON.parse( ( await storedState( page ) ) ?? '{}' ) ).toMatchObject( { scale: 1.2 } );
	} );

	test( 'stepper stops at the bounds without losing focus', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		for ( let i = 0; i < 5; i++ ) {
			await w.scaleUp.click();
		}
		await expect( w.scaleOutput ).toHaveText( '150%' );
		await expect( w.scaleUp ).toHaveAttribute( 'aria-disabled', 'true' );
		await expect( w.scaleUp ).not.toHaveAttribute( 'disabled' ); // aria-disabled keeps it focusable
		await w.scaleUp.click( { force: true } );
		await expect( w.scaleOutput ).toHaveText( '150%' );

		for ( let i = 0; i < 7; i++ ) {
			await w.scaleDown.click();
		}
		await expect( w.scaleOutput ).toHaveText( '80%' );
		await expect( w.scaleDown ).toHaveAttribute( 'aria-disabled', 'true' );
	} );

	test( 'line height applies to paragraphs', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await w.lineHeight( '2' ).click();

		await expect( w.lineHeight( '2' ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( w.lineHeight( '1.6' ) ).toHaveAttribute( 'aria-pressed', 'false' );
		expect( ( await htmlState( page ) ).lineHeight ).toBe( '2' );

		const ratio = await page.evaluate( () => {
			const p = document.querySelector( '.wp-block-post-content p' );
			const cs = getComputedStyle( p );
			return parseFloat( cs.lineHeight ) / parseFloat( cs.fontSize );
		} );
		expect( ratio ).toBeCloseTo( 2, 1 );
	} );

	test( 'contrast presets swap the palette', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );

		await w.contrast( 'high' ).click();
		await expect( w.contrast( 'high' ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( w.contrast( 'default' ) ).toHaveAttribute( 'aria-pressed', 'false' );
		expect( ( await htmlState( page ) ).contrast ).toBe( 'high' );
		await expect( page.locator( 'body' ) ).toHaveCSS( 'background-color', 'rgb(0, 0, 0)' );
		await expect( page.locator( 'body' ) ).toHaveCSS( 'color', 'rgb(255, 255, 255)' );

		await w.contrast( 'sepia' ).click();
		expect( ( await htmlState( page ) ).contrast ).toBe( 'sepia' );
		await expect( page.locator( 'body' ) ).toHaveCSS( 'background-color', 'rgb(243, 233, 214)' );

		await w.contrast( 'default' ).click();
		expect( ( await htmlState( page ) ).contrast ).toBeNull();
		await expect( w.contrast( 'default' ) ).toHaveAttribute( 'aria-pressed', 'true' );
	} );

	test( 'preferences persist and are restored before load', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await w.scaleUp.click();
		await w.lineHeight( '1.8' ).click();
		await w.contrast( 'sepia' ).click();

		// Navigate without waiting for scripts: the head script must already have run.
		await page.goto( routes.page, { waitUntil: 'commit' } );
		const early = await page.evaluate( () => {
			const h = document.documentElement;
			return [ h.getAttribute( 'data-serif-font-scale' ), h.getAttribute( 'data-serif-line-height' ), h.getAttribute( 'data-serif-contrast' ) ];
		} );
		expect( early ).toEqual( [ '1.1', '1.8', 'sepia' ] );

		// And the widget's UI agrees once its script has run.
		await page.waitForLoadState( 'networkidle' );
		const w2 = await openWidget( page );
		await expect( w2.scaleOutput ).toHaveText( '110%' );
		await expect( w2.lineHeight( '1.8' ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( w2.contrast( 'sepia' ) ).toHaveAttribute( 'aria-pressed', 'true' );
	} );

	test( 'reset clears everything', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await w.scaleUp.click();
		await w.lineHeight( '1.6' ).click();
		await w.contrast( 'high' ).click();
		await w.reset.click();

		expect( await htmlState( page ) ).toMatchObject( { scale: null, scaleVar: '', lineHeight: null, contrast: null } );
		expect( await storedState( page ) ).toBeNull();
		await expect( w.scaleOutput ).toHaveText( '100%' );
		await expect( w.contrast( 'default' ) ).toHaveAttribute( 'aria-pressed', 'true' );
		await expect( w.lineHeight( '1.6' ) ).toHaveAttribute( 'aria-pressed', 'false' );
	} );

	test( 'ignores corrupt storage', async ( { page } ) => {
		await page.goto( routes.single );
		await page.evaluate( ( key ) => localStorage.setItem( key, '{not json' ), STORAGE_KEY );
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		await expect( w.scaleOutput ).toHaveText( '100%' );
		await w.scaleUp.click();
		await expect( w.scaleOutput ).toHaveText( '110%' );
	} );

	test( 'is hidden in print', async ( { page } ) => {
		await gotoReady( page, routes.single );
		await page.emulateMedia( { media: 'print' } );
		await expect( widget( page ).root ).toBeHidden();
	} );
} );

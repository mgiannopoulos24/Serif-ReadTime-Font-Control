import { test, expect } from '@playwright/test';
import { routes, gotoReady } from './helpers';

test.describe( 'Reading Time block', () => {
	test( 'is hooked into the Serif post-meta row on single posts', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const block = page.locator( '.wp-block-serif-read-time' ).first();
		await expect( block ).toBeVisible();
		await expect( block ).toHaveText( /\d+ min read/ );
		await expect( block.locator( 'svg' ) ).toHaveAttribute( 'aria-hidden', 'true' );

		// Directly after the date, inside the same meta row.
		const order = await page.evaluate( () => {
			const date = document.querySelector( '.wp-block-post-date' );
			return date?.nextElementSibling?.classList.contains( 'wp-block-serif-read-time' );
		} );
		expect( order ).toBe( true );
	} );

	test( 'short posts read "Under a minute" or a small number', async ( { page } ) => {
		await gotoReady( page, routes[ 'single (kitchen sink)' ] );
		await expect( page.locator( '.wp-block-serif-read-time' ).first() ).toHaveText( /Under a minute|^\s*[1-3] min read\s*$/ );
	} );

	test( 'is absent on pages', async ( { page } ) => {
		await gotoReady( page, routes.page );
		await expect( page.locator( '.wp-block-serif-read-time' ) ).toHaveCount( 0 );
	} );
} );

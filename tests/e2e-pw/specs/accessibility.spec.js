import { test, expect } from '@playwright/test';
import { routes, gotoReady, expectAccessible, openWidget } from './helpers';

/**
 * axe-core scans (WCAG 2.2 AA + best practices): the widget closed and open,
 * under every contrast preset, plus a page with the hooked block.
 */
test.describe( 'Accessibility (axe)', () => {
	test( 'single post with block and widget closed', async ( { page } ) => {
		await gotoReady( page, routes.single );
		await expectAccessible( page, 'single (widget closed)' );
	} );

	test( 'page with widget closed', async ( { page } ) => {
		await gotoReady( page, routes.page );
		await expectAccessible( page, 'page (widget closed)' );
	} );

	test( 'widget open', async ( { page } ) => {
		await gotoReady( page, routes.single );
		await openWidget( page );
		await expectAccessible( page, 'widget open' );
	} );

	for ( const preset of [ 'high', 'sepia' ] ) {
		test( `widget open — ${ preset } contrast`, async ( { page } ) => {
			await gotoReady( page, routes.single );
			const w = await openWidget( page );
			await w.contrast( preset ).click();
			await expect( w.contrast( preset ) ).toHaveAttribute( 'aria-pressed', 'true' );
			await expectAccessible( page, `widget open (${ preset })` );

			await page.keyboard.press( 'Escape' );
			await expectAccessible( page, `widget closed (${ preset })` );
		} );
	}

	test( 'largest text size', async ( { page } ) => {
		await gotoReady( page, routes.single );
		const w = await openWidget( page );
		for ( let i = 0; i < 5; i++ ) {
			await w.scaleUp.click();
		}
		await expectAccessible( page, 'widget open (150%)' );
	} );
} );

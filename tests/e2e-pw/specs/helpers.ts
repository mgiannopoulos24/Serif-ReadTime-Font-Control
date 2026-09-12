import { expect } from '@playwright/test';
import type { Page } from '@playwright/test';
import { AxeBuilder } from '@axe-core/playwright';
import type { Result as AxeViolation } from 'axe-core';

/** Routes created by the Serif theme's scripts/seed.sh. */
export const routes = {
	single: '/slow-return-long-read/',
	'single (kitchen sink)': '/kitchen-sink/',
	page: '/about/',
	home: '/journal/',
} as const;

export const STORAGE_KEY = 'serif-font-control';

/** WCAG 2.2 AA plus axe best practices. */
export const axeTags = [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice' ];

export async function gotoReady( page: Page, path: string ) {
	await page.goto( path, { waitUntil: 'networkidle' } );
	await page.evaluate( () => document.fonts.ready );
}

function describeViolations( violations: AxeViolation[] ) {
	return violations
		.map( ( v ) => {
			const nodes = v.nodes.slice( 0, 5 ).map( ( n ) => `      - ${ n.target.join( ' ' ) }\n        ${ n.failureSummary?.split( '\n' ).join( '\n        ' ) }` ).join( '\n' );
			return `  [${ v.impact }] ${ v.id }: ${ v.help } (${ v.helpUrl })\n${ nodes }`;
		} )
		.join( '\n\n' );
}

/** Run axe on the current page state and fail with a readable report. */
export async function expectAccessible( page: Page, label: string, options: { exclude?: string[]; disableRules?: string[] } = {} ) {
	let builder = new AxeBuilder( { page } ).withTags( axeTags );
	for ( const sel of options.exclude ?? [] ) {
		builder = builder.exclude( sel );
	}
	if ( options.disableRules?.length ) {
		builder = builder.disableRules( options.disableRules );
	}
	const results = await builder.analyze();
	expect( results.violations, `axe violations on ${ label }:\n${ describeViolations( results.violations ) }` ).toEqual( [] );
}

/** Locators for the Font Control widget. */
export function widget( page: Page ) {
	const root = page.locator( '[data-serif-font-control]' );
	return {
		root,
		toggle: root.locator( '.serif-font-control__toggle' ),
		panel: root.locator( '#serif-font-control-panel' ),
		scaleUp: root.locator( '[data-action="scale-up"]' ),
		scaleDown: root.locator( '[data-action="scale-down"]' ),
		scaleOutput: root.locator( '[data-output="scale"]' ),
		lineHeight: ( value: string ) => root.locator( `[data-action="line-height"][data-value="${ value }"]` ),
		contrast: ( value: string ) => root.locator( `[data-action="contrast"][data-value="${ value }"]` ),
		reset: root.locator( '[data-action="reset"]' ),
		close: root.locator( '[data-action="close"]' ),
	};
}

export async function openWidget( page: Page ) {
	const w = widget( page );
	await w.toggle.click();
	await expect( w.panel ).toBeVisible();
	return w;
}

/** Read the <html> state the head script / widget JS maintain. */
export function htmlState( page: Page ) {
	return page.evaluate( () => {
		const h = document.documentElement;
		return {
			scale: h.getAttribute( 'data-serif-font-scale' ),
			scaleVar: h.style.getPropertyValue( '--serif-font-scale' ),
			lineHeight: h.getAttribute( 'data-serif-line-height' ),
			contrast: h.getAttribute( 'data-serif-contrast' ),
			fontSize: parseFloat( getComputedStyle( h ).fontSize ),
		};
	} );
}

export function storedState( page: Page ) {
	return page.evaluate( ( key ) => localStorage.getItem( key ), STORAGE_KEY );
}

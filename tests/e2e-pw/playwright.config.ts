import { defineConfig, devices } from '@playwright/test';

/**
 * Serif ReadTime & Font Control end-to-end + accessibility tests.
 *
 *   bun run test:e2e
 *
 * Expects the wp-env site (bun run start) — it is started if not reachable.
 * The Serif theme's seed script gives the templates real content.
 *
 * Specs are plain JS (with JSDoc types): when Playwright runs under Bun, its
 * TypeScript transform is bypassed for test files. Shared helpers can be TS.
 */
const baseURL = process.env.WP_BASE_URL ?? 'http://localhost:8888';

export default defineConfig( {
	testDir: './specs',
	fullyParallel: true,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: [
		[ 'list' ],
		[ 'html', { open: 'never', outputFolder: 'playwright-report' } ],
	],
	outputDir: 'test-results',
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{
			name: 'desktop',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
		{
			name: 'mobile',
			use: { ...devices[ 'Pixel 7' ] },
		},
	],
	webServer: {
		command: 'bun run start',
		url: baseURL,
		reuseExistingServer: true,
		timeout: 300_000,
	},
} );

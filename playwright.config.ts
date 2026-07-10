import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60000,
  // The CI suite targets one shared QA site, so concurrent browser sessions
  // must not race over its lazy-loading state or third-party integrations.
  workers: process.env.CI ? 1 : 5,
  retries: process.env.CI ? 2 : 0,
  reporter: process.env.CI
    ? [['line'], ['html', { open: 'never', outputFolder: 'playwright-report' }]]
    : 'list',
  use: {
    // CI supplies the shared QA URL. Local runs are deliberately local by
    // default to avoid exercising the mutable QA site accidentally.
    baseURL: process.env.E2E_BASE_URL || 'http://localhost',
    trace: 'on-first-retry',
    navigationTimeout: 45000,
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    viewport: { width: 1280, height: 720 },
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
    {
      name: 'firefox',
      use: { browserName: 'firefox' },
    },
  ]
});

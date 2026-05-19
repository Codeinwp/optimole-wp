import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60000,
  workers: process.env.CI ? 2 : 5,
  retries: process.env.CI ? 2 : 0,
  use: {
    baseURL: 'http://testing.optimole.com', // Replace with your local WordPress URL
    trace: 'on-first-retry',
    navigationTimeout: 45000,
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
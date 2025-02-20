import { test, expect } from '@playwright/test';

test.describe('Check Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/beaver/');
  });

  test('Beaver column should have background lazyloaded', async ({ page }) => {
    const column = page.locator('.entry-content .fl-col-content').first();
    await expect(column).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('Beaver row content should have background lazyloaded', async ({ page }) => {
    const row = page.locator('.entry-content .fl-row-bg-photo > .fl-row-content-wrap').first();
    await expect(row).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('Beaver row content not in view should have no background', async ({ page }) => {
    const row = page.locator('.entry-content .fl-row-bg-photo > .fl-row-content-wrap').nth(1);
    await expect(row).toHaveCSS('background-image', /none/);
  });

  test('After scroll the background images should be loaded', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 2500));

    const column = page.locator('.entry-content .fl-col-content').nth(4);
    await expect(column).toHaveClass(/optml-bg-lazyloaded/);

    const row = page.locator('.entry-content .fl-row-bg-photo > .fl-row-content-wrap').nth(1);
    await expect(row).toHaveClass(/optml-bg-lazyloaded/);
  });
}); 
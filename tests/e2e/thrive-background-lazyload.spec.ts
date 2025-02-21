import { test, expect } from '@playwright/test';

test.describe('Check Thrive Background Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/thrive/');
  });

  test('tve-content-box-background in view should have background lazyloaded', async ({ page }) => {
    const box = page.locator('.entry-content .tve-content-box-background').first();
    await expect(box).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('thrv_text_element in view should have background lazyloaded', async ({ page }) => {
    const text = page.locator('.entry-content .thrv_text_element').first();
    await expect(text).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('Elements not in view should have no background', async ({ page }) => {
    const box = page.locator('.entry-content .tve-content-box-background').nth(1);
    await expect(box).toHaveCSS('background-image', /none/);

    const section = page.locator('.entry-content .tve-page-section-out').nth(1);
    await expect(section).toHaveCSS('background-image', /none/);

    const text = page.locator('.entry-content .thrv_text_element').nth(2);
    await expect(text).toHaveCSS('background-image', /none/);
  });

  test('After scroll backgrounds should be loaded', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 3000));

    const box = page.locator('.entry-content .tve-content-box-background').nth(1);
    await expect(box).toHaveClass(/optml-bg-lazyloaded/);

    const section = page.locator('.entry-content .tve-page-section-out').nth(1);
    await expect(section).toHaveClass(/optml-bg-lazyloaded/);

    const text = page.locator('.entry-content .thrv_text_element').nth(2);
    await expect(text).toHaveClass(/optml-bg-lazyloaded/);
  });
}); 
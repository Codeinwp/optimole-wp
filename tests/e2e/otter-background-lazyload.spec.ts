import { test, expect } from '@playwright/test';

test.describe('Check Otter Background Lazyload', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/otter/background-lazyload/');
  });

  test('Otter Flip block front should have background lazyloaded', async ({ page }) => {
    const front = page.locator('.wp-block-themeisle-blocks-flip .o-flip-front').first();
    await expect(front).toHaveClass(/optml-bg-lazyloaded/);
    await expect(front).toHaveCSS('background-image', /url\(.*\.i\.optimole\.com.*\)/);
  });

  test('Otter Flip block back should have background lazyloaded', async ({ page }) => {
    const back = page.locator('.wp-block-themeisle-blocks-flip .o-flip-back').first();
    await expect(back).toHaveClass(/optml-bg-lazyloaded/);
    await expect(back).toHaveCSS('background-image', /url\(.*\.i\.optimole\.com.*\)/);
  });

  test('Otter Section Block should have background lazyloaded', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 500));
    
    const section = page.locator('#wp-block-themeisle-blocks-advanced-columns-e62611eb').first();
    await expect(section).toHaveClass(/optml-bg-lazyloaded/);
    await expect(section).toHaveCSS('background-image', /url\(.*\.i\.optimole\.com.*\)/);

    const overlay = section.locator('.wp-block-themeisle-blocks-advanced-columns-overlay').first();
    await expect(overlay).toHaveClass(/optml-bg-lazyloaded/);
    await expect(overlay).toHaveCSS('background-image', /url\(.*\.i\.optimole\.com.*\)/);
  });
}); 
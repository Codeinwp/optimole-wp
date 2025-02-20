import { test, expect } from '@playwright/test';

test.describe('Check Metaslider Background Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/metaslider/');
  });

  test('Slider background images in view should be lazyloaded', async ({ page }) => {
    const slider = page.locator('.entry-content .coin-slider > .coin-slider').first();
    await expect(slider).toHaveClass(/optml-bg-lazyloaded/);

    for (let i = 4; i <= 30; i++) {
      const slide = page.locator('.entry-content .coin-slider > .coin-slider > a').nth(i);
      await expect(slide).toHaveClass(/optml-bg-lazyloaded/);
    }
  });

  test('After scroll slider background images not in view should have no background', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 2500));

    const slider = page.locator('.entry-content .coin-slider > .coin-slider').nth(1);
    await expect(slider).toHaveCSS('background-image', /none/);

    for (let i = 35; i <= 67; i++) {
      const slide = page.locator('.entry-content .coin-slider > .coin-slider > a').nth(i);
      await expect(slide).toHaveCSS('background-image', /none/);
    }
  });
}); 
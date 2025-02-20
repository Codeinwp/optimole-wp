import { test, expect } from '@playwright/test';

test.describe('Check foo gallery page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/foo-gallery/');
  });

  test('Gallery images all attributes replaced properly', async ({ page }) => {
    const images = await page.locator('.foogallery img').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('data-src-fg', /i\.optimole\.com/);
    }
  });

  test('Gallery images should never have lazyload on', async ({ page }) => {
    const images = await page.locator('.foogallery img').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).not.toHaveAttribute('data-opt-src');
    }
  });
}); 
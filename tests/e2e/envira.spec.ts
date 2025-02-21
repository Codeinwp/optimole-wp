import { test, expect } from '@playwright/test';

test.describe('Check envira page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/envira-test-page/');
  });

  test('Gallery images all attributes replaced properly', async ({ page }) => {
    const images =  await page.locator('#envira-gallery-wrap-92 .envira-gallery-image').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-envira-src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-envira-srcset', /i\.optimole\.com/);
    }

    const safeImages = await page.locator('#envira-gallery-wrap-97 .envira-gallery-image').all();
    for (const image of safeImages) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('data-safe-src', /i\.optimole\.com/);
    }

    const links = await page.locator('.envira-gallery-link').all();
    for (const link of links) {
      await link.scrollIntoViewIfNeeded();
      await expect(link).toHaveAttribute('href', /i\.optimole\.com/);
    }
  });

  test('Gallery images should never have lazyload on', async ({ page }) => {
    const images = await page.locator('.envira-gallery-image').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).not.toHaveAttribute('data-opt-src');
    }
  });

  test('Gallery should have proper crop', async ({ page }) => {
    const images = await page.locator('#envira-gallery-wrap-98 img').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('data-envira-src', /rt:fill/);
    }
  });
}); 
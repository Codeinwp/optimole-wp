import { test, expect } from '@playwright/test';

test.describe('Check gif page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/no-builder/testing-gif-with-video/');
  });

  test('Gallery images all attributes replaced properly', async ({ page }) => {
    const video = page.locator('video');
    await expect(video).toHaveAttribute('autoplay', '');
    await expect(video).toHaveAttribute('muted', '');
    await expect(video).toHaveAttribute('loop', '');
    await expect(video).toHaveAttribute('playsinline', '');
    await expect(video).toHaveAttribute('poster', /i\.optimole\.com/);
    await expect(video).toHaveAttribute('poster', /q:eco/);
    await expect(video).toHaveAttribute('original-src', /i\.optimole\.com/);

    const source = page.locator('video > source');
    await expect(source).toHaveAttribute('src', /f:mp4/);
    await expect(source).toHaveAttribute('src', /i\.optimole\.com/);
    await expect(source).toHaveAttribute('type', 'video/mp4');
  });

  test('successfully loads', async ({ page }) => {
    await page.goto('/gif-test/');
  });

  test('Images with gifs has proper tags', async ({ page }) => {
    // Skip if browser has native lazy loading
    if (typeof HTMLImageElement !== 'undefined' && 'loading' in HTMLImageElement.prototype) {
      test.skip();
    }

    const images = await page.locator('.wp-block-image img').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('src', /data:image\/svg\+xml/);
      await expect(image).toHaveAttribute('data-opt-src', /i\.optimole\.com/);
    }
  });
}); 
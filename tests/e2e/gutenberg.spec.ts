import { test, expect } from '@playwright/test';

test.describe('Check gutenberg page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/gutenberg');
  });

  test('Gutenberg images should have replaced srcs', async ({ page }) => {
    const images = page.locator('.wp-block-image > img');
    await expect(images).toHaveAttribute('src', /i\.optimole\.com/);
  });

  test('Gutenberg images should have data-opt-src attribute', async ({ page }) => {
    if (typeof HTMLImageElement !== 'undefined' && 'loading' in HTMLImageElement.prototype) {
      test.skip();
    }
    const image = await page.locator('.wp-block-media-text__media > img');
    await expect(image).toHaveAttribute('data-opt-src', /i\.optimole\.com/);
  });

  test('Gutenberg images should have noscript tag', async ({ page }) => {
    const noscript = await page.locator('.wp-block-media-text__media > noscript');
    const noscriptContent = await noscript.innerHTML();
    expect(noscriptContent).toMatch(/img.*i\.optimole\.com/);
  });

  test('Gutenberg block cover background image should be properly replaced', async ({ page }) => {
    const cover = page.locator('.wp-block-cover');
    await cover.scrollIntoViewIfNeeded();
    await expect(cover).toHaveCSS('background-image', /i\.optimole\.com/);
  });
}); 
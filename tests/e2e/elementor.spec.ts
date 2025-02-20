import { test, expect } from '@playwright/test';

test.describe('Elementor Check Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('Header should not have lazyload applied', async ({ page }) => {
    const header = await page.locator('header');
    await expect(header).not.toHaveAttribute('data-opt-src');
  });

  test('Elementor images should have replaced srcs', async ({ page }) => {
    const images = await page.locator('.elementor-image > img').all();
    for (const image of images) {
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
    }
  });

  test('Elementor gallery should be properly replaced', async ({ page }) => {
    const links = await page.locator('.elementor-image-gallery a').all();
    for (const link of links) {
      await link.scrollIntoViewIfNeeded();
      await expect(link).toHaveAttribute('href', /i\.optimole\.com/);

      const image = await link.locator('img');
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
    }
  });
}); 
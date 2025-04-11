import { test, expect } from '@playwright/test';

test.describe('Check Shortcode ultimate page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/blog/test/');
  });

  test('Carousel should have proper resize type', async ({ page }) => {
    const images = await page.locator('.su-carousel-slide img').all();
    for (const image of images) {
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
     // change this to check only if data-opt-src if exists and if not check src 
     if(await image.getAttribute('data-opt-src')){
      await expect(image).toHaveAttribute('data-opt-src', /rt:fill/); 
     }else{
      await expect(image).toHaveAttribute('src', /rt:fill/);
     }
    }
  });

  test('Slider should have proper resize type', async ({ page }) => {
    const images = await page.locator('.su-slider img').all();
    for (const image of images) {
      await expect(image).toHaveAttribute('data-opt-src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-opt-src', /rt:fill/);
    }
  });

  test('Gallery should have proper resize type', async ({ page }) => {
    const images = await page.locator('.su-custom-gallery img').all();
    for (const image of images) {
      await expect(image).toHaveAttribute('data-opt-src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-opt-src', /rt:fill/);
    }
  });
}); 
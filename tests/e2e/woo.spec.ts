import { test, expect } from '@playwright/test';

test.describe('Check product page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/product/test-product/');
  });

  test('Gallery wrapper should have proper data-thumb', async ({ page }) => {
    const wrappers = await page.locator('.woocommerce-product-gallery__wrapper > div').all();
    for (const wrapper of wrappers) {
      await expect(wrapper).toHaveAttribute('data-thumb', /i\.optimole\.com/);
    }
  });

  test('Gallery wrapper link should have auto sizes', async ({ page }) => {
    const links = await page.locator('.woocommerce-product-gallery__wrapper > div > a').all();
    for (const link of links) {
      await expect(link).toHaveAttribute('href', /i\.optimole\.com/);
    }
  });

  test('Gallery wrapper image should have proper images', async ({ page }) => {
    const images = await page.locator('.woocommerce-product-gallery__wrapper > div > a > img').all();
    for (const image of images) {
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-src', /i\.optimole\.com/);
      await expect(image).toHaveAttribute('data-large_image', /i\.optimole\.com/);
    }
  });
});

test.describe('Test quick view', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/shop');
    await page.locator('.yith-wcqv-button').first().click();
  });

  test('Quick view have optimole images', async ({ page }) => {
    const images = await page.locator('#yith-quick-view-content .woocommerce-product-gallery__wrapper img').all();
    for (const image of images) {
      await expect(image).toHaveAttribute('src', /i\.optimole\.com/);
    }
  });
}); 
import { test, expect } from '@playwright/test';

test.describe('Check Elementor Background Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/elementor/');
  });

  // test('Elementor widgets should have background lazyloaded', async ({ page }) => {
  //   const widget = page.locator('.elementor-inner .elementor-widget-container').first();
  //   await expect(widget).toHaveClass(/optml-bg-lazyloaded/);
  //   await expect(widget).toHaveCSS('background-image', /url\(.*\.i\.optimole\.com.*\)/);
  // });

  // test('Elementor widgets not in view should have no background', async ({ page }) => {
  //   const widget = page.locator('.elementor-inner .elementor-widget-container').nth(4);
  //   await expect(widget).toHaveCSS('background-image', /none/);
  // });

  // test('Elementor background images should have background lazyloaded', async ({ page }) => {
  //   const overlay = page.locator('.elementor-inner .elementor-background-overlay').first();
  //   await expect(overlay).toHaveClass(/optml-bg-lazyloaded/);
  // });

  // test('Elementor background gallery items in view should be lazyloaded', async ({ page }) => {
  //   await page.evaluate(() => window.scrollTo(0, 200));
    
  //   for (let i = 0; i < 6; i++) {
  //     const item = page.locator('.elementor-inner .elementor-background-slideshow__slide__image').nth(i);
  //     await expect(item).toHaveClass(/optml-bg-lazyloaded/);
  //   }
  // });

  test('After scroll backgrounds should be loaded', async ({ page }) => {
    const overlay = page.locator('.elementor-inner .elementor-background-overlay').nth(1);
    
    await overlay.scrollIntoViewIfNeeded();
    await expect(overlay).toHaveClass(/optml-bg-lazyloaded/);

    const widget = page.locator('.elementor-inner .elementor-widget-container').nth(4);
    await widget.scrollIntoViewIfNeeded();
    await expect(widget).toHaveClass(/optml-bg-lazyloaded/);

    for (let i = 6; i < 12; i++) {
      const item = page.locator('.elementor-inner .elementor-background-slideshow__slide__image').nth(i);
      await expect(item).toHaveClass(/optml-bg-lazyloaded/);
    }
  });
}); 
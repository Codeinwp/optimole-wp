import { test, expect } from '@playwright/test';

test.describe('Check Divi Background Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/divi/');
  });

  test('Divi slide in view should have background lazyloaded', async ({ page }) => {
    const slide = page.locator('.entry-content .et_pb_slides > .et_pb_slide_0').first();
    await expect(slide).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('Divi slides not in view should have no background', async ({ page }) => {
    const slide1 = page.locator('.entry-content .et_pb_slides > .et_pb_slide_1').first();
    await expect(slide1).toHaveCSS('background-image', /none/);

    const slide2 = page.locator('.entry-content .et_pb_slides > .et_pb_slide_2').first();
    await expect(slide2).toHaveCSS('background-image', /none/);
  });

  test('Divi row and module in view should have background lazyloaded', async ({ page }) => {
    const row = page.locator('.entry-content .et_pb_row_0').first();
    await expect(row).toHaveClass(/optml-bg-lazyloaded/);

    const module = page.locator('.entry-content .et_pb_module').nth(1);
    await expect(module).toHaveClass(/optml-bg-lazyloaded/);
  });

  test('After scroll backgrounds should be loaded', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 4250));

    const slide = page.locator('.entry-content .et_pb_slides > .et_pb_slide_3').first();
    await expect(slide).toHaveClass(/optml-bg-lazyloaded/);

    const module = page.locator('.entry-content .et_pb_module').nth(4);
    await expect(module).toHaveClass(/optml-bg-lazyloaded/);

    const row = page.locator('.entry-content .et_pb_row_3').first();
    await expect(row).toHaveClass(/optml-bg-lazyloaded/);
  });
}); 
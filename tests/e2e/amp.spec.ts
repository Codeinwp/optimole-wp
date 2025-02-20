import { test, expect } from '@playwright/test';

test.describe('Check amp page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/amp-mode/?amp');
  });

  test('AMP body has Optimole no script class', async ({ page }) => {
    const html = page.locator('html');
    await expect(html).not.toHaveClass(/optml_no_js/);
  });

  test('AMP images should have replaced srcs', async ({ page }) => {
    const ampImg = page.locator('amp-img');
    await expect(ampImg).toHaveAttribute('src', /i\.optimole\.com/);
  });

  test('AMP no script lazyload', async ({ page }) => {
    const scripts = page.locator('script');
    const elements = await scripts.all();
    for (const script of elements) {
      const text = await script.textContent();
      expect(text).not.toContain('d5jmkjjpb7yfg.cloudfront.net');
    }
  });
}); 
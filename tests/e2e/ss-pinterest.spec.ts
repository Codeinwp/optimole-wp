import { test, expect } from '@playwright/test';

test.describe('Sassy Social Share', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/sassy-social-share/');
  });

  test('click on button', async ({ page }) => {
    const buttons = await page.locator('.heateorSssSharing.heateorSssPinterestBackground').all();
    for (const button of buttons) {
      await button.click({ force: true });
    }
  });

  test('images should not have quality:eco', async ({ page }) => {
    await page.evaluate(() => window.scrollTo(0, 2500));
    
    const images = await page.locator('img').all();
    expect(images.length).toBe(5);

    for (let i = 0; i < 4; i++) {
      const src = await images[i].getAttribute('src');
      expect(src).not.toMatch(/eco/);
    }
  });
}); 
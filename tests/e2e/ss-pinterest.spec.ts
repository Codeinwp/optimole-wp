import { test, expect } from '@playwright/test';

test.describe('Sassy Social Share', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/sassy-social-share/');
  });

  test('Pinterest share controls are available', async ({ page }) => {
    const buttons = page.locator('.heateorSssSharing.heateorSssPinterestBackground');
    await expect(buttons).not.toHaveCount(0);
    await expect(buttons.first()).toBeVisible();
  });

  test('images should not have quality:eco', async ({ page }) => {
    const images = page.locator('img');
    await expect(images).toHaveCount(5);

    for (let i = 0; i < 4; i++) {
      const image = images.nth(i);
      await image.scrollIntoViewIfNeeded();
      await expect(image).toHaveAttribute('src', /^(?!.*eco).+$/);
    }
  });
});

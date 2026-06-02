import { test, expect } from '@playwright/test';

const BASE = process.env.TEST_URL || 'https://glowza.fr';

test.describe('Pixels & Analytics', () => {

  test('GTM chargé — dataLayer disponible', async ({ page }) => {
    await page.goto(`${BASE}/`);
    const gtmLoaded = await page.evaluate(() => {
      return typeof window.dataLayer !== 'undefined' && window.dataLayer.length > 0;
    });
    expect(gtmLoaded).toBe(true);
  });

  test('Meta Pixel — fbq défini', async ({ page }) => {
    await page.goto(`${BASE}/`);
    const fbqDefined = await page.evaluate(() => typeof (window as any).fbq !== 'undefined');
    expect(fbqDefined).toBe(true);
  });

  test('TikTok Pixel — ttq défini', async ({ page }) => {
    await page.goto(`${BASE}/`);
    const ttqDefined = await page.evaluate(() => typeof (window as any).ttq !== 'undefined');
    expect(ttqDefined).toBe(true);
  });

  test('Event view_item déclenché sur fiche produit', async ({ page }) => {
    const dlEvents: string[] = [];

    await page.addInitScript(() => {
      (window as any).__glowzaDLEvents = [];
      const _push = Array.prototype.push;
      (window as any).dataLayer = new Proxy([], {
        get(target, prop) {
          if (prop === 'push') {
            return (...args: any[]) => {
              args.forEach(a => { if (a.event) (window as any).__glowzaDLEvents.push(a.event); });
              return _push.apply(target, args);
            };
          }
          return (target as any)[prop];
        }
      });
    });

    await page.goto(`${BASE}/boutique/`);
    await page.locator('ul.products li.product').first().click();
    await page.waitForLoadState('networkidle');

    const events = await page.evaluate(() => (window as any).__glowzaDLEvents || []);
    expect(events).toContain('view_item');
  });

  test('Event add_to_cart déclenché', async ({ page }) => {
    let addToCartFired = false;

    await page.goto(`${BASE}/boutique/`);
    await page.locator('ul.products li.product').first().click();
    await page.waitForLoadState('networkidle');

    // Écouter les requêtes Meta Pixel
    page.on('request', req => {
      if (req.url().includes('facebook.com/tr') && req.url().includes('AddToCart')) {
        addToCartFired = true;
      }
    });

    await page.locator('.single_add_to_cart_button').click();
    await page.waitForTimeout(2000);

    // Vérifier dans le dataLayer
    const dlEvents = await page.evaluate(() => {
      return (window.dataLayer || []).filter((d: any) => d.event === 'add_to_cart');
    });
    expect(dlEvents.length).toBeGreaterThan(0);
  });

});

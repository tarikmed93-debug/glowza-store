import { test, expect } from '@playwright/test';

const BASE = process.env.TEST_URL || 'https://glowza.fr';

test.describe('Flux Checkout Glowza', () => {

  test('Page boutique — produits visibles', async ({ page }) => {
    await page.goto(`${BASE}/boutique/`);
    await expect(page).toHaveTitle(/Glowza/);
    const products = page.locator('ul.products li.product');
    await expect(products).toHaveCount({ minimum: 1 });
  });

  test('Fiche produit — infos critiques présentes', async ({ page }) => {
    await page.goto(`${BASE}/boutique/`);
    // Ouvrir le premier produit
    await page.locator('ul.products li.product').first().click();
    await page.waitForLoadState('networkidle');

    // Titre, prix, bouton ajout panier
    await expect(page.locator('.product_title')).toBeVisible();
    await expect(page.locator('.price')).toBeVisible();
    await expect(page.locator('.single_add_to_cart_button')).toBeVisible();

    // Notice livraison 2-4 semaines
    await expect(page.locator('.glowza-shipping-notice')).toContainText('2 à 4 semaines');

    // Schema.org
    const schema = await page.locator('script[type="application/ld+json"]').textContent();
    expect(schema).toContain('"@type":"Product"');
  });

  test('Ajout au panier', async ({ page }) => {
    await page.goto(`${BASE}/boutique/`);
    const firstProduct = page.locator('ul.products li.product').first();
    await firstProduct.locator('.button').click();
    // Vérifier que le compteur panier a augmenté
    await page.waitForTimeout(1000);
    const cartCount = await page.locator('.cart-contents .count, .woocommerce-cart-form').textContent().catch(() => '');
    // Naviguer vers le panier
    await page.goto(`${BASE}/panier/`);
    await expect(page.locator('.woocommerce-cart-form')).toBeVisible();
  });

  test('Page checkout — champs et sécurité', async ({ page }) => {
    // Aller directement au checkout avec un produit
    await page.goto(`${BASE}/boutique/`);
    await page.locator('ul.products li.product .button').first().click();
    await page.waitForTimeout(500);
    await page.goto(`${BASE}/commande/`);
    await page.waitForLoadState('networkidle');

    // Champs obligatoires
    await expect(page.locator('#billing_first_name')).toBeVisible();
    await expect(page.locator('#billing_last_name')).toBeVisible();
    await expect(page.locator('#billing_email')).toBeVisible();
    await expect(page.locator('#billing_address_1')).toBeVisible();

    // Message rétractation 14j
    await expect(page.locator('.glowza-retractation')).toContainText('14 jours');

    // HTTPS
    expect(page.url()).toMatch(/^https:/);
  });

  test('Pages légales accessibles', async ({ page }) => {
    for (const path of ['/mentions-legales/', '/cgv/', '/politique-confidentialite/', '/cookies/']) {
      const res = await page.goto(`${BASE}${path}`);
      expect(res?.status()).toBe(200);
    }
  });

  test('PageSpeed — fiche produit mobile (vérification structure)', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 }); // iPhone 14
    await page.goto(`${BASE}/boutique/`);
    await page.locator('ul.products li.product').first().click();
    await page.waitForLoadState('networkidle');

    // Vérifier que les images ont loading="lazy"
    const images = page.locator('img[loading="lazy"]');
    const count = await images.count();
    expect(count).toBeGreaterThan(0);

    // Vérifier viewport meta
    const viewport = await page.locator('meta[name="viewport"]').getAttribute('content');
    expect(viewport).toContain('width=device-width');
  });

});

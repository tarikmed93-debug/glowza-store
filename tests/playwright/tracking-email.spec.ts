import { test, expect } from '@playwright/test';

/**
 * Test du webhook DSers → tracking email.
 * Simule un appel webhook comme DSers le ferait après expédition.
 *
 * Prérequis : TEST_ORDER_ID + WEBHOOK_SECRET dans les env vars de test.
 */

const BASE          = process.env.TEST_URL     || 'https://glowza.fr';
const ORDER_ID      = process.env.TEST_ORDER_ID || '1';
const WEBHOOK_SECRET= process.env.WEBHOOK_SECRET|| '';

test('Webhook DSers — tracking enregistré et statut mis à jour', async ({ request }) => {
  test.skip(!WEBHOOK_SECRET, 'WEBHOOK_SECRET non défini — skip');

  const res = await request.post(`${BASE}/wp-json/glowza/v1/dsers-tracking`, {
    headers: {
      'Content-Type': 'application/json',
      'X-Glowza-Secret': WEBHOOK_SECRET,
    },
    data: {
      order_id:    parseInt(ORDER_ID),
      tracking:    'TEST-TRACKING-123456',
      tracking_url:'https://parcelsapp.com/en/tracking/TEST-TRACKING-123456',
    },
  });

  expect(res.status()).toBe(200);
  const body = await res.json();
  expect(body.success).toBe(true);
  expect(body.order_id).toBe(parseInt(ORDER_ID));
});

test('Webhook DSers — secret invalide renvoie 401', async ({ request }) => {
  const res = await request.post(`${BASE}/wp-json/glowza/v1/dsers-tracking`, {
    headers: {
      'Content-Type': 'application/json',
      'X-Glowza-Secret': 'mauvais-secret',
    },
    data: { order_id: 1, tracking: 'XXX' },
  });

  expect(res.status()).toBe(401);
});

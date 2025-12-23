import { test } from '@playwright/test';
import { login } from './utils/auth';

test('logged in user can see dashboard', async ({ page }) => {
  await login(page);
  // Continue with your test...
});

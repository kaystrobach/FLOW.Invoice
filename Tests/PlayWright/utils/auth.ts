import { Page, expect } from '@playwright/test';
import * as dotenv from 'dotenv';
import path from 'path';

// Load .env.local
dotenv.config({ path: path.resolve(__dirname, '../../../../../../.env.local') });

/**
 * Reusable login function for the application
 * @param page Playwright Page object
 */
export async function login(page: Page) {
  const url = process.env.APP_URL;
  const email = process.env.APP_LOGIN;
  const password = process.env.APP_PASSWORD;

  if (!url || !email || !password) {
    throw new Error('Missing environment variables for login. Please check .env.local');
  }

  await page.goto(url);

  // Fill the login form using the provided IDs
  await page.fill('#inputEmail', email);
  await page.fill('#inputPassword', password);

  // Click the sign-in button
  await page.click('.btn-signin');

  // Optional: Add a check to verify login success (e.g., waiting for a specific URL or element)
  // await expect(page).toHaveURL(/.*dashboard.*/);
}

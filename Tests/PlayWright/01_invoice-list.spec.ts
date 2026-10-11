import { expect, InvoiceListPage, test } from '@playwright-common/index';

test.describe('invoice list', () => {
    test('is reachable via the menu and offers the invoice tools', { tag: '@ui' }, async ({ invoiceListPage }) => {
        await expect(invoiceListPage.page).toHaveURL(/\/kaystrobach\.invoice\/standard\/index/);
        await expect(invoiceListPage.title).toContainText('Rechnungen');
        await expect(invoiceListPage.newInvoiceLink).toBeVisible();
        for (const tool of ['Fälligkeiten', 'Buchungssätze', 'Tagesjournal']) {
            await expect(invoiceListPage.main.getByRole('link', { name: tool }).first()).toBeVisible();
        }
        for (const preset of ['Entwürfe', 'Dauerrechnungen', 'Rechnungen', String(new Date().getFullYear())]) {
            await expect(invoiceListPage.preset(preset)).toBeVisible();
        }
    });

    test('shows a draft without number', { tag: '@ui' }, async ({ draftInvoice }) => {
        const identity = draftInvoice.identity;

        const list = new InvoiceListPage(draftInvoice.page);
        await list.goto();
        await list.preset('Entwürfe').click();

        const row = list.row(identity);
        await expect(row).toHaveAttribute('data-state', 'draft');
        await expect(row.getByRole('link', { name: 'Entwurf', exact: true })).toBeVisible();
        await expect(row).toContainText('Nummer bei Festschreibung');
        await expect(row).toContainText('Auftrag PW-ORDER');
        await expect(row).toContainText('Angebot PW-OFFER');
    });
});

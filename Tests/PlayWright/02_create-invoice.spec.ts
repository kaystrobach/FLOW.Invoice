import { expect, test } from '@playwright-common/index';

test.describe('create invoice', () => {
    test('wizard offers the configured invoice series', { tag: '@ui' }, async ({ invoiceWizardPage }) => {
        await expect(invoiceWizardPage.title).toContainText('Rechnung anlegen');
        await expect(invoiceWizardPage.series('Rechnung')).toBeVisible();
        await expect(invoiceWizardPage.series('Dauerrechnung')).toBeVisible();
        await expect(invoiceWizardPage.customerNumberField).toBeVisible();
    });

    test('wizard creates a draft with order and customer data', { tag: '@ui' }, async ({ draftInvoice, invoiceCustomerNumber }) => {
        await expect(draftInvoice.page).toHaveURL(/\/standard\/edit/);
        await expect(draftInvoice.titleField).toHaveValue('Rechnung');

        await draftInvoice.openTab('Auftrag');
        await expect(draftInvoice.field('offerNumber')).toHaveValue('PW-OFFER');
        await expect(draftInvoice.field('order.orderNumber')).toHaveValue('PW-ORDER');

        await draftInvoice.openTab('Kunde');
        await expect(draftInvoice.field('customer.deptorNumber')).toHaveValue(invoiceCustomerNumber);
        // filled from the CRM customer
        await expect(draftInvoice.field('customer.name')).not.toHaveValue('');
    });

    test('order data is saved', { tag: '@ui' }, async ({ draftInvoice }) => {
        await draftInvoice.fillOrder({
            projectName: 'Playwright Projekt',
            commissioningDate: '2026-10-01',
            periodStart: '2026-10-01',
            periodEnd: '2026-10-31',
        });
        await draftInvoice.save();

        await draftInvoice.openTab('Auftrag');
        await expect(draftInvoice.field('order.customerReference')).toHaveValue('Playwright Projekt');
        await expect(draftInvoice.field('order.commissioningDate.date')).toHaveValue('2026-10-01');
        await expect(draftInvoice.field('periodOfPerformance.start.date')).toHaveValue('2026-10-01');
        await expect(draftInvoice.field('periodOfPerformance.end.date')).toHaveValue('2026-10-31');
        await expect(draftInvoice.main).toContainText('Leistungszeitraum 01.10.2026 - 31.10.2026');
    });

    test('invoice items are summed up with taxes', { tag: '@ui' }, async ({ draftInvoice }) => {
        await draftInvoice.addItem();
        await draftInvoice.fillItem(0, { name: 'Position 1', price: '1000,00' });
        await draftInvoice.addItem();
        await draftInvoice.fillItem(1, { name: 'Position 2', price: '50,00', amount: '3' });
        await draftInvoice.save();

        await expect(draftInvoice.field('invoiceItems.0.name')).toHaveValue('Position 1');
        await expect(draftInvoice.itemTotal(0)).toHaveValue('1.000,00');
        await expect(draftInvoice.itemTotal(1)).toHaveValue('150,00');
        await expect(draftInvoice.netTotal).toHaveValue('1.150,00');
        await expect(draftInvoice.grossTotal).toHaveValue('1.368,50');
    });
});

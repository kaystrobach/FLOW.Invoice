import { expect, InvoiceShowPage, test } from '@playwright-common/index';

const currentYear = String(new Date().getFullYear());

test.describe('copy invoice', () => {
    test('a copy of a finalized invoice is a new draft with the new period', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        const original = await (await finalizableInvoice.finalize()).invoiceNumber();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        const clonePage = await show.copy();
        await expect(clonePage.title).toContainText(`Rechnung ${original.combined} clonen`);
        const copy = await clonePage.clone('2026-11-01', '2026-11-30');

        expect(copy.identity).not.toBe(identity);
        await expect(copy.main).toContainText('Leistungszeitraum 01.11.2026 - 30.11.2026');
        await expect(copy.field('invoiceItems.0.name')).toHaveValue('Playwright Testposition');
        await expect(copy.anyToolbarItem('button', 'Löschen')).toHaveCount(1);

        const copyNumber = await (await copy.finalize()).invoiceNumber();
        expect(copyNumber.prefix).toBe(original.prefix);
        expect(copyNumber.postfix).toBe(`-${currentYear}`);
        expect(copyNumber.number).toBeGreaterThan(original.number);
    });
});

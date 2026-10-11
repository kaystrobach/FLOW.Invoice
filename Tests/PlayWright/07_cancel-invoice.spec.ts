import { expect, InvoiceShowPage, test } from '@playwright-common/index';

test.describe('cancel invoice', () => {
    test('cancelling creates a cancellation draft linked to the original', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        const original = await (await finalizableInvoice.finalize()).invoiceNumber();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        const cancellation = await show.cancel();

        // StornoInvoiceController keeps the cancellation invoice changeable: no number yet
        await expect(cancellation.toast('Stornorechnung wurde erstellt')).toBeVisible();
        expect(cancellation.identity).not.toBe(identity);
        await expect(await cancellation.toolbarItem('link', `Rechnung: ${original.combined}`)).toBeVisible();

        await show.goto(identity);
        await expect(show.anyToolbarItem('link', 'Rechnung stornieren')).toHaveCount(1);
        await expect(show.main.getByRole('link', { name: /^Stornorechnung: / , includeHidden: true })).toHaveCount(1);
    });
});

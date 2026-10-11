import { expect, InvoiceListPage, InvoiceShowPage, test } from '@playwright-common/index';

const currentYear = String(new Date().getFullYear());

test.describe('cancel invoice', () => {
    test('cancelling creates a finalized cancellation invoice linked to the original', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        const original = await (await finalizableInvoice.finalize()).invoiceNumber();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        const cancellation = await show.cancel();
        const cancellationIdentity = cancellation.identity;

        // no draft: the cancellation invoice has its own number and is read-only right away
        const number = await cancellation.invoiceNumber();
        expect(number.prefix).toBe('KR-');
        expect(number.postfix).toBe(`-${currentYear}`);
        await expect(cancellation.toast(`Stornorechnung ${number.combined} wurde erstellt`)).toBeVisible();
        await expect(cancellation.lockedNotice).toBeVisible();
        await expect(cancellation.anyToolbarItem('button', 'Löschen')).toHaveCount(0);
        await expect(cancellation.anyToolbarItem('link', `Rechnung: ${original.combined}`)).toHaveCount(1);
        // the rendered document is shown instead of the "Vorschau" of a draft
        await expect(cancellation.main.locator('iframe.iframe')).toHaveCount(1);

        await show.goto(identity);
        await expect(show.anyToolbarItem('link', `Stornorechnung: ${number.combined}`)).toHaveCount(1);

        const list = new InvoiceListPage(show.page);
        await list.goto();
        await expect(list.row(cancellationIdentity)).toHaveAttribute('data-state', 'cancellation');
        await expect(list.row(cancellationIdentity)).toContainText(`zu ${original.combined}`);
    });

    test('cancelling twice opens the existing cancellation invoice', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        await finalizableInvoice.finalize();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        const first = await (await show.cancel()).invoiceNumber();

        await show.goto(identity);
        const second = await (await show.cancel()).invoiceNumber();
        expect(second.combined).toBe(first.combined);
    });
});

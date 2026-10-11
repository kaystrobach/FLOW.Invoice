import { expect, InvoiceShowPage, test } from '@playwright-common/index';

const currentYear = String(new Date().getFullYear());

test.describe('finalize invoice', () => {
    test('incomplete draft can not be finalized', { tag: '@ui' }, async ({ draftInvoice }) => {
        await draftInvoice.openFinalize();
        await expect(draftInvoice.finalizeOffcanvas).toContainText('Es gibt ein Problem mit der Rechnung');
        await expect(draftInvoice.finalizeOffcanvas.getByRole('button', { name: 'Rechnung Festschreiben' })).toHaveCount(0);
    });

    test('check passes for a complete draft', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        await finalizableInvoice.check();
        await expect(finalizableInvoice.toast('Prüfung erfolgreich')).toBeVisible();
    });

    test('finalizing assigns a number of the current year and locks the invoice', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;

        const message = await finalizableInvoice.finalize();
        const number = await message.invoiceNumber();
        expect(number.prefix).toBe('R-');
        expect(number.postfix).toBe(`-${currentYear}`);
        await expect(message.subjectField).toHaveValue(`Rechnung ${number.combined}`);

        // the edit action redirects finalized invoices to the read-only view
        await finalizableInvoice.goto(identity);
        await expect(finalizableInvoice.page).toHaveURL(/\/standard\/show/);
        const show = new InvoiceShowPage(finalizableInvoice.page);
        await expect(show.lockedNotice).toBeVisible();
        expect((await show.invoiceNumber()).combined).toBe(number.combined);
        await expect(show.main.getByRole('textbox', { name: 'Titel', exact: true })).toBeDisabled();
    });

    test('invoices of one series are numbered consecutively', { tag: '@ui' }, async ({ createDraftInvoice }) => {
        const first = await createDraftInvoice();
        await first.makeFinalizable();
        const firstNumber = await (await first.finalize()).invoiceNumber();

        const second = await createDraftInvoice();
        await second.makeFinalizable();
        const secondNumber = await (await second.finalize()).invoiceNumber();

        expect(secondNumber.prefix).toBe(firstNumber.prefix);
        expect(secondNumber.postfix).toBe(firstNumber.postfix);
        expect(secondNumber.number).toBe(firstNumber.number + 1);
    });
});

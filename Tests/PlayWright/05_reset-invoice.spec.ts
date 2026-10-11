import { expect, flow, test } from '@playwright-common/index';

test.describe('reset invoice (./flow invoice:reset)', () => {
    test.skip(!!process.env.PLAYWRIGHT_SKIP_SETUP, 'needs the Flow CLI of the instance under test');

    test('keeps the invoice number when finalizing again', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        const number = await (await finalizableInvoice.finalize()).invoiceNumber();

        flow('invoice:reset', identity);

        // the invoice is a draft again and can be edited
        await finalizableInvoice.goto(identity);
        await expect(finalizableInvoice.page).toHaveURL(/\/standard\/edit/);
        // ... but it is not deletable, the number is assigned for good
        await expect(finalizableInvoice.anyToolbarItem('button', 'Löschen')).toHaveCount(0);

        const again = await (await finalizableInvoice.finalize()).invoiceNumber();
        expect(again.combined).toBe(number.combined);
    });
});

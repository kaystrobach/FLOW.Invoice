import { expect, InvoiceListPage, InvoiceShowPage, test } from '@playwright-common/index';

test.describe('delete invoice', () => {
    test('a draft can be deleted', { tag: '@ui' }, async ({ draftInvoice }) => {
        const identity = draftInvoice.identity;
        await expect(draftInvoice.anyToolbarItem('button', 'Löschen')).toHaveCount(1);

        await draftInvoice.delete();

        const list = new InvoiceListPage(draftInvoice.page);
        await list.preset('Entwürfe').click();
        await expect(list.row(identity)).toHaveCount(0);
    });

    test('a finalized invoice has no delete button', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        await finalizableInvoice.finalize();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        await expect(show.lockedNotice).toBeVisible();
        await expect(show.anyToolbarItem('button', 'Löschen')).toHaveCount(0);
    });

    test('a finalized invoice survives a direct call of the remove action', { tag: '@ui' }, async ({ finalizableInvoice }) => {
        const identity = finalizableInvoice.identity;
        const number = await (await finalizableInvoice.finalize()).invoiceNumber();

        const show = new InvoiceShowPage(finalizableInvoice.page);
        await show.goto(identity);
        // forge the request the delete button of a draft would send
        const navigated = show.page.waitForResponse((response) => response.url().includes('/standard/remove'));
        await show.page.evaluate((identity) => {
            const form = document.createElement('form');
            form.method = 'post';
            form.action = `/kaystrobach.invoice/standard/remove?object%5B__identity%5D=${identity}`;
            const csrfToken = document.querySelector<HTMLInputElement>('input[name="__csrfToken"]');
            if (csrfToken) {
                form.append(csrfToken.cloneNode());
            }
            document.body.append(form);
            form.submit();
        }, identity);
        await navigated;
        await show.page.waitForLoadState('load');

        // refused with a flash message on the read-only view of the invoice
        await expect(show.page).toHaveURL(new RegExp(`/standard/show\\?object%5B__identity%5D=${identity}`));
        await expect(show.toast(`Rechnung ${number.combined} ist festgeschrieben und kann nicht gelöscht werden`)).toBeVisible();

        await show.goto(identity);
        await expect(show.lockedNotice).toBeVisible();
        expect((await show.invoiceNumber()).combined).toBe(number.combined);
    });
});

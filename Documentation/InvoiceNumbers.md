# Invoice numbers

Invoice numbers must run without gaps and without duplicates per series. A series is the
combination of prefix and postfix, e.g. `R-2026-` + `` → `R-2026-1`, `R-2026-2`, …

## How a number is assigned

`InvoiceFactory::setInvoiceNumber()` (finalize):

1. `%year`/`%month` in prefix/postfix are resolved with the invoice date, so a draft from December
   that is finalized in January belongs to the new year's series.
2. `InvoiceNumberRangeService::ensureSeries()` creates the row of the series in
   `kaystrobach_invoice_numberrange` if needed. This happens outside the transaction, otherwise
   concurrent finalizations deadlock.
3. In one transaction: lock the series row (`SELECT … FOR UPDATE`), take
   `max(lastNumber, MAX(number of existing invoices)) + 1`, store it, assign it to the invoice,
   lock the invoice, commit.

A second finalization of the same series waits for the lock. A rollback does not use up the number.
The unique index `invoice_number_unique` (prefix, number, postfix) is the safety net.

Verified on MariaDB 10.11 with 20 parallel processes × 15 finalizations, ~10 % rolled back:

| | invoices | numbers | duplicates | gaps |
|---|---|---|---|---|
| before (`MAX + 1` without lock) | 286 | 1–54 | 42 numbers | 0 |
| now | 286 | 1–286 | 0 | 0 |
| now, first concurrent use of a new series | 281 | 1–281 | 0 | 0 |

## Numbers are never released

- Invoices with a number, a rendered document or that are finalized cannot be deleted
  (`InvoiceLockedException`). Corrections are done with a cancellation invoice.
- `./flow invoice:reset --invoice <id>` unlocks the invoice and deletes the PDF, **the number is
  kept**. Finalizing again only locks it.
- To only re-render a broken PDF: `./flow invoice:regenerateinvoicepdf --invoice <id>`.

## Checking a database

```bash
./flow invoice:checknumbers                    # all series
./flow invoice:checknumbers --prefix R-2026-   # one series
```

Lists per series the number of invoices, the highest number, gaps (as ranges) and duplicates (with
the persistence identifiers of the affected invoices). Exit code 1 if anything was found.

Gaps from the past cannot be closed afterwards. Document them, for example in the procedure
documentation (Verfahrensdokumentation): which numbers are missing, and why, if known.

# Responsive list

The invoice list (`Standard/Index.html`) uses a generic list that other lists (orders, leads) can reuse.
On wide containers it is a table with seven columns. Below 860px *container* width each row becomes a card.
A CSS container query does this, so the list also adapts inside narrow layouts, not only on small screens.

## Files

| File | Generic | Purpose |
|---|---|---|
| `Resources/Public/Css/List.css` | yes | layout, card mode, badge tones (`rl-*`) |
| `Resources/Private/Partials/List/Table.html` | yes | frame, header, empty message |
| `Resources/Private/Partials/List/Badge.html` | yes | status badge |
| `Resources/Private/Partials/List/Icon.html` | yes | status and action icons |
| `Resources/Private/Partials/Domain/Model/Invoice/ListRow.html` | no | maps an invoice to the row cells |
| `Classes/Domain/Model/Invoice/ListState.php` | no | status, tone, icon, label and due date of an invoice |

The generic parts can move to `kaystrobach/backend` once a second list uses them.

## Row cells

Every row is `<div class="rl-cols rl-row" role="row">` with these cells, in this order:

| Cell class | Desktop column | Card position | Invoice | Order (planned) | Lead (planned) |
|---|---|---|---|---|---|
| `rl-c-id` | 1 | top left | number or "Entwurf", title, order/offer | order number, title, offer/contract | lead number, title, source |
| `rl-c-party` | 2 | full width | customer, debtor, tags | customer, debtor, tags | prospect, tags |
| `rl-c-date` | 3 | left | invoice date | ordered at | received at |
| `rl-c-status` | 4 | top right | badge + link to cancellation | badge (acceptance, progress) | badge |
| `rl-c-key` | 5 | right | due date, installment | acceptance deadline / planned end, optional `.rl-bar` | next step / quote valid until |
| `rl-c-amount` | 6 | bottom left | gross, net | order value, invoiced | expected value, probability |
| `rl-c-actions` | 7 | bottom right | PDF, open | document, open | quote, open |

Cells 3, 5 and 6 start with `<span class="rl-lbl">…</span>`; that label only shows in card mode.

## Badge tones and icons

Tones: `neutral` (draft), `info` (running), `warn` (deadline close), `alert` (overdue, defects),
`ok` (done), `muted` (inactive), `violet` (special documents).
Icons: `edit`, `circle`, `clock`, `alert`, `check`, `slash`, `back`, `play`, plus `download`, `open` for actions.

## Usage

```html
<f:variable name="rowCount">{paginatedObjects -> f:count()}</f:variable>
<f:render partial="List/Table" arguments="{label: 'Rechnungen', count: rowCount, columns: {id: 'Rechnung', party: 'Kunde', date: 'Datum', status: 'Status', key: 'Fällig', amount: 'Betrag'}}" contentAs="rows">
    <f:for each="{paginatedObjects}" as="object">
        <f:render partial="Domain/Model/Invoice/ListRow" arguments="{object: object}" />
    </f:for>
</f:render>
```

## Invoice status

`Invoice::getListState()` decides in this order: cancelled (has a cancellation invoice), cancellation invoice,
draft (no number), no due dates, then by the oldest unpaid due date: overdue (before today),
due soon (today up to 7 days), open. If all due dates are paid: paid.

Overdue starts the day *after* the due date. `Invoice::isPaymentDelayed()` (used elsewhere) already counts
the due date itself as delayed; the list deliberately does not.

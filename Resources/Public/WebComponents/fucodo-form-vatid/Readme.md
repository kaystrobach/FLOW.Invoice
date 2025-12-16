
fucodo-form-vatid — EU VAT ID input (format-only validation)

This Web Component renders a user-friendly VAT ID input with:
- Live, format-only validation for many EU country prefixes (e.g., AT, DE, FR, …)
- Optional separate country field to enforce the correct 2-letter prefix
- Hidden real input value suitable for form submissions

Notes
- Validation is format-based only. It does not call VIES or perform checksum/registry lookups.
- Greece: both `EL` and `GR` are accepted as prefix; if a `country` slot is provided, that value is enforced.

Quick start
Include/register the component (example from Storybook packages):

```html
<script type="module" src="./packages/fucodo-form-vatid/index.js"></script>
```

Basic usage

```html
<div class="container mt-3">
  <fucodo-form-vatid placeholder="e.g. DE123456789" hint="Enter your EU VAT ID">
    <input slot="value" name="vat_id" value="ATU12345678" />
  </fucodo-form-vatid>
  <!-- Hidden field "vat_id" will be kept in normalized form like ATU12345678 -->
  <!-- The visible field shows a pretty version (e.g., "AT U12345678"). -->
  <!-- The component dispatches standard `input` and `change` events on the host. -->
  <!-- Add your own CSS framework if needed (e.g., Bootstrap) for nicer visuals. -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" crossorigin="anonymous">
  <style>
    .vatid-input-wrapper { max-width: 360px; }
  </style>
  
  <!-- Example submit button next to it -->
  <button class="btn btn-primary mt-2" type="button">Submit</button>
  
  <script>
    // Listen to changes
    document.querySelector('fucodo-form-vatid')?.addEventListener('change', (e) => {
      console.log('Normalized VAT value:', e.currentTarget.querySelector('[slot="value"]').value);
    });
  </script>
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
```

With country selector (enforces prefix)

```html
<fucodo-form-vatid>
  <select slot="country" name="country">
    <option value="DE">DE</option>
    <option value="AT">AT</option>
    <option value="FR">FR</option>
    <option value="EL">EL</option>
    <option value="GR">GR</option>
  </select>
  <input slot="value" name="vat_id" value="" />
  <!-- Typing will be auto-prefixed/replaced to match selected country. -->
  <!-- e.g., selecting DE and typing ATU123... becomes DE… -->
  <!-- Hidden value is kept normalized: uppercase, no spaces/dots/dashes/slashes/underscores. -->
  <!-- On blur, the visible input is pretty-printed as "CC rest". -->
  
  <!-- Optional UI texts via attributes: -->
  <!-- hint="Your VAT ID" valid-text="Looks good" invalid-text="Check format" -->
</fucodo-form-vatid>
```

Attributes
- `placeholder` (optional): Placeholder for the visible input.
- `hint` (optional): Helper text when empty or undecided.
- `valid-text` (optional): Message when the current value matches the country format.
- `invalid-text` (optional): Message when the format is invalid.

Slots
- `value` (required): The hidden input whose normalized value is submitted, e.g. `<input slot="value" name="vat_id">`.
- `country` (optional): A select or input whose 2-letter `value` enforces the prefix (e.g., `DE`, `AT`, `FR`, `EL`).

Events
- `input`: Fired on the component while typing; hidden value stays in sync.
- `change`: Fired on blur after pretty-printing; useful for form logic.

Supported formats (selection)
- AT, BE, BG, CY, CZ, DE, DK, EE, EL/GR, ES, FI, FR, HR, HU, IE, IT, LT, LU, LV, MT, NL, PL, PT, RO, SE, SI, SK, and XI (permissive)

Limitations
- Format-only checks; no checksum or network validation.
- Country list/regexes may evolve. See `index.js` for the current patterns.

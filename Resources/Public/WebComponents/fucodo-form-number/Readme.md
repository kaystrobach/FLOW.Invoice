# fucodo currency 

```html
<!-- Beispiel-Formular -->
<div class=container>
<form>
  <fucodo-form-currency currency="EUR">
    <!-- wird vom Component-Script versteckt & als echte Form-Felder benutzt -->
    <input slot="currency" name="currency">
    <input slot="value"   name="amount_cents">
  </fucodo-form-currency>

  <button type="submit">Absenden</button>
</form>

<script src="currency-input.js"></script>
```

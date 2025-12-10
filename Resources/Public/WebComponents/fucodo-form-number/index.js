class CurrencyInput extends HTMLElement {
    constructor() {
        super();
        this._onInput = this._onInput.bind(this);
        this._onBlur = this._onBlur.bind(this);

        this._decimalSeparator = this._detectDecimalSeparator();
        this._numberFormatter = this._createNumberFormatter();
    }

    connectedCallback() {
        if (this._initialized) return;
        this._initialized = true;

        this.currencyField = this.querySelector('[slot="currency"]');
        this.valueField    = this.querySelector('[slot="value"]');

        if (!this.currencyField || !this.valueField) {
            console.warn('<currency-input> requires inputs with slot="currency" and slot="value".');
            return;
        }

        this.currency = this.getAttribute('currency') || this.currencyField.value || 'EUR';
        if (!this.currencyField.value) {
            this.currencyField.value = this.currency;
        }

        // interne Felder verstecken
        this.currencyField.style.display = 'none';
        this.valueField.style.display = 'none';
        if (this.valueField.type !== 'hidden') {
            this.valueField.type = 'hidden';
        }

        // Wrapper (Bootstrap input-group kompatibel)
        const wrapper = document.createElement('div');
        wrapper.className = 'currency-input-wrapper input-group';

        const prefix = document.createElement('span');
        prefix.className = 'input-group-text';
        prefix.textContent = this._currencySymbol(this.currency);

        this.visibleInput = document.createElement('input');
        this.visibleInput.type = 'text';
        this.visibleInput.inputMode = 'decimal';
        this.visibleInput.autocomplete = 'off';
        this.visibleInput.className = 'form-control currency-input-field';

        this.visibleInput.disabled = this.currencyField.disabled || this.valueField.disabled;

        // rechtsbündig + Monospace
        this.visibleInput.style.textAlign = 'right';
        this.visibleInput.style.fontFamily = 'monospace';

        wrapper.appendChild(this.visibleInput);
        wrapper.appendChild(prefix);
        this.appendChild(wrapper);

        // Initialwert aus Cent
        const initialCents = parseInt(this.valueField.value, 10);
        if (!isNaN(initialCents)) {
            this.visibleInput.value = this._formatFromCents(initialCents);
        }

        this.visibleInput.addEventListener('input', this._onInput);
        this.visibleInput.addEventListener('blur', this._onBlur);
    }

    disconnectedCallback() {
        if (!this.visibleInput) return;
        this.visibleInput.removeEventListener('input', this._onInput);
        this.visibleInput.removeEventListener('blur', this._onBlur);
    }

    // ------------------------------------------------------------
    // Events
    // ------------------------------------------------------------

    _onInput() {
        const cents = this._parseToCents(this.visibleInput.value);
        this.valueField.value = cents.toString();
        this.dispatchEvent(new Event('input', { bubbles: true }));
    }

    _onBlur() {
        const cents = this._parseToCents(this.visibleInput.value);
        this.valueField.value = cents.toString();
        this.visibleInput.value = this._formatFromCents(cents);
        this.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------

    _detectDecimalSeparator() {
        try {
            const formatted = new Intl.NumberFormat(navigator.language || 'de-DE').format(1.1);
            const nonDigits = formatted.replace(/\d/g, '');
            return nonDigits[0] || ',';
        } catch {
            return ',';
        }
    }

    _createNumberFormatter() {
        try {
            return new Intl.NumberFormat(navigator.language || 'de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
                useGrouping: true
            });
        } catch {
            // Fallback: keine Gruppierung, aber 2 Nachkommastellen
            return {
                format: (value) => {
                    const [intPart, fracPart = '00'] = value.toFixed(2).split('.');
                    return intPart + this._decimalSeparator + fracPart;
                }
            };
        }
    }

    _currencySymbol(code) {
        const map = { EUR: '€', USD: '$', GBP: '£', CHF: 'CHF' };
        return map[code] || code;
    }

    /**
     * String -> Cent (Integer)
     *
     * Features:
     * - letzter Char "," oder "."  -> Euros * 100  (z.B. "12," -> 1200)
     * - Dezimalteil mit 1 Stelle   -> aufgefüllt auf 2 Stellen ("12,3" -> 12,30)
     * - Tausendertrennzeichen egal ("1.234,5" -> 1234,50)
     */
    _parseToCents(str) {
        if (!str) return 0;

        let trimmed = str.trim();
        if (!trimmed) return 0;

        // Negativ berücksichtigen
        let sign = 1;
        if (trimmed[0] === '-') {
            sign = -1;
            trimmed = trimmed.slice(1);
        }

        if (!trimmed) return 0;

        const lastChar = trimmed.charAt(trimmed.length - 1);

        // Fall 1: letzter Char ist Dezimaltrennzeichen → ",00" ergänzen
        if (lastChar === ',' || lastChar === '.') {
            const eurosDigits = (trimmed.slice(0, -1).match(/\d/g) || []).join('');
            if (eurosDigits.length === 0) return 0;
            let euros = parseInt(eurosDigits, 10) || 0;
            return sign * euros * 100;
        }

        // Fall 2: String enthält Dezimalteil (Komma oder Punkt)
        const lastComma = trimmed.lastIndexOf(',');
        const lastDot   = trimmed.lastIndexOf('.');
        const sepIndex  = Math.max(lastComma, lastDot);

        if (sepIndex !== -1) {
            const intPartStr  = trimmed.slice(0, sepIndex);
            const fracPartStr = trimmed.slice(sepIndex + 1);

            const intDigits  = (intPartStr.match(/\d/g)  || []).join('');
            const fracDigits = (fracPartStr.match(/\d/g) || []).join('');

            if (!intDigits && !fracDigits) return 0;

            const euros = parseInt(intDigits || '0', 10) || 0;

            let decimals = 0;
            if (fracDigits.length === 0) {
                decimals = 0;
            } else if (fracDigits.length === 1) {
                // eine Nachkommastelle -> auffüllen
                decimals = parseInt(fracDigits, 10) * 10;    // "3" -> 30
            } else {
                // mehr als 2 Stellen -> auf 2 Stellen kürzen
                decimals = parseInt(fracDigits.slice(0, 2), 10);
            }

            return sign * (euros * 100 + decimals);
        }

        // Fall 3: kein Dezimaltrennzeichen -> gesamte Zahl ist Eurobetrag (keine automatische Cent-Interpretation)
        const digits = (trimmed.match(/\d/g) || []).join('');
        if (digits.length === 0) return 0;

        const euros = parseInt(digits, 10) || 0;
        return sign * (euros * 100);
    }

    /**
     * Cent -> formatierter String mit Locale:
     * - Tausendertrennzeichen (z.B. 1.234,56)
     * - immer 2 Nachkommastellen
     */
    _formatFromCents(cents) {
        if (isNaN(cents)) cents = 0;

        let sign = '';
        if (cents < 0) {
            sign = '-';
            cents = Math.abs(cents);
        }

        const valueInEuros = cents / 100;
        const formatted = this._numberFormatter.format(valueInEuros);

        return sign + formatted;
    }
}

customElements.define('fucodo-form-currency', CurrencyInput);

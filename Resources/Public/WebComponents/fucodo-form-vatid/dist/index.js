(() => {
  // packages/fucodo-form-vatid/index.js
  (() => {
    class VatIdInput extends HTMLElement {
      constructor() {
        super();
        this._onInput = this._onInput.bind(this);
        this._onBlur = this._onBlur.bind(this);
        this._patterns = [
          { cc: "AT", re: /^ATU\d{8}$/ },
          { cc: "BE", re: /^BE\d{10}$/ },
          { cc: "BG", re: /^BG\d{9,10}$/ },
          { cc: "CY", re: /^CY\d{8}[A-Z]$/ },
          { cc: "CZ", re: /^CZ\d{8,10}$/ },
          { cc: "DE", re: /^DE\d{9}$/ },
          { cc: "DK", re: /^DK\d{8}$/ },
          { cc: "EE", re: /^EE\d{9}$/ },
          { cc: "EL", re: /^EL\d{9}$/ },
          { cc: "GR", re: /^GR\d{9}$/ },
          // accept GR too
          { cc: "ES", re: /^ES[A-Z0-9]\d{7}[A-Z0-9]$/ },
          { cc: "FI", re: /^FI\d{8}$/ },
          { cc: "FR", re: /^FR[A-Z0-9]{2}\d{9}$/ },
          { cc: "HR", re: /^HR\d{11}$/ },
          { cc: "HU", re: /^HU\d{8}$/ },
          // Ireland has multiple legacy variants; this covers common ones.
          { cc: "IE", re: /^IE(\d{7}[A-Z]{1,2}|\d[A-Z*+]\d{5}[A-Z])$/ },
          { cc: "IT", re: /^IT\d{11}$/ },
          { cc: "LT", re: /^LT(\d{9}|\d{12})$/ },
          { cc: "LU", re: /^LU\d{8}$/ },
          { cc: "LV", re: /^LV\d{11}$/ },
          { cc: "MT", re: /^MT\d{8}$/ },
          { cc: "NL", re: /^NL\d{9}B\d{2}$/ },
          { cc: "PL", re: /^PL\d{10}$/ },
          { cc: "PT", re: /^PT\d{9}$/ },
          { cc: "RO", re: /^RO\d{2,10}$/ },
          { cc: "SE", re: /^SE\d{12}$/ },
          { cc: "SI", re: /^SI\d{8}$/ },
          { cc: "SK", re: /^SK\d{10}$/ },
          // Optional (EU trade with Northern Ireland uses XI)
          // Keeping it permissive for format-only checks.
          { cc: "XI", re: /^XI[A-Z0-9]{5,12}$/ }
        ];
      }
      connectedCallback() {
        if (this._initialized) return;
        this._initialized = true;
        this.countryField = this.querySelector('[slot="country"]');
        this.valueField = this.querySelector('[slot="value"]');
        if (!this.valueField) {
          console.warn('fucodo-form-vatid requires an input with slot="value".');
          return;
        }
        if (this.countryField) this.countryField.style.display = "none";
        this.valueField.style.display = "none";
        if (this.valueField.type !== "hidden") this.valueField.type = "hidden";
        const wrapper = document.createElement("div");
        wrapper.className = "vatid-input-wrapper";
        this.visibleInput = document.createElement("input");
        this.visibleInput.type = "text";
        this.visibleInput.inputMode = "text";
        this.visibleInput.autocomplete = "off";
        this.visibleInput.className = "form-control vatid-input-field";
        this.visibleInput.placeholder = this.getAttribute("placeholder") || "e.g. DE123456789";
        this.visibleInput.disabled = !!(this.valueField.disabled || this.countryField && this.countryField.disabled);
        this.visibleInput.style.fontFamily = "monospace";
        this.helpLine = document.createElement("div");
        this.helpLine.className = "form-text vatid-help";
        this.helpLine.textContent = "";
        wrapper.appendChild(this.visibleInput);
        wrapper.appendChild(this.helpLine);
        this.appendChild(wrapper);
        const initial = (this.valueField.value || "").trim();
        if (initial) {
          const norm = this._normalize(initial);
          this.visibleInput.value = this._pretty(norm);
          this._syncHiddenAndValidate(
            norm,
            /*emit*/
            false
          );
        } else {
          this._syncHiddenAndValidate(
            "",
            /*emit*/
            false
          );
        }
        this.visibleInput.addEventListener("input", this._onInput);
        this.visibleInput.addEventListener("blur", this._onBlur);
        if (this.countryField) {
          this.countryField.addEventListener("change", () => {
            const norm = this._normalize(this.visibleInput.value);
            const enforced = this._enforceCountryPrefix(norm);
            this.visibleInput.value = this._pretty(enforced);
            this._syncHiddenAndValidate(
              enforced,
              /*emit*/
              true
            );
          });
        }
      }
      disconnectedCallback() {
        if (!this.visibleInput) return;
        this.visibleInput.removeEventListener("input", this._onInput);
        this.visibleInput.removeEventListener("blur", this._onBlur);
      }
      // ------------------------------------------------------------
      // Events
      // ------------------------------------------------------------
      _onInput() {
        const norm = this._normalize(this.visibleInput.value);
        const enforced = this._enforceCountryPrefix(norm);
        this._syncHiddenAndValidate(
          enforced,
          /*emit*/
          true,
          /*live*/
          true
        );
      }
      _onBlur() {
        const norm = this._normalize(this.visibleInput.value);
        const enforced = this._enforceCountryPrefix(norm);
        this.valueField.value = enforced;
        this.visibleInput.value = this._pretty(enforced);
        this._applyValidationUI(
          this._validate(enforced),
          enforced,
          /*live*/
          false
        );
        this.dispatchEvent(new Event("change", { bubbles: true }));
      }
      // ------------------------------------------------------------
      // Core helpers
      // ------------------------------------------------------------
      _normalize(value) {
        if (!value) return "";
        return String(value).toUpperCase().replace(/[\s\.\-_/]/g, "");
      }
      _pretty(norm) {
        if (!norm) return "";
        const cc = norm.slice(0, 2);
        const rest = norm.slice(2);
        if (!/^[A-Z]{2}$/.test(cc)) return norm;
        return cc + (rest ? " " + rest : "");
      }
      _countryFromField() {
        if (!this.countryField) return "";
        const v = (this.countryField.value || "").trim().toUpperCase();
        return v;
      }
      _enforceCountryPrefix(norm) {
        const forced = this._countryFromField();
        if (!forced) return norm;
        if (!norm) return forced;
        if (norm.startsWith(forced)) return norm;
        if (/^[A-Z]{2}/.test(norm)) return forced + norm.slice(2);
        return forced + norm;
      }
      _validate(norm) {
        if (!norm) return { ok: true, empty: true, reason: "" };
        const cc = norm.slice(0, 2);
        const rest = norm.slice(2);
        if (!/^[A-Z]{2}$/.test(cc)) {
          return { ok: false, empty: false, reason: "Missing country prefix (2 letters)." };
        }
        if (!rest) {
          return { ok: false, empty: false, reason: "Missing VAT number body after country prefix." };
        }
        const entry = this._patterns.find((p) => p.cc === cc);
        if (!entry) {
          return { ok: false, empty: false, reason: `Unsupported country code: ${cc}` };
        }
        if (!entry.re.test(norm)) {
          return { ok: false, empty: false, reason: `Invalid format for ${cc}.` };
        }
        return { ok: true, empty: false, reason: "" };
      }
      _syncHiddenAndValidate(norm, emit, live = false) {
        this.valueField.value = norm;
        const res = this._validate(norm);
        this._applyValidationUI(res, norm, live);
        if (emit) this.dispatchEvent(new Event("input", { bubbles: true }));
      }
      _applyValidationUI(res, norm, live) {
        this.visibleInput.classList.remove("is-valid", "is-invalid");
        if (res.empty) {
          this.visibleInput.removeAttribute("aria-invalid");
          this.helpLine.textContent = this.getAttribute("hint") || "";
          return;
        }
        if (res.ok) {
          this.visibleInput.classList.add("is-valid");
          this.visibleInput.setAttribute("aria-invalid", "false");
          const cc = norm.slice(0, 2);
          this.helpLine.textContent = this.getAttribute("valid-text") || `VAT ID format looks valid (${cc}).`;
        } else {
          if (live && norm.length < 4) {
            this.visibleInput.removeAttribute("aria-invalid");
            this.helpLine.textContent = this.getAttribute("hint") || "";
            return;
          }
          this.visibleInput.classList.add("is-invalid");
          this.visibleInput.setAttribute("aria-invalid", "true");
          this.helpLine.textContent = this.getAttribute("invalid-text") || res.reason;
        }
      }
    }
    customElements.define("fucodo-form-vatid", VatIdInput);
  })();
})();

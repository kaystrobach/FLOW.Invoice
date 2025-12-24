class FucodoFormAutosave extends HTMLElement {
    static get observedAttributes() {
        return ["seconds", "disabled", "indicator"];
    }

    constructor() {
        super();
        this._timer = null;
        this._indicatorTimer = null;
        this._seconds = 10;
        this._indicatorSeconds = 10;
        this._onActivity = this._onActivity.bind(this);
        this._onSubmit = this._onSubmit.bind(this);
    }

    connectedCallback() {
        this._readAttrs();
        this._attachToForm();
        this._resolveIndicator();
        this._startTimer();
    }

    disconnectedCallback() {
        this._clearTimer();
        this._detachFromForm();
    }

    attributeChangedCallback() {
        this._readAttrs();
        this._resolveIndicator();
        this._startTimer();
    }

    /* ---------- setup ---------- */

    _readAttrs() {
        const s = Number(this.getAttribute("seconds"));
        this._seconds = Number.isFinite(s) && s > 0 ? s : 10;
        this._disabled = this.hasAttribute("disabled");
        this._indicatorSelector = this.getAttribute("indicator");
    }

    _resolveIndicator() {
        this._indicator = null;
        this._indicatorMode = null;

        if (!this._indicatorSelector) return;

        try {
            this._indicator = this.querySelector(this._indicatorSelector);
        } catch {
            return;
        }

        if (!this._indicator) return;

        // Mode-Erkennung
        if (this._indicator.tagName === "PROGRESS") {
            this._indicatorMode = "progress";
            this._indicator.max ||= 100;
        } else {
            this._indicatorMode = "style";
        }

        this._resetIndicator();
    }

    _attachToForm() {
        const form = this.querySelector("form");
        if (!form) return;

        this._form = form;
        const events = ["input", "change", "keyup", "paste", "cut", "click"];
        events.forEach(e => form.addEventListener(e, this._onActivity, true));
        form.addEventListener("submit", this._onSubmit, true);
    }

    _detachFromForm() {
        if (!this._form) return;
        const events = ["input", "change", "keyup", "paste", "cut", "click"];
        events.forEach(e => this._form.removeEventListener(e, this._onActivity, true));
        this._form.removeEventListener("submit", this._onSubmit, true);
        this._form = null;
    }

    /* ---------- activity ---------- */

    _onActivity() {
        if (this._disabled) return;
        this._startTimer();
    }

    _onSubmit() {
        this._clearTimer();
    }

    /* ---------- timers ---------- */

    _startTimer() {
        this._clearTimer();
        if (this._disabled || !this._form) return;

        const totalMs = this._seconds * 1000;
        const indicatorStartMs = Math.max(
            0,
            totalMs - this._indicatorSeconds * 1000
        );

        if (this._indicator) {
            setTimeout(() => this._startIndicatorCountdown(), indicatorStartMs);
        }

        this._timer = setTimeout(() => {
            if (!this._form?.isConnected) return;
            this._form.requestSubmit
                ? this._form.requestSubmit()
                : this._form.submit();
        }, totalMs);
    }

    _clearTimer() {
        clearTimeout(this._timer);
        clearInterval(this._indicatorTimer);
        this._timer = null;
        this._indicatorTimer = null;
        this._resetIndicator();
    }

    /* ---------- indicator ---------- */

    _resetIndicator() {
        if (!this._indicator) return;

        if (this._indicatorMode === "progress") {
            this._indicator.value = this._indicator.max;
        } else {
            this._indicator.style.width = "100%";
        }
    }

    _setIndicator(percent) {
        if (!this._indicator) return;

        if (this._indicatorMode === "progress") {
            this._indicator.value =
                (this._indicator.max * percent) / 100;
        } else {
            this._indicator.style.width = percent + "%";
        }
    }

    _startIndicatorCountdown() {
        if (!this._indicator) return;

        let percent = 100;
        this._resetIndicator();

        clearInterval(this._indicatorTimer);
        this._indicatorTimer = setInterval(() => {
            percent -= 10;
            this._setIndicator(Math.max(percent, 0));

            if (percent <= 0) {
                clearInterval(this._indicatorTimer);
                this._indicatorTimer = null;
            }
        }, 1000);
    }
}

customElements.define("fucodo-form-autosave", FucodoFormAutosave);
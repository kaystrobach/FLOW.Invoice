import { LitElement, html} from 'lit';

export class LazyLoad extends LitElement {
  static properties = {
    url: { type: String },
    selectors: { type: String },
  }

  createRenderRoot() {
    return this;
  }

  connectedCallback() {
    super.connectedCallback();

    this.observer = new IntersectionObserver(this.handleVisibilityChange.bind(this));

    this.observer.observe(this);
  }

  disconnectedCallback() {
    this.observer.disconnect();

    super.disconnectedCallback();
  }

  handleVisibilityChange(entries) {
    // the observer also reports the initial state, e.g. hidden inside a closed offcanvas:
    // load only once the element is actually visible, and only once
    if (!entries.some(entry => entry.isIntersecting)) {
      return;
    }
    this.observer.disconnect();

    this.fetchContent(this.url)
      .then(content => {
        const template = new DOMParser().parseFromString(content, 'text/html')

        if (this.selectors) {
          this.innerHTML = template.querySelector(this.selectors)?.innerHTML;

          return;
        }

        this.innerHTML = template.body.innerHTML;
      })
      .catch(error => {
        console.error('Error fetching content:', error);
      });
  }

  async fetchContent(url) {
    const options = {
      method: 'GET',
      headers: {
        'Accept': 'text/html',
      },
    };

    const response = await fetch(url, options);

    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }

    return await response.text();
  }

  render() {
    return html`
        <slot></slot>
    `;
  }
}

customElements.define('fucodo-lazy-load', LazyLoad);
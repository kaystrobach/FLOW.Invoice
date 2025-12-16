import '../index.js';

export default {
  title: 'Form/fucodo-form-vatid',
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Web Component for EU VAT ID entry with live format-only validation and optional enforced country prefix.',
      },
    },
  },
  argTypes: {
    country: {
      control: { type: 'text' },
      description: 'Two-letter country code to enforce as prefix (slot="country"). Leave empty to allow free typing.',
      table: { category: 'content (slot: country)' },
    },
    value: {
      control: { type: 'text' },
      description: 'Initial normalized VAT value (slot="value"). Example: DE123456789',
      table: { category: 'content (slot: value)' },
    },
    placeholder: {
      control: { type: 'text' },
      description: 'Placeholder for the visible input.',
      table: { category: 'attributes' },
    },
    hint: {
      control: { type: 'text' },
      description: 'Helper text when empty or undecided.',
      table: { category: 'attributes' },
    },
    validText: {
      control: { type: 'text' },
      description: 'Message when current value matches the country format (maps to valid-text).',
      table: { category: 'attributes' },
    },
    invalidText: {
      control: { type: 'text' },
      description: 'Message when the format is invalid (maps to invalid-text).',
      table: { category: 'attributes' },
    },
  },
};

const Template = ({ country = '', value = '', placeholder = 'e.g. DE123456789', hint = '', validText = '', invalidText = '' }) => {
  const host = document.createElement('div');
  host.style.minWidth = '360px';

  const hasCountry = !!(country && String(country).trim());

  host.innerHTML = `
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" crossorigin="anonymous">
    <fucodo-form-vatid
      ${placeholder ? `placeholder="${placeholder}"` : ''}
      ${hint ? `hint="${hint}"` : ''}
      ${validText ? `valid-text="${validText}"` : ''}
      ${invalidText ? `invalid-text="${invalidText}"` : ''}
    >
      ${hasCountry ? `<select slot="country" name="country"><option value="${country}">${country}</option></select>` : ''}
      <input slot="value" name="vat_id" value="${value || ''}" />
    </fucodo-form-vatid>
  `;

  return host;
};

export const Basic = Template.bind({});
Basic.args = {
  country: '',
  value: 'DE123456789',
  placeholder: 'e.g. DE123456789',
  hint: 'Enter your EU VAT ID',
  validText: 'VAT ID format looks valid',
  invalidText: 'Please check the format',
};

export const WithCountrySelector = Template.bind({});
WithCountrySelector.args = {
  country: 'DE',
  value: '',
  placeholder: 'DE…',
  hint: 'Prefixed with DE',
  validText: '',
  invalidText: '',
};

export const InvalidExample = Template.bind({});
InvalidExample.args = {
  country: 'AT',
  value: 'AT123',
  placeholder: 'AT…',
  hint: 'ATU followed by 8 digits',
  validText: '',
  invalidText: 'Invalid format',
};

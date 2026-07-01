import '../index.js';

export default {
  title: 'Form/CurrencyInput',
  parameters: {
    layout: 'centered',
    docs: {
      description: {
        component:
          'Web Component for currency input that keeps a hidden cent value and shows a formatted number with locale-aware separators.',
      },
    },
  },
  argTypes: {
    currency: {
      control: { type: 'text' },
      description: 'Currency code used for the prefix indicator (e.g., EUR, USD).',
      table: { category: 'attributes' },
    },
    valueInCents: {
      control: { type: 'number' },
      description: 'Initial numeric value in cents for the hidden value input.',
      table: { category: 'content (slot: value)' },
    },
    disabled: {
      control: { type: 'boolean' },
      description: 'Whether the input is disabled.',
      table: { category: 'attributes' },
    },
    readOnly: {
      control: { type: 'boolean' },
      description: 'Whether the input is read-only.',
      table: { category: 'attributes' },
    },
  },
};

const Template = ({ currency, valueInCents, disabled, readOnly }) => {
  const host = document.createElement('div');
  host.style.minWidth = '320px';

  host.innerHTML = `
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" crossorigin="anonymous">
    <fucodo-form-currency currency="${currency}">
      <input slot="currency" name="currency" value="${currency}" ${disabled ? 'disabled' : ''} ${readOnly ? 'readonly' : ''} />
      <input slot="value" name="amount" value="${Number(valueInCents) || 0}" ${disabled ? 'disabled' : ''} ${readOnly ? 'readonly' : ''} />
    </fucodo-form-currency>
  `;

  return host;
};

export const Basic = Template.bind({});
Basic.args = {
  currency: 'EUR',
  valueInCents: 12345,
};

export const Empty = Template.bind({});
Empty.args = {
  currency: 'EUR',
  valueInCents: 0,
};

export const Disabled = Template.bind({});
Disabled.args = {
  currency: 'EUR',
  valueInCents: 12345,
  disabled: true,
};

export const ReadOnly = Template.bind({});
ReadOnly.args = {
  currency: 'EUR',
  valueInCents: 12345,
  readOnly: true,
};

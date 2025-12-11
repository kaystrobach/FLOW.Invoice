import '../dist/index';

export default {
  title: 'Components/fucodo-lazy-load',
  parameters: {
    layout: 'centered',
  },
  argTypes: {
    url: { control: 'text', description: 'URL to fetch and inject into the element' },
    selectors: { control: 'text', description: 'CSS selector(s) to extract from the fetched HTML' },
  },
};

const Template = ({ url = 'lazy.html', selectors = '' }) => {
  const el = document.createElement('fucodo-lazy-load');
  el.setAttribute('url', url);
  if (selectors) el.setAttribute('selectors', selectors);

  const loading = document.createElement('span');
  loading.setAttribute('aria-busy', 'true');
  loading.textContent = 'Loading...';
  el.appendChild(loading);

  return el;
};

export const Default = Template.bind({});
Default.args = {
  url: 'lazy.html',
  selectors: '',
};
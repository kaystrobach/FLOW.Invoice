import '../dist/index';

export default {
  title: 'Apps/Editors/Markdown',
};

export const Default = () => {
  const wrapper = document.createElement('main');
  wrapper.innerHTML = `
    <fucodo-editor class="custom">
      <textarea>
# Hello World!
This is a simple markdown editor.
- foo
- bar
      </textarea>
    </fucodo-editor>
    <button id="save" class="button">Save</button>
    <code id="output"></code>
    <style>
      .custom {
        border: 1px solid black;
        border-radius: 5px;
      }
    </style>
  `;

  wrapper.querySelector('#save').addEventListener('click', () => {
    const editor = wrapper.querySelector('fucodo-editor');
    const output = wrapper.querySelector('#output');
    output.innerText = editor.getMarkdown();
  });

  return wrapper;
};
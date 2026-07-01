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

export const Disabled = () => {
  const wrapper = document.createElement('main');
  wrapper.innerHTML = `
    <fucodo-editor class="custom">
      <textarea disabled>
# Disabled Editor
This editor is disabled because the textarea has the disabled attribute.
      </textarea>
    </fucodo-editor>
    <style>
      .custom {
        border: 1px solid black;
        border-radius: 5px;
      }
    </style>
  `;

  return wrapper;
};

export const Readonly = () => {
  const wrapper = document.createElement('main');
  wrapper.innerHTML = `
    <fucodo-editor class="custom">
      <textarea readonly>
# Readonly Editor
This editor is readonly because the textarea has the readonly attribute.
      </textarea>
    </fucodo-editor>
    <style>
      .custom {
        border: 1px solid black;
        border-radius: 5px;
      }
    </style>
  `;

  return wrapper;
};
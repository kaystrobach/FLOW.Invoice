import {LitElement, html, css, unsafeCSS} from 'lit';

import { Editor } from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import {TaskList} from '@tiptap/extension-task-list';
import {TaskItem} from '@tiptap/extension-task-item';
import {Image} from '@tiptap/extension-image';
import {CodeBlock} from '@tiptap/extension-code-block';
import {Link} from '@tiptap/extension-link';

import {Markdown} from "tiptap-markdown";

import * as icons from './icons'

import style from './style.scss'

class MyEditor extends LitElement {
  static styles = css`${unsafeCSS(style)}`;

  render() {
    return html`
        <slot></slot>
        <div class="toolbar">
            <button class="button" aria-label="bold" @click="${() => {this.editor.chain().focus().toggleBold().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.boldIcon}></div></button>
            <button class="button" aria-label="italic" @click="${() => {this.editor.chain().focus().toggleItalic().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.italicIcon}></div></button>
            <button class="button" aria-label="strike" @click="${() => {this.editor.chain().focus().toggleStrike().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.strikeIcon}></div></button>
            <button class="button" aria-label="list unordered" @click="${() => {this.editor.chain().focus().toggleBulletList().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.bulletListIcon}></div></button>
            <button class="button" aria-label="list ordered" @click="${() => {this.editor.chain().focus().toggleOrderedList().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.orderedListIcon}></div></button>
            <button class="button" aria-label="list tasks" @click="${() => {this.editor.chain().focus().toggleTaskList().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.taskListIcon}></div></button>
            <span>
                <input type="file" id="imageUpload" accept="image/*"  aria-label="image upload" @change="${this.handleImageUpload}" ?disabled="${this._markdownMode}">
                <label for="imageUpload" class="button"><div class="icon" .innerHTML=${icons.imageIcon}></div></label>
            </span>
            <button class="button" aria-label="quote" @click="${() => {this.editor.chain().focus().toggleBlockquote().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.quoteIcon}></div></button>
            <button class="button" aria-label="undo" @click="${() => {this.editor.chain().focus().undo().run()}}" ?disabled="${!this._canUndo || this._markdownMode}"><div class="icon" .innerHTML=${icons.undoIcon}></div></button>
            <button class="button" aria-label="redo" @click="${() => {this.editor.chain().focus().redo().run()}}" ?disabled="${!this._canRedo || this._markdownMode}"><div class="icon" .innerHTML=${icons.redoIcon}></div></button>
            <button class="button" aria-label="code block" @click="${() => {this.editor.chain().focus().toggleCodeBlock().run()}}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.codeIcon}></div></button>
            <button class="button" aria-label="link" @click="${this.handleSetLink}" ?disabled="${this._markdownMode}"><div class="icon" .innerHTML=${icons.linkIcon}></div></button>
            <button class="button" aria-label="markdown mode" @click="${this.toggleMode}"><div class="icon" .innerHTML=${icons.markdownIcon}></div></button>
        </div>
        <span class="divider"></span>
        <div id="editor" style="${this._markdownMode ? 'display: none;' : ''}"></div>
        ${this._markdownMode ? html`<textarea class="markdown-input" .value="${this._markdownText}" @input="${this.updateFromTextarea}"></textarea>` : null}
    `;
  }

  static get properties() {
    return {
      _canUndo: { state: true },
      _canRedo: { state: true },
      _markdownMode: { state: true },
      _markdownText: { state: true },
    };
  }

  constructor() {
    super();

    this._canUndo = false;
    this._canRedo = false;
    this._markdownMode = false;
    this._markdownText = '';

    this._origin = null;
  }

  connectedCallback() {
    super.connectedCallback();
  }

  firstUpdated(_changedProperties) {
    super.firstUpdated(_changedProperties);

    let content = '';

    this.shadowRoot.querySelector('slot').assignedElements().forEach((element) => {
      if (element.tagName.toLowerCase() === 'textarea') {
        content = element.innerHTML.split('\n').map(line => line.trim()).join('\n');
      }

      element.style.display = 'none';

      this._origin = element;
    });

    this.editor = new Editor({
      element: this.shadowRoot.querySelector('#editor'),
      extensions: [
        StarterKit,
        TaskList,
        TaskItem,
        Image.configure({
          allowBase64: true,
        }),
        Markdown,
        CodeBlock,
        Link,
      ],
      content: content,
      onUpdate: () => {
        this._canUndo = this.editor.can().undo();
        this._canRedo = this.editor.can().redo();

        this.updateOrigin();
      },
      editorProps: {
        attributes: {
          class: 'editor',
        },
      },
    })
  }

  disconnectedCallback() {
    super.disconnectedCallback();

    this.editor.destroy();
  }

  handleImageUpload(event) {
    const reader = new FileReader();

    reader.onload = (event) => {
      this.editor.chain().focus().setImage({src: event.target.result}).run();
    }

    if (event.target.files.length > 0) {
      reader.readAsDataURL(event.target.files[0]);
    }
  }

  handleSetLink() {
    const previousUrl = this.editor.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) {
      return
    }

    if (url === '') {
      this.editor.chain().focus().extendMarkRange('link').unsetLink().run();

      return
    }

    this.editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
  }

  getMarkdown() {
    if (this._markdownMode) {
      this.editor.commands.setContent(this._markdownText);
    }

    return this.editor.storage.markdown.getMarkdown();
  }

  toggleMode() {
    if (!this._markdownMode) {
      this._markdownText = this.getMarkdown();
    } else {
      this.editor.commands.setContent(this._markdownText);
    }
    this._markdownMode = !this._markdownMode;
  }

  updateFromTextarea(event) {
    this._markdownText = event.target.value;

    this.updateOrigin();
  }

  updateOrigin() {
    if (this._origin == null) {
      return;
    }

    this._origin.value = this.getMarkdown();
  }
}

customElements.define('fucodo-editor', MyEditor);

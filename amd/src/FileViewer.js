// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software:
// you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ProFormA Question Type for Moodle.
// If not, see <http://www.gnu.org/licenses/>.

/**
 * The ProFormA Question CodeMirror support functions
 *
 * @package
 * @subpackage proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */


/**
 * NOTE:
 * Currently creating subfolders is disabled, because the moodle question filesaver does not support it
 */

let autosaveIntervall = -1; // In milliseconds

/* eslint-disable no-unused-vars */

// Use these imports for Moodle
// -----------------------------
import {debugLog} from './debug';
import "./MoodleSyncer";

import './codemirror-global';
import CodeMirror from "./codemirror";

import "./clike";
import "./python";
import "./javascriptmode"; // Renamed from javascript
import "./xml";
import "./matchbrackets";
import "./closebrackets";
import "./active-line";

// Import Config from 'core/config';
import * as Str from 'core/str';
import Notification from 'core/notification';
// Import {get_string as getString} from 'core/str';
import ModalEvents from 'core/modal_events';
import SaveCancelModal from 'core/modal_save_cancel';
import CancelModal from "core/modal_cancel";

// Use this for editortest.html
// -----------------------------
/*
import {debugLog} from './debug.js';
import './codemirror-global.js';
import "./FakeSyncer.js";

import CodeMirror from "./codemirror/src/codemirror.js";
import "./codemirror/mode/clike/clike.js";
import "./codemirror/mode/javascript/javascript.js";
import "./codemirror/mode/python/python.js";
import "./codemirror/mode/xml/xml.js";
import "./codemirror/addon/selection/active-line.js";
import "./codemirror/addon/edit/matchbrackets.js";
import "./codemirror/addon/edit/closebrackets.js";
class Config { // Fake
    static wwwroot = '';
    static sesskey = '';
}
class FakeAjaxResult {
    constructor(result) {
        this.result = result;
    }
    done(callback) {
        callback(this.result);
        return this;
    }
    fail(callback) {
        return this;
    }
}
class Str {
    static get_strings(dict) {
        console.log('fake get_strings');
        console.log(dict);
        let result = [];
        let index = 0;
        dict.forEach(function(item, index, array) {
            // var value = dict[key];
            result[index] = item['key'];
        });
        console.log(result);
        return new FakeAjaxResult(result);
        // return FakeAjaxResult.create(result);
        // return Promise.resolve(result);
    }
}
function getString(text) { return text; }
*/


// 'use strict'; ecma6 code is always strict


// TODO:
// - Split View: Problem mit Flackern
// - Theme wechseln
// - Menu erstmal raus - außer zum Wechseln des Themes
// - Andere Browser testen

/**
 * Element for overlays (context menu, dialogs): in fullscreen mode only the fullscreen element
 * (e.g. the task editor) is visible.
 *
 * @returns {HTMLElement}
 */
const getOverlayParent = () => document.fullscreenElement || document.body;

/**
 * Colour theme of the explorer ('dark' or 'light'). The choice is a per-browser preference
 * (localStorage) and applies to all explorers on the page.
 */
const themeStorageKey = 'qtype_proforma_ide_theme';
const themeChangedEvent = 'qtype_proforma_ide_theme_changed';
const storedIdeTheme = (() => {
    try {
        const theme = window.localStorage.getItem(themeStorageKey);
        return (theme === 'light' || theme === 'dark') ? theme : null;
    } catch (e) {
        return null;
    }
})();
let ideTheme = storedIdeTheme || 'dark';

/**
 * @returns {string} CodeMirror theme for the current explorer theme
 */
const getCodemirrorTheme = () => (ideTheme === 'light' ? 'default' : 'darcula');

/**
 * Changes the explorer theme of all explorers on the page and remembers it.
 *
 * @param {string} theme 'dark' or 'light'
 */
const setIdeTheme = theme => {
    ideTheme = theme;
    try {
        window.localStorage.setItem(themeStorageKey, theme);
    } catch (e) {
        // Storage not available (e.g. private mode) => theme only for this page.
    }
    window.dispatchEvent(new CustomEvent(themeChangedEvent));
};

/**
 * Moves a shown modal into the fullscreen element (Moodle appends modals to the body).
 *
 * @param {Object} modal Moodle modal
 */
const moveModalToFullscreen = modal => {
    const fullscreen = document.fullscreenElement;
    if (fullscreen) {
        fullscreen.appendChild(modal.getRoot()[0]);
    }
};

/**
 * Replacement for prompt needed for behat tests.
 * Behat tests fail on prompt and alert.
 *
 * @param {string} titleId string identifier of the title
 * @param {string} labelId string identifier of the input label
 * @param {string} defaultValue default value of the input field
 * @param {Function} callback function called with the entered value
 */
function modalPrompt(titleId, labelId, defaultValue, callback) {
    let stringsPromise = Str.get_strings([
        {key: titleId, component: 'qtype_proforma'},
        {key: labelId, component: 'qtype_proforma'},
        ]);
    let modalPromise = SaveCancelModal.create();

    Promise.all([stringsPromise, modalPromise])
        .then(([strings, modal]) => {
            modal.setTitle(strings[0]);
            modal.setSaveButtonText('Ok');
            modal.setBody(strings[1] +
                '<input type="text" name="promptname" value="' + defaultValue + '" size="40"></input>');
            modal.getRoot().on(ModalEvents.save, () => {
                let result = document.querySelector("input[name='promptname']").value;
                modal.getRoot().remove();
                // The callback is called from an event handler, not from the promise chain.
                // eslint-disable-next-line promise/no-callback-in-promise
                callback(result);
            });
            const shown = modal.show();
            // Add trigger for return to trigger default action.
            let defaultButton = modal.getRoot().find('.btn-primary');
            document.querySelector("input[name='promptname']")
                .addEventListener("keyup", function(event) {
                    event.preventDefault();
                    if (event.keyCode === 13) {
                        defaultButton.click();
                    }
                });
            return shown.then(() => moveModalToFullscreen(modal));
        })
        // Set focus into input field.
        .then(() => document.querySelector("input[name='promptname']").focus())
        .catch(error => {
            console.error('error:', error);
            Notification.exception(error);
        });
}


/**
 * Shows an information dialog (replacement for alert).
 *
 * @param {string} textId string identifier of the text
 * @param {*} param optional parameter for the string
 */
function modalAlert(textId, param) {
    let stringsPromise;
    if (param) {
        stringsPromise = Str.get_strings([
            {key: 'info'},
            {key: textId, component: 'qtype_proforma', param},
        ]);
    } else {
        stringsPromise = Str.get_strings([
            {key: 'info'},
            {key: textId, component: 'qtype_proforma'},
        ]);
    }
    let modalPromise = CancelModal.create();
    Promise.all([stringsPromise, modalPromise])
        .then(([strings, modal]) => {
            modal.setTitle(strings[0]);
            modal.setBody(strings[1]);
            return modal.show().then(() => moveModalToFullscreen(modal));
        })
        .catch(error => {
            console.error('error:', error);
            Notification.exception(error);
        });
}

/**
 * Shows a confirmation dialog (replacement for confirm).
 *
 * @param {string} titleId string identifier of the title
 * @param {string} textId string identifier of the text
 * @param {Function} callback function called if the user confirms
 * @param {*} param optional parameter for the text string
 */
function modalConfirm(titleId, textId, callback, param) {
    let stringsPromise;
    if (param) {
        stringsPromise = Str.get_strings([
            {key: titleId, component: 'qtype_proforma'},
            {key: textId, component: 'qtype_proforma', param},
        ]);
    } else {
        stringsPromise = Str.get_strings([
            {key: titleId, component: 'qtype_proforma'},
            {key: textId, component: 'qtype_proforma'},
        ]);
    }
    let modalPromise = SaveCancelModal.create();
    Promise.all([stringsPromise, modalPromise])
        .then(([strings, modal]) => {
            modal.setTitle(strings[0]);
            modal.setSaveButtonText('Ok');
            modal.setBody(strings[1]);
            modal.getRoot().on(ModalEvents.save, () => {
                modal.getRoot().remove();
                // The callback is called from an event handler, not from the promise chain.
                // eslint-disable-next-line promise/no-callback-in-promise
                callback();
            });
            return modal.show().then(() => moveModalToFullscreen(modal));
        })
        .catch(error => {
            console.error('error:', error);
            Notification.exception(error);
        });
}

/**
 * TreeNode
 */
class TreeNode {
    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // Parent Treenode

        this.boundHandleContextMenu = event => {
            event.preventDefault();
            event.stopPropagation(); // Otherwise parent node handles event, too

            this.setContextMenu()
                .then(() => {
                    if (this.getFramework().menu === undefined) {
                        return false;
                    }
                    const showMenu = ({top, left}) => {
                        this.getFramework().menu.style.left = `${left}px`;
                        this.getFramework().menu.style.top = `${top}px`;
                        // This.getFramework().menu.style.setProperty('--mouse-x', event.clientX + 'px');
                        // this.getFramework().menu.style.setProperty('--mouse-y', event.clientY + 'px');
                        this.getFramework().toggleContextmenu('show');
                    };


                    let origin;
                    if (document.fullscreenElement) {
                        // In fullscreen mode the menu is moved into the fullscreen element
                        // (see toggleContextmenu) and positioned relative to the viewport.
                        this.getFramework().menu.style.position = 'fixed';
                        origin = {left: event.clientX, top: event.clientY};
                    } else {
                        this.getFramework().menu.style.position = '';
                        origin = {left: event.pageX, top: event.pageY};
                    }
                    showMenu(origin);
                    return true;
                })
                .catch(Notification.exception);
        };
        this.handleDragStart = event => {
            if (event.dataTransfer.getData('treeitem').length == 0) {
                event.dataTransfer.setData('treeitem', this.getPath());
            }
        };
    }
    getPath() {
        return this.parent === undefined ? this.name : this.parent.getPath() + '/' + this.name;
    }
    // Override
    setContextMenu() {
        TreeNode.menu = undefined;
        return Promise.resolve(null);
    }
    displayInTreeview(domnode) {
        const li = document.createElement('li');
        li.setAttribute('role', 'treeitem');
        li.setAttribute('draggable', 'true');
        domnode.appendChild(li);
        li.addEventListener('contextmenu', this.boundHandleContextMenu);
        li.addEventListener('dragstart', this.handleDragStart);
        this.element = li; // Store element
        return li;
    }

    getFramework() {
        return this.parent.getFramework();
    }

    async alreadyExists(name) {
        modalAlert('alreadyexists', name);
    }
}

/**
 * FileNode
 */
export class FileNode extends TreeNode {
    static getEditorModeFromFilename(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        switch (extension) {
            case "java":
                return "text/x-java";
            case "py":
                return "text/x-python";
            case "setlx":
                return "text/text";
            case "c":
                return "text/x-csrc";
            case "cpp":
            case "cxx":
            case "h":
            case "hpp":
                return "text/x-c++src";
            case "xml":
                return "application/xml";
            case "html":
                return "text/html";
            case "sql":
                return "text/x-sql";
            case "js":
                return "text/javascript";
            case "php":
                return "application/x-httpd-php";
            case 'txt':
            case 'log':
            case 'md':
            case 'csv':
                return "text";
            default:
                return undefined;
        }
    }

    constructor(name) {
        super(name);
        this.filecontent = '';
        this.mode = FileNode.getEditorModeFromFilename(this.name);
        this.handleDelete = event => {
            this.getFramework().handleClick(event);
            let context = this;
            modalConfirm('delete', 'deletefile', function() {
                context.getFramework().deleteEditor(context);
                context.getFramework().syncer.deleteFileOrFolder(context.getPath());
                context.element.remove();
                context.parent.files = context.parent.files.filter(item => item !== context);
            }, this.getPath());
        };
        this.boundHandleRename = event => {
            this.getFramework().handleClick(event);
            let thecontext = this;
            modalPrompt('rename', 'enterfilename', thecontext.name, (name) => {
                if (name !== null && name.length > 0) {
                    if (!thecontext.parent.isNameChildUnique(name)) {
                        thecontext.alreadyExists(name);
                        return;
                    }
                    const oldpath = thecontext.getPath();
                    thecontext.name = name;
                    const newpath = thecontext.getPath();
                    thecontext.mode = FileNode.getEditorModeFromFilename(thecontext.name);
                    thecontext.getFramework().syncer.renameFile(oldpath, newpath);
                    thecontext.updateLabel();
                    // Thecontext.element.tabIndex = 0;
                    // Update name in tab if open
                    thecontext.getFramework().editorstack.update(thecontext);
                }
            });
        };
        this.boundHandleClick = event => {
            this.getFramework().toggleContextmenu("hide");
            this.getFramework().setFocusTo(this.element);
            event.stopPropagation();
            // Event.preventDefault();
        };
        this.handleDoubleClick = event => {
            this.getFramework().toggleContextmenu("hide");
            // Document.getElementById('last_action').value = this.name;
            if (this.filecontent != undefined) {
                this.getFramework().switchEditorTo(this);
            }
            this.getFramework().setFocusTo(this.element);
            event.stopPropagation();
            // Event.preventDefault();
        };
    }
    getContent() {
        if (this.filecontent.length == 0) {
            return this.getFramework().syncer.download(this.getPath())
                .then(result => {
                    this.filecontent = result;
                    return result;
                });
        } else {
            return Promise.resolve(this.filecontent);
        }
    }
    updateContent(newcontent, async) {
        this.filecontent = newcontent;
        debugLog('Update ' + this.getPath() + ' with ' + newcontent.substr(0, 20) + '...');
        return this.getFramework().syncer.update(this.getPath(), newcontent, async);
    }
    /**
     * Displays the name and optional labels from the syncer (e.g. which tests use the file).
     */
    updateLabel() {
        this.element.textContent = this.name;
        const syncer = this.getFramework().syncer;
        if (syncer && typeof syncer.getFileLabels === 'function') {
            syncer.getFileLabels(this.getPath()).forEach(label => {
                const span = document.createElement('span');
                span.classList.add('proforma-fileusage');
                span.textContent = label.text;
                span.title = label.title;
                this.element.appendChild(span);
            });
        }
    }
    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        this.updateLabel();
        li.classList.add('doc');

        li.addEventListener('dblclick', this.handleDoubleClick);
        li.addEventListener('click', this.boundHandleClick);

        //        Li.addEventListener('mouseover', this.handleMouseOver);
//        li.addEventListener('mouseout', this.handleMouseOut);
    }

    setContextMenu() {
        debugLog('FileNode setContextMenu');
        // This is something from codemirror in promise done function???
        // so this is renamed
        let thecontext = this;
        return Str.get_strings([
            {key: 'delete', component: 'qtype_proforma'},
            {key: 'rename', component: 'qtype_proforma'}
        ]).done(function(strings) {
            let menu = [
                [strings[0] + '...', thecontext.handleDelete], // Delete
                [strings[1] + '...', thecontext.boundHandleRename] // Rename
            ];
            // Additional entries from the syncer (e.g. assign the file to tests in the task editor).
            const syncer = thecontext.getFramework().syncer;
            if (typeof syncer.getContextMenuEntries === 'function') {
                const entries = syncer.getContextMenuEntries(thecontext.getPath(), thecontext);
                if (entries.length > 0) {
                    menu.push(null); // Separator
                    entries.forEach(([text, handler]) => {
                        menu.push([text, event => {
                            thecontext.getFramework().handleClick(event);
                            handler(event);
                        }]);
                    });
                }
            }
            thecontext.getFramework().createContextMenu(menu);
        }) /* .fail(notification.exception)*/
            .fail(function(response) {
                console.error(response);
        });
    }
}

/**
 * FolderNode
 */
export class FolderNode extends TreeNode {
    constructor(name) {
        super(name);
        this.files = []; // Empty list of files.
        this.folders = []; // Empty list of folders.
        this.handleDelete = event => {
            this.getFramework().handleClick(event);
            let context = this;
            modalConfirm('delete', 'deletefolder', function() {
                context.getFramework().syncer.deleteFileOrFolder(context.getPath() + '/.');
                context.element.remove();
                context.parent.folders = context.parent.folders.filter(item => item !== context);
            }, this.getPath());
        };
        this.boundHandleNewFile = event => {
            this.getFramework().handleClick(event);
            let thecontext = this;
            modalPrompt('newemptyfile', 'filename', '', (filename) => {
                if (filename !== null && filename.length > 0) {
                    if (!thecontext.isNameChildUnique(filename)) {
                        thecontext.alreadyExists(filename);
                        // Alert(filename + ' already exists');
                        return;
                    }
                    let node = new FileNode(filename);
                    thecontext.appendFile(node);
                    node.displayInTreeview(thecontext.element.querySelector('[role="group"]'));
                    thecontext.expand(true);
                    thecontext.getFramework().syncer.newfile(node.getPath())
                        .then(() => {
                            // Open editor with new empty file for input
                            thecontext.getFramework().addEditor(node);
                            thecontext.getFramework().setFocusTo(node.element);
                            return true;
                        })
                        .catch(Notification.exception);
                }
            });
        };
        this.boundHandleLoadFile = event => {
            this.getFramework().handleClick(event);
            let input = document.createElement('input');
            input.type = 'file';
            input.onchange = e => {
                let file = e.target.files[0];
                this._addFileFromOs(file, true);
            };
            input.click();
        };
        this.handleDragOver = event => {
            event.preventDefault();
        };
        this.handleDragEnter = () => {
            if (this.getFramework().readOnly) {
                return;
            }
            this.element.querySelector('.name').classList.add('dragover');
        };
        this.handleDragLeave = () => {
            if (this.getFramework().readOnly) {
                return;
            }
            this.element.querySelector('.name').classList.remove('dragover');
        };

        this.handleDrop = event => {
            event.preventDefault();
            event.stopPropagation();
            this.getFramework().toggleContextmenu("hide");
            if (this.getFramework().readOnly) {
                return;
            }
            this.element.querySelector('.name').classList.remove('dragover');
            const path = event.dataTransfer.getData('treeitem');
            if (path !== undefined && path.length > 0) {
                debugLog('drop ' + path + ' onto ' + this.getPath());
                // Node element from tree
                const node = this.getFramework().findNodeByPath(path);
                if (node !== undefined && !this.isNameChildUnique(node.name)) {
                    // TODO: wenn der Ordner schon existiert, sollte nur der Inhalt gemergt werden
                    // alert(node.name + ' already exists');
                    this.alreadyExists(node.name);
                    return;
                }
                if (node instanceof FolderNode) {
                    // Remove folder in old parent
                    const oldpath = node.getPath();
                    node.parent.folders = node.parent.folders.filter(item => item !== node);
                    // Add folder to this
                    this.appendFolder(node);
                    this.element.querySelector('ul').appendChild(node.element);
                    // Node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.expand(true);
                    this.getFramework().syncer.renameFolder(oldpath, node.getPath());
                } else if (node instanceof FileNode) {
                    const oldpath = node.getPath();
                    node.parent.files = node.parent.files.filter(item => item !== node);
                    // Add folder to this
                    this.appendFile(node);
                    // Node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.element.querySelector('ul').appendChild(node.element);
                    this.expand(true);
                    this.getFramework().syncer.renameFile(oldpath, node.getPath());
                } else {
                    console.error('node cannot be moved');
                    debugLog(node);
                }
            } else {
                // External file or folder
                debugLog('drop file/folder');
                let items = event.dataTransfer.items;
                for (let i = 0; i < items.length; i++) {
                    let item = items[i].webkitGetAsEntry(); // Might be renamed to GetAsEntry()
                    if (item) {
                        this._getFileTree(item);
                    }
                }
            }
        };
        this.boundHandleNewFolder = event => {
            this.getFramework().handleClick(event);
            let thecontext = this;
            modalPrompt('newfolder', 'enterfoldername', '', (foldername) => {
                if (foldername !== null && foldername.length > 0) {
                    if (!thecontext.isNameChildUnique(foldername)) {
                        thecontext.alreadyExists(foldername);
                        // Alert(foldername + ' already exists');
                        return;
                    }
                    let node = new FolderNode(foldername);
                    thecontext.appendFolder(node);
                    node.displayInTreeview(thecontext.element.querySelector('[role="group"]'));
                    thecontext.expand(true);
                    debugLog('create new folder ' + node.getPath());
                    thecontext.getFramework().syncer.mkdir(node.getPath());
                }
            });
        };

        this.boundHandleClick = event => {
            debugLog('FolderNode click');
            this.getFramework().toggleContextmenu("hide");
            // Problem: child nodes also get focus
            this.getFramework().setFocusTo(this.element);
            event.stopPropagation();
            event.preventDefault();
        };
        this.boundHandleRename = event => {
            this.getFramework().handleClick(event);
            let thecontext = this;
            modalPrompt('rename', 'enterfoldername', thecontext.name, (name) => {
                if (name !== null && name.length > 0) {
                    if (!thecontext.parent.isNameChildUnique(name)) {
                        thecontext.alreadyExists(name);
                        return;
                    }
                    const oldpath = thecontext.getPath() + '/.';
                    thecontext.name = name;
                    thecontext.element.querySelector('.name').innerHTML = name;
                    const newpath = thecontext.getPath() + '/.';
                    thecontext.getFramework().syncer.renameFolder(oldpath, newpath);
                }
            });
        };
        this.toggleExpand = () => {
            const expand = !this.isExpanded();
            this.element.setAttribute('aria-expanded', expand);
            if (expand) {
                // Folders without files and with exactly one subfolder (e.g. Java packages
                // like src/de/hsh): open the subfolders, too.
                let folder = this;
                while (folder.files.length === 0 && folder.folders.length === 1) {
                    folder = folder.folders[0];
                    folder.expand(true);
                }
            }
        };
        this.handleMouseOver = event => {
            event.currentTarget.classList.add('hover');
        };
        this.handleMouseOut = event => {
            event.currentTarget.classList.remove('hover');
        };
    }
    findNodeByPath(path) {
        let first = path.shift();
        for (let i = 0; i < this.files.length; i++) {
            if (this.files[i].name === first) {
                return this.files[i];
            }
        }
        for (let i = 0; i < this.folders.length; i++) {
            if (this.folders[i].name === first) {
                if (path.length == 0) {
                    return this.folders[i];
                } else {
                    return this.folders[i].findNodeByPath(path);
                }
            }
        }
        return undefined;
    }
    createPath(path) {
        let first = path.shift();
        if (first === undefined || first.length == 0) {
            if (path.lenghth > 0) {
                console.error('Bug in creating path');
            }
            return this;
        }
        for (let i = 0; i < this.folders.length; i++) {
            if (this.folders[i].name === first) {
                // Subpath exists
                if (path.length == 0) {
                    // Full path exists => return folder object.
                    return this.folders[i];
                } else {
                    return this.folders[i].createPath(path);
                }
            }
        }
        // Path does not exist => create.
        let node = this.createFolderNode(first);
        this.appendFolder(node);
        return node.createPath(path);
    }

    /**
     * Creates a subfolder node (overridden for read-only trees).
     *
     * @param {string} name folder name
     * @returns {FolderNode}
     */
    createFolderNode(name) {
        return new FolderNode(name);
    }

    isNameChildUnique(name) {
        for (let i = 0; i < this.files.length; i++) {
            if (name.localeCompare(this.files[i].name) == 0) {
                return false;
            }
        }
        for (let i = 0; i < this.folders.length; i++) {
            if (name.localeCompare(this.folders[i].name) == 0) {
                return false;
            }
        }
        return true;
    }

    _getFileTree(item, path = undefined) {
        const recurseinit = (path === undefined);
        path = path || "";
        if (item.isFile) {
            item.file(file => {
                // Show file content only if no path given
                // i.e. no recursion
                this._addFileFromOs(file, recurseinit);
            });
        } else if (item.isDirectory) {
            /* ******
            // Create new folder
            let node = new FolderNode(item.name);
            this.appendFolder(node);
            node.displayInTreeview(this.element.querySelector('[role="group"]'));
            this.expand(true);
            this.getFramework().syncer.mkdir(node.getPath());

            // Get folder contents
            let dirReader = item.createReader();
            dirReader.readEntries(entries => {
                for (let i=0; i < entries.length; i++) {
                    node._getFileTree(entries[i], path + item.name + "/");
                }
            });
             */
        }
    }

    _addFileFromOs(file, show = false) {
        if (!this.isNameChildUnique(file.name)) {
            this.alreadyExists(file.name);
            return;
        }
        let node = new FileNode(file.name);
        let reader = new FileReader();
        reader.readAsText(file, 'UTF-8');
        reader.onload = readerEvent => {
            let content = readerEvent.target.result; // This is the content!
            node.filecontent = content;
            if (show) {
                this.getFramework().addEditor(node);
                this.getFramework().setFocusTo(node.element);
            }
            this.getFramework().syncer.upload(node.getPath(), file);
        };
        this.appendFile(node);
        node.displayInTreeview(this.element.querySelector('[role="group"]'));
        this.expand(true);
    }
    expand(doit) {
        this.element.setAttribute('aria-expanded', doit);
    }

    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.setAttribute('aria-expanded', 'false');

        const span2 = document.createElement('span');
        span2.addEventListener('dblclick', this.toggleExpand);
        span2.innerHTML = this.name;
        span2.classList.add('name');
        span2.addEventListener('click', this.boundHandleClick);
        span2.addEventListener('dragenter', this.handleDragEnter);
        span2.addEventListener('dragleave', this.handleDragLeave);
        span2.addEventListener('drop', this.handleDrop);
        span2.addEventListener('dragover', this.handleDragOver);
        li.appendChild(span2);

        const subul = document.createElement('ul');
        subul.setAttribute('role', 'group');
        li.appendChild(subul);

        for (let j = 0; j < this.folders.length; j++) {
            this.folders[j].displayInTreeview(subul);
        }
        for (let j = 0; j < this.files.length; j++) {
            this.files[j].displayInTreeview(subul);
        }
    }

    isExpanded() {
        return this.element.getAttribute('aria-expanded') === 'true';
    }
    setContextMenu() {
        debugLog('FolderNode setContextMenu');
        let thecontext = this; // This is changed to something codemirror in promise
        return Str.get_strings([
            {key: 'newemptyfile', component: 'qtype_proforma'},
            {key: 'loadfile', component: 'qtype_proforma'},
            {key: 'newfolder', component: 'qtype_proforma'},
            {key: 'rename', component: 'qtype_proforma'},
            {key: 'delete', component: 'qtype_proforma'}
        ]).done(function(strings) {
            let menu = [
                [strings[0] + '...', thecontext.boundHandleNewFile], // Newemptyfile
                [strings[1] + '...', thecontext.boundHandleLoadFile], // Loadfile
            ];
            // Folders are only supported if the syncer can store them (e.g. task editor).
            if (thecontext.getFramework().allowFolders) {
                menu.push([strings[2] + '...', thecontext.boundHandleNewFolder]); // Newfolder
            }
            menu.push([strings[3] + '...', thecontext.boundHandleRename]); // Rename
            menu.push([strings[4] + '...', thecontext.handleDelete]); // Delete
            thecontext.getFramework().createContextMenu(menu);
        }) // . fail(notification.exception)
            .fail(function(response) {
                console.error(response);
            });
    }

    appendFile(node) {
        this.files.push(node); node.parent = this;
    }
    appendFolder(node) {
        this.folders.push(node); node.parent = this;
    }
}

/**
 * RootNode
 */
export class RootNode extends FolderNode {
    constructor(name, framework) {
        super(name);
        debugLog('CREATE root node ' + name);
        this.framework = framework;
        framework.roots.push(this);
    }
    getFramework() {
        return this.framework;
    }
    getPath() {
        return '';
    }
    setContextMenu() {
        debugLog('RootNode setContextMenu');
        let thecontext = this; // This is changed to something codemirror in promise
        return Str.get_strings([
            {key: 'newemptyfile', component: 'qtype_proforma'},
            {key: 'loadfile', component: 'qtype_proforma'},
            {key: 'newfolder', component: 'qtype_proforma'}
        ]).done(function(strings) {
            let menu = [
                [strings[0] + '...', thecontext.boundHandleNewFile], // Newemptyfile
                [strings[1] + '...', thecontext.boundHandleLoadFile], // Loadfile
            ];
            // Folders are only supported if the syncer can store them (e.g. task editor).
            if (thecontext.getFramework().allowFolders) {
                menu.push([strings[2] + '...', thecontext.boundHandleNewFolder]); // Newfolder
            }
            thecontext.getFramework().createContextMenu(menu);
        }) // . fail(notification.exception)
            .fail(function(response) {
                console.error(response);
            });
    }

}

/**
 * Ignores an event in read-only tree views (no context menu, no drag and drop).
 *
 * @param {TreeNode} node tree node
 * @returns {Function} event handler
 */
const ignoreEventIn = node => event => {
    event.preventDefault();
    event.stopPropagation();
    node.getFramework().toggleContextmenu("hide");
};

/**
 * Read-only folder for additional tree views (e.g. the tests in the task editor).
 */
export class ReadOnlyFolderNode extends FolderNode {
    constructor(name) {
        super(name);
        this.boundHandleContextMenu = ignoreEventIn(this);
        this.handleDrop = ignoreEventIn(this);
        this.handleDragStart = event => event.preventDefault();
        this.handleDragEnter = () => null;
        this.handleDragLeave = () => null;
    }
    createFolderNode(name) {
        return new ReadOnlyFolderNode(name);
    }
    expandAll() {
        this.expand(true);
        this.folders.forEach(folder => folder.expandAll());
    }
    displayInTreeview(domnode) {
        super.displayInTreeview(domnode);
        this.element.setAttribute('draggable', 'false');
    }
}

/**
 * Top level node of a read-only tree view.
 */
export class ViewRootNode extends ReadOnlyFolderNode {
    constructor(name, framework) {
        super(name);
        this.framework = framework;
    }
    getFramework() {
        return this.framework;
    }
    getPath() {
        return '';
    }
}

/**
 * Read-only link to a file of the main tree. A double click opens the linked file in the editor.
 */
export class LinkNode extends FileNode {
    /**
     * Constructor.
     *
     * @param {string} name displayed name
     * @param {string} targetPath path of the file in the main tree (e.g. '/src/Foo.java')
     */
    constructor(name, targetPath) {
        super(name);
        this.targetPath = targetPath;
        this.boundHandleContextMenu = ignoreEventIn(this);
        this.handleDragStart = event => event.preventDefault();
        this.handleDoubleClick = event => {
            const framework = this.getFramework();
            framework.toggleContextmenu("hide");
            // Open the file node of the main tree => only one editor per file.
            const target = framework.findNodeByPath(this.targetPath);
            if (target !== undefined) {
                framework.switchEditorTo(target);
            }
            framework.setFocusTo(this.element);
            event.stopPropagation();
        };
    }
    updateLabel() {
        this.element.textContent = this.name;
        this.element.title = this.targetPath.substring(1);
    }
    displayInTreeview(domnode) {
        super.displayInTreeview(domnode);
        this.element.setAttribute('draggable', 'false');
    }
}

class EditorItem {
    constructor(fileNode, textarea, tabDomNode, readOnly) {
        debugLog('Create Codemirror ' + readOnly);

        this.fileNode = fileNode;
        this.editor = CodeMirror.fromTextArea(textarea, {
            tabMode: "indent",
            indentUnit: 4,
            matchBrackets: true,
            autoCloseBrackets: true,
            styleActiveLine: true,
            readOnly: readOnly,
            lineNumbers: true
            // ViewportMargin: Infinity
        });
        this.editor.setSize("100%", "100%");
        this.editor.setOption('theme', getCodemirrorTheme());
        this.tab = tabDomNode;
    }
}

class EditorStack {
    static maxEditors = 12;
    constructor(donNodeEditor, donNodeTabs, framework) {
        this.editortextarea = donNodeEditor.querySelector('textarea');
        // Initialise readonly editor
        this.editor = CodeMirror.fromTextArea(this.editortextarea, {
            tabMode: "indent",
            indentUnit: 4,
            matchBrackets: true,
            autoCloseBrackets: true,
            styleActiveLine: true,
            readOnly: true,
            lineNumbers: true
            // ViewportMargin: Infinity
        });
        this.editor.setSize("100%", "100%");
        this.editor.setOption('theme', getCodemirrorTheme());

        this.activeNode = undefined; // ActiveNode associated with Codemirror

        this.nodes = []; // All filenodes with open editor
        // this.donNodeEditor = donNodeEditor;
        this.donNodeTabs = donNodeTabs;
        this.focus = undefined; // The tab that has got the focus
        this.framework = framework;

    }

    /**
     * Sets the CodeMirror theme of all editors.
     *
     * @param {string} theme CodeMirror theme
     */
    setTheme(theme) {
        this.editor.setOption('theme', theme);
        this.nodes.forEach(item => item.editor.setOption('theme', theme));
    }

    cleanup() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }

    _switchTo(item, index = undefined) {
        this.saveCurrentEditor(true);

        if (index === undefined) {
            // Figure out value of i
            for (index = 0; index < this.nodes.length; index++) {
                if (this.nodes[index] === item) {
                    break;
                }
            }
        }
        debugLog('item index is ' + index);

        // Move on top
        this.nodes.splice(index, 1);
        this.nodes.push(item);

        // Hide all editors
        for (index = 0; index < this.nodes.length; index++) {
            this.nodes[index].editor.getWrapperElement().style.display = 'none';
        }

        item.editor.getWrapperElement().style.display = 'block';
        item.editor.refresh();
        item.editor.focus();

        // Switch focus
        // The close button is only visible for the focused tab (see styles.css).
        if (this.focus !== undefined) {
            this.focus.classList.remove('focus');
        }
        item.tab.classList.add('focus');
        this.focus = item.tab;
    }

    _delete(item) {
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i] === item) {
                debugLog('** Delete item from editor');
                // Read back (modified) content
                this.nodes[i].fileNode.updateContent(this.nodes[i].editor.getValue());

                this.nodes.splice(i, 1);
                // Delete Codemirror element (in order to avoid resource leak)
                item.editor.getWrapperElement().remove();
                if (this.nodes.length > 0) {
                    this._switchTo(this.nodes[this.nodes.length - 1], this.nodes.length - 1);
                }
                return;
            }
        }
        console.error('could not find filenode');
    }

    deleteEditor(filenode) {
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i].fileNode === filenode) {
                this.nodes[i].tab.remove();
                this.nodes[i].tab = undefined;
                this._delete(this.nodes[i]);
                return;
            }
        }
    }
    addEditor(filenode) {
        if (EditorStack.maxEditors === this.nodes.length) {
            Notification.alert('', 'maximum number of editors reached');
            return;
        }
        // Create tab
        // let tab = document.createElement('span');
        let tab = document.createElement('button');

        // Mode is known => display new text content
        let item = new EditorItem(filenode, this.editortextarea, tab, this.framework.readOnly);
        filenode.getContent()
            .then(text => {
                if (text === undefined) {
                    text = '???';
                }
                item.editor.setValue(text);
                if (filenode.mode !== undefined) {
                    item.editor.setOption("mode", filenode.mode);
                } // Else {
                    // E.g. makefile has no extension and therefore no known mode.
                    // console.error('unknown file mode');
                // }
                // item.editor.setOption("readOnly", this.readOnly);
                item.editor.refresh(); // For old version of Codemirror
                return true;
            })
            .catch(error => {
                console.error('error:', error);
                Notification.exception(error);
            });

        tab.classList.add('tab');
        let close = document.createElement('span');
        close.classList.add('close');
        close.innerHTML = '&#x2715';
        close.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            this._delete(item);
            close.parentElement.remove();
        });
        // Separate element for the name so that renaming does not remove the close button.
        let tabname = document.createElement('span');
        tabname.classList.add('tab-name');
        tabname.textContent = filenode.name;
        tab.append(tabname);
        tab.append(close);
        tab.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            this._switchTo(item);
        });
        this.donNodeTabs.append(tab);

        this.nodes.push(item);
        this._switchTo(item);

    }

    // Handle filename update
    update(filenode) {
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i].fileNode === filenode) {
                // Filenode is in list
                // => update filename
                this.nodes[i].tab.querySelector('.tab-name').textContent = filenode.name;
                // Update filemode
                if (filenode.mode !== undefined) {
                    this.nodes[i].editor.setOption("mode", filenode.mode);
                }
                return;
            }
        }
    }
    switchEditorTo(filenode) {
        // Check if filenode is already in stack
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i].fileNode === filenode) {
                // Filenode is in list
                this._switchTo(this.nodes[i], i);
                return;
            }
        }
        this.addEditor(filenode);
        // Start auto-save timer
        if (this.timer) {
            clearInterval(this.timer);
        }
        let that = this;
        if (autosaveIntervall > 0) {
            this.timer = setInterval(function() {
                debugLog('proforma editor autosave');
                that.saveCurrentEditor(true);
            }, autosaveIntervall);
        }
    }

    saveCurrentEditor(async) {
        let currentNode = this._getCurrentNode();
        if (currentNode) {
            // Save content of current editor
            const text = currentNode.editor.getValue();
            if (text.length > 0) {
                currentNode.fileNode.updateContent(text, async);
            } else {
                // CurrentNode.fileNode.updateContent(' ', true);
            }
        }
    }

    _getCurrentNode() {
        if (this.nodes.length > 0) {
            // Call refresh for current Codemirror
            // in order to update text window. Otherwise
            // text is cut off
            return this.nodes[this.nodes.length - 1];
        }
        return null;
    }
    handleResize() {
        if (this.nodes.length > 0) {
            // Call refresh for current Codemirror
            // in order to update text window. Otherwise
            // text is cut off
            this.nodes[this.nodes.length - 1].editor.refresh();
        }
    }
    save() {
        // Save all
        // (we could save current if file is saved on switching)
        // this.issaved = false;
        debugLog('currently open editors ' + this.nodes.length.toString());
        /* eslint-disable no-console */
        console.timeStamp('save');
        console.time('save');
        /* eslint-enable no-console */

        for (let i = 0; i < this.nodes.length; i++) {
            this.nodes[i].fileNode.updateContent(this.nodes[i].editor.getValue(), false);
        }

        /*
        Let promises = [];
        for (let i = 0; i < this.nodes.length; i++) {
            console.log('add promise to list ' + i.toString());
            promises.push(this.nodes[i].fileNode.updateContent(this.nodes[i].editor.getValue()));
        }
        return Promise.all(promises).
            then(() => {
                console.log('all files saved');
                console.timeStamp('save');
                console.timeEnd('save');
                this.issaved = true;

            // return true;
                // alert('look');
            })
            .catch( error => {
                console.timeStamp('save');
                console.timeEnd('save');
                console.error('error:', error);
                alert(error);
            });*/
        debugLog('all files saved');
        /* eslint-disable no-console */
        console.timeStamp('save');
        console.timeEnd('save');
        /* eslint-enable no-console */
    }
/*    Needssaving() {
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i].fileNode.filecontent != this.nodes[i].editor.getValue()) {
                return true;
            }
        }
        return false;
    }
    issaved() {
        return this.issaved;
    }*/
}


export class Framework {
    constructor() {
        this.roots = []; // All root nodes
        this.syncer = undefined;
        this.editorstack = undefined;
        this.mainDomNode = undefined;
        this.menu = undefined;
        this.menuVisible = false;
        this.focus = undefined;
        this.readOnly = false;
        this.rootnode = 'Submission';
        this.allowFolders = false;
    }

    buildFramework(domnode) {
        debugLog('buildFramework');
        domnode.innerHTML = `<div class="ide" style="display: flex;flex-direction: column; align-items: stretch;
    resize: vertical;
    overflow: hidden;
    min-height: 450px">
    <!--<div class="menu" style="flex: none">menu</div>-->

    <div class="body" style="display: flex; flex-direction: row; flex: 1 1 0; min-height: 0">
        <!--<div class="fake" style="min-width: 100px; flex: 1 0 0; overflow: auto;">Fake element</div> -->
        <!-- Only the explorer has a width (changed by the splitter), the canvas takes the rest. -->
        <div class="explorer" style="min-width: 20px; flex: 0 0 25%; overflow: auto;">
            <div class="explorer-header"></div>
        </div>
        <div class="resize"></div>
        <div class="canvas" style="min-width: 20px; flex: 1 1 0; display: flex; flex-direction: row;">
            <!-- set flex-basis = 50% for 2 two columns and 100%V for one column -->
            <div class="canvascol" style="display: flex; flex-direction: column; flex: 1 1 50%; overflow: hidden;">
                <div class="tabbar" style="flex: none; display: flex;">
                    <div class="tabs" style="flex: 1 1 auto; min-width: 0;">
                    </div>
                    <button type="button" class="themetoggle"></button>
                </div>
                <div class="editor" style="flex: 1 1 0; overflow: hidden;">
                    <textarea></textarea>
                </div>
            </div>
            <!--
            <div class="resize"></div>
            <div class="canvascol" style="display: flex; flex-direction: column; flex: 1 1 50%; min-height: 0;">
                <div class="tabs" style="flex: none; ">
                </div>
                <div class="editor" style="flex: 1 1 0; min-height: 0; overflow: hidden;">
                    <textarea></textarea>
                </div>
            </div> --> 
        </div>
    </div>

    <!--<div class="status" style="flex: none">status</div>-->
</div>
<p><!--<label>File or Folder Selected: <input id="last_action" type="text" size="15" readonly=""></label>--></p>
`;
        // We only need one context menu that must be placed outside
        // all other elements (esp. those that are positioned relative)
        // in order to have the menu placed correctly.
        let contextmenu = `<div class="contextmenu" id="context-menu">
    <ul class="menu-options">
        <li class="menu-option">New file</li>
        <li class="menu-option">New folder</li>
        <li class="menu-option">Delete...</li>
    </ul>
</div>`;
        // The menu is shared by all frameworks on the page (e.g. when the explorer in the
        // task editor is rebuilt) => create it only once.
        if (!document.getElementById('context-menu')) {
            const menu = document.createElement('div');
            menu.innerHTML = contextmenu;
            let body = document.querySelector('body');
            body.appendChild(menu);
        }

        this.mainDomNode = domnode;
        this.editorstack = new EditorStack(domnode.querySelector('.editor'),
            domnode.querySelector('.tabs'), this);

        // Switch between dark and light theme.
        this.themeToggle = domnode.querySelector('.themetoggle');
        this.themeToggle.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            setIdeTheme(ideTheme === 'light' ? 'dark' : 'light');
        });
        this.boundThemeChanged = () => this.applyTheme();
        window.addEventListener(themeChangedEvent, this.boundThemeChanged);
        this.applyTheme();
    }

    /**
     * Applies the current theme (dark or light) to the explorer.
     */
    applyTheme() {
        const light = ideTheme === 'light';
        this.mainDomNode.querySelector('.ide').classList.toggle('ide-light', light);
        this.editorstack.setTheme(getCodemirrorTheme());
        // Symbol and title show the theme the button switches to.
        this.themeToggle.textContent = light ? '\u263E' : '\u2600';
        Str.get_string(light ? 'explorerdarkmode' : 'explorerlightmode', 'qtype_proforma')
            .then(text => {
                this.themeToggle.title = text;
                this.themeToggle.setAttribute('aria-label', text);
                return true;
            })
            .catch(Notification.exception);
    }

    init(node, syncer, readOnly, options) {
        this.readOnly = readOnly;
        this.rootnode = options.rootnode;
        this.allowFolders = !!options.allowfolders;
        // Theme used as long as the user has not chosen one (stored preference wins).
        if (!storedIdeTheme && (options.defaulttheme === 'light' || options.defaulttheme === 'dark')) {
            ideTheme = options.defaulttheme;
            this.applyTheme();
        }
        if (options.explorerautosave !== undefined && options.explorerautosave > 0) {
            autosaveIntervall = 1000 * options.explorerautosave;
        } else {
            autosaveIntervall = -1;
        }
        const initSplit = resizer => {
            // From https://htmldom.dev/create-resizable-split-views/
            const before = resizer.previousElementSibling;
            const after = resizer.nextElementSibling;

            // The current position of mouse
            let x = 0;

            let oldValue = 0;
            let mousedown = false;

            // Limits for the width of the explorer (in percent of the available width).
            const minBasis = 10;
            const maxBasis = 70;

            // While dragging, text selection and mouse events in the panes are disabled
            // (otherwise the editor selects text and swallows mouse events).
            const disableSelection = () => {
                document.body.style.cursor = 'col-resize';
                [before, after].forEach(element => {
                    if (element) {
                        element.style.userSelect = 'none';
                        element.style.pointerEvents = 'none';
                    }
                });
            };
            const removeSelection = () => {
                document.body.style.removeProperty('cursor');
                [before, after].forEach(element => {
                    if (element) {
                        element.style.removeProperty('user-select');
                        element.style.removeProperty('pointer-events');
                    }
                });
            };
            // Handle the mousedown event
            // that's triggered when user drags the resizer
            const mouseDownHandler = e => {
                e.preventDefault();
                // Get the current mouse position
                x = e.clientX;

                this.toggleContextmenu("hide");
                oldValue = before.getBoundingClientRect().width;
                mousedown = true;
                // Attach the listeners to `document`
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);

                disableSelection();
            };

            const mouseMoveHandler = e => {
                if (!mousedown) {
                    mouseUpHandler();
                    return;
                }
                // How far the mouse has been moved
                const dx = e.clientX - x;
                const parentWidth = resizer.parentNode.getBoundingClientRect().width;
                let newBasis = ((oldValue + dx) * 100) / parentWidth;
                newBasis = Math.min(Math.max(newBasis, minBasis), maxBasis);
                // Only the explorer gets a new width, the canvas fills the remaining space.
                before.style.flexBasis = `${newBasis}%`;
            };

            const mouseUpHandler = () => {
                mousedown = false;
                removeSelection();

                // Remove the handlers of `mousemove` and `mouseup`
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
                // The editor width has changed => let CodeMirror re-measure.
                this.editorstack.handleResize();
            };
            // Attach the handler
            resizer.addEventListener('mousedown', mouseDownHandler);
        };

        const fileviewer = node.querySelector('.explorer');
        // Prevent browser from opening a dropped file in a new tab.
        fileviewer.addEventListener('drop', event => {
            event.preventDefault();
        });
        fileviewer.addEventListener('dragover', event => {
            event.preventDefault();
        });
        // Mouse wheel over the tree scrolls the tree, never the page (the browser does not
        // always choose the tree, e.g. Moodle scrolls the page in its own container).
        fileviewer.addEventListener('wheel', event => {
            const canScrollY = fileviewer.scrollHeight > fileviewer.clientHeight;
            const canScrollX = fileviewer.scrollWidth > fileviewer.clientWidth;
            if (event.ctrlKey || (!canScrollY && !canScrollX)) {
                return; // Zoom or nothing to scroll => default behaviour.
            }
            event.preventDefault();
            // DeltaMode: 0 = pixel, 1 = line, 2 = page.
            const factor = event.deltaMode === 1 ? 16 : (event.deltaMode === 2 ? fileviewer.clientHeight : 1);
            fileviewer.scrollTop += event.deltaY * factor;
            fileviewer.scrollLeft += event.deltaX * factor;
        }, {passive: false});

        let ul = document.createElement("ul");
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'fileviewer');
        fileviewer.appendChild(ul);

        if (options.treeviews && options.treeviews.length > 0) {
            this._initTreeViews(fileviewer, ul, options);
        }

        this.syncer = syncer;
        // Build folder/file structure.
        /* this.syncer.dir(); Da fehlen die Dateien */
        this.createPath('/'); // Needed when no files come from syncer.
        // Promise for the displayed tree (e.g. to restore a previous display state).
        this.ready = this.syncer.list(this)
            .then(() => {
                debugLog('DISPLAY ROOTS');
                debugLog(this.roots);
                for (let i = 0; i < this.roots.length; i++) {
                    let root = this.roots[i];
                    root.displayInTreeview(ul);
                    root.toggleExpand();
                }
                return true;
            })
            .catch(Notification.exception);

        // Hide context menu on every left click
        this.boundWindowClick = () => {
            this.handleClick();
        };
        window.addEventListener("click", this.boundWindowClick);

        let el = this.mainDomNode.querySelector('.ide');
        this.resizeObserver = new ResizeObserver(() => {
            this.editorstack.handleResize();
        });
        this.resizeObserver.observe(el);
        initSplit(node.querySelector('.ide .body > .resize'), 'w');
        // InitSplit(node.querySelector('.ide .canvas > .resize'), 'w');

        // Read context menu strings in order to have them in
        // the browser cache and the menu can open immediately
        /*
        Str.get_strings([
            {key: 'delete', component: 'qtype_proforma'},
            {key: 'rename', component: 'qtype_proforma'},
            {key: 'loadfile', component: 'qtype_proforma'},
            {key: 'newemptyfile', component: 'qtype_proforma'},
            {key: 'newfolder', component: 'qtype_proforma'},
        ]).done(function(strings) {
            console.log('context menu string read.');
            console.log(strings);
        }).fail(function (response) {
            console.error(response);
        }); */

        /*
        RootNode.syncer.sendRequest('mkdir', 'newproformafolder');
        RootNode.syncer.sendRequest('dir'); */
    }

    /**
     * Adds tabs above the tree: the main tree and additional read-only tree views.
     * Each view is built anew when its tab is selected.
     *
     * @param {HTMLElement} fileviewer explorer element
     * @param {HTMLElement} mainUl list element of the main tree
     * @param {Object} options options.treeviews: list of {title, build(framework) => [ViewRootNode]}
     */
    _initTreeViews(fileviewer, mainUl, options) {
        const header = fileviewer.querySelector('.explorer-header');
        header.classList.add('explorer-views');
        const viewUl = document.createElement('ul');
        viewUl.setAttribute('role', 'tree');
        viewUl.style.display = 'none';
        fileviewer.appendChild(viewUl);

        const views = [{title: options.rootnode, build: undefined}].concat(options.treeviews);
        let buttons = [];
        views.forEach(view => {
            const button = document.createElement('button');
            button.type = 'button';
            button.classList.add('tab');
            button.textContent = view.title;
            button.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                this.toggleContextmenu("hide");
                buttons.forEach(other => other.classList.toggle('focus', other === button));
                if (view.build === undefined) {
                    mainUl.style.display = '';
                    viewUl.style.display = 'none';
                } else {
                    viewUl.innerHTML = '';
                    view.build(this).forEach(root => {
                        root.displayInTreeview(viewUl);
                        root.expandAll();
                    });
                    mainUl.style.display = 'none';
                    viewUl.style.display = '';
                }
            });
            header.appendChild(button);
            buttons.push(button);
        });
        buttons[0].classList.add('focus');
    }

    /**
     * Removes global listeners and timers (needed when the framework is rebuilt,
     * e.g. in the task editor). The DOM is not touched.
     */
    destroy() {
        if (this.boundWindowClick) {
            window.removeEventListener("click", this.boundWindowClick);
        }
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }
        if (this.boundThemeChanged) {
            window.removeEventListener(themeChangedEvent, this.boundThemeChanged);
        }
        this.toggleContextmenu("hide");
        this.editorstack.cleanup();
    }

    switchEditorTo(filenode) {
        this.editorstack.switchEditorTo(filenode);
    }
    addEditor(filenode) {
        this.editorstack.switchEditorTo(filenode);
        // This.editorstack.addEditor(filenode);
    }
    deleteEditor(filenode) {
        this.editorstack.deleteEditor(filenode);
    }

    findNodeByPath(path) {
        debugLog('find <' + path + '>');
        if (path.substr(0, 1) != '/') {
            console.error('path does not start with /: ' + path);
            return undefined;
        }

        let pathsplit = path.split('/');
        pathsplit.shift(); // First element is always empty
        // let first = pathsplit.shift();
        let root = this.roots[0];
        return root.findNodeByPath(pathsplit);

        /*
        For (let i = 0; i < this.roots.length; i++) {
            if (this.roots[i].name === first) {
                return this.roots[i].findNodeByPath(pathsplit);
            }
        }
        return undefined; */
    }

    createPath(path) {
        debugLog('Framework: create folder ' + path);
        // Assume first char is always /
        if (path[0] !== '/') {
            console.error('first char in path is not /: ' + path);
        }
        let pathsplit = path.split('/');
        pathsplit.shift(); // First element in array is always empty

        let root;
        let context = this;
        if (this.roots.length === 0) {
            root = new RootNode(context.rootnode, context);
            return root.createPath(pathsplit);
/*
            GetString('rootsubmission', 'qtype_proforma')
                .done(function(string) {
                    root = new RootNode(string, context);
                    return root.createPath(pathsplit);
                })
                .fail(function (response) {
                    console.error(response);
                });*/
        } else {
            root = this.roots[0];
            return root.createPath(pathsplit);
        }
    }

    createContextMenu(list) {
        if (this.readOnly) {
            return;
        }
        debugLog('createContextMenu ' + list.length);
        // Let ul = this.mainDomNode.querySelector(".contextmenu .menu-options");
        let ul = document.querySelector(".contextmenu .menu-options");
        ul.innerHTML = ''; // Delete all children
        for (let i = 0; i < list.length; i++) {
            const li = document.createElement('li');
            if (list[i] === null) {
                // Separator
                li.setAttribute('class', 'menu-separator');
                ul.appendChild(li);
                continue;
            }
            li.setAttribute('class', 'menu-option');
            // Text only: entries may contain user input (e.g. test titles).
            li.textContent = list[i][0];
            li.addEventListener('click', list[i][1]);
            debugLog(list[i][0]);
            ul.appendChild(li);
        }

        this.menu = ul.parentNode;
    }

    toggleContextmenu = command => {
        if (this.menu === undefined) {
            return;
        }
        if (command === "show") {
            // Only the fullscreen element (e.g. the task editor) is visible in fullscreen mode
            // => the menu must be inside it. Otherwise it belongs to the body.
            const parent = getOverlayParent();
            if (this.menu.parentElement !== parent) {
                parent.appendChild(this.menu);
            }
        }
        this.menu.style.display = command === "show" ? "block" : "none";
        this.menuVisible = (command === "show");
    };

    handleClick() {
        this.toggleContextmenu("hide");
        this.setFocusTo(undefined);
    }
    setFocusTo(element) {
        if (this.focus !== undefined) {
            this.focus.classList.remove('focus');
        }
        if (element !== undefined) {
            element.classList.add('focus');
            this.focus = element;
        } else {
            this.focus = undefined;
        }
    }

    /*    Needssaving() {
        return this.editorstack.needssaving();
    }
    issaved() {
        return this.editorstack.issaved();
    } */
    save() {
        debugLog(this);
        debugLog(this.editorstack);
        return this.editorstack.save();
        // Alert('hallo');
        // setTimeout(() => { return p1; }, 60000);
    }
}
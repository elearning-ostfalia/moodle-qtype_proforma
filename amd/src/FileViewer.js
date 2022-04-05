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
 * @package    qtype
 * @subpackage proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint-disable no-unused-vars */

// Use these imports for Moodle
// -----------------------------
import "./MoodleSyncer";

import './codemirror-global';
import CodeMirror from "./codemirror";

import "./clike";
import "./python";
import "./javascriptmode"; // renamed from javascript
import "./xml";
import "./matchbrackets";
import "./closebrackets";
import "./active-line";

// import {get_string as getString} from 'core/str';
import Config from 'core/config';


// Use this for editortest.html
// -----------------------------
/*
import './codemirror-global.js';

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
*/


// 'use strict'; ecma6 code is always strict


// TODO:
// - Split View: Problem mit Flackern
// - Theme wechseln
// - Menu erstmal raus - außer zum Wechseln des Themes
// - Andere Browser testen

/**
 * TreeNode
 */
class TreeNode {
    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // parent Treenode

        this.boundHandleContextMenu = event => {
            this.setContextMenu();
            if (this.getFramework().menu === undefined) {
                return;
            }
            const showMenu = ({ top, left }) => {
                this.getFramework().menu.style.left = `${left}px`;
                this.getFramework().menu.style.top = `${top}px`;
                // this.getFramework().menu.style.setProperty('--mouse-x', event.clientX + 'px');
                // this.getFramework().menu.style.setProperty('--mouse-y', event.clientY + 'px');
                this.getFramework().toggleContextmenu('show');
            };

            console.log(`contextmenu: ${event}`);
            // console.log(event)
            event.preventDefault();
            event.stopPropagation(); // otherwise parent node handles event, too

            const origin = {
                left: event.pageX,
                top: event.pageY
            };
            console.log(`${event.pageX}px ${event.pageY}px`);
            console.log(event);
            showMenu(origin);
        };
        this.handleDragStart = event => {
            if (event.dataTransfer.getData('treeitem').length == 0) {
                console.log('dragstart: ' + this.getPath());
                event.dataTransfer.setData('treeitem', this.getPath());
            }
        };
    }
    getPath() {
        return this.parent === undefined? this.name : this.parent.getPath() + '/' + this.name ;
    }

    // Override
    setContextMenu() {
        TreeNode.menu = undefined;
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
        }
    }

    constructor(name) {
        super(name);
        this.filecontent = '';
        this.mode = FileNode.getEditorModeFromFilename(this.name);
        this.handleDelete = event => {
            this.getFramework().handleClick(event);
            this.getFramework().syncer.delete(this.getPath());
            this.element.remove();
            this.parent.files = this.parent.files.filter(item => item !== this);
        };
        this.boundHandleRename = event => {
            this.getFramework().handleClick(event);
            let name = prompt("Please enter new name:", this.name);
            if (name !== null && name.length > 0) {
                const oldpath = this.getPath();
                this.name = name;
                this.element.innerHTML = name;
                const newpath = this.getPath();
                this.getFramework().syncer.renameFile(oldpath, newpath);

                // this.element.tabIndex = 0;
            }
        };
        this.boundHandleClick = event => {
            this.getFramework().toggleContextmenu("hide");
            this.getFramework().setFocusTo(this.element);
            event.stopPropagation();
            // event.preventDefault();
        };
        this.handleDoubleClick = event => {
            this.getFramework().toggleContextmenu("hide");
            document.getElementById('last_action').value = this.name;
            if (this.filecontent != undefined) {
                this.getFramework().switchEditorTo(this);
            }
            this.getFramework().setFocusTo(this.element);
            event.stopPropagation();
            // event.preventDefault();
        };
    }
    getContent(callback) {
        if (this.filecontent.length == 0) {
            this.getFramework().syncer.download(this.getPath(), result => {
                this.filecontent = result;
                callback(result);
            });
        } else {
            callback(this.filecontent);
        }
    }
    updateContent(newcontent) {
        this.filecontent = newcontent;
        this.getFramework().syncer.update(this.getPath(), newcontent);
    }
    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.innerHTML = this.name;
        li.classList.add('doc');

        li.addEventListener('dblclick', this.handleDoubleClick);
        li.addEventListener('click', this.boundHandleClick);

//        li.addEventListener('mouseover', this.handleMouseOver);
//        li.addEventListener('mouseout', this.handleMouseOut);
    }

    setContextMenu() {
        console.log('FileNode setContextMenu');
        this.getFramework().createContextMenu([
            ['Delete', this.handleDelete],
            ['Rename', this.boundHandleRename]]
        );
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
            this.getFramework().syncer.delete(this.getPath() + '/.');
            this.element.remove();
            this.parent.folders = this.parent.folders.filter(item => item !== this);
            // console.log(RootNode.projects);
        };
        this.boundHandleNewFile = event => {
            this.getFramework().handleClick(event);
            let filename = prompt("Please enter filename:", "");
            if (filename !== null && filename.length > 0) {
                if (!this.isNameChildUnique(filename)) {
                    alert(filename + ' already exists');
                    return;
                }
                let node = new FileNode(filename);
                this.appendFile(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
                this.getFramework().syncer.newfile(filename);
            }
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
        this.handleDragEnter = () =>  {
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
                console.log('drop ' + path + ' onto ' + this.getPath());
                // Node element from tree
                const node = this.getFramework().findNodeByPath(path);
                if (node !== undefined && !this.isNameChildUnique(node.name)) {
                    // TODO: wenn der Ordner schon existiert, sollte nur der Inhalt gemergt werden
                    alert(node.name + ' already exists');
                    return;
                }
                if (node instanceof FolderNode) {
                    // remove folder in old parent
                    const oldpath = node.getPath();
                    node.parent.folders = node.parent.folders.filter(item => item !== node);
                    // add folder to this
                    this.appendFolder(node);
                    this.element.querySelector('ul').appendChild(node.element);
                    // node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.expand(true);
                    this.getFramework().syncer.renameFolder(oldpath, node.getPath());
                } else if (node instanceof FileNode) {
                    const oldpath = node.getPath();
                    node.parent.files = node.parent.files.filter(item => item !== node);
                    // add folder to this
                    this.appendFile(node);
                    // node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.element.querySelector('ul').appendChild(node.element);
                    this.expand(true);
                    this.getFramework().syncer.renameFile(oldpath, node.getPath());
                } else {
                    console.error('node cannot be moved');
                    console.log(node);
                }
            } else {
                // External file or folder
                console.log('drop file/folder');
                let items = event.dataTransfer.items;
                for (let i=0; i<items.length; i++) {
                    let item = items[i].webkitGetAsEntry();  //Might be renamed to GetAsEntry()
                    if (item) {
                        this._getFileTree(item);
                    }
                }
            }
        };
        this.boundHandleNewFolder = event => {
            this.getFramework().handleClick(event);
            let foldername = prompt("Please enter foldername:", "");
            if (foldername !== null && foldername.length > 0) {
                if (!this.isNameChildUnique(foldername)) {
                    alert(foldername + ' already exists');
                    return;
                }
                let node = new FolderNode(foldername);
                this.appendFolder(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
                console.log('create new folder ' + node.getPath());
                this.getFramework().syncer.mkdir(node.getPath());
            }
        };

        this.boundHandleClick = event => {
            console.log('FolderNode click');
            this.getFramework().toggleContextmenu("hide");
            // TreeNode.setFocusTo(this.element);
            // this.element.classList.add('focus');
            event.stopPropagation();
            event.preventDefault();
        };
        this.boundHandleRename = event => {
            this.getFramework().handleClick(event);
            let name = prompt("Please enter new name:", this.name);
            if (name !== null && name.length > 0) {
                if (!this.parent.isNameChildUnique(name)) {
                    alert(name + ' already exists');
                    return;
                }
                const oldpath = this.getPath() + '/.';
                this.name = name;
                this.element.querySelector('.name').innerHTML = name;
                const newpath = this.getPath() + '/.';
                this.getFramework().syncer.renameFolder(oldpath, newpath);
            }
        };
        this.toggleExpand = () => {
            this.element.setAttribute('aria-expanded', !this.isExpanded());
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
        // console.log(path);
        let first = path.shift();
        // console.log('foldernode: create node for <' + first + '>');
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
                    // full path exists => return folder object.
                    return this.folders[i];
                } else {
                    return this.folders[i].createPath(path);
                }
            }
        }
        // Path does not exist => create.
        // console.log('create folder node for ' + first);
        let node = new FolderNode(first);
        this.appendFolder(node);
        return node.createPath(path);
    }

    isNameChildUnique(name) {
        for (let i = 0; i < this.files.length; i++) {
            if (name.localeCompare(this.files[i].name) == 0 ) {
                return false;
            }
        }
        for (let i = 0; i < this.folders.length; i++) {
            if (name.localeCompare(this.folders[i].name) == 0 ) {
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
            // Create new folder
            let node = new FolderNode(item.name);
            this.appendFolder(node);
            node.displayInTreeview(this.element.querySelector('[role="group"]'));
            this.expand(true);
            this.getFramework().syncer.mkdir(node.getPath());

            // Get folder contents
            // console.log(item.fullPath);
            let dirReader = item.createReader();
            dirReader.readEntries(entries => {
                for (let i=0; i < entries.length; i++) {
                    node._getFileTree(entries[i], path + item.name + "/");
                }
            });
        }
    }

    _addFileFromOs(file, show = false) {
        if (!this.isNameChildUnique(file.name)) {
            alert(file.name + ' already exists');
            return;
        }
        let node = new FileNode(file.name);
        let reader = new FileReader();
        reader.readAsText(file,'UTF-8');
        reader.onload = readerEvent => {
            let content = readerEvent.target.result; // this is the content!
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

        const span1 = document.createElement('span');
        span1.classList.add('before');
        span1.addEventListener('click', this.toggleExpand);
        li.appendChild(span1);

        const span2 = document.createElement('span');
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
        console.log('FolderNode setContextMenu');
        this.getFramework().createContextMenu([
            ['New empty file...', this.boundHandleNewFile],
            ['Load file...', this.boundHandleLoadFile],
            ['New folder...', this.boundHandleNewFolder],
            ['Rename', this.boundHandleRename],
            ['Delete', this.handleDelete]
            ]
        );
    }

    appendFile(node) { this.files.push(node); node.parent = this; }
    appendFolder(node) { this.folders.push(node); node.parent = this; }
}

/**
 * RootNode
 */
export class RootNode extends FolderNode {
    constructor(name, framework) {
        super(name);
        console.log('CREATE root node ' + name);
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
        console.log('RootNode setContextMenu');
        this.getFramework().createContextMenu([
                ['New empty file...', this.boundHandleNewFile],
                ['Load file...', this.boundHandleLoadFile],
                ['New folder...', this.boundHandleNewFolder],
            ]
        );
    }

}

class EditorItem {
    constructor(fileNode, textarea, tabDomNode, readOnly) {
        console.log('Create Codemirror ' + readOnly);

        this.fileNode = fileNode;
        this.editor = CodeMirror.fromTextArea(textarea, {
            tabMode: "indent",
            indentUnit: 4,
            matchBrackets: true,
            autoCloseBrackets: true,
            styleActiveLine: true,
            readOnly: readOnly,
            extraKeys: {'Tab': function(){ this.editor.replaceSelection('    ' , 'end');}},
            lineNumbers: true
            //viewportMargin: Infinity
        });
        this.editor.setSize("100%", "100%");
        // RootNode.editor.setOption('theme', "blackboard");
        this.editor.setOption('theme', "darcula");
        // this.editor.setOption('theme', "abcdef");
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
            extraKeys: {'Tab': function(){ this.editor.replaceSelection('    ' , 'end');}},
            lineNumbers: true
            //viewportMargin: Infinity
        });
        this.editor.setSize("100%", "100%");
        // RootNode.editor.setOption('theme', "blackboard");
        this.editor.setOption('theme', "darcula");
        // this.editor.setOption('theme', "abcdef");

        this.activeNode = undefined; // activeNode associated with Codemirror

        this.nodes = []; // all filenodes with open editor
        this.donNodeEditor = donNodeEditor;
        this.donNodeTabs = donNodeTabs;
        this.focus = undefined; // the tab that has got the focus
        this.framework = framework;
    }

    _switchTo(item, index = undefined) {
        if (index === undefined) {
            // figure out value of i
            for (index = 0; index < this.nodes.length; index++) {
                if (this.nodes[index] === item) {
                    break;
                }
            }
        }
        console.log('item index is ' + index);

        // move on top
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
        if (this.focus !== undefined) {
            this.focus.classList.remove('focus');
            let focusClose = this.focus.querySelector('.close');
            focusClose.style.display = 'none';
        }
        item.tab.classList.add('focus');
        item.tab.querySelector('.close').style.display = 'inline';
        this.focus = item.tab;
    }

    _delete(item) {
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i] === item) {
                // Read back (modified) content
                this.nodes[i].fileNode.updateContent(this.nodes[i].editor.getValue());

                this.nodes.splice(i, 1);
                // Delete Codemirror element (in order to avoid resource leak)
                item.editor.getWrapperElement().remove();
                if (this.nodes.length > 0) {
                    this._switchTo(this.nodes[this.nodes.length-1], this.nodes.length-1);
                }
                return;
            }
        }
        console.error('could not find filenode');
    }

    addEditor(filenode) {
        if (EditorStack.maxEditors == this.nodes.length) {
            alert('maximum number of editors reached');
            return;
        }
        if (filenode.mode !== undefined) {
            // Create tab
            // let tab = document.createElement('span');
            let tab = document.createElement('button');

            // Mode is known => display new text content
            let item = new EditorItem(filenode, this.editortextarea, tab, this.framework.readOnly);
            filenode.getContent(text => {
                item.editor.setValue(text);
            });
            item.editor.setOption("mode", filenode.mode);
            // item.editor.setOption("readOnly", this.readOnly);
            item.editor.refresh(); // for old version of Codemirror

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
            tab.innerHTML = filenode.name;
            tab.append(close);
            tab.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                this._switchTo(item);
            });
            this.donNodeTabs.append(tab);

            this.nodes.push(item);
            this._switchTo(item);
        } else {
            alert('unknown mode');
        }
    }

    switchEditorTo(filenode) {
        // Check if filenode is already in stack
        for (let i = 0; i < this.nodes.length; i++) {
            if (this.nodes[i].fileNode === filenode) {
                // filenode is in list
                this._switchTo(this.nodes[i], i);
                return;
            }
        }
        this.addEditor(filenode);
    }

    handleResize() {
        if (this.nodes.length > 0) {
            // Call refresh for current Codemirror
            // in order to update text window. Otherwise
            // text is cut off
            this.nodes[this.nodes.length-1].editor.refresh();
        }
    }
}

export class Framework {
    constructor() {
        this.roots = []; // all root nodes
        this.syncer = undefined;
        this.mainDomNode = undefined;
        this.menu = undefined;
        this.menuVisible = false;
        this.focus = undefined;
        this.editorstack = undefined;
        this.readOnly = false;
    }

    buildFramework(domnode) {
        console.log('buildFramework');
        domnode.innerHTML = `<div class="ide" style="display: flex;flex-direction: column; align-items: stretch;
    resize: vertical;
    overflow: hidden;
    min-height: 150px">
    <div class="menu" style="flex: none">menu</div>

    <div class="body" style="display: flex; flex-direction: row; flex: 1 1 0; min-height: 0">
        <!--<div class="fake" style="min-width: 100px; flex: 1 0 0; overflow: auto;">Fake element</div> -->
        <div class="explorer" style="min-width: 20px; flex: 1 0 0; overflow: auto;">
        </div>
        <div class="resize"></div>
        <div class="canvas" style="min-width: 20px;  flex: 0 0 75%; display: flex; flex-direction: row;">
            <!-- set flex-basis = 50% for 2 two columns and 100%V for one column -->
            <div class="canvascol" style="display: flex; flex-direction: column; flex: 1 1 50%; overflow: hidden;">
                <div class="tabs" style="flex: none; ">
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

    <div class="status" style="flex: none">status</div>
</div>
<p><label>File or Folder Selected: <input id="last_action" type="text" size="15" readonly=""></label></p>
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
        const menu = document.createElement('div');
        menu.innerHTML = contextmenu;
        let body = document.querySelector('body');
        body.appendChild(menu);

        this.mainDomNode = domnode;
        this.editorstack = new EditorStack(domnode.querySelector('.editor'),
            domnode.querySelector('.tabs'), this);
    }

    init(node, syncer, readOnly) {
        this.readOnly = readOnly;
        const initSplit = resizer =>  {
            // from https://htmldom.dev/create-resizable-split-views/
            const before = resizer.previousElementSibling;
            const after = resizer.nextElementSibling;

            // The current position of mouse
            let x = 0;

            let oldValue = 0;
            let mousedown = false;

            const removeSelection = () => {
                resizer.style.removeProperty('cursor');
                document.body.style.removeProperty('cursor');

                before.style.removeProperty('user-select');
                before.style.removeProperty('pointer-events');

                if (after != undefined) {
                    after.style.removeProperty('user-select');
                    after.style.removeProperty('pointer-events');
                }
            };
            // Handle the mousedown event
            // that's triggered when user drags the resizer
            const mouseDownHandler = e => {
                // Get the current mouse position
                x = e.clientX;

                this.toggleContextmenu("hide");
                oldValue = before.getBoundingClientRect().width;
                mousedown = true;
                // Attach the listeners to `document`
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);

                removeSelection();
            };

            const mouseMoveHandler = e =>  {
                if (mousedown) {
                    // How far the mouse has been moved
                    const dx = e.clientX - x;
                    let newBasis = ((oldValue + dx) * 100) / resizer.parentNode.getBoundingClientRect().width;
                    before.style.flexBasis =`${newBasis}%`;
                    if (after != undefined) {
                        after.style.flexBasis =`${100-newBasis}%`;
                    } else {
                        resizer.parentNode.getBoundingClientRect().width =
                            resizer.parentNode.getBoundingClientRect().width - dx;
                    }
                    removeSelection();
                } else {
                    mouseUpHandler();
                }
            };

            const mouseUpHandler = function () {
                removeSelection();

                // Remove the handlers of `mousemove` and `mouseup`
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };
            // Attach the handler
            resizer.addEventListener('mousedown', mouseDownHandler);
        };

        const fileviewer = node.querySelector('.explorer');

        let ul = document.createElement("ul");
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'fileviewer');
        fileviewer.appendChild(ul);

        this.syncer = syncer;
        // build folder/file structure.
        /* this.syncer.dir(); Da fehlen die Dateien */
        this.createPath('/'); // needed when no files come from syncer.
        this.syncer.list(() => {
            console.log('DISPLAY ROOTS');
            console.log(this.roots);
            for (let i = 0; i < this.roots.length; i++) {
                let root = this.roots[i];
                root.displayInTreeview(ul);
                root.toggleExpand();
            }
        }, this);

        // Hide context menu on every left click
        window.addEventListener("click", e => {
            this.handleClick();
        });

        let el = this.mainDomNode.querySelector('.ide');
        const observer = new ResizeObserver(() => {
            this.editorstack.handleResize();
        });
        observer.observe(el);
        initSplit(node.querySelector('.ide .body > .resize'),  'w');
        // initSplit(node.querySelector('.ide .canvas > .resize'), 'w');

        /*
        RootNode.syncer.sendRequest('mkdir', 'newproformafolder');
        RootNode.syncer.sendRequest('dir'); */
    }

    switchEditorTo(filenode) {
        this.editorstack.switchEditorTo(filenode);
    }
    addEditor(filenode) {
        this.editorstack.addEditor(filenode);
    }

    findNodeByPath(path) {
        console.log('find <' + path + '>');
        if (path.substr(0,1) != '/') {
            console.error('path does not start with /: ' + path);
            return undefined;
        }

        let pathsplit = path.split('/');
        pathsplit.shift(); // first element is always empty
        // let first = pathsplit.shift();
        let root = this.roots[0];
        return root.findNodeByPath(pathsplit);

        /*
        for (let i = 0; i < this.roots.length; i++) {
            if (this.roots[i].name === first) {
                return this.roots[i].findNodeByPath(pathsplit);
            }
        }
        return undefined; */
    }

    createPath(path) {
        console.log('Framework: create folder ' + path);
        // Assume first char is always /
        if (path[0] != '/') {
            console.error('first char in path is not /: ' + path);
        }
        let root;
        if (this.roots.length == 0) {
            root = new RootNode('ROOT', this);
        } else {
            root = this.roots[0];
        }

        let pathsplit = path.split('/');
        pathsplit.shift(); // first element in array is always empty
        return root.createPath(pathsplit);
    }

    createContextMenu(list) {
        if (this.getFramework().readOnly) {
            return;
        }
        console.log('createContextMenu');
        // console.log(list);
        // let ul = this.mainDomNode.querySelector(".contextmenu .menu-options");
        let ul = document.querySelector(".contextmenu .menu-options");
        // console.log(ul);
        ul.innerHTML = ''; // Delete all children
        for (let i = 0; i < list.length; i++) {
            const li = document.createElement('li');
            li.setAttribute('class', 'menu-option');
            li.innerHTML = list[i][0];
            li.addEventListener('click', list[i][1]);
            ul.appendChild(li);
        }

        this.menu = ul.parentNode;
    }

    toggleContextmenu = command => {
        if (this.menu === undefined) {
            return;
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
}

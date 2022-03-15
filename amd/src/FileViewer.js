

// import { Tree }  from "./Tree.js";

import './codemirror-global.js';
import CodeMirror from "./codemirror/src/codemirror.js";
import "./codemirror/mode/clike/clike.js";
import "./codemirror/mode/javascript/javascript.js";
import "./codemirror/mode/python/python.js";
import "./codemirror/mode/xml/xml.js";
import "./codemirror/addon/selection/active-line.js";
import "./codemirror/addon/edit/matchbrackets.js";
import "./codemirror/addon/edit/closebrackets.js";


'use strict';

/**
 * TreeNode
 */
class TreeNode {
    static menu = undefined;
    static menuVisible = false;
    static focus = undefined;

    static toggleContextmenu = command => {
        if (TreeNode.menu === undefined) {
            return;
        }
        TreeNode.menu.style.display = command === "show" ? "block" : "none";
        TreeNode.menuVisible = (command === "show");
    };

    static setFocusTo(element) {
        if (TreeNode.focus !== undefined) {
            TreeNode.focus.classList.remove('focus');
        }
        if (element !== undefined) {
            element.classList.add('focus');
            TreeNode.focus = element;
        } else {
            TreeNode.focus = undefined;
        }
    }
    static handleClick() {
        TreeNode.toggleContextmenu("hide");
        TreeNode.setFocusTo(undefined);
    }

    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // parent Treenode

        this.boundHandleContextMenu = event => {
            this.setContextMenu();
            if (TreeNode.menu === undefined) {
                return;
            }
            const showMenu = ({ top, left }) => {
                TreeNode.menu.style.left = `${left}px`;
                TreeNode.menu.style.top = `${top}px`;
                TreeNode.toggleContextmenu('show');
            };

            console.log(`contextmenu: ${event}`)
            // console.log(event)
            event.preventDefault();
            event.stopPropagation(); // otherwise parent node handles event, too

            const origin = {
                left: event.pageX,
                top: event.pageY
            };
            showMenu(origin);
        }
        this.handleDragStart = event => {
            if (event.dataTransfer.getData('treeitem').length == 0) {
                console.log('dragstart: ' + this.getPath());
                event.dataTransfer.setData('treeitem', this.getPath());
            }
        }
    }
    getPath() {
        return this.parent === undefined? this.name : this.parent.getPath() + '/' + this.name ;
    }

    createContextMenu(list) {
        console.log('createContextMenu');
        console.log(list);
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

        TreeNode.menu = ul.parentNode;
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
            case 'csv':
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
            TreeNode.handleClick(event);
            this.element.remove();
            this.parent.files = this.parent.files.filter(item => item !== this);
            console.log(ProjectNode.projects);
        }
        this.boundHandleRename = event => {
            TreeNode.handleClick(event);
            let name = prompt("Please enter new name:", this.name);
            if (name !== null && name.length > 0) {
                this.name = name;
                this.element.innerHTML = name;
                // this.element.tabIndex = 0;
            }
        }
        this.boundHandleClick = event => {
            console.log('FileNode click');

            TreeNode.toggleContextmenu("hide");
            document.getElementById('last_action').value = this.name;
            if (this.filecontent != undefined) {
                ProjectNode.setEditorContent(this);
            }
            TreeNode.setFocusTo(this.element);
            event.stopPropagation();
            // event.preventDefault();
        }
    }

    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.innerHTML = this.name;
        li.classList.add('doc');

        li.addEventListener('click', this.boundHandleClick);

//        li.addEventListener('mouseover', this.handleMouseOver);
//        li.addEventListener('mouseout', this.handleMouseOut);
    }

    setContextMenu() {
        console.log('FileNode setContextMenu');
        this.createContextMenu([
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
            TreeNode.handleClick(event);
            this.element.remove();
            this.parent.folders = this.parent.folders.filter(item => item !== this);
            console.log(ProjectNode.projects);
        }
        this.boundHandleNewFile = event => {
            TreeNode.handleClick(event);
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
            }
        }
        this.boundHandleLoadFile = event => {
            TreeNode.handleClick(event);
            let input = document.createElement('input');
            input.type = 'file';
            input.onchange = e => {
                let file = e.target.files[0];
                this._addFileFromOs(file, true);
            }
            input.click();
        }
        this.handleDragOver = event => {
            event.preventDefault();
        }
        this.handleDragEnter = event => {
            this.element.querySelector('.name').classList.add('dragover');
        }
        this.handleDragLeave = event => {
            this.element.querySelector('.name').classList.remove('dragover');
        }

        this.handleDrop = event => {
            event.preventDefault();
            event.stopPropagation();
            TreeNode.toggleContextmenu("hide");
            this.element.querySelector('.name').classList.remove('dragover');
            const path = event.dataTransfer.getData('treeitem');
            if (path !== undefined && path.length > 0) {
                console.log('drop ' + path + ' onto ' + this.getPath());
                // Node element from tree
                const node = ProjectNode.findNodeByPath(path);
                if (node !== undefined && !this.isNameChildUnique(node.name)) {
                    // TODO: wenn der Ordner schon existiert, sollte nur der Inhalt gemergt werden
                    alert(node.name + ' already exists');
                    return;
                }
                if (node instanceof FolderNode) {
                    // remove folder in old parent
                    node.parent.folders = node.parent.folders.filter(item => item !== node);
                    // add folder to this
                    this.appendFolder(node);
                    this.element.querySelector('ul').appendChild(node.element);
                    // node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.expand(true);
                } else if (node instanceof FileNode) {
                    node.parent.files = node.parent.files.filter(item => item !== node);
                    // add folder to this
                    this.appendFile(node);
                    // node.displayInTreeview(this.element.querySelector('[role="group"]'));
                    this.element.querySelector('ul').appendChild(node.element);
                    this.expand(true);
                } else {
                    console.log('node cannot be moved');
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
        }
        this.boundHandleNewFolder = event => {
            TreeNode.handleClick(event);
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
            }
        }
        this.boundHandleClick = event => {
            console.log('FolderNode click');
            TreeNode.toggleContextmenu("hide");
            // TreeNode.setFocusTo(this.element);
            // this.element.classList.add('focus');
            event.stopPropagation();
            event.preventDefault();
        }
        this.boundHandleRename = event => {
            TreeNode.handleClick(event);
            let name = prompt("Please enter new name:", this.name);
            if (name !== null && name.length > 0) {
                if (!this.parent.isNameChildUnique(name)) {
                    alert(node.name + ' already exists');
                    return;
                }
                this.name = name;
                this.element.querySelector('.name').innerHTML = name;
            }
        }
        this.toggleExpand = event => {
            this.element.setAttribute('aria-expanded', !this.isExpanded());
        }
        this.handleMouseOver = event => {
            event.currentTarget.classList.add('hover');
        }
        this.handleMouseOut = event => {
            event.currentTarget.classList.remove('hover');
        }
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

    _getFileTree(item, path) {
        path = path || "";
        if (item.isFile) {
            item.file(file => {
                this._addFileFromOs(file);
            });
        } else if (item.isDirectory) {
            // Create new folder
            let node = new FolderNode(item.name);
            this.appendFolder(node);
            node.displayInTreeview(this.element.querySelector('[role="group"]'));
            this.expand(true);

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
                ProjectNode.setEditorContent(node);
            }
        }
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
        this.createContextMenu([
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
 * ProjectNode
 */
export class ProjectNode extends FolderNode {
    static projects = []; // all projects
    static roots = [];
    static editor = undefined;
    static filenode = undefined;


    static buildFramework(node) {
        node.innerHTML = `<div class="ide" style="display: flex;flex-direction: column; /* align-items: stretch;*/
    resize: vertical;
    overflow: hidden;
    min-height: 100px;
    border:black">
    <div class="menu" style="flex: none">menu</div>

    <div class="body"
         style="display: flex; flex-direction: row; flex: auto">
        <div class="explorercol" style="display: flex; flex-direction: column; flex: 1 1 20%;">
            <div class="explorer" style="flex: 1 1 0; min-height: 0; overflow: auto;">
            </div>
        </div>
        <div class="resize"></div>
        <div class="canvas" style="display: flex; flex-direction: row; flex: 1 1">
            <!-- set flex-basis = 50% for 2 two columns and 100%V for one column -->
            <div class="canvascol" style="display: flex; flex-direction: column; flex: 1 1 50%; min-height: 0;">
                <div class="tabs" style="flex: none; ">
                    <button>tab1</button>
                    <button>tab2</button>
                    <button>tab3</button>
                </div>
                <div class="editor" style="flex: 1 1 0; min-height: 0; overflow: hidden;">
                    <textarea></textarea>
                </div>
            </div>
            <div class="resize"></div>
            <div class="canvascol" style="display: flex; flex-direction: column; flex: 1 1 50%; min-height: 0;">
                <div class="tabs" style="flex: none; ">
                    <button>tab1</button>
                    <button>tab2</button>
                    <button>tab3</button>
                </div>
                <div class="editor" style="flex: 1 1 0; min-height: 0; overflow: hidden;">
                    <textarea></textarea>
                </div>
            </div> 
        </div>
    </div>

    <div class="status" style="flex: none">
        status
    </div>
</div>
`;
    }

    static init(node) {
        function initSplit(resizer) {
            // from https://htmldom.dev/create-resizable-split-views/
            const before = resizer.previousElementSibling;
            const after = resizer.nextElementSibling;

            // The current position of mouse
            let x = 0;
            let y = 0;

            let oldValue = 0;
            let mousedown = false;

            // Handle the mousedown event
            // that's triggered when user drags the resizer
            const mouseDownHandler = function (e) {
                // Get the current mouse position
                x = e.clientX;
                y = e.clientY;

                TreeNode.toggleContextmenu("hide");
                oldValue = before.getBoundingClientRect().width;
                mousedown = true;
                // Attach the listeners to `document`
                document.addEventListener('mousemove', mouseMoveHandler);
                document.addEventListener('mouseup', mouseUpHandler);
            };

            const mouseMoveHandler = function (e) {
                if (mousedown) {
                    // How far the mouse has been moved
                    const dx = e.clientX - x;
                    const dy = e.clientY - y;

                    let newBasis = ((oldValue + dx) * 100) / resizer.parentNode.getBoundingClientRect().width;

                    before.style.flexBasis =`${newBasis}%`;
                    if (after != undefined) {
                        after.style.flexBasis =`${100-newBasis}%`;
                    } else {
                        resizer.parentNode.getBoundingClientRect().width =
                                resizer.parentNode.getBoundingClientRect().width - dx;
                    }
                } else {
                    mouseUpHandler();
                }
            };

            const mouseUpHandler = function () {
                resizer.style.removeProperty('cursor');
                document.body.style.removeProperty('cursor');

                before.style.removeProperty('user-select');
                before.style.removeProperty('pointer-events');

                if (after != undefined) {
                    after.style.removeProperty('user-select');
                    after.style.removeProperty('pointer-events');
                }

                // Remove the handlers of `mousemove` and `mouseup`
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };
            // Attach the handler
            resizer.addEventListener('mousedown', mouseDownHandler);
        }

        const fileviewer = node.querySelector('.explorer');
        const editor = node.querySelector('.editor textarea');

        let ul = document.createElement("ul")
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'fileviewer');
        fileviewer.appendChild(ul);

        for (let i = 0; i < ProjectNode.projects.length; i++) {
            let project = ProjectNode.projects[i];
            const li = project.displayInTreeview(ul);
        }

        ProjectNode.editor = CodeMirror.fromTextArea(editor, {
            tabMode: "indent",
            indentUnit: 4,
            matchBrackets: true,
            autoCloseBrackets: true,
            styleActiveLine: true,
            readOnly: false,
            extraKeys: {'Tab': function(){editor.replaceSelection('    ' , 'end');}},
            lineNumbers: true
            //viewportMargin: Infinity
        });
        ProjectNode.editor.setSize("100%", "100%");
        // ProjectNode.editor.setOption('theme', "blackboard");
        // ProjectNode.editor.setOption('theme', "darcula");
        ProjectNode.editor.setOption('theme', "abcdef");

        // Hide context menu on every left click
        window.addEventListener("click", e => {
            TreeNode.handleClick();
         });
        initSplit(document.querySelector('.ide .body > .resize'),  'w');
        initSplit(document.querySelector('.ide .canvas > .resize'), 'w');
    }

    static setEditorContent(filenode) {
        if (ProjectNode.filenode != undefined && ProjectNode.filenode.mode !== undefined) {
            // Store (modified) content
            ProjectNode.filenode.filecontent = ProjectNode.editor.getValue();
        }
        if (filenode.mode !== undefined) {
            // Display new content
            ProjectNode.filenode = filenode;
            ProjectNode.editor.setValue(filenode.filecontent);
            ProjectNode.editor.setOption("mode", filenode.mode);
        }
    }
    static findNodeByPath(path) {
        let pathsplit = path.split('/');
        let first = pathsplit.shift();
        for (let i = 0; i < ProjectNode.projects.length; i++) {
            if (ProjectNode.projects[i].name === first) {
                return ProjectNode.projects[i].findNodeByPath(pathsplit);
            }
        }
        return undefined;
    }
    constructor(name) {
        super(name);
        ProjectNode.projects.push(this);
    }
}

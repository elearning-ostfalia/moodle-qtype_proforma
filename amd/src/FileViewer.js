

// import { Tree }  from "./Tree.js";

import './codemirror-global.js';
import CodeMirror from "./codemirror/src/codemirror.js";
import "./codemirror/mode/clike/clike.js";

'use strict';

/**
 * TreeNode
 */
class TreeNode {
    static menu = undefined;
    static menuVisible = false;
    static focus = undefined;

    static toggleMenu = command => {
        if (TreeNode.menu === undefined) {
            // console.log('no context menu');
            return;
        }
        // console.log(command);
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
        TreeNode.toggleMenu("hide");
        TreeNode.setFocusTo(undefined);
    }

    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // parent

        this.boundHandleContextMenu = event => {
            this.setContextMenu();
            if (TreeNode.menu === undefined) {
                return;
            }
            const showMenu = ({ top, left }) => {
                TreeNode.menu.style.left = `${left}px`;
                TreeNode.menu.style.top = `${top}px`;
                TreeNode.toggleMenu('show');
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
        domnode.appendChild(li);
        li.addEventListener('contextmenu', this.boundHandleContextMenu);
        this.element = li; // Store element
        return li;
    }
}

/**
 * FileNode
 */
export class FileNode extends TreeNode {
    constructor(name) {
        super(name);
        this.filecontent = '';
        this.handleDelete = event => {
            TreeNode.handleClick(event);
            this.element.remove();
            this.parent.files = this.parent.files.filter(item => item !== this);
            console.log(ProjectNode.projects);
        }
        this.boundHandleRename = event => {
            TreeNode.handleClick(event);
            let name = prompt("Please enter new name:", "");
            if (name !== null && name.length > 0) {
                this.name = name;
                this.element.innerHTML = name;
                // this.element.tabIndex = 0;
            }
        }
        this.boundHandleClick = event => {
            console.log('FileNode click');

            TreeNode.toggleMenu("hide");
            document.getElementById('last_action').value = this.name;
            if (this.filecontent != undefined) {
                ProjectNode.editor.setValue(this.filecontent);
            }
            TreeNode.setFocusTo(this.element);
            event.stopPropagation();
            // event.preventDefault();
        }
/*        this.handleMouseOver = event => {
            event.currentTarget.classList.add('hover');
        }
        this.handleMouseOut = event => {
            event.currentTarget.classList.remove('hover');
        }*/
    }

    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.innerHTML = this.name;
        li.setAttribute('class', 'doc');
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
                let node = new FileNode(file.name);
                // setting up the reader
                let reader = new FileReader();
                reader.readAsText(file, 'UTF-8');
                reader.onload = readerEvent => {
                    let content = readerEvent.target.result; // this is the content!
                    console.log(content);
                    node.filecontent = content;
                    ProjectNode.editor.setValue(this.content);
                }

                this.appendFile(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
            }
            input.click();
        }
        this.allowDrop = event => {
            event.preventDefault();
        }

        this.drop = event => {
            event.preventDefault();
            event.stopPropagation();
            if (event.dataTransfer.items) {
                // Use DataTransferItemList interface to access the file(s)
                for (let i = 0; i < event.dataTransfer.items.length; i++) {
                    // If dropped items aren't files, reject them
                    if (event.dataTransfer.items[i].kind === 'file') {
                        let file = event.dataTransfer.items[i].getAsFile();
                        this._addFileFromOs(file);
                    }
                }
            } else {
                // Use DataTransfer interface to access the file(s)
                for (let i = 0; i < event.dataTransfer.files.length; i++) {
                    this._addFileFromOs(event.dataTransfer.files[i]);
                }
            }
        }


        this.boundHandleNewFolder = event => {
            TreeNode.handleClick(event);
            let foldername = prompt("Please enter foldername:", "");
            if (foldername !== null && foldername.length > 0) {
                let node = new FolderNode(foldername);
                this.appendFolder(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
            }
        }
        this.boundHandleClick = event => {
            console.log('FolderNode click');

            TreeNode.toggleMenu("hide");
            document.getElementById('last_action').value = this.name;
            if (ProjectNode.editor != undefined) {
                ProjectNode.editor.setValue(this.name);
            }
            // TreeNode.setFocusTo(this.element);

            // this.element.classList.add('focus');
            event.stopPropagation();
            event.preventDefault();
        }
        this.boundHandleRename = event => {
            TreeNode.handleClick(event);
            let name = prompt("Please enter new name:", "");
            if (name !== null && name.length > 0) {
                this.name = name;
                this.element.querySelector('span').innerHTML = name;
            }
        }
        this.toggleExpand = event => {
            this.element.setAttribute('aria-expanded', !this.isExpanded());
        }

    }

    _addFileFromOs(file) {
        let node = new FileNode(file.name);
        let reader = new FileReader();
        reader.readAsText(file,'UTF-8');
        reader.onload = readerEvent => {
            let content = readerEvent.target.result; // this is the content!
            // console.log( content );
            node.filecontent = content;
            ProjectNode.editor.setValue(this.content);
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
        // span1.innerHTML = '';
        span1.classList.add('before');
        span1.addEventListener('click', this.toggleExpand);
        li.appendChild(span1);

        const span2 = document.createElement('span');
        span2.innerHTML = this.name;
        span1.classList.add('name');
        span2.addEventListener('click', this.boundHandleClick);
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
        li.addEventListener('drop', this.drop);
        li.addEventListener('dragover', this.allowDrop);

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


    static init(fileviewer, editor) {
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
        ProjectNode.editor.setOption("mode", "text/x-java");
        // ProjectNode.editor.setOption('theme', "blackboard");
        // ProjectNode.editor.setOption('theme', "darcula");
        ProjectNode.editor.setOption('theme', "abcdef");

        // Hide context menu on every left click
        window.addEventListener("click", e => {
            TreeNode.handleClick();
         });
    }

    constructor(name) {
        super(name);
        ProjectNode.projects.push(this);
    }
}

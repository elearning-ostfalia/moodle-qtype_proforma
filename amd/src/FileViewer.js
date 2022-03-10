

// import { Tree }  from "./Tree.js";
// import './codemirror-global.js';

import CodeMirror from "./codemirror/src/codemirror.js";

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
    static handleClickEvent() {
        console.log('TreeNode click');
        TreeNode.toggleMenu("hide");
        TreeNode.setFocusTo(undefined);
    }

    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // parent
/*        this.boundHandleClick = event => {
            TreeNode.toggleMenu("hide");
            document.getElementById('last_action').value = this.name;
            document.getElementById('canvas').innerHTML = this.name;
            event.stopPropagation();
            event.preventDefault();
        } */
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
        li.addEventListener('click', this.boundHandleClick);
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
        this.boundHandleDelete = event => {
            TreeNode.handleClickEvent(event);
            this.element.remove();
            this.parent.files = this.parent.files.filter(item => item !== this);
            console.log(ProjectNode.projects);
        }
        this.boundHandleRename = event => {
            TreeNode.handleClickEvent(event);
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
                document.getElementById('canvas').innerHTML = this.filecontent;
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
//        li.addEventListener('mouseover', this.handleMouseOver);
//        li.addEventListener('mouseout', this.handleMouseOut);
    }

    setContextMenu() {
        console.log('FileNode setContextMenu');
        this.createContextMenu([
            ['Delete', this.boundHandleDelete],
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
        this.boundHandleDelete = event => {
            TreeNode.handleClickEvent(event);
            this.element.remove();
            this.parent.folders = this.parent.folders.filter(item => item !== this);
            console.log(ProjectNode.projects);
        }

        this.boundHandleNewFile = event => {
            TreeNode.handleClickEvent(event);
            let filename = prompt("Please enter filename:", "");
            if (filename !== null && filename.length > 0) {
                let node = new FileNode(filename);
                this.appendFile(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
            }
        }
        this.boundHandleLoadFile = event => {
            TreeNode.handleClickEvent(event);
            let input = document.createElement('input');
            input.type = 'file';
            input.onchange = e => {
                let file = e.target.files[0];
                let node = new FileNode(file.name);
                // setting up the reader
                let reader = new FileReader();
                reader.readAsText(file,'UTF-8');
                reader.onload = readerEvent => {
                    let content = readerEvent.target.result; // this is the content!
                    console.log( content );
                    node.filecontent = content;
                    document.getElementById('canvas').innerHTML = content;
                }

                this.appendFile(node);
                node.displayInTreeview(this.element.querySelector('[role="group"]'));
                this.expand(true);
            }
            input.click();
        }


        this.boundHandleNewFolder = event => {
            TreeNode.handleClickEvent(event);
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
            document.getElementById('canvas').innerHTML = this.name;
            this.element.setAttribute('aria-expanded', !this.isExpanded());
            // TreeNode.setFocusTo(this.element);

            // this.element.classList.add('focus');
            event.stopPropagation();
            event.preventDefault();
        }
        this.boundHandleRename = event => {
            TreeNode.handleClickEvent(event);
            let name = prompt("Please enter new name:", "");
            if (name !== null && name.length > 0) {
                this.name = name;
                this.element.querySelector('span').innerHTML = name;
            }
        }
    }

    expand(doit) {
        this.element.setAttribute('aria-expanded', doit);
    }
    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.setAttribute('aria-expanded', 'false');

        const span = document.createElement('span');
        span.innerHTML = this.name;
        li.appendChild(span);

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
            ['Delete', this.boundHandleDelete]
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
        ProjectNode.editor.setSize("100%", null);

        // Hide context menu on every left click
        window.addEventListener("click", e => {
            TreeNode.handleClickEvent();
         });
    }

    constructor(name) {
        super(name);
        ProjectNode.projects.push(this);
    }
}



// import { Tree }  from "./Tree.js";
// 'use strict';

/**
 * TreeNode
 */
class TreeNode {
    static menu = undefined;
    static menuVisible = false;

    static toggleMenu = command => {
        if (TreeNode.menu === undefined) {
            console.log('no context menu');
            return;
        }
        // console.log(command);
        TreeNode.menu.style.display = command === "show" ? "block" : "none";
        TreeNode.menuVisible = (command === "show");
    };

    static handleClickEvent(event) {
        TreeNode.toggleMenu("hide");
        event.preventDefault();
        event.stopPropagation(); // otherwise parent node handles event, too

    }

    constructor(name) {
        this.name = name;
        this.element = undefined; // DOM element
        this.parent = undefined; // parent
/*        this.boundHandleFocus = event => {
            // this.element.classList.add('focus');
            event.stopPropagation();
            event.preventDefault();
        }*/
        this.boundHandleClick = event => {
            TreeNode.toggleMenu("hide");
            document.getElementById('last_action').value = this.name;
            document.getElementById('canvas').innerHTML = this.name;
            event.stopPropagation();
            event.preventDefault();
        }
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
            }
        }
    }

    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.innerHTML = this.name;
        li.setAttribute('class', 'doc');
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
            TreeNode.toggleMenu("hide");
            document.getElementById('last_action').value = this.name;
            document.getElementById('canvas').innerHTML = this.name;
            this.element.setAttribute('aria-expanded', !this.isExpanded());
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


    static displayInTreeview(domnode) {
        let ul = document.createElement("ul")
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'fileviewer');
        domnode.appendChild(ul);

        for (let i = 0; i < ProjectNode.projects.length; i++) {
            let project = ProjectNode.projects[i];
            const li = project.displayInTreeview(ul);
        }

        // Hide context menu on every left click
        window.addEventListener("click", e => {
            if (TreeNode.menuVisible) {
                TreeNode.toggleMenu("hide");
            }
        });
    }

    constructor(name) {
        super(name);
        ProjectNode.projects.push(this);
    }
}

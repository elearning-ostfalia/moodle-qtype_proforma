


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

    constructor(name) {
        this.name = name;
        this.boundHandleClick = event => {
            // console.log(`clicked: ${event}`)
            const xCoordinate = event.pageX;
            const yCoordinate = event.pageY;
            //console.log(`x: ${xCoordinate}, y: ${yCoordinate}`)

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
            event.stopPropagation();

            const origin = {
                left: event.pageX,
                top: event.pageY
            };

            showMenu(origin);
            // return false;
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
            li.innerHTML = list[i];
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
        return li;
    }

}

export class FileNode extends TreeNode {
    constructor(name) {
        super(name);
    }

    displayInTreeview(domnode) {
        const li = super.displayInTreeview(domnode);
        li.innerHTML = this.name;
        li.setAttribute('class', 'doc');
    }

    setContextMenu() {
        console.log('FileNode setContextMenu');
        this.createContextMenu(['Delete...', 'Save', 'Rename']);
    }

}

export class FolderNode extends TreeNode {
    constructor(name) {
        super(name);
        // Empty list of files.
        this.files = [];
        // Empty list of folders.
        this.folders = [];
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

    setContextMenu() {
        console.log('FolderNode setContextMenu');
        this.createContextMenu(['New file...', 'New folder...', 'Delete']);
    }
}

export class ProjectNode extends FolderNode {
    static projects = []; // all projects


    static displayInTreeview(domnode) {
        let ul = document.createElement("ul")
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'tree_label');
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



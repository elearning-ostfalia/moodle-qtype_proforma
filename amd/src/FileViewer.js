


class TreeNode {
    constructor(name) {
        this.name = name;
    }
}

export class FileNode extends TreeNode {
    constructor(name) {
        super(name);
    }

    display(domnode) {
        const li = document.createElement('li');
        li.setAttribute('role', 'treeitem');
        li.setAttribute('class', 'doc');
        li.innerHTML = this.name;
        domnode.appendChild(li);
    }
}

export class FolderNode extends TreeNode {
    constructor(name) {
        super(name);
        // Empty list of files.
        this.files = [];
        this.folders = [];
    }
    display(domnode) {
        const li = document.createElement('li');
        li.setAttribute('role', 'treeitem');
        li.setAttribute('aria-expanded', 'false');
        domnode.appendChild(li);

        const span = document.createElement('span');
        span.innerHTML = this.name;
        li.appendChild(span);

        const subul = document.createElement('ul');
        subul.setAttribute('role', 'group');
        li.appendChild(subul);

        for (let j = 0; j < this.folders.length; j++) {
            this.folders[j].display(subul);
        }
        for (let j = 0; j < this.files.length; j++) {
            this.files[j].display(subul);
        }
    }
}

export class ProjectNode extends TreeNode {
    static projects = []; // all projects
    static display(domnode) {
        let ul = document.createElement("ul")
        ul.setAttribute('role', 'tree');
        ul.setAttribute('aria-labelledby', 'tree_label');
        domnode.appendChild(ul);

        for (let i = 0; i < ProjectNode.projects.length; i++) {
            const li = document.createElement('li');
            li.setAttribute('role', 'treeitem');
            li.setAttribute('aria-expanded', 'false');
            ul.appendChild(li);

            const span = document.createElement('span');
            span.innerHTML = ProjectNode.projects[i].name;
            ul.appendChild(span);

            const subul = document.createElement('ul');
            subul.setAttribute('role', 'group');
            let project = ProjectNode.projects[i];
            for (let j = 0; j < project.folders.length; j++) {
                project.folders[j].display(subul);
            }
            for (let j = 0; j < project.files.length; j++) {
                project.files[j].display(subul);
            }
            ul.appendChild(subul);
        }


    }
    constructor(name) {
        super(name);
        // Empty list of nodes.
        this.folders = [];
        this.files = [];
        ProjectNode.projects.push(this);
    }
}



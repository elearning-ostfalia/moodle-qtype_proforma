


class TreeNode {
    constructor(name) {
        this.name = name;
    }
}

export class FileNode extends TreeNode {
    constructor(name) {
        super(name);
    }
}

export class FolderNode extends TreeNode {
    constructor(name) {
        super(name);
        // Empty list of files.
        this.files = [];
    }
}

export class ProjectNode extends TreeNode {
    constructor(name) {
        super(name);
        // Empty list of nodes.
        this.folders = [];
    }
}



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
 * Explorer view for the files of the task editor.
 *
 * The explorer (FileViewer.js) works on the same data as the file boxes of the
 * task editor (FileWrapper). The syncer maps the explorer operations onto the
 * file boxes. Folders are given by the filenames (ProFormA filenames are paths).
 * Nothing is stored in Moodle: all files are zipped into the task file later.
 *
 * @package
 * @subpackage proforma
 * @copyright  2026 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

import {debugLog} from '../debug';
import Notification from 'core/notification';
import * as Str from 'core/str';
import {Syncer} from '../MoodleSyncer';
import {Framework, FileNode, LinkNode, ViewRootNode} from '../FileViewer';
import $ from 'jquery';
import {FileWrapper} from './file';
import {getExtension, readAndCreateFileData} from './helper';
import {FileReferenceList, TestFileReference} from './filereflist';
import {TestWrapper} from './test';
import {ModelSolutionWrapper} from './modelsol';

/**
 * Converts an explorer path ('/src/Foo.java') into a task filename ('src/Foo.java').
 *
 * @param {string} path explorer path
 * @returns {string} task filename
 */
const toTaskFilename = path => path.replace(/^\/+/, '');

/**
 * Converts an explorer folder path ('/src/de' or '/src/de/.') into a filename prefix ('src/de/').
 *
 * @param {string} path explorer folder path
 * @returns {string} prefix of all filenames in this folder
 */
const toFolderPrefix = path => {
    let prefix = toTaskFilename(path);
    if (prefix.endsWith('/.')) {
        prefix = prefix.substring(0, prefix.length - 2);
    }
    if (prefix.endsWith('/')) {
        prefix = prefix.substring(0, prefix.length - 1);
    }
    return prefix.length > 0 ? prefix + '/' : '';
};

/**
 * Calls the callback for all file boxes with a filename.
 *
 * @param {Function} callback called with the FileWrapper
 */
const doOnAllNamedFiles = callback => {
    FileWrapper.doOnAllFiles(uiFile => {
        if (uiFile && uiFile.filename) {
            callback(uiFile);
        }
    });
};

/**
 * Returns all tests and model solutions that can reference files.
 *
 * @param {Object} strings localised strings (modelsolution)
 * @returns {Array} list of {kind ('test' or 'modelsolution'), short, text, root}
 */
const getFileUsers = strings => {
    let users = [];
    TestWrapper.doOnAll(test => {
        if (!test) {
            return;
        }
        // Tests without file references: the table is hidden (see TestWrapper.createFromTemplate).
        const table = test.root.find('table.xml_fileref_table');
        if (table.length === 0 || table.css('display') === 'none') {
            return;
        }
        const title = test.title ? ': ' + test.title : '';
        users.push({kind: 'test', short: 'T' + test.id, text: 'Test ' + test.id + title, root: test.root});
    });
    ModelSolutionWrapper.doOnAll(ms => {
        if (!ms) {
            return true;
        }
        users.push({kind: 'modelsolution', short: 'M' + ms.id, text: strings.modelsolution + ' ' + ms.id, root: ms.root});
        return true;
    });
    return users;
};

/**
 * Checks whether the file is referenced in a test or model solution.
 *
 * @param {Object} root jQuery root of the test or model solution
 * @param {string} fileid file id
 * @returns {boolean}
 */
const isReferenced = (root, fileid) =>
    root.find('.fileref_fileref').toArray().some(item => item.value === fileid);

/**
 * Adds a file reference to a test or model solution (in the first empty row or in a new row).
 *
 * @param {Object} root jQuery root of the test or model solution
 * @param {string} filename filename
 * @returns {Promise}
 */
const addReference = (root, filename) => {
    // The handlers of the file reference rows of tests and model solutions are those of
    // TestFileReference (see FileReferenceList.addCallbacks).
    const list = TestFileReference.getInstance();
    const empty = root.find('.xml_fileref_filename').filter((index, element) => {
        const value = $(element).val();
        return !value || value.trim().length === 0;
    }).first();
    let promise;
    if (empty.length > 0) {
        promise = Promise.resolve(empty);
    } else {
        promise = list.addItem(root.find('.add_fileref').last())
            .then(() => root.find('.xml_fileref_filename').last());
    }
    return promise.then(select => {
        FileReferenceList.updateFilenameList(select);
        // Change handler sets the file id (FileReferenceList.onFileSelectionChanged).
        select.val(filename).change();
        return select;
    });
};

/**
 * Removes all references to a file from a test or model solution.
 * The file itself is kept (unlike removing the row with the remove button which deletes
 * files that are no longer referenced).
 *
 * @param {Object} root jQuery root of the test or model solution
 * @param {string} fileid file id
 */
const removeReference = (root, fileid) => {
    root.find('.fileref_fileref').toArray()
        .filter(item => item.value === fileid)
        .forEach(item => {
            const select = $(item).closest('tr').find('.xml_fileref_filename').first();
            // Clears the file id before the row is removed => the file is not deleted.
            FileReferenceList.removeContent(select, true);
        });
};

/**
 * Builds a read-only tree view: one node per test (or model solution) with its files.
 *
 * @param {Framework} framework explorer
 * @param {Object} strings localised strings
 * @param {string} kind 'test' or 'modelsolution'
 * @returns {Array} list of ViewRootNode
 */
const buildUsersView = (framework, strings, kind) =>
    getFileUsers(strings)
        .filter(user => user.kind === kind)
        .map(user => {
            const root = new ViewRootNode(user.text, framework);
            user.root.find('.fileref_fileref').toArray()
                .map(item => item.value)
                .filter(fileid => fileid)
                .forEach(fileid => {
                    const uiFile = FileWrapper.constructFromId(fileid);
                    if (!uiFile || !uiFile.filename) {
                        return;
                    }
                    const [folder, name] = Syncer.splitFullname('/' + uiFile.filename);
                    let pathsplit = folder.split('/');
                    pathsplit.shift(); // First element is always empty.
                    root.createPath(pathsplit).appendFile(new LinkNode(name, '/' + uiFile.filename));
                });
            return root;
        });

/**
 * Syncer for the explorer in the task editor.
 */
export class TaskeditorSyncer extends Syncer {
    /**
     * Constructor.
     *
     * @param {Object} options explorer options
     * @param {Function} onFilesChanged called when the tree must be rebuilt (e.g. after an upload)
     */
    constructor(options, onFilesChanged) {
        super(options);
        this.onFilesChanged = onFilesChanged;
        this.binaryText = options.binarytext;
        this.strings = options.strings;
    }

    /**
     * Labels for a file in the tree: which tests and model solutions use the file.
     *
     * @param {string} path explorer path
     * @returns {Array} list of {text, title}
     */
    getFileLabels(path) {
        const uiFile = TaskeditorSyncer.findFile(path);
        if (!uiFile) {
            return [];
        }
        return getFileUsers(this.strings)
            .filter(user => isReferenced(user.root, uiFile.id))
            .map(user => ({text: user.short, title: user.text}));
    }

    /**
     * Additional context menu entries for a file: assign the file to tests and model solutions.
     *
     * @param {string} path explorer path
     * @param {FileNode} node tree node (its label is updated)
     * @returns {Array} list of [text, handler]
     */
    getContextMenuEntries(path, node) {
        const uiFile = TaskeditorSyncer.findFile(path);
        if (!uiFile) {
            return [];
        }
        return getFileUsers(this.strings).map(user => {
            const used = isReferenced(user.root, uiFile.id);
            const text = (used ? '\u2611 ' : '\u2610 ') + user.text;
            return [text, () => {
                if (used) {
                    removeReference(user.root, uiFile.id);
                    node.updateLabel();
                } else {
                    addReference(user.root, uiFile.filename)
                        .then(() => node.updateLabel())
                        .catch(Notification.exception);
                }
            }];
        });
    }

    /**
     * Finds the file box for an explorer path.
     *
     * @param {string} path explorer path
     * @returns {FileWrapper|undefined}
     */
    static findFile(path) {
        const filename = toTaskFilename(path);
        let result;
        doOnAllNamedFiles(uiFile => {
            if (uiFile.filename === filename) {
                result = uiFile;
            }
        });
        return result;
    }

    list(framework) {
        let filenames = [];
        doOnAllNamedFiles(uiFile => filenames.push(uiFile.filename));
        filenames.sort();
        filenames.forEach(filename => {
            const [folder, name] = Syncer.splitFullname('/' + filename);
            framework.createPath(folder).appendFile(new FileNode(name));
        });
        return Promise.resolve();
    }

    download(path) {
        const uiFile = TaskeditorSyncer.findFile(path);
        if (!uiFile) {
            return Promise.reject(new Error('cannot find file ' + path));
        }
        if (uiFile.isBinary) {
            return Promise.resolve(this.binaryText);
        }
        return Promise.resolve(uiFile.text);
    }

    update(path, text) {
        const uiFile = TaskeditorSyncer.findFile(path);
        // Binary files cannot be edited in the explorer (it only shows a hint).
        if (uiFile && !uiFile.isBinary && uiFile.text !== text) {
            debugLog('explorer: update ' + path);
            uiFile.text = text;
        }
        return Promise.resolve();
    }

    newfile(path) {
        return FileWrapper.createFromTemplate()
            .then(uiFile => {
                uiFile.filename = toTaskFilename(path);
                return uiFile;
            });
    }

    upload(path, file) {
        const target = toTaskFilename(path);
        readAndCreateFileData(file, undefined, (filename, fileid) => {
            // Java files get their filename from the package declaration (see readAndCreateFileData).
            if (getExtension(filename) !== 'java' && filename !== target) {
                FileWrapper.constructFromId(fileid).filename = target;
            }
            // Show the file where it actually is.
            this.onFilesChanged();
        });
        return Promise.resolve();
    }

    renameFile(pathold, pathnew) {
        const uiFile = TaskeditorSyncer.findFile(pathold);
        if (uiFile) {
            // The setter also updates the file selections in the tests.
            uiFile.filename = toTaskFilename(pathnew);
        }
        return Promise.resolve();
    }

    renameFolder(pathold, pathnew) {
        const oldprefix = toFolderPrefix(pathold);
        const newprefix = toFolderPrefix(pathnew);
        doOnAllNamedFiles(uiFile => {
            if (uiFile.filename.startsWith(oldprefix)) {
                uiFile.filename = newprefix + uiFile.filename.substring(oldprefix.length);
            }
        });
        return Promise.resolve();
    }

    deleteFileOrFolder(path) {
        if (path.endsWith('/.')) {
            const prefix = toFolderPrefix(path);
            doOnAllNamedFiles(uiFile => {
                if (uiFile.filename.startsWith(prefix)) {
                    uiFile.delete();
                }
            });
        } else {
            const uiFile = TaskeditorSyncer.findFile(path);
            if (uiFile) {
                // Also removes the file from the file selections in the tests.
                uiFile.delete();
            }
        }
        return Promise.resolve();
    }

    mkdir(path) {
        // Empty folders do not exist in ProFormA. The folder only exists in the tree
        // until a file is created in it.
        debugLog('explorer: mkdir ' + path + ' (tree only)');
        return Promise.resolve();
    }
}

/**
 * Explorer view in the files tab of the task editor.
 */
export class TaskeditorExplorer {
    /**
     * Constructor.
     *
     * @param {HTMLElement} container element for the explorer
     */
    constructor(container) {
        this.container = container;
        this.framework = undefined;
        this.strings = undefined;
        this.state = undefined; // Display state of the last explorer (see _saveState).
    }

    /**
     * (Re)builds the explorer from the current files of the task editor.
     *
     * @returns {Promise}
     */
    show() {
        return this._getStrings()
            .then(([rootname, binarytext, modelsolution, tests]) => {
                this.destroy();
                const strings = {modelsolution: modelsolution};
                const options = {
                    rootnode: rootname,
                    allowfolders: true,
                    defaulttheme: 'light',
                    binarytext: binarytext,
                    strings: strings,
                    // Read-only views (tabs above the tree).
                    treeviews: [
                        {title: tests, build: framework => buildUsersView(framework, strings, 'test')},
                        {title: modelsolution, build: framework => buildUsersView(framework, strings, 'modelsolution')},
                    ],
                };
                const state = this.state;
                const framework = new Framework();
                this.framework = framework;
                framework.buildFramework(this.container);
                const syncer = new TaskeditorSyncer(options, () => this.show());
                framework.init(this.container, syncer, false, options);
                return framework.ready.then(() => {
                    this._restoreState(framework, state);
                    return framework;
                });
            })
            .catch(Notification.exception);
    }

    /**
     * Writes the content of all open editors back into the file boxes.
     */
    save() {
        if (this.framework) {
            this.framework.save();
        }
    }

    /**
     * Saves the open editors and removes the explorer.
     */
    destroy() {
        if (this.framework) {
            this.state = this._saveState(this.framework);
            this.framework.save();
            this.framework.destroy();
            this.framework = undefined;
        }
        this.container.innerHTML = '';
    }

    /**
     * Returns the display state: expanded folders, open editors and scroll position of the tree.
     *
     * @param {Framework} framework explorer
     * @returns {Object}
     */
    _saveState(framework) {
        let expanded = [];
        const collect = folder => {
            if (folder.element && folder.isExpanded()) {
                expanded.push(folder.getPath());
            }
            folder.folders.forEach(collect);
        };
        if (framework.roots.length > 0) {
            framework.roots[0].folders.forEach(collect);
        }
        const tree = this.container.querySelector('.explorer');
        return {
            expanded: expanded,
            // The last editor is the active one.
            editors: framework.editorstack.nodes.map(item => item.fileNode.getPath()),
            scrollTop: tree ? tree.scrollTop : 0,
        };
    }

    /**
     * Restores the display state after the explorer has been rebuilt.
     *
     * @param {Framework} framework explorer
     * @param {Object} state display state (see _saveState)
     */
    _restoreState(framework, state) {
        if (!state || framework.roots.length === 0) {
            return;
        }
        const expanded = new Set(state.expanded);
        const restore = folder => {
            folder.expand(expanded.has(folder.getPath()));
            folder.folders.forEach(restore);
        };
        framework.roots[0].folders.forEach(restore);
        state.editors.forEach(path => {
            // Files may have been renamed or deleted in the meantime.
            const node = framework.findNodeByPath(path);
            if (node instanceof FileNode) {
                framework.switchEditorTo(node);
            }
        });
        const tree = this.container.querySelector('.explorer');
        if (tree) {
            tree.scrollTop = state.scrollTop;
        }
    }

    _getStrings() {
        if (this.strings) {
            return Promise.resolve(this.strings);
        }
        return Str.get_strings([
            {key: 'taskeditorfiles', component: 'qtype_proforma'},
            {key: 'taskeditorbinaryfile', component: 'qtype_proforma'},
            {key: 'modelsolution', component: 'qtype_proforma'},
            {key: 'taskeditortests', component: 'qtype_proforma'},
        ])
            .then(strings => {
                this.strings = strings;
                return strings;
            });
    }
}

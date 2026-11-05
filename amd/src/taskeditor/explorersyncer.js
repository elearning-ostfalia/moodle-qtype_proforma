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
import {Framework, FileNode} from '../FileViewer';
import {FileWrapper} from './file';
import {getExtension, readAndCreateFileData} from './helper';

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
    }

    /**
     * (Re)builds the explorer from the current files of the task editor.
     *
     * @returns {Promise}
     */
    show() {
        return this._getStrings()
            .then(([rootname, binarytext]) => {
                this.destroy();
                const options = {
                    rootnode: rootname,
                    allowfolders: true,
                    binarytext: binarytext,
                };
                this.framework = new Framework();
                this.framework.buildFramework(this.container);
                const syncer = new TaskeditorSyncer(options, () => this.show());
                this.framework.init(this.container, syncer, false, options);
                return this.framework;
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
            this.framework.save();
            this.framework.destroy();
            this.framework = undefined;
        }
        this.container.innerHTML = '';
    }

    _getStrings() {
        if (this.strings) {
            return Promise.resolve(this.strings);
        }
        return Str.get_strings([
            {key: 'taskeditorfiles', component: 'qtype_proforma'},
            {key: 'taskeditorbinaryfile', component: 'qtype_proforma'},
        ])
            .then(strings => {
                this.strings = strings;
                return strings;
            });
    }
}

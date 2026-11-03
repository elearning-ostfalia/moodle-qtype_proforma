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

/* eslint no-console: ["error", {"allow": ["error"]}] */


/* eslint-disable no-unused-vars */


import {debugLog} from './debug';
import Config from 'core/config';
import Notification from 'core/notification';
import {FileNode} from "./FileViewer";

/* Syncer base class */
export class Syncer {
    static splitFullname(path) {
        const index = path.lastIndexOf('/', path.length - 1);
        if (index < 0) {
            return ['/', path];
        }
        let pathname = path.substring(0, index + 1);
        if (pathname.length > 1) {
            // Strip trailing /
            // if (path[pathname.length-1] == '/') {
            //     pathname = path.substring(0, index);
            // }
        }
        return [pathname, path.substring(index + 1)];
        // Return [path.substring(0, index + 1), path.substring(index + 1)];
    }
    constructor(options) {
        if (new.target === Syncer) {
            throw new TypeError("Cannot construct Syncer instances directly");
        }
        this.options = options;
    }
    deleteFileOrFolder(path) {
        return Promise.resolve('fake implentation');
    }
    download(path) {
        return Promise.resolve('fake implentation');
    }
    renameFile(pathold, pathnew) {
        return Promise.resolve('fake implentation');
    }
    renameFolder(pathold, pathnew) {
        return Promise.resolve('fake implentation');
    }
    mkdir(path) {
        return Promise.resolve('fake implentation');
    }
    list(framework) {
        return Promise.resolve('fake implentation');
    }
    update(filename, text) {
        return Promise.resolve('fake implentation');
    }
    newfile(filename) {
        return Promise.resolve('fake implentation');
    }
    upload(filename, file, overwrite) {
        return Promise.resolve('fake implentation');
    }
}


export class MoodleQuestionAttemptSyncer extends Syncer {
    constructor(options) {
        super(options);
    }
    list(framework) {
        let firstFile;
        this.options.files.forEach(path => {
            let values = Syncer.splitFullname(path);
            let node = new FileNode(values[1]);
            let folder = framework.createPath(values[0]);
            folder.appendFile(node);
            if (firstFile === undefined) {
                firstFile = node;
                // Framework.addEditor(node);
                framework.editorstack.switchEditorTo(node);
            }
        });
        // Dummy
        return Promise.resolve();
    }
    download(path) {
        const addon = '/question/response_attachments/' +
            this.options.usageid + '/' +
            this.options.slot + '/' +
            this.options.itemid + path;
        const url = Config.wwwroot + '/pluginfile.php/' + this.options.contextid + addon;
        const promise = fetch(url, {method: 'GET'})
            .then(response => {
                if (response.status != 200) {
                    throw new Error(response.statusText);
                }
                return response;
            })
            .then(response => response.text())
            .catch(error => {
                console.error('error:', error);
                Notification.exception(error);
            });
        return promise;
    }
}

/* Class for synchronizing explorer with draft area */
export class MoodleSyncer extends Syncer {
    constructor(options) {
        super(options);
        // This.options = options;
    }
    _sendRequest(action, options = undefined) {
        const url = Config.wwwroot + '/repository/draftfiles_ajax.php';
        let params = {};
        params.sesskey = Config.sesskey;
        // Parameter name is defined by Moodle.
        // eslint-disable-next-line camelcase
        params.client_id = this.options.client_id;
        params.itemid = this.options.itemid;
        if (options !== undefined) {
            params = Object.assign(params, options);
        }
        debugLog(params);
        // Const promise =
        return fetch(
            url + '?action=' + action + '&' + window.build_querystring(params),
            {
                method: 'POST',
            }
        )
            .then(response => response.json())
            .then(json => {
                if (json.error) {
                    console.error(action + ' error:', json.error);
                    throw new Error(json.error);
                    //                    Alert(json.error);
                }
                debugLog(json);
                // Const text = JSON.stringify(json);
                // if (callback != undefined) {
                //    callback(json);
                // }
                return json;
            })
            .catch(error => {
                console.error('error on ' + action + ':', error);
                Notification.exception(error);
            });

        // Return promise;
    }

    deleteFileOrFolder(path) {
        debugLog('delete ' + path);
        let params = {};
        let values = MoodleSyncer.splitFullname(path);
        params.filepath = values[0];
        params.filename = values[1];
        debugLog('delete ' + params.filepath + ' ' + params.filename);
        return this._sendRequest('delete', params);
    }
    download(path) {
        const contextid = this.options.contextid;
        const addon = '/user/draft/' + this.options.itemid + path;
        const url = Config.wwwroot + '/draftfile.php/' + contextid + addon;
        debugLog(url);

        const promise = fetch(url, {method: 'GET'})
            .then(response => response.text())
            .catch(error => {
                    console.error('error:', error);
                    Notification.exception(error);
                });
        return promise;

        // Download many files as zip archive
        /*
        let params = {};
        let values = MoodleSyncer.splitFullname(path);
        let selected = new Object();
        selected.filepath = values[0];
        selected.filename = values[1];
        let selectedarray = [];
        selectedarray.push(selected);
        params['selected'] = JSON.stringify(selectedarray);
        this._sendRequest('downloadselected', jsonResult => {
            console.log(jsonResult);
            callback(jsonResult);
        }, params); */
    }
    renameFile(pathold, pathnew) {
        debugLog('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params.filepath = values[0];
        params.filename = values[1];
        params.newfilepath = newValue[0];
        params.newfilename = newValue[1];
        return this._sendRequest('updatefile', params);
    }
    renameFolder(pathold, pathnew) {
        debugLog('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        // Let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params.filepath = pathold + '/'; // Values[0];
        params.newdirname = newValue[1];
        params.newfilepath = newValue[0].substr(0, newValue[0].length - 1); // Strip trailing /
        return this._sendRequest('updatedir', params);
    }
    mkdir(path) {
        debugLog('mkdir ' + path);
        const index = path.lastIndexOf('/', path.length - 1);
        let params = {};
        if (index < 0) {
            params.filepath = '/';
            params.newdirname = path;
        } else {
            params.filepath = path.substring(0, index + 1);
            /* If (params['filepath'].substring(0, 1) != '/') {
                params['filepath'] = '/' + params['filepath'];
            } */
            params.newdirname = path.substring(index + 1);
        }
        debugLog('path = ' + params.filepath);
        debugLog('dir = ' + params.newdirname);
        debugLog(params);
        return this._sendRequest('mkdir', params);
    }
    /*    Dir() {
        let params = {};
        params['filepath'] = '/';
        this._sendRequest('dir', jsonResult => {
            console.log('dir fertig');
            console.log(jsonResult);
        }, params);
    } */
    list(framework) {
        debugLog('Start list');
        /**
         * Removes a trailing slash from the path (except for the root path).
         *
         * @param {string} path
         * @returns {string} path without trailing slash
         */
        function stripSlashes(path) {
            if (path.length > 1 && path.substring(path.length - 1) === '/') { // Strip '/'
                path = path.substring(0, path.length - 1);
            }
            return path;
        }
        const listfolder = (path) => new Promise((resolve, reject) => {
            let params = {};
            params.filepath = path;
            this._sendRequest('list', params)
                .then(json => {
                    let firstFile;
                    json.list.forEach(item => {
                        if (item.filename === '.') {
                            // Create Folder.
                            let path = stripSlashes(item.filepath);
                            debugLog('Syncer: create folder ' + path);
                            framework.createPath(path);
                            resolve(listfolder(path));
                        } else {
                            debugLog('Syncer: create file ' + item.filename);
                            let folder = framework.createPath(stripSlashes(item.filepath));
                            let filenode = new FileNode(item.filename);
                            folder.appendFile(filenode);
                            if (firstFile === undefined) {
                                firstFile = filenode;
                                // Open first file in editor.
                                framework.editorstack.switchEditorTo(filenode);
                                // Framework.addEditor(filenode);
                            }
                        }
                    });
                    resolve();
                    return json;
                })
                .catch(reject);
        });
        return listfolder('/');
    }
    update(filename, text, async = false) {
        debugLog('update file ' + filename);
        // Const tmp_filename = "/file" + Math.random().toString(16).slice(2) + '.txt';
        const file = new File([text], filename, {
            type: "text/plain"
        });
        return this.upload(filename, file, true, async);
    }
    newfile(filename) {
        debugLog('create new empty file ' + filename);
        let values = Syncer.splitFullname(filename);
        const file = new File([' '], values[1], {
            type: "text/plain"
        });
        return this.upload(filename, file);
    }
    upload(filename, file, overwrite = false, async = true) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';
        debugLog('upload ' + file.name + ' as ' + filename);
        // Alert('upload/overrite: ' + filename);

        // let values = MoodleSyncer.splitFullname(filename);

        let formData = new FormData();
        formData.append('sesskey', Config.sesskey);
        formData.append('repo_upload_file', file);
        formData.append('filepath', '/');
        formData.append('client_id', this.options.client_id);
        formData.append('title', file.name);
        formData.append('overwrite', overwrite);
        formData.append('maxbytes', this.options.maxbytes);
        // Since we are uploading the file to the 'draft area',
        // there is no point in limiting the size of the file area.
        // The draft area is used for all users.
        // formData.append('areamaxbytes', this.options['areamaxbytes']);
        formData.append('savepath', '/');
        formData.append('repo_id', this.options.repo_id);
        formData.append('itemid', this.options.itemid);
        if (async) {
            const promise = fetch(
                url + '?action=' + action, //  + '&' + window.build_querystring(params),
                {
                    method: 'POST',
                    body: formData // File
                }
            )
                .then(response => response.json())
                .then(json => {
                    if (json.error) {
                        throw new Error(json.error);
                    }
                    debugLog(json);
                    let originalFilename = file.name;
                    if (originalFilename.substr(0, 1) != '/') {
                        originalFilename = '/' + originalFilename;
                    }
                    if (originalFilename != filename) {
                        return this.renameFile(originalFilename, filename);
                    }
                    return json;
                })
                .catch(error => {
                    console.error('upload error:', error);
                    Notification.exception(error);
                });
            return promise;
        } else {
            // Synchronous
            // In Chrome this is declared as dismissal.
            debugLog('SYNCHRONOUSE UPDATE!');
            let request = new XMLHttpRequest();
            request.open('POST', url + '?action=' + action, false);
            try {
                request.send(formData);
            } catch (e1) {
                // Chrome does not support sync. posts.
                // So we try asynchronous post
                console.error(e1);
                debugLog('sync post failed => try async');
                try {
                    request.open('POST', url + '?action=' + action, true);
                    request.send(formData);
                } catch (e2) {
                    console.error(e2);
                    return Promise.resolve();
                }
            }
            debugLog('sync/async post succeeded');
            const jsonResponse = JSON.parse(request.responseText);
            debugLog(jsonResponse);
            if (jsonResponse.error !== undefined) {
                Notification.alert('', jsonResponse.error);
            }
            return Promise.resolve(jsonResponse);
        }
    }
}


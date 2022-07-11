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

/* eslint-disable max-len */
/* eslint-disable no-unused-vars */


import Config from 'core/config';
import { FileNode } from "./FileViewer";

/* Syncer base class */
export class Syncer {
    static splitFullname(path) {
        const index = path.lastIndexOf('/', path.length-1);
        if (index < 0) {
            return ['/', path];
        }
        let pathname = path.substring(0, index+1);
        if (pathname.length > 1) {
            // Strip trailing /
            // if (path[pathname.length-1] == '/') {
            //     pathname = path.substring(0, index);
            // }
        }
        return [pathname, path.substring(index + 1)];
        // return [path.substring(0, index + 1), path.substring(index + 1)];
    }
    constructor(options) {
        if (new.target === Syncer) {
            throw new TypeError("Cannot construct Syncer instances directly");
        }
        this.options = options;
        console.log(this.options);
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
//        console.log('Start listing question attempt files');
//        console.log(this.options['files']);
        this.options['files'].forEach(path => {
            let values = Syncer.splitFullname(path);
            let folder = framework.createPath(values[0]);
            folder.appendFile(new FileNode(values[1]));
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
//        console.log(url);
        const promise = fetch(url, { method: 'GET' })
            .then( response => response.text() )
            .catch( error => {
                console.error('error:', error);
                alert(error);
            });
        return promise;
    }
}

/* Class for synchronizing explorer with draft area */
export class MoodleSyncer extends Syncer {
    constructor(options) {
        super(options);
        // this.options = options;
        // console.log(this.options);
    }
    _sendRequest(action, options = undefined) {
        const url = Config.wwwroot + '/repository/draftfiles_ajax.php';
        let params = {};
        params['sesskey'] = Config.sesskey;
        params['client_id'] = this.options['client_id'];
        params['itemid'] = this.options['itemid'];
        if (options !== undefined) {
            params = Object.assign(params, options);
        }
        console.log('action ' + action);
        console.log(params);
        // const promise =
        return fetch(
            url + '?action=' + action + '&' + window.build_querystring(params),
            {
                method: 'POST',
            }
        )
            // .then( response => console.log(response))
            .then( response => response.json() )
            .then( json => {
                console.log('got response for requested action ' + action);
                if (json.error) {
                    console.error(action + ' error:', json.error);
                    throw new Error(json.error);
//                    alert(json.error);
                }
                console.log(json);
                // const text = JSON.stringify(json);
                // console.log(text);
                // if (callback != undefined) {
                //    callback(json);
                // }
                return json;
            })
            .catch( error => {
                console.error('error on ' + action + ':', error);
                alert(error);
            } );

        // return promise;
    }

    deleteFileOrFolder(path) {
        console.log('delete ' + path);
        let params = {};
        let values = MoodleSyncer.splitFullname(path);
        params['filepath'] = values[0];
        params['filename'] = values[1];
        console.log('delete ' + params['filepath'] + ' ' + params['filename']);
        return this._sendRequest('delete', params);
    }
    download(path) {
        console.log('DOWNLOAD');
        console.log(this.options);
        const contextid = this.options.contextid;
        const addon = '/user/draft/' + this.options.itemid + path;
        const url = Config.wwwroot + '/draftfile.php/' + contextid + addon;
        console.log(url);

        const promise = fetch(url, { method: 'GET' })
                .then( response => response.text())
                .catch( error => {
                    console.error('error:', error);
                    alert(error);
                });
        return promise;

      // download many files as zip archive
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
        console.log('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params['filepath'] = values[0];
        params['filename'] = values[1];
        params['newfilepath'] = newValue[0];
        params['newfilename'] = newValue[1];
        return this._sendRequest('updatefile', params);
    }
    renameFolder(pathold, pathnew) {
        console.log('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        // let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params['filepath'] = pathold + '/'; // values[0];
        params['newdirname'] = newValue[1];
        params['newfilepath'] = newValue[0].substr(0, newValue[0].length-1); // strip trailing /
        return this._sendRequest('updatedir', params);
    }
    mkdir(path) {
        console.log('mkdir ' + path);
        const index = path.lastIndexOf('/', path.length-1);
        let params = {};
        if (index < 0) {
            params['filepath'] = '/';
            params['newdirname'] = path;
        } else {
            params['filepath'] = path.substring(0, index + 1);
            /* if (params['filepath'].substring(0, 1) != '/') {
                params['filepath'] = '/' + params['filepath'];
            } */
            params['newdirname'] = path.substring(index + 1);
        }
        console.log('path = ' + params['filepath']);
        console.log('dir = ' + params['newdirname']);
        console.log(params);
        return this._sendRequest('mkdir', params);
    }
/*    dir() {
        let params = {};
        params['filepath'] = '/';
        this._sendRequest('dir', jsonResult => {
            console.log('dir fertig');
            console.log(jsonResult);
        }, params);
    } */
    list(framework) {
        console.log('Start list');
        function stripSlashes(path) {
            if (path.length > 1 && path.substring(path.length-1) === '/') { // Strip '/'
                path = path.substring(0, path.length-1);
            }
            return path;
        }
        const listfolder = (path) => new Promise((resolve, reject) => {
            let params = {};
            params['filepath'] = path;
            this._sendRequest('list', params)
                .then (json => {
                    let firstFile = undefined;
                    json.list.forEach(item => {
                        // console.log('syncer List Response');
                        if (item.filename === '.') {
                            // Create Folder.
                            let path = stripSlashes(item.filepath);
                            console.log('Syncer: create folder ' + path);
                            framework.createPath(path);
                            // console.log('** RECURSION FOR ' + path + ' => request');
                            resolve(listfolder(path));
                        } else {
                            console.log('Syncer: create file ' + item.filename);
                            let folder = framework.createPath(stripSlashes(item.filepath));
                            let filenode = new FileNode(item.filename);
                            folder.appendFile(filenode);
                            if (firstFile === undefined) {
                                firstFile = filenode;
                                // Open first file in editor.
                                framework.addEditor(filenode);
                            }
                        }
                    });
                    resolve();
                });
        });
        return listfolder('/');
    }
    update(filename, text, async = false) {
        console.log('update file ' + filename);
        // const tmp_filename = "/file" + Math.random().toString(16).slice(2) + '.txt';
        // console.log('create tmp file ' + tmp_filename);
        const file = new File([text], filename, {
            type: "text/plain"
        });
        return this.upload(filename, file, true, async);
    }
    newfile(filename) {
        console.log('create new empty file ' + filename);
        let values = Syncer.splitFullname(filename);
        const file = new File([' '], values[1], {
            type: "text/plain"
        });
        return this.upload(filename, file);
    }
    upload(filename, file, overwrite = false, async = true) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';
        console.log('upload ' + file.name + ' as ' + filename);

        // let values = MoodleSyncer.splitFullname(filename);
        // console.log(values[0]);

        let formData = new FormData();
        formData.append('sesskey', Config.sesskey);
        formData.append('repo_upload_file', file);
        formData.append('filepath', '/');
        formData.append('client_id', this.options['client_id']);
        formData.append('title', file.name);
        formData.append('overwrite', overwrite);
        formData.append('savepath', '/');
        formData.append('repo_id', this.options['repo_id']);
        formData.append('itemid', this.options['itemid']);
        // console.log(formData);
        if (async) {
            const promise = fetch(
                url + '?action=' + action, //  + '&' + window.build_querystring(params),
                {
                    method: 'POST',
                    body: formData // file
                }
            )
                .then( response => response.json() )
                .then( json => {
                    // console.log(action);
                    if (json.error) {
                        throw new Error(json.error);
                    }
                    console.log(json);
                    let originalFilename = file.name;
                    if (originalFilename.substr(0,1) != '/') {
                        originalFilename = '/' + originalFilename;
                    }
                    if (originalFilename != filename) {
                        return this.renameFile(originalFilename, filename);
                    }
                    return json;
                })
                .catch( error => {
                    console.error('upload error:', error);
                    alert(error);
                });
            return promise;
        } else {
            // synchronous
            console.log('SYNCHRONOUSE UPDATE');
            let request = new XMLHttpRequest();
            request.open('POST', url + '?action=' + action, false);
            request.send(formData);
            if (request.status === 200) {
                console.log(request.responseText);
            }
        }

    }
}


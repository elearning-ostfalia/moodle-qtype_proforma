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
import { FileNode  } from "./FileViewer";

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
        this.options = options;
        console.log(this.options);
    }
    delete(path, callback) {
        callback();
    }
    download(path, callback) {
        console.log('DOWNLOAD');
        console.log(this.options);
        // let pathsplit = MoodleSyncer.splitFullname(path);
        const contextid = this.options.contextid;
        const addon = '/user/draft/' + this.options.itemid + path; // '/' + pathsplit[1];
        const url = Config.wwwroot + '/draftfile.php/' + contextid + addon;
        console.log(url);
        fetch(url, { method: 'GET' })
            .then( response => response.text() )
            .then( text => {
                console.log('download draftfile');
                callback(text);
            })
            .catch( error => {
                console.error('error:', error);
                alert(error);
            });

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
    renameFile(pathold, pathnew) {}
    renameFolder(pathold, pathnew) {}
    mkdir(path) {}
    list(callback, framework) {
        callback(framework);
    }
    update(filename, text) {}
    newfile(filename) {}
    upload(filename, file) {}
}

export class MoodleQuestionAttemptSyncer extends Syncer {
    constructor(options) {
        super(options);
    }
    list(callback, framework) {
//        console.log('Start listing question attempt files');
//        console.log(this.options['files']);
        this.options['files'].forEach(path => {
            let values = Syncer.splitFullname(path);
            let folder = framework.createPath(values[0]);
            folder.appendFile(new FileNode(values[1]));
        });
        callback();
    }
    download(path, callback) {
        const addon = '/question/response_attachments/' +
            this.options.usageid + '/' +
            this.options.slot + '/' +
            this.options.itemid + path;
        const url = Config.wwwroot + '/pluginfile.php/' + this.options.contextid + addon;
//        console.log(url);
        fetch(url, { method: 'GET' })
            .then( response => response.text() )
            .then( text => {
                console.log('download responsefile');
                callback(text);
            })
            .catch( error => {
                console.error('error:', error);
                alert(error);
            });
    }
}

/* Class for synchronizing explorer with draft area */
export class MoodleSyncer extends Syncer {
    constructor(options) {
        super(options);
        // this.options = options;
        // console.log(this.options);
    }
    _sendRequest(action, callback, options = undefined) {
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
        fetch(
            url + '?action=' + action + '&' + window.build_querystring(params),
            {
                method: 'POST',
            }
        )
            // .then( response => console.log(response))
            .then( response => response.json() )
            .then( json => {
                console.log('got response for action ' + action);
                if (json.error) {
                    console.error('error:', json.error);
                    alert(json.error);
                } else {
                    console.log(json);
                    // const text = JSON.stringify(json);
                    // console.log(text);
                    if (callback != undefined) {
                        callback(json);
                    }
                }
            })
            .catch( error => console.error('error:', error) );
    }

    delete(path, callback) {
        console.log('delete ' + path);
        let params = {};
        let values = MoodleSyncer.splitFullname(path);
        params['filepath'] = values[0];
        params['filename'] = values[1];
        console.log('delete ' + params['filepath'] + ' ' + params['filename']);
        this._sendRequest('delete', jsonResult => {
            console.log(jsonResult);
            if (callback !== undefined) {
                callback(jsonResult);
            }
        }, params);
    }
    download(path, callback) {
        console.log('DOWNLOAD');
        console.log(this.options);
        // let pathsplit = MoodleSyncer.splitFullname(path);
        const contextid = this.options.contextid;
        const addon = '/user/draft/' + this.options.itemid + path; // '/' + pathsplit[1];
        const url = Config.wwwroot + '/draftfile.php/' + contextid + addon;
        console.log(url);
        fetch(url, { method: 'GET' })
            .then( response => response.text() )
            .then( text => {
                console.log('download draftfile');
                callback(text);
            })
            .catch( error => {
                console.error('error:', error);
                alert(error);
            });

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
        this._sendRequest('updatefile', jsonResult => {
            console.log(jsonResult);
        }, params);
    }
    renameFolder(pathold, pathnew) {
        console.log('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        // let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params['filepath'] = pathold + '/'; // values[0];
        params['newdirname'] = newValue[1];
        params['newfilepath'] = newValue[0].substr(0, newValue[0].length-1); // strip trailing /
        this._sendRequest('updatedir', jsonResult => {
            console.log(jsonResult);
        }, params);
    }
    mkdir(path) {
        console.log(path);
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
        this._sendRequest('mkdir', undefined, params);
    }
/*    dir() {
        let params = {};
        params['filepath'] = '/';
        this._sendRequest('dir', jsonResult => {
            console.log('dir fertig');
            console.log(jsonResult);
        }, params);
    } */
    list(callback, framework) {
        // this.dir();
        console.log('Start list');
        let params = {};
        params['source'] = '1';
        // Counter for counting active list requests.
        // Needed to detect finishing the last one in order
        // to display resulting tree.
        let listcounter = 0;
        function stripSlashes(path) {
            /*if (path.substring(0,1) == '/') { // Strip '/'
                path = path.substring(1);
            } */
            if (path.length > 1 && path.substring(path.length-1) == '/') { // Strip '/'
                path = path.substring(0, path.length-1);
            }
            return path;
        }
        this.handleListResponse = json => {
            json.list.forEach(item => {
                console.log('syncer List Response');
                if (item.filename == '.') {
                    // Create Folder.
                    let path = stripSlashes(item.filepath);
                    // console.log('Syncer: create folder ' + path);
                    framework.createPath(path);
                    let params = {};
                    params['filepath'] = path;
                    // console.log('RECURSION FOR ' + path);
                    listcounter++;
                    this._sendRequest('list', jsonResultSub => {
                            this.handleListResponse(jsonResultSub);
                    }, params);
                } else {
                    // console.log('Syncer: create file ' + item.filename);
                    let folder = framework.createPath(stripSlashes(item.filepath));
                    folder.appendFile(new FileNode(item.filename));
                }
            });
            listcounter--;
            if (listcounter == 0) {
                callback();
            }
        };
        listcounter++;
        this._sendRequest('list', jsonResult => {
            // Toplevel folders?
            /* jsonResult.path.forEach(item => {
                console.log('syncer List path');
                console.log(item);
                let path = item.path + item.name;
                path = stripSlashes(path);
                console.log(path);
                framework.createPath(path);
            }); */
            // Files and folders
            this.handleListResponse(jsonResult);
        }, params);
    }
    update(filename, text) {
        this.delete(filename, () => {
            console.log(filename + ' is deleted, upload new version');
            // upload when file is deleted (otherwise nameclash)
            let values = MoodleSyncer.splitFullname(filename);
            const file = new File([text], values[1], {
                type: "text/plain"
            });
            console.log('upload as new file with name ' + file.name);
            this.upload(filename, file);
        });
    }
    newfile(filename) {
        let values = MoodleSyncer.splitFullname(filename);
        const file = new File([''], values[1], {
            type: "text/plain"
        });
        this.upload(file, filename);
    }
    upload(filename, file) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';
        console.log('upload ' + file.name + ' as ' + filename);

        let values = MoodleSyncer.splitFullname(filename);
        console.log(values[0]);

        let formData = new FormData();
        formData.append('sesskey', Config.sesskey);
        formData.append('repo_upload_file', file);
        formData.append('filepath', '/');
        formData.append('client_id', this.options['client_id']);
        formData.append('title', file.name);
        formData.append('savepath', '/');
        formData.append('repo_id', this.options['repo_id']);
        formData.append('itemid', this.options['itemid']);
        console.log(formData);
        fetch(
            url + '?action=' + action, //  + '&' + window.build_querystring(params),
            {
                method: 'POST',
                body: formData // file
            }
        )
            .then( response => response.json() )
            .then( json => {
                console.log(action);
                if (json.error) {
                    console.error('error:', json.error);
                    alert(json.error);
                } else {
                    console.log(json);
                    this.renameFile('/' + file.name, filename);
                }
            })
            .catch( error => {
                console.error('error:', error);
                alert(error);
            } );
    }
}


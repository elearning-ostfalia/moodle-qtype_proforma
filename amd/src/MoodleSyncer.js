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

import Config from 'core/config';
import { FileNode  } from "./FileViewer";

export class MoodleSyncer {
    // Fake response
    static response = '{"path":[{"name":"Dateien","path":"/","icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-24"}],"itemid":0,"list":[{"filename":".","filepath":"/Dateien/","fullname":"Dateien","size":null,"filesize":0,"sortorder":"0","author":null,"license":null,"datemodified":1648799808,"datecreated":1648799808,"isref":false,"icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-24","type":"folder","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-64","datemodified_f":"1. April 2022, 09:56","datemodified_f_s":"1.04.2022 09:56","datecreated_f":"1. April 2022, 09:56","datecreated_f_s":"1.04.2022 09:56"},{"filename":".","filepath":"/Dateienverz/","fullname":"Dateienverz","size":null,"filesize":0,"sortorder":"0","author":null,"license":null,"datemodified":1648800382,"datecreated":1648800382,"isref":false,"icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-24","type":"folder","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-64","datemodified_f":"1. April 2022, 10:06","datemodified_f_s":"1.04.2022 10:06","datecreated_f":"1. April 2022, 10:06","datecreated_f_s":"1.04.2022 10:06"},{"filename":".","filepath":"/hhh/","fullname":"hhh","size":null,"filesize":0,"sortorder":"0","author":null,"license":null,"datemodified":1648808962,"datecreated":1648808962,"isref":false,"icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-24","type":"folder","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-64","datemodified_f":"1. April 2022, 12:29","datemodified_f_s":"1.04.2022 12:29","datecreated_f":"1. April 2022, 12:29","datecreated_f_s":"1.04.2022 12:29"},{"filename":".","filepath":"/kkk/","fullname":"kkk","size":null,"filesize":0,"sortorder":"0","author":null,"license":null,"datemodified":1648809005,"datecreated":1648809005,"isref":false,"icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-24","type":"folder","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/folder-64","datemodified_f":"1. April 2022, 12:30","datemodified_f_s":"1.04.2022 12:30","datecreated_f":"1. April 2022, 12:30","datecreated_f_s":"1.04.2022 12:30"},{"filename":"MyString.java","filepath":"/","fullname":"MyString.java","size":276,"filesize":"276 Bytes","sortorder":"0","author":null,"license":"allrightsreserved","datemodified":1648798737,"datecreated":1648798737,"isref":false,"mimetype":"Unformatierte Textdatei","type":"file","url":"http://10.235.1.41/moodle/draftfile.php/5/user/draft/0/MyString.java","icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/sourcecode-24","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/sourcecode-80","status":0,"size_f":"276 Bytes","license_f":"Alle Rechte vorbehalten","datemodified_f":"1. April 2022, 09:38","datemodified_f_s":"1.04.2022 09:38","datecreated_f":"1. April 2022, 09:38","datecreated_f_s":"1.04.2022 09:38"},{"filename":"palindrome.c","filepath":"/","fullname":"palindrome.c","size":1121,"filesize":"1.1KB","sortorder":"0","author":null,"license":"allrightsreserved","datemodified":1648798748,"datecreated":1648798748,"isref":false,"mimetype":"Unformatierte Textdatei","type":"file","url":"http://10.235.1.41/moodle/draftfile.php/5/user/draft/0/palindrome.c","icon":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/sourcecode-24","thumbnail":"http://10.235.1.41/moodle/theme/image.php/boost_campus/core/1648452664/f/sourcecode-80","status":0,"size_f":"1.1KB","license_f":"Alle Rechte vorbehalten","datemodified_f":"1. April 2022, 09:39","datemodified_f_s":"1.04.2022 09:39","datecreated_f":"1. April 2022, 09:39","datecreated_f_s":"1.04.2022 09:39"}],"filecount":2,"filesize":1397,"tree":{"children":[{"sortorder":"0","filepath":"/Dateien/","fullname":"Dateien","id":"624725f53d40e","children":[{"sortorder":"0","filepath":"/Dateien/ggg/","fullname":"ggg","id":"624725f53da94","children":[]},{"sortorder":"0","filepath":"/Dateien/kkk/","fullname":"kkk","id":"624725f53e0d8","children":[]},{"sortorder":"0","filepath":"/Dateien/Kverz/","fullname":"Kverz","id":"624725f53e6de","children":[]},{"sortorder":"0","filepath":"/Dateien/verz/","fullname":"verz","id":"624725f53ece9","children":[]},{"sortorder":"0","filepath":"/Dateien/verzeichnis/","fullname":"verzeichnis","id":"624725f53f30d","children":[]},{"sortorder":"0","filepath":"/Dateien/xxx/","fullname":"xxx","id":"624725f53f97c","children":[]}]},{"sortorder":"0","filepath":"/Dateienverz/","fullname":"Dateienverz","id":"624725f53ffa7","children":[]},{"sortorder":"0","filepath":"/hhh/","fullname":"hhh","id":"624725f5405cc","children":[]},{"sortorder":"0","filepath":"/kkk/","fullname":"kkk","id":"624725f540bbd","children":[]}]}}';
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
                console.log(action);
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

    delete(path) {
        console.log('delete ' + path);
        let params = {};
        let values = MoodleSyncer.splitFullname(path);
        params['filepath'] = values[0];
        params['filename'] = values[1];
        console.log('delete ' + params['filepath'] + ' ' + params['filename']);
        this._sendRequest('delete', jsonResult => {
            console.log(jsonResult);
        }, params);
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
        // TODO:
        console.log('rename ' + pathold + ' => ' + pathnew);
        let params = {};
        let values = MoodleSyncer.splitFullname(pathold);
        let newValue = MoodleSyncer.splitFullname(pathnew);
        params['filepath'] = values[0];
        // params['filename'] = values[1];
        params['newdirname'] = newValue[0];
        params['newfilepath'] = newValue[1];
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
    dir() {
        let params = {};
        params['filepath'] = '/';
        this._sendRequest('dir', jsonResult => {
            console.log('dir fertig');
            console.log(jsonResult);
        }, params);
    }
    list(callback, framework) {
        function stripSlashes(path) {
            /*if (path.substring(0,1) == '/') { // Strip '/'
                path = path.substring(1);
            } */
            if (path.length > 1 && path.substring(path.length-1) == '/') { // Strip '/'
                path = path.substring(0, path.length-1);
            }
            return path;
        }
        // Fake
        // const obj = JSON.parse(MoodleSyncer.response);
        // callback(obj);
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
            this.handleListResponse = json => {
                json.list.forEach(item => {
                    console.log('syncer List list');
                    console.log(item.filename);
                    // console.log(item);
                    if (item.filename == '.') {
                        // Create Folder.
                        let path = stripSlashes(item.filepath);
                        console.log('Syncer: create folder ' + path);
                        framework.createPath(path);
                    } else {
                        console.log('Syncer: create file ' + item.filename);
                        let folder = framework.createPath(stripSlashes(item.filepath));
                        console.log(folder);
                        folder.appendFile(new FileNode(item.filename));
                    }
                });
            };
            jsonResult.list.forEach(item => {
                console.log('syncer List list');
                console.log(item.filename);
                // console.log(item);
                if (item.filename == '.') {
                    // Create Folder.
                    let path = stripSlashes(item.filepath);
                    console.log('Syncer: create folder ' + path);
                    framework.createPath(path);
                } else {
                    console.log('Syncer: create file ' + item.filename);
                    let folder = framework.createPath(stripSlashes(item.filepath));
                    console.log(folder);
                    folder.appendFile(new FileNode(item.filename));
                }
            });

            let params = {};
            params['filepath'] = '/jj';
            this._sendRequest('list', jsonResultSub => {
                // Toplevel folders?
                /* jsonResultSub.path.forEach(item => {
                    console.log('syncer List path');
                    console.log(item);
                    let path = item.path + item.name;
                    path = stripSlashes(path);
                    console.log(path);
                    framework.createPath(path);
                }); */
                // Files and folders
                jsonResultSub.list.forEach(item => {
                    console.log('syncer List list');
                    console.log(item);
                    if (item.filename == '.') {
                        // Create Folder.
                        let path = stripSlashes(item.filepath);
                        console.log('Syncer: create folder ' + path);
                        framework.createPath(path);
                    } else {
                        console.log('Syncer: create file ' + item.filename);
                        let path = stripSlashes(item.filepath);
                        let folder = framework.createPath(path);
                        console.log(folder);
                        // let folder = this.findNodeByPath(path);
                        let file = new FileNode(item.filename);
                        folder.appendFile(file);
                    }
                    console.log(item.filename);
                });
                // callback(jsonResult);
            }, params);

            callback(jsonResult);
        });

    }
    upload(file, filename) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';
        console.log(file.name);
        console.log(filename);

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

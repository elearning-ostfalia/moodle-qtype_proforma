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


// import Config from 'core/config';
// import { FileNode } from "./FileViewer";

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




export class FakeSyncer extends Syncer {
    constructor(options) {
        super(options);
        // this.options = options;
        // console.log(this.options);
    }
}


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
 * functions for uploading a task.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

import {TestFileReference, FileReferenceList} from "./filereflist";



export const test = (filereftableid, testid) => {

    function init(testid) {
        console.log(testid);
        let testcontainer = document.getElementById(testid);
        console.log(testcontainer);

        // register dragenter, dragover.
        testcontainer.ondragover = function(e) {
            e.preventDefault();
            e.stopPropagation();
            //e.dataTransfer.dropEffect = 'copy';
        };

        testcontainer.ondragenter = function(e) {
            e.preventDefault();
            e.stopPropagation();
        };
            /*
                        drop: function(e){
                            if(e.originalEvent.dataTransfer){
                                if(e.originalEvent.dataTransfer.files.length) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    //UPLOAD FILES HERE
                                    this.JsClassname.uploadFiles(e.originalEvent.dataTransfer.files, e.currentTarget);
                                }
                            }
                        } */

    }


    function init2(filereftableid, testid) {
        let testroot = document.getElementById(testid);

        TestFileReference.getInstance().createTableString('TestFileReference',
            'Testfile Label'/*config.fileRefLabel*/,
            true /*config.manadatoryFile*/, 'xml_fileref_table');
        let html = TestFileReference.getInstance().getTableString();
        console.log(html);
        let table = document.getElementById(filereftableid);
        table.innerHTML = html;

        FileReferenceList.init(null, null, TestFileReference, testroot);
    }

    init2(filereftableid, testid);
};
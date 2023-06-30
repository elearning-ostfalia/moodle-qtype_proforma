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
 * Helper functions
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm, Dr.U.Priss
 */

// Known bugs: search the code for the string "ToDo" below and check faq.html and installationFAQ.html

import $ from 'jquery';
import {FileWrapper, FileStorage, fileStorages} from "./file";
import {getExtension, setErrorMessage} from "./util";
import {javaParser} from "./java";
import {readAndDisplayXml} from "./task";
import * as Str from 'core/str';


export var readXmlActive = false;

export function isBinaryFile(file, mimetype) {
    if (file.name.toLowerCase() === 'makefile') {
        return false;
    }
    if (mimetype && mimetype.match(/(text\/)/i))  // mimetype is 'text/...'
        return false;

    const extension = file.name.split('.').pop();
    switch (extension.toLowerCase()) {
        case 'c' :
        case 'h' :
        case 'cpp' :
        case 'hpp' :
        case 'hxx' :
        case 'cxx' :
        case 'java' :
        case 'log' :
        case 'py' :
        case 'txt' :
        case 'xml' :
        case 'php' :
        case 'js' :
        case 'html' :
        case 'csv' :
            return false;
        default: break;
    }
    return true;
}


export function readAndCreateFileData(file, fileId, callback) {
    if (!file)
        return;
    let filename = file.name;

    // check if a file with filename already is stored
    if (FileWrapper.doesFilenameExist(filename)) {
        Str.get_string('fileexists', 'qtype_proforma', filename)
            .then(content => alert(content));
        return;
    }

    const size = file.size; //get file size
    const mimetype = getMimeType(file.type, filename); //get mime type
    // determine if we have a binary or non-binary file
    let isBinaryFile = isBinaryFile(file, mimetype);
    let reader = new FileReader();
    reader.onload = function (e) {
        function finishFile(ui_file) {
            // set filename
            ui_file.filename = filename;

            /*        if (size > taskeditorconfig.maxSizeForEditor) {
                        //console.log('file '+ filename + ' is too large => no editor support');
                        //isBinaryFile = true;
                    }*/

            if (isBinaryFile) {
                // binary file
                // at first update fileStorages because
                // it is needed for changing file type
                let fileObject = new FileStorage(isBinaryFile, mimetype, e.target.result, filename);
                fileObject.setSize(size);
                fileStorages[ui_file.id] = fileObject;
                ui_file.type = 'file';
            } else {
                // assume non binary file
                let fileObject = new FileStorage(isBinaryFile, mimetype, 'text is in editor', filename);
                fileStorages[ui_file.id] = fileObject;
                ui_file.text = e.target.result;
                ui_file.type = 'embedded';
            }

            if (callback)
                callback(filename, ui_file.id);
        }

        // special handling for JAVA: extract class name and package name and
        // recalc filename!
        if (getExtension(filename) === 'java') {
            const text = e.target.result;
            filename = javaParser.getFilenameWithPackage(text, filename);
        }

        // recheck if a file with that filename already is stored
        if (FileWrapper.doesFilenameExist(filename)) {
            Str.get_string('fileexists', 'qtype_proforma', filename)
                .then(content => alert(content));
            return;
        }

        if (!fileId) {
            // create new file box
            FileWrapper.createFromTemplate()
                .then(ui_file => {
                    finishFile(ui_file);
                });
        } else {
            // file box already exists
            finishFile(FileWrapper.constructFromId(fileId));
        }
    };

    //console.log("read file");
    if (isBinaryFile)
        reader.readAsArrayBuffer(file);
    else
        reader.readAsText(file);
}

function uploadFilesWhenDropped(files) {
    $.each(files, function (index, file) {
        readAndCreateFileData(file, undefined /*-1*/, function (filename) {
            // nothing extra to be done
        });
    });
}


///////////////////////////////////////////////////////// function: readXML

export function readXMLWithLock (taskXmlText) {
    readXmlActive = true; // lock automatic input field update
    try {
        return readAndDisplayXml(taskXmlText);
    } catch (err) {
        setErrorMessage("uncaught exception", err);
    }
    finally {
        readXmlActive = false;
    }
}


// disable (drag&)drop in whole application except
// for the intended drop zones
// (otherwise dropping a file in the browser leaves the editor site)

/*
    const dropzoneClass = "drop_zone";
    function noDragNDropSupport(e) {
        if (e.target.class !== dropzoneClass) {
            e.preventDefault();
            e.dataTransfer.effectAllowed = "none";
            e.dataTransfer.dropEffect = "none";
        }
    }
    window.addEventListener("dragenter", noDragNDropSupport, false);
    window.addEventListener("dragover", noDragNDropSupport);
    window.addEventListener("drop", noDragNDropSupport);

    // enable dropping files in the file section
    // with creating new file boxes
    var filesection = $("#proforma-files-section").parent();
    // use parent instead of filesection here because
    // the acual file section is too small and is not what is expected
    filesection.on({
        dragover: function (e) {
            e.preventDefault();
            e.stopPropagation();
            //e.dataTransfer.dropEffect = 'copy';
        },
        dragenter: function (e) {
            e.preventDefault();
            e.stopPropagation();
        },
        drop: function (e) {
            if (e.originalEvent.dataTransfer) {
                if (e.originalEvent.dataTransfer.files.length) {
                    e.preventDefault();
                    e.stopPropagation();
                    //UPLOAD FILES HERE
                    uploadFilesWhenDropped(e.originalEvent.dataTransfer.files, e.currentTarget);
                }
            }
        }
    });

    // add file reference for template, library instruction
    if (USE_VISIBLES)
        FileReferenceList.init("#visiblefiledropzone", '#visiblesection', VisibleFileReference);

    //FileReferenceList.init("#multimediadropzone", '#multimediasection', MultimediaFileReference);
    FileReferenceList.init("#downloaddropzone", '#downloadsection', DownloadableFileReference);

    if (!USE_VISIBLES)
        $("#visiblefiledropzone").hide();

    $("#files_restriction").append(SubmissionFileList.getInstance().getTableString());

*/

///////////////////////////////////////////////////////// end of document ready function

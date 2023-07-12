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
import {javaParser} from "./java";
import {readAndDisplayXml} from "./task";
import * as Str from 'core/str';


export const DEBUG_MODE       = false;
export const TEST_MODE        = false;
export const SUBMISSION_TEST  = false;
export const USE_VISIBLES     = false;

export var readXmlActive = false;

export const version094    = 'xsd/taskxml0.9.4.xsd';                // name of schema files
export const version101    = 'xsd/taskxml1.0.1.xsd';


export function setErrorMessage(errormess, exception) { // setting the error console
    // console.log('setErrorMessage');
    console.log(errormess);
    console.log(exception);
    window.alert(errormess);
}

export function clearErrorMessage() {

}

// without . (MyString.Java = java)
// to lowercase
export function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

// convert to mimetype that can be directely handeled by codemirror
export function getMimeType(mimetype, filename) {
    const extension = filename.split('.').pop().toLowerCase();
    switch (extension) {
        case 'h':    return 'text/x-chdr';
        case 'c':    return 'text/x-csrc';
        case 'cpp':  return 'text/x-c++src';
        case 'java': return 'text/x-java';
        case 'py':   return 'text/x-python';
        case 'stlx': return 'text/x-setlx'; // no actual mode availble
        case 'xml':  return 'application/xml';
        case 'html':  return 'text/html';
        default: return mimetype;
    }
}



let newUuid;
/**
 * generetae new UUID. Note that this function always returns the same UUID
 * whenever it is called later on.
 *
 * @returns {string|*}
 */
export function generateUUID(){
    if (newUuid !== undefined) {
        // console.log('newUuid is ' + newUuid + ' (do not change)');
        return newUuid;
    }
    let date = new Date().getTime();
    newUuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c_value) {
        let rand = (date + Math.random()*16)%16 | 0;
        date = Math.floor(date/16);
        return (c_value === 'x' ? rand : (rand&0x3|0x8)).toString(16);
    });
    console.log('newUuid is ' + newUuid);
    return newUuid;
}


//////////////////////////////////////////////////////////////////////////////
/* setcounter and deletecounter are only used for fileIDs, modelSolIDs, testIDs
 * setcounter finds the first available ID and returns it
 * setcounter should be called when a new item is created
 * deletecounter deletes an ID from the hash, to be used when deleting an item
 */
export function setcounter(temphash) {
    let tempcnter = 1;
    while (temphash.hasOwnProperty(tempcnter)) {         // if the counter is already used, take next one
        tempcnter++;
    }
    temphash[tempcnter] = 1;
    return tempcnter;
}


export function handleFilenameChangeInTest(newFilename, tempSelElem) {
    function setJavaClassname(newFilename) {
        let testBox = tempSelElem.closest(".xml_test");
        if (testBox) {
            const ui_classname = $(testBox).find(".xml_entry_point");
            if (ui_classname.length === 1 && // test has entrypoint
                ui_classname.first().val().trim() === '' && // and it is not yet set
                getExtension(newFilename) === 'java') { // and filename is java => JUnit
                    // set classname if file belongs to JUNIT and if exactly one file is assigned
                    ui_classname.first().val(javaParser.getFullClassnameFromFilename(newFilename));

                // $.each(ui_classname, function(index, element) {
                //     //let currentFilename = $(element).val();
                //     if (!readXmlActive)
                //         $(element).val(javaParser.getFullClassnameFromFilename(newFilename)).change();
                // });
            }
        }
    }

    function setResponseFilename(newFilename) {
        let msBox = tempSelElem.closest(".xml_model-solution");
        if (msBox && msBox.length > 0) {
            // console.log(msBox);
            // Filename belongs to model solution
            // => get response filename in Moodle form
            let editorfilename = document.getElementById('id_responsefilename');
            if (editorfilename) {
                if (editorfilename.value.trim() === '') {
                    // Response filename is empty => set
                    editorfilename.value = newFilename;
                }
            }
        }
    }
    /*
        function setJUnitDefaultTitle(newFilename) {
            // set description according to classname
            let testBox = $(tempSelElem).closest(".xml_test");
            const ui_title = $(testBox).find(".xml_test_title");
            if (ui_title.length === 1) {
                $.each(ui_title, function(index, element) {
                    let currentTitle = $(element).val();
                    if (!readXmlActive && currentTitle === JUnitTest.DefaultTitle)
                        $(element).val("Junit Test " + javaParser.getPureClassnameFromFilename(newFilename)).change();
                });
            }
        }
    */
    setJavaClassname(newFilename);
    setResponseFilename(newFilename);
    // setJUnitDefaultTitle(newFilename);
}



function isBinaryFile(file, mimetype) {
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
    let isBinary = isBinaryFile(file, mimetype);
    let reader = new FileReader();
    reader.onload = function (e) {
        function finishFile(ui_file) {
            // set filename
            ui_file.filename = filename;

            /*        if (size > taskeditorconfig.maxSizeForEditor) {
                        //console.log('file '+ filename + ' is too large => no editor support');
                        //isBinary = true;
                    }*/

            if (isBinary) {
                // binary file
                // at first update fileStorages because
                // it is needed for changing file type
                let fileObject = new FileStorage(isBinary, mimetype, e.target.result, filename);
                fileObject.setSize(size);
                fileStorages[ui_file.id] = fileObject;
                ui_file.type = 'file';
            } else {
                // assume non binary file
                let fileObject = new FileStorage(isBinary, mimetype, 'text is in editor', filename);
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
    if (isBinary)
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

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
 * Helper functions for zipping and unzipping task
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     // This file is part of ProFormA Question Type for Moodle
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
import {taskeditorconfig} from "./config";
import {readAndDisplayXml} from "./task";
import * as Str from 'core/str';


export var readXmlActive = false;

export const testTypes = 'EMPTY LIST'; // getTesttypeOptions();

// create option list string with all test types
function getTesttypeOptions() {
    let list = "";
    let first = true;
    $.each(taskeditorconfig.testInfos, function (index, item) {
        list = list + "<option";
        if (first) {
            list = list + " selected='selected'";
            first = false;
        }
        list = list + ">" + item.testType;
        list = list + "</option>";
    });
    return list;
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
    const mimetype = taskeditorconfig.getMimeType(file.type, filename); //get mime type
    // determine if we have a binary or non-binary file
    let isBinaryFile = taskeditorconfig.isBinaryFile(file, mimetype);
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
        // show/hide buttons according to new programming language
        // TODO:
        // switchProgLang();

    } catch (err) {
        setErrorMessage("uncaught exception", err);
    }
    finally {
        readXmlActive = false;
    }
}

/*
    codeskeleton = CodeMirror.fromTextArea(
        $("#code_template")[0], {
            mode: "text/x-java",
            indentUnit: 4,
            lineNumbers: true,
            matchBrackets: true,
            tabMode: "shift",
            styleActiveLine: true, autoCloseBrackets: true,
            theme: "eclipse",
            dragDrop: false
        });

    $(codeskeleton.getWrapperElement()).resizable({
        handles: 's', // only resize in north-south-direction
        resize: function () {
            editor.refresh();
        }
    });
    codeskeleton.on("drop", function (editor, e) {
        //uploadFileWhenDropped(e.originalEvent.dataTransfer.files, e.currentTarget);
        console.log('codemirror drop: ' + e);
    });

    // show/hide buttons according to programming language
    switchProgLang();

    // register callback
    $("#xml_programming-language").on("change", switchProgLang)


    $("#button_generate_restrictions").click(function () {
        $("#files_restriction")[0].textContent = "";
        $("#files_restriction").append(SubmissionFileList.getInstance().getTableString());
        let index = 0;
        let size = 0;
        // read model solution files
        ModelSolutionWrapper.doOnAll(function (ms) {
            FileReferenceList.doOnAllIds(ms.root, function (id) {
                const ui_file = FileWrapper.constructFromId(id);
                if (index > 0) {
                    // create new row
                    SubmissionFileList.getInstance().appendRow();
                }
                SubmissionFileList.getInstance().setLastRowContent(ui_file.filename, false, false);
                size += ui_file.size;
                index++;
            });
        });

        size *= 5; // add a lot of tolerance!
        size = Math.ceil(size/100)*100;

        $("#xml_submission_size").val(size);
    })

    $("#button_load").click(function () {
        $("#upload_xml_file").click();
    })
*/
    /*
    $("#button_new").click(function(){
    $("#upload_xml_file").click();
    })
     */

var enableTestMode = false;
/*
    if (!DEBUG_MODE) {
        $("#buttonClear").hide();
        $("#output").attr("readonly", true);

        $("#buttonExport").hide();
        $("#buttonImport").hide();
    }

    // function is used only in test environment!!
    enableTestMode = function () {
        // enable support for tests!
        console.log("enable test mode");
        //$("#buttonExport").show();
        //$("#buttonImport").show();

        $("#addFile").show();
        $("#loadFile").show();
    }

    if (TEST_MODE)
        enableTestMode();
*/
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

    $("#xml_task_internal_description").append(getInternalDescriptionString(''));

    // saving files is realised with an anchor having the download attribute set.
    // Unfortunately not every browser supports downloads and not every browser
    // supports data URI as a download link.
    // The following functions check whether this feature is supported
    checkDataURISupport(function (checkResult) {
        if (checkResult) {
            console.log('Files in data URIs are supported.');
        } else {
            alert('Files in data URIs are probabely NOT supported in this browser. ' +
                'Thus saving the task file will not be possible. ' +
                'Please use another browser (Firefox, Chrome).');
        }
    });

    function checkDataURISupport(callback) {
        try {
            var request = new XMLHttpRequest();
            request.onload = function reqListener() {
                if (callback)
                    callback(true);
            };
            request.onerror = function reqListener() {
                if (callback)
                    callback(false);
                else
                    console.log('Files in data URIs are supported.');
            };
            request.open('GET', 'data:application/pdf;base64,cw==');
            request.send();
        } catch (ex) {
            callback(false);
        }
    }

    checkDataURISupport();
*/

///////////////////////////////////////////////////////// end of document ready function

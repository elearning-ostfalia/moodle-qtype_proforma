/*
 * This proformaEditor was created by the eCULT-Team of Ostfalia University
 * http://ostfalia.de/cms/de/ecult/
 * The software is distributed under a CC BY-SA 3.0 Creative Commons license
 * https://creativecommons.org/licenses/by-sa/3.0/
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Author:
 * Karin Borm, Dr. Uta Priss
 */

// Known bugs: search the code for the string "ToDo" below and check faq.html and installationFAQ.html

import $ from 'jquery';
import {FileWrapper, FileStorage, fileStorages} from "./taskeditorfile";
import {getExtension, setErrorMessage} from "./taskeditorutil";
import {javaParser} from "./taskeditorjava";
import {taskeditorconfig} from "./taskeditorconfig";
import {readAndDisplayXml} from "./taskeditortask";
import {zipme} from "./zipper";


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
        alert("A file named '" + filename + "' already exists.");
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
            alert("A file named '" + filename + "' already exists.");
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

/*
function addTestButtons() {
    $.each(taskeditorconfig.testInfos, function (index, item) {
        $("#testbuttons").append("<button id='" + item.buttonJQueryId + "'>New " + item.title + "</button> ");
        $("#" + item.buttonJQueryId).click(function () {

            //var testNo = setcounter(testIDs);    // sets the corresponding fileref, filename and title "SetlX-Syntax-Test"
            let ui_test = TestWrapper.create(null, item.title, item); // item.htmlExtraFields, item.testType, item.withFileRef);
            item.onCreate(ui_test.id);

            $("#tabs").tabs("option", "active", tab_page.TESTS);
        });
    });
}

 */
/*
function switchProgLang() {
    let progLang = $("#xml_programming-language").val();
    console.log("changing programming language to " + progLang);

    // hide all test buttons
    $.each(taskeditorconfig.testInfos, function (index, test) {
        $("#" + test.buttonJQueryId).hide();
    });

    // show only test buttons needed for programming language
    let found = false;
    $.each(taskeditorconfig.proglangInfos, function (index, pl) {
        if (pl.name === progLang) {
            found = true;
            $.each(pl.tests, function (index, test) {
                $("#" + test.buttonJQueryId).show();
            });
        }
    });

    if (!found) {
        window.confirm("Unsupported Programming Language: " + progLang);
    }

    switch (progLang.split("/")[0].toLowerCase()) {
        case 'java':
            codeskeleton.setOption("mode", "text/x-java");
            break;
        case 'python':
            codeskeleton.setOption("mode", "text/x-python");
            break;
        case 'c':
            codeskeleton.setOption("mode", "text/x-csrc");
            break;
        case 'cpp':
            codeskeleton.setOption("mode", "text/x-c++src");
            // This does not work properly:
            // The unittest element is missing in task.xml output
            // taskeditorconfig.onProglangChanged('cpp');
            break;
    }
}

 */


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

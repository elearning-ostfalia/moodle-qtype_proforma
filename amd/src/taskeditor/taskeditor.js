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
 * main function for taskeditor
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm
 */


import Notification, {exception as displayException} from 'core/notification';
import Y from 'core/yui';
import {TestWrapper } from "./test";
import {downloadTask, getCheckstyleVersions, getJunitVersions} from "../repository";
import {generateUUID, getExtension} from "./helper";
import * as taskeditorconfig from "./config";
import {unzipme, zipme, taskTitleToFilename} from "./zipper";
import {readXMLWithLock} from "./helper";
import {convertToXML, InputError} from "./task";
import Config from 'core/config';
import {ModelSolutionWrapper} from "./modelsol";
import {TaskFileRef, TaskModelSolution} from "./taskdata";
import {ModelSolutionFileReference} from "./filereflist";
import {fileStorages, FileWrapper} from "./file";
import * as zip from "../zip/zip";
import * as logmonitor from "../logmonitor";

var draftitemid = null;
var draftfilename = null;
var taskrepositoryparams = null;
let modelsolrepositoryparams = null;
var t0;
let taskmaxbytes;

function fillSelectOptions(id, selectArray, textForError) {
    let selectElem = document.getElementById(id);
    if (!selectElem) {
        throw new Error('could not find element ' + id);
    }
    // At first check if there is a selected version which is not in the list.
    if (selectElem.options.length === 1) {
        const selectedVersion = selectElem.options[0].value;
        if (!selectArray.includes(selectedVersion)) {
            if (selectedVersion.trim() !== '') {
                alert('invalid ' + textForError + ' version ' + selectedVersion);
            }
            // Remove invalid option
            selectElem.remove(0);
        }
    }
    // Then add versions from Moodle server.
    selectArray.forEach(version => {
        // Check if version is already in list:
        const optionLabels = Array.from(selectElem.options).map((opt) => opt.value);
        if (!optionLabels.includes(version)) {
            let option = document.createElement("option");
            option.text = version;
            selectElem.add(option);
        }
    });

}

/**
 * get JUnit version from Moodle configuration and add to JUnit list
 * @param id identifier of select element
 */
export const setJunitVersions = (id) => {
    // TODO: kann man die JUnit version nicht besser über eine Core-Funktion holen??
    getJunitVersions()
        .then(response => {
            fillSelectOptions(id, response['junitversions'], 'JUnit');
        })
        .fail(Notification.exception);
}

/**
 * get Checkstyle version from Moodle server and add to select options
 * @param id identifier of select element
 */
export const setCheckstyleVersions = (id) => {
    getCheckstyleVersions()
        .then(response => {
            fillSelectOptions(id, response['checkstyleversions'], 'CheckStyle');
        })
        .fail(Notification.exception);
}

function createTestForm(testconfig) {
    TestWrapper.createFromTemplate(null,
        testconfig.mustacheTemplate, testconfig.getTemplateContext(), testconfig.withFileRef);
}

export const initproglang = (proglangdiv, buttondiv, langselect) => {

    function addButtonCallbacks() {
        document.querySelector('#addJUnitTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoJavaJUnit);
        }

        document.querySelector('#addCheckStyleTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoCheckStyle);
        }

        document.querySelector('#addCompilerTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoJavaComp);
        }

        document.querySelector('#addGoogleTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoGoogleTest);
        }

        document.querySelector('#addCUnitTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoCUnit);
        }

        document.querySelector('#addPythonUnittest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoPythonUnittest);
        }

        document.querySelector('#addPythonDocTest').onclick = function (e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoPythonDoctest);
        }
    }

    let langselectelem = document.getElementById(langselect);
    const lang = langselectelem.value;
    // show versions
    document.querySelector('#xml_programming-language-' + lang).style.display = '';
    // show buttons
    document.querySelectorAll('#' + buttondiv + ' .' + lang).forEach(
        e => {
            e.style.display = '';
        }
    );

    // Add change callback.
    langselectelem.onchange = function() {
        const lang = langselectelem.value;
        // Show versions for this language
        // document.querySelector('#xml_programming-language-' + lang).style.display = '';
        let versionElement = document.getElementById("xml_programming-language-" + lang);
        versionElement.disabled = (versionElement.options.length === 0);
        versionElement.style.display = '';

        // Show buttons for this language
        document.querySelectorAll('#' + buttondiv + ' .' + lang).forEach(
            e => e.style.display = ''
        );
        // Hide other versions
        document.querySelectorAll('#' + proglangdiv + ' select:not(#xml_programming-language-' + lang + ')').forEach(
            e => e.style.display = 'None'
        );
        // Hide other buttons
        document.querySelectorAll('#' + buttondiv + ' :not(.' + lang + ')').forEach(
            e => e.style.display = 'None'
        );
    };

    // Add button callbacks (depend on initialisation of config).
    taskeditorconfig.initStrings()
        .then(() => addButtonCallbacks());
}

export const download = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        convertToXML()
            .then((context) => {
                zipme(context, true, taskmaxbytes);
            })
            .catch(error => {
                if (!(error instanceof InputError)) {
                    console.error(error);
                }
            });
    }
}

export const downloadModelsolution = (buttonid) => {
    let button = document.getElementById(buttonid);

    /*    let blob = new Blob([ TEXT_CONTENT ], {
            type : "application/zip"
        });
    */
    button.onclick = async function (e) {
        e.preventDefault();
        createModelSolutionZip()
            .then(zippedBlob => {
                console.log(zippedBlob);
                const url = window.URL.createObjectURL(zippedBlob);
                let b = document.createElement("a");
                b.style = "display: none";
                b.download = 'modelsolution.zip';
                b.href = url;
                document.body.appendChild(b);
                b.click();
            });
    }
}

/**
 * send task with model solution and grading hints to Moodle server in order let
 * the task run on grader. The result is shown in extra div element.
 *
 * @param buttonid
 * @param containerid
 */
export function checkModelsolution(buttonid, containerid) {
    let button = document.getElementById(buttonid);
    let container = document.getElementById(containerid);
    let blobtask;
    let defaultcursor = container.style.cursor;

    let htmlFeedback = '';
    let feedbackstarted = false;

    function onFeedbackStart() {
        container.style.display = '';
        container.style.cursor = defaultcursor;
        htmlFeedback = '';
        feedbackstarted = true;
    }
    function onFeedbackData(text) {
        if (feedbackstarted) {
            htmlFeedback += text + '\n';
        } else {
            htmlFeedback += text + '<br>';
        }
    }
    function onFeedbackEnd() {
        container.innerHTML = htmlFeedback;
        document.querySelectorAll('#check-feedback-id .collapsibleregion')
            .forEach(element => {
                console.log('create collapsible region for ' + element.id);
                M.util.init_collapsible_region(Y, element.id, '', 'EIN VERSUCH IST ES WERT');
            });
    }

    button.onclick = function (e) {
        e.preventDefault();
        // clean old check feedback
        container.innerHTML = '';
        container.style.cursor = "wait";
        feedbackstarted = false;
        const aggstrategy = document.querySelector("select[name='aggregationstrategy']").value;
        button.disabled = true;

        // create task zipfile
        convertToXML()
            .then((taskxml) => {
                // Zip task
                return zipme(taskxml, false, taskmaxbytes);
            })
            .then(blob => {
                // Task is zipped => zip model solution
                // (could be made in parallel but makes code a bit more complex
                // so I do not do this)
                console.log('task zip created ');
                // blob is the zipped version of the whole task
                blobtask = blob;
                return createModelSolutionZip();
            })
            .then(modelsolutionzip => {
                const gradinghints = createGradingHints(true);
                const proglang = document.getElementById("xml_programming-language").value;
                // Model solution is zipped => send to Moodle server
                console.log('created model solution zip');
                const url = Config.wwwroot + '/question/type/proforma/checksolution_ajax.php';
                const questionId = document.querySelector("input[name='id']").value;
                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('task', blobtask, 'task.zip');
                formData.append('modelsolution', modelsolutionzip, 'modelsolution.zip');
                formData.append('itemid', modelsolrepositoryparams['checkitemid']);
                formData.append('contextid', modelsolrepositoryparams['contextid']);
                // courseContextId is only required for security checks so that
                // not anybody can execute the function on the server.
                formData.append('coursecontextid', Config.courseContextId);
                // formData.append('questionid', questionId);
                formData.append('gradinghints', gradinghints);
                formData.append('proglang', proglang);
                formData.append('aggregationstrategy', aggstrategy);
                return fetch(url, {
                    method : "POST",
                    body: formData,
                });
            })
            .then(response => {
                if (!response.ok) {
                    console.error(response);
                    return Promise.reject(response.statusText);
                }
                // Moodle server has received task with model solution
                // => convert to json
                return response.json()
            })
            .then(json => {
                // forward json to logmonitor.
                if (json.error) {
                    console.log(json);
                    return Promise.reject(json.error);
                }
                let url = Config.wwwroot + '/question/type/proforma/checksolution_ajax.php?runtest=1';
                url += '&sesskey=' + Config.sesskey +
                    //                        '&questionid=' + questionId +
                    '&itemid=' + json.itemid +
                    '&contextid=' + json.contextid +
                    '&taskfilename=' + json.taskfilename +
                    '&proglang=' + json.proglang +
                    '&coursecontextid=' + Config.courseContextId +
                    '&aggregationstrategy=' + aggstrategy +
                    '&modelsolutionfilename=' + json.modelsolutionfilename;

                logmonitor.show('checkmodelsollog', url, onFeedbackStart, onFeedbackData, onFeedbackEnd);
            })
            .catch(error => {
                if (!(error instanceof InputError)) {
                    console.log(error);
                    alert(error);
                }
            })
            .finally(() => {
                // console.log('finally promise');
                button.disabled = false;
            });
    }
}


export function uploadTaskToGrader(buttonid) {
    let button = document.getElementById(buttonid);
    if (!button) {
        console.error('invalid button id');
        return;
    }

    button.onclick = function (e) {
        e.preventDefault();
        button.disabled = true;
        convertToXML()
            .then((context) => {
                return zipme(context, false, taskmaxbytes);
            })
            .then(blobtask => {
                console.log('now let us upload task to grader');
                const url = Config.wwwroot + '/question/type/proforma/taskeditor_ajax.php';
                // const questionId = document.querySelector("input[name='id']").value;
                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('task', blobtask, 'task.zip');
                // Which itemid???
                // Modelsolution parameters contain new (unused) draftarea itemids.
                // checkitemid is used for temporary files used for checks.
                formData.append('coursecontextid', Config.courseContextId);
                formData.append('itemid', modelsolrepositoryparams['checkitemid']);
                // Context id is sent to Moodle in order to perform security checks:
                formData.append('contextid', modelsolrepositoryparams['contextid']);
                // formData.append('questionid', questionId);

                return fetch(url, {
                    method: "POST",
                    body: formData,
                });
            })
            .then(response => {
                if (!response.ok) {
                    console.error(response);
                    return Promise.reject(response.statusText);
                }
                return response.json()
            })
            .then(json => {
                if (json.error) {
                    console.log(json);
                    return Promise.reject(json.error);
                }
                const questionId = document.querySelector("input[name='id']").value;
                let url = Config.wwwroot + '/question/type/proforma/upload_sse.php';
                url += '?sesskey=' + Config.sesskey + '&id=' + questionId;
                if (json.itemid) {
                    url += '&itemid=' + json.itemid +
                        '&contextid=' + json.contextid +
                        '&filename=' + json.filename +
                        '&coursecontextid=' + Config.courseContextId;
                }
                logmonitor.show('uploadlog', url);
                // taskupload.upload(null, json.itemid, json.contextid, json.filename);
            })
            .catch(error => {
                if (!(error instanceof InputError)) {
                    console.log(error);
                    alert(error);
                }
            })
            .finally(() => {
                button.disabled = false;
            });
    }
}

/*
export const savetask = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        saveToServer();
    }
}
*/

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
 * @author     K.Borm
 */


// import ModalFactory from 'core/modal_factory';
// import ModalEvents from 'core/modal_events';
// import {get_string as getString} from 'core/str';
// import Str from 'core/str';
// import {get_strings as getStrings} from 'core/str';
import Notification, {exception as displayException} from 'core/notification';
import Y from 'core/yui';

import Templates from 'core/templates';
import {TestWrapper } from "./test";
import {downloadTask, getCheckstyleVersions, getJunitVersions} from "../repository";
import {getExtension, setErrorMessage} from "./util";
import {taskeditorconfig} from "./config";
import {unzipme, zipme} from "./zipper";
import {readXMLWithLock} from "./helper";
import {convertToXML} from "./task";
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
var modelsolrepositoryparams = null;
var t0;

/**
 * edit task
 * @param buttonid
 * @param context
 * @param taskrepoparams
 * @param msrepoparams
 * @param inline
 * @returns {Promise<void>}
 */
export async function edit(buttonid, context, taskrepoparams, msrepoparams, inline) {
    console.log(context);
    taskrepositoryparams = taskrepoparams;
    modelsolrepositoryparams = msrepoparams;
    console.log(taskrepositoryparams);

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

    function downloadTaskFromServer() {
        // Find file from {files} where itemid = value of #id_task

        let questionId = document.querySelector("input[name='id']").value;
        if (questionId === "") {
            // New question => finished.
            const fakeUrl = { "url": "" };
            return Promise.resolve(fakeUrl);
        }
        draftitemid = document.querySelector("#id_task").value;
        console.log('download task ' + draftitemid);
        return downloadTask(draftitemid)
            .then(response => {
                console.log(response.fileurl);
                draftfilename = decodeURIComponent(response.fileurl.split('/').reverse()[0]);
                if (!response.fileurl) {
                    reject(new Error('invalid fileurl ' + response.fileurl));
                }
                return response.fileurl;
            })
            .then(url => fetch(url, {method: 'GET'}));
    }

    /**
     * Originally there was a way to enter the grading parameters
     * separately from the task. If changes were made here,
     * they must now be transferred to the form fields.
     */
    function mergeWithGradingHints() {
        const gradinghints = document.querySelector('input[name="gradinghints"]');
        if (!gradinghints) {
            console.error('No gradinghints field found => ignore');
            return;
        }

        const aggregationstrategy = document.querySelector('#id_aggregationstrategy');
        // console.log('aggregationstrategy ' + aggregationstrategy.value);

        // console.log(gradinghints.value);
        const count = document.querySelectorAll('.proforma-taskeditor .xml_test').length;
        for (let i = 0; i < count; i++) {
            const testid = document.getElementById('id_testid_' + i);
            const testweight = document.getElementById('id_testweight_' + i);
            const testtitle = document.getElementById('id_testtitle_' + i);
            const testdescription = document.getElementById('id_testdescription_' + i);
            const testtype = document.getElementById('id_testtype_' + i);
            if (!testid) {
                console.error('cannot find element with id_testid_' + i);
                continue;
            }
            if (!testweight) {
                console.error('cannot find element with id_testweight_' + i);
                continue;
            }
            if (!testtitle) {
                console.error('cannot find element with id_testtitle_' + i);
                continue;
            }
            if (!testdescription) {
                console.error('cannot find element with id_testdescription_' + i);
                continue;
            }
            if (!testtype) {
                console.error('cannot find element with id_testtype_' + i);
                continue;
            }
            const ref = testid.value;
            let ui_test = TestWrapper.constructFromId(ref);
            // if (aggregationstrategy.value === '2') { // gewichtete Summe
                ui_test.weight = testweight.value;
            // } else {
 //               console.log('do not use testweight value because of aggregation strategy ' + aggregationstrategy.value);
//            }
            ui_test.title = testtitle.value;
            ui_test.description = testdescription.value;
            if (testtype.value !== ui_test.testtype) {
                console.error('Testtype for test ' + ui_test.id + ' does not match value from grading hints')
            }
        }

        // Finally hide original test input fields:
        console.log('*** ' + count);
        // (better use hide if ???)
        for (let i = 0; i < count; i++) {
            document.getElementById('fgroup_id_testoptions_' + i).style.display = 'None';
            document.getElementById('fitem_id_testtitle_' + i).style.display = 'None';
            document.getElementById('fitem_id_testdescription_' + i).style.display = 'None';
            // const selector = 'div[data-groupname="testoptions[' + i + ']"]';
            // document.querySelector(selector).style.display = 'None';
        }

        const t1 = performance.now();
        console.log("expanding details took " + (t1 - t0) + " milliseconds.");
    }

    function displayTaskdata(taskresponse) {
        const extension = getExtension(taskresponse.url);
        switch (extension)
        {
            case 'zip':
                console.log('task file is zipped! => extract');
                return taskresponse.blob()
                    .then(blob => {
                        // console.log('blob is');
                        // console.log(blob);
                        unzipme(blob, function(text) {
                            readXMLWithLock(text)
                                .then(() => mergeWithGradingHints());
                        });
                    });
            case 'xml':
                console.log('task file is not zipped');
                return taskresponse.text()
                    .then(text => {
                        readXMLWithLock(text)
                            .then(() => mergeWithGradingHints());
                    });
            default:
                return Promise.resolve('N/A');
        }
    }


    function updateEnvironment() {
        // Collapse main headers
        let header = document.querySelector('a[href="#id_generalheadercontainer"]');
        if (header) {
            if (header.getAttribute('aria-expanded') === "true") {
                header.click();
            }
        }
        // Collapse response options
        header = document.querySelector('a[href="#id_responseoptionscontainer"]');
        if (header) {
            if (header.getAttribute('aria-expanded') === "true") {
                header.click();
            }
        }

        // Hide edit details button
        document.getElementById(buttonid).style.display = 'none';
        // Hide grader options
        if (document.getElementById('id_graderoptions_header')) {
            document.getElementById('id_graderoptions_header').style.display = 'None';
        }
        // Hide model solution links
        if (document.getElementById('fitem_id_mslinks')) {
            document.getElementById('fitem_id_mslinks').style.display = 'None';
        }

        // Set taskeditor value to 1 in order to notify the server that the
        // task editor is visible
        console.log('set taskeditor field to 1');
        const taskeditorField = document.querySelector('input[name="taskeditor"]');
        console.log(taskeditorField);
        taskeditorField.value = "1";
        console.log(taskeditorField);

        // Save task on submit/update.
        let updatebutton = document.getElementById('id_updatebutton');
        if (updatebutton !== null) {
            let realUpdateClick = updatebutton.onclick;
            updatebutton.onclick = (event) => {
                event.preventDefault();
                console.log('save before update');
                uploadTaskToServer().then(() => {
                    console.log('uploadTaskToServer returned');
                    updatebutton.onclick = realUpdateClick;
                    updatebutton.click();
                });
            };
        } else {
            console.error('Could not find update button');
        }

        let submitbutton = document.getElementById('id_submitbutton');
        if (submitbutton !== null) {
            let realSubmitClick = submitbutton.onclick;
            submitbutton.onclick = (event) => {
                event.preventDefault();
                console.log('save before submit');
                uploadTaskToServer().then(() => {
                    console.log('uploadTaskToServer returned');
                    submitbutton.onclick = realSubmitClick;
                    submitbutton.click();
                });
            };
        } else {
            console.error('Could not find submit button');
        }

    }

    function showTaskeditor() {
        t0 = performance.now();
        console.log('edit task');
        downloadTaskFromServer()
            .then(taskresponse => {
                displayTaskdata(taskresponse);
                updateEnvironment();
                document.querySelector('.proforma-taskeditor').style.display = '';
            })
            .fail(Notification.exception);
    }

    const questionId = document.querySelector("input[name='id']").value;
    // hide editor if hidden 'taskeditor' input field is set to 0 (default)
    const taskeditorRequested = document.querySelector("input[name='taskeditor']");
    console.log('Check if taskeditor shall be visible or not');
    console.log(taskeditorRequested);

    if (taskeditorRequested && taskeditorRequested.value === '1') {
        console.log('show editor');
        // Hide details button.
        document.getElementById(buttonid).style.display = 'none';
        // Show and fill editor
        showTaskeditor();
    } else {
        console.log('hide editor');
        // Hide editor
        document.querySelector('.proforma-taskeditor').style.display = 'none';
        // Show editor on button click
        document.getElementById(buttonid).addEventListener('click', function (e) {
            showTaskeditor();
        });
    }

    /*
            let taskPromise = downloadTaskFromServer();

            let stringsPromise = getStrings([
                {
                    // All string beginning with taskeditor.
                    key: 'taskeditor',
                    component: 'qtype_proforma'
                }
            ]);
            let modalPromise = ModalFactory.create(
                {
                    type: ModalFactory.types.SAVE_CANCEL,
                    large: true
                }
            );

            context['tests'] = '';
            context['files'] = '';
            let bodyPromise = Templates.renderForPromise('qtype_proforma/taskeditor', context);

            $.when(stringsPromise, modalPromise, bodyPromise, taskPromise)
                .then(function(strings, modal, {html, js}, taskresponse) {
                    // console.log(html);
                    // console.log(js);

                    modal.setTitle(strings[0]);

                    modal.setBody(html);
                    // Change size (TODO: actually do with css)
                    modal.getModal().css('min-width', '70%');
                    modal.getModal().css('min-height', '90%');

                    modal.getRoot().on(ModalEvents.save, function(e) {
                        e.preventDefault();
                        alert('TODO save');
                        modal.destroy();
                    });

                    modal.getRoot().on(ModalEvents.cancel, function(e) {
                        e.preventDefault();
                        ModalFactory.create({
                            type: ModalFactory.types.SAVE_CANCEL,
                            title: 'Close task editor',
                            body: 'Do you really want to close the task editor?',
                        })
                            .then(function(confirm) {
                                confirm.setSaveButtonText('Close');
                                confirm.getRoot().on(ModalEvents.save, function() {
                                    modal.destroy();
                                });
                                confirm.show();
                            });
                    });

                    modal.getRoot().on(ModalEvents.hidden, modal.destroy.bind(modal));
                    modal.getRoot().on(ModalEvents.outsideClick, (e) => {
                        console.log('click outside modal');
                        e.preventDefault();
                    });
                    modal.getRoot().on(ModalEvents.destroyed, (e) => {
                        console.log('destroyed');
                        e.preventDefault();
                    });
                    // Hide close button
                    // modal.getRoot()[0].querySelector('.modal-header button .close').style.display = 'none';
                    let root = modal.getRoot()[0];
                    let header = root.querySelector('.modal-header');
                    header.querySelector('button').style.display = 'none';

                    modal.show();
                    if (js) {
                        Templates.runTemplateJS(js);
                    }

                    // Fill modal with data
                    console.log('response from fetch is');
                    console.log(taskresponse);
                    displayTaskdata(taskresponse);
                    return modal;
            }).fail(Notification.exception);
        */

}

/**
 * get JUnit version from Moodle configuration and add to JUnit list
 */
export const setJunitVersions = () => {
    // TODO: kann man die JUnit version nicht besser über eine Core-Funktion holen??
    // console.log('setJunitVersions');
    getJunitVersions()
        .then(response => {
            // console.log(response['junitversions']);
            document.querySelectorAll('.xml_ju_version').forEach(
                selectElem => {
                    // console.log(selectElem);
                    if (selectElem.querySelectorAll('option').length === 0) {
                        // No options yet.
                        response['junitversions'].forEach(version => {
                            let option = document.createElement("option");
                            option.text = version;
                            selectElem.add(option);
                        });
                    }
                }
            );
        })
        .fail(Notification.exception);
}

export const setCheckstyleVersions = () => {
    getCheckstyleVersions()
        .then(response => {
            document.querySelectorAll('.xml_pr_CS_version').forEach(
                selectElem => {
                    if (selectElem.querySelectorAll('option').length === 0) {
                        response['checkstyleversions'].forEach(version => {
                            let option = document.createElement("option");
                            option.text = version;
                            selectElem.add(option);
                        });
                    }
                }
            );
        })
        .fail(Notification.exception);
}

export const initproglang = (proglangdiv, buttondiv, langselect) => {

    function addButtonCallbacks() {
        document.querySelector('#addJUnitTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoJavaJUnit.createTestForm();
        }

        document.querySelector('#addCheckStyleTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoCheckStyle.createTestForm();
        }

        document.querySelector('#addCompilerTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoJavaComp.createTestForm();
        }

        document.querySelector('#addGoogleTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoGoogleTest.createTestForm();
        }

        document.querySelector('#addCUnitTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoCUnit.createTestForm();
        }

        document.querySelector('#addPythonUnittest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoPython.createTestForm();
        }

        document.querySelector('#addPythonDocTest').onclick = function (e) {
            e.preventDefault();
            taskeditorconfig.infoPythonDoctest.createTestForm();
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

    // Add button callbacks.
    addButtonCallbacks();
}

export const download = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        const zipname = $("#id_name").val();
        const context = convertToXML();
        if (context) {
            zipme(context, zipname, true);
        }
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
                b.download = 'modelsoluation.zip';
                b.href = url;
                document.body.appendChild(b);
                b.click();
            });
    }
}

function createGradingHints(temporary=false) {
    let doc = document.implementation.createDocument(null, null, null);
    let gh = doc.createElement("grading-hints");
    let root = doc.createElement("root");
    root.setAttribute('function', 'sum');
    gh.appendChild(root);

    TestWrapper.doOnAll(ui_test => {
        let test = doc.createElement("test-ref");
        root.appendChild(test);
        test.setAttribute('ref', ui_test.id);
        test.setAttribute('weight', ui_test.weight);
        let title = doc.createElement("title");
        title.innerHTML = ui_test.title;
        test.appendChild(title);
        let description = doc.createElement("description");
        description.innerHTML = ui_test.description;
        test.appendChild(description);
        let testtype = doc.createElement("test-type");
        testtype.innerHTML = ui_test.testtype;
        test.appendChild(testtype);
    });

    console.log('create new grading hints');
    console.log(doc);
    console.log(gh);
    const gradinghints = document.querySelector('input[name="gradinghints"]');
    if (!gradinghints) {
        console.error('No gradinghints field found => ignore');
        return;
    }
    let serializer = new XMLSerializer();
    let result = serializer.serializeToString (gh);

    if ((result.substring(0, 5) !== "<?xml")){
        result = '<?xml version="1.0"?>' + result;
        // result = "<?xml version='1.0' encoding='UTF-8'?>" + result;
    }
    console.log(result);
    if (!temporary) {
        gradinghints.value = encodeURIComponent(result);
    }
    console.log('grading hints are finished');
    return encodeURIComponent(result);
}

function uploadModelSolutionToServer() {
    // Instead of using the current draftid and delete all files
    // we use a new unused draftid.
    const draftitemid = modelsolrepositoryparams['newitemid'];

    // const draftitemid = document.querySelector("input[name='modelsol']").value;
    console.log('draftid for model sol is ' + draftitemid);

    console.log('now let us model solution in Moodle server');

    function uploadFile(formData) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';

        let request = new XMLHttpRequest();
        request.open('POST', url + '?action=' + action, false);
        console.log('send');
        try {
            request.send(formData);
            if (request.status !== 200) {
                alert(`Error ${request.status}: ${request.statusText}`);
            } else {
                console.log(request.response);
            }
        } catch(err) { // instead of onerror
            alert("Request failed");
        }
        console.log('parse repsonse');
        const jsonResponse = JSON.parse(request.responseText);
        console.log('response from Moodle');
        console.log(jsonResponse);
        if (jsonResponse.error !== undefined) {
            console.error(request.responseText);
            alert(jsonResponse.error);
        }
    }

    // write model solutions
    ModelSolutionWrapper.doOnAll(function(ms) {
        let modelSolution = new TaskModelSolution();
        modelSolution.id = ms.id;
        let counter = 0;
        console.log('MS id is ' + ms.id);
        ModelSolutionFileReference.getInstance().doOnAll(function(id) {
            modelSolution.filerefs[counter++] = new TaskFileRef(id);
            console.log('MS Fileref is ' + id);
            let file = FileWrapper.constructFromId(id);
            console.log('filename is ' + fileStorages[id].filename);
            const formData = new FormData();
            console.log(fileStorages);
            formData.append('sesskey', Config.sesskey);
            formData.append('client_id', modelsolrepositoryparams['client_id']);
            formData.append('overwrite', true);
            formData.append('repo_id', modelsolrepositoryparams['repo_id']);
            formData.append('itemid', draftitemid);
            let filename = fileStorages[id].filename.split("/").pop();
            let length = fileStorages[id].filename.length - filename.length;
            let filepath = fileStorages[id].filename.substring(0, length);
            formData.append('title', filename);
            if (fileStorages[id].isBinary) {
                let blob = new Blob([fileStorages[id].content], { type : fileStorages[id].mimetype });
                // console.log(blob);
                formData.append('repo_upload_file', blob);
            } else {
                let content = file.text;
                // console.log('Content is ' + content);
                formData.append('repo_upload_file', new Blob([content], { type : 'plain/text' }));
            }
            formData.append('filepath', '/');
            formData.append('savepath', filepath);
            console.log(formData);
            uploadFile(formData);
        }, ms.root);
    })

    // set draftitemid to new value
    document.querySelector("input[name='modelsol']").value = draftitemid;

}

async function createModelSolutionZip() {
    const zipFileWriter = new zip.BlobWriter("application/zip");
    const zipWriter = new zip.ZipWriter(zipFileWriter);

    // create zipfile with model solutions
    ModelSolutionWrapper.doOnAll(function(ms) {
        let modelSolution = new TaskModelSolution();
        modelSolution.id = ms.id;
        // console.log('MS id is ' + ms.id);
        ModelSolutionFileReference.getInstance().doOnAll(async function(id) {
            const filename = fileStorages[id].filename;
            // console.log('filename is ' + filename);
            let content = null;
            if (fileStorages[id].isBinary) {
                // console.log('binary');
                content = new Blob([fileStorages[id].content]);
            } else {
                // console.log('non binary');
                let file = FileWrapper.constructFromId(id);
                content = new Blob([file.text], { type : 'plain/text' });
                // formData.append('repo_upload_file', new Blob([content], { type : 'plain/text' }));
            }
            // console.log('Content is ' + content);
            await zipWriter.add(filename, new zip.BlobReader(content));
        }, ms.root);
    })
    // console.log('wait for close');
    await zipWriter.close();
    // console.log('return content');
    return zipFileWriter.getData();
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

    function onFeedbackStart() {
        container.style.display = '';
        container.style.cursor = defaultcursor;
        htmlFeedback = '';
    }
    function onFeedbackData(text) {
        htmlFeedback += text + '<br>';
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
        container.innerHTML = '...';
        container.style.cursor = "wait";
        // create task zipfile
        const taskxml = convertToXML();
        if (taskxml != null) {
            // if there is no taskxml then the input is invalid.
            const gradinghints = createGradingHints(true);
            // Zip task
            return zipme(taskxml, 'task.zip', false)
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
                    // Model solution is zipped => send to Moodle server
                    console.log('created model solution zip');
                    const url = Config.wwwroot + '/question/type/proforma/checksolution_ajax.php';
                    const questionId = document.querySelector("input[name='id']").value;
                    const formData = new FormData();
                    formData.append('sesskey', Config.sesskey);
                    formData.append('task', blobtask, 'task.zip');
                    formData.append('modelsolution', modelsolutionzip, 'modelsolution.zip');
                    formData.append('itemid', modelsolrepositoryparams['checkitemid']);
                    formData.append('questionid', questionId);
                    formData.append('gradinghints', gradinghints);

                    fetch(url, {
                        method : "POST",
                        body: formData,
                    })
                    .then(response => {
                        // Moodle server has received task with model solution
                        // => convert to json
                        console.log(response);
                        return response.json()
                    })
                    .then(json => {
                        // forward json to logmonitor.
                        console.log(json);
                        let url = Config.wwwroot + '/question/type/proforma/checksolution_ajax.php?runtest=1';
                        url += '&sesskey=' + Config.sesskey +
                            '&questionid=' + questionId +
                            '&itemid=' + json.itemid +
                            '&contextid=' + json.contextid +
                            '&taskfilename=' + json.taskfilename +
                            '&modelsolutionfilename=' + json.modelsolutionfilename;

                        logmonitor.show('checkmodelsollog', url, onFeedbackStart, onFeedbackData, onFeedbackEnd);
                    })
                    .catch(error => {
                        console.log(error)
                    });
                });
        }
    }
}

function uploadTaskToServer() {
    createGradingHints();
    uploadModelSolutionToServer();
    const zipname = $("#id_name").val();
    const context = convertToXML();
    if (context) {
        return zipme(context, zipname, false)
            .then(blob => {
                console.log('now let us update task in  Moodle server');
                const url = Config.wwwroot + '/repository/repository_ajax.php';
                const action = 'upload';

                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('repo_upload_file', blob);
                formData.append('filepath', '/');
                formData.append('client_id', taskrepositoryparams['client_id']);
                formData.append('title', draftfilename);
                formData.append('overwrite', true);
                // formData.append('maxbytes', -1);
                // since we are uploading the file to the 'draft area',
                // there is no point in limiting the size of the file area.
                // The draft area is used for all users.
                // formData.append('areamaxbytes', this.options['areamaxbytes']);
                formData.append('savepath', '/');
                formData.append('repo_id', taskrepositoryparams['repo_id']);
                formData.append('itemid', draftitemid);
                let request = new XMLHttpRequest();
                request.open('POST', url + '?action=' + action, false);
                console.log('send');
                try {
                    request.send(formData);
                    if (request.status !== 200) {
                        alert(`Error ${request.status}: ${request.statusText}`);
                    } else {
                        console.log(request.response);
                        // alert(request.response);
                    }
                } catch(err) { // instead of onerror
                    alert("Request failed");
                }
                console.log('parse repsonse');
                const jsonResponse = JSON.parse(request.responseText);
                console.log('response from Moodle');
                console.log(jsonResponse);
                if (jsonResponse.error !== undefined) {
                    console.error(request.responseText);
                    alert(jsonResponse.error);
                }
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
        const zipname = $("#id_name").val();
        const context = convertToXML();
        if (context) {
            return zipme(context, zipname, false)
                .then(blobtask => {
                    console.log('now let us upload task to grader');
                    const url = Config.wwwroot + '/question/type/proforma/taskeditor_ajax.php';
                    const questionId = document.querySelector("input[name='id']").value;
                    const formData = new FormData();
                    formData.append('sesskey', Config.sesskey);
                    formData.append('task', blobtask, 'task.zip');
                    // Which itemid???
                    formData.append('itemid', modelsolrepositoryparams['checkitemid']);
                    formData.append('questionid', questionId);

                    fetch(url, {
                        method : "POST",
                        body: formData,
                    })
                        .then(response => {
                            // console.log(response);
                            return response.json()
                        })
                        .then(json => {
                            console.log(json);
                            const questionId = document.querySelector("input[name='id']").value;
                            let url = Config.wwwroot + '/question/type/proforma/upload_sse.php';
                            url += '?sesskey=' + Config.sesskey + '&id=' + questionId;
                            if (json.itemid) {
                                url += '&itemid=' + json.itemid + '&contextid=' + json.contextid + '&filename=' + json.filename;
                            }

                            logmonitor.show('uploadlog', url);
                            // taskupload.upload(null, json.itemid, json.contextid, json.filename);
                        })
                        .catch(error => {
                            console.log(error)
                        });

                });
        }
    }
}

export const savetask = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        uploadTaskToServer();
    }
}


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
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */


import {debugLog} from '../debug';
import Notification from 'core/notification';
import Y from 'core/yui';
import Templates from 'core/templates';
import {TestWrapper} from "./test";
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
// eslint-disable-next-line no-unused-vars
var draftfilename = null;
var taskrepositoryparams = null;
let modelsolrepositoryparams = null;
var t0;
let taskmaxbytes;


/**
 * Edit task
 * @param {string} buttonid id of button to trigger opening taskeditor
 * @param {Object} context programming language contexts
 * @param {Object} taskrepoparams parameters for interacting with draft tasks
 * @param {Object} msrepoparams parameters for interacting with draft model solutions
 * @returns {Promise<void>}
 */
export async function edit(buttonid, context, taskrepoparams, msrepoparams) {
    taskrepositoryparams = taskrepoparams;
    modelsolrepositoryparams = msrepoparams;
    taskmaxbytes = context.taskmaxbytes;
    debugLog('Task max bytes: ' + taskmaxbytes);


    /**
     * Downloads the task file of the draft area from the Moodle server.
     *
     * @returns {Promise}
     */
    function downloadTaskFromServer() {
        debugLog('download task ' + draftitemid);
        const t0 = performance.now();
        return downloadTask(draftitemid)
            .then(response => {
                const t1 = performance.now();
                debugLog("getting task url took " + (t1 - t0) + " milliseconds.");
                debugLog(response.fileurl);
                draftfilename = decodeURIComponent(response.fileurl.split('/').reverse()[0]);
                if (!response.fileurl) {
                    throw new Error('invalid fileurl ' + response.fileurl);
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
            let uiTest = TestWrapper.constructFromId(ref);
            if (!uiTest) {
                Notification.alert('', 'cannot create test ' + ref);
            } else {
                uiTest.weight = testweight.value;
                uiTest.title = testtitle.value;
                uiTest.description = testdescription.value;
                if (testtype.value !== uiTest.testtype) {
                    Notification.alert('', 'Task file does not match grading hints in Moodle:<br>' +
                        'Testtype for test ' + uiTest.id + ' does not match testtype from grading hints');
                }
            }
        }

        const t1 = performance.now();
        debugLog("expanding details took " + (t1 - t0) + " milliseconds.");
    }

    /**
     * Reads the task.xml content into the task editor and merges it with the grading hints.
     *
     * @param {string} text content of task.xml
     * @returns {Promise}
     */
    function readTaskXml(text) {
        return readXMLWithLock(text)
            .then(() => mergeWithGradingHints())
            .catch(Notification.exception);
    }

    /**
     * Reads the downloaded task file (zip or xml) and displays it in the task editor.
     *
     * @param {Response} taskresponse fetch response with the task file
     * @returns {Promise}
     */
    function displayTaskdata(taskresponse) {
        const extension = getExtension(taskresponse.url);
        switch (extension) {
            case 'zip':
                return taskresponse.blob()
                    .then(blob => {
                        unzipme(blob, readTaskXml);
                        return true;
                    });
            case 'xml':
                return taskresponse.text()
                    .then(text => readTaskXml(text));
            default:
                return Promise.resolve('N/A');
        }
    }

    /**
     * Some disabled input fields are enabled in order to submit
     * the values to moodle so that they will be stored in the database.
     */
    // eslint-disable-next-line no-unused-vars
    function revertChangesForSubmission() {
        let uuid = document.querySelector("input[name='uuid']");
        if (!uuid) {
            console.error('cannot find uuid element');
        } else {
            uuid.disabled = true;
        }
        let proformaversion = document.querySelector("input[name='proformaversion']");
        if (!proformaversion) {
            console.error('cannot find proformaversion element');
        } else {
            proformaversion.disabled = true;
        }
    }
    /**
     * Adapts the Moodle form to the task editor (e.g. hides the original test input fields).
     */
    function updateEnvironment() {
        debugLog('*** updateEnvironment');
        // Hide original test input fields:
        // (better use hide if ???)
        document.querySelectorAll('[id^="fgroup_id_testoptions_"]').forEach(item => {
            item.style.display = 'None';
        });
        document.querySelectorAll('[id^="fitem_id_testtitle_"]').forEach(item => {
            item.style.display = 'None';
        });
        document.querySelectorAll('[id^="fitem_id_testdescription_"]').forEach(item => {
            item.style.display = 'None';
        });


        // Since the editor was opened, a new uuid is generated immediately,
        // because changes are not tracked.
        // This means that when the task is saved, a new uuid is set.
        let uuid = document.querySelector("input[name='uuid']");
        if (!uuid) {
            console.error('cannot find uuid element');
        } else {
            uuid.value = generateUUID();
        }
        let proformaversion = document.querySelector("input[name='proformaversion']");
        if (!proformaversion) {
            console.error('cannot find proformaversion element');
        } else {
            proformaversion.value = '2.0';
        }

        // Do not collapse other headers as there might be missing input fields after
        // import that can not be seen on save (submit)
/*        if (questionId !== "") {
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
        }
*/

        // Hide edit details button
        document.getElementById(buttonid).style.display = 'none';
        // Hide grader options
        if (document.getElementById('id_graderoptions_header')) {
            // Document.getElementById('id_graderoptions_header').style.display = 'None';
        }
        // Hide model solution links
        if (document.getElementById('fitem_id_mslinks')) {
            document.getElementById('fitem_id_mslinks').style.display = 'None';
        }
        // Hide task filemanager
        if (document.getElementById('fitem_id_task')) {
            document.getElementById('fitem_id_task').style.display = 'None';
        }
        // Hide upload button in grader section
        if (document.getElementById('fitem_id_uploadbutton')) {
            document.getElementById('fitem_id_uploadbutton').style.display = 'None';
        }
        // Collapse grader options
        let header = document.querySelector('a[href="#id_graderoptions_headercontainer"]');
        if (header) {
            if (header.getAttribute('aria-expanded') === "true") {
                header.click();
            }
        }

        // Set taskeditor value to 1 in order to notify the server that the
        // task editor is visible
        // (Does not open editor on reload :-()
        const taskeditorField = document.querySelector('input[name="taskeditor"]');
        taskeditorField.value = "1";


        // Save task on submit/update.
        let updatebutton = document.getElementById('id_updatebutton');
        if (updatebutton !== null) {
            let realUpdateClick = updatebutton.onclick;
            updatebutton.onclick = (event) => {
                event.preventDefault();
                debugLog('save before update');
                updatebutton.disabled = true;
                saveToServer()
                    .then(() => {
                        updatebutton.disabled = false;
                        debugLog('Task is uploaded to server');
                        updatebutton.onclick = realUpdateClick;
                        updatebutton.click();
                        return true;
                    })
                   .catch(error => {
                       if (!(error instanceof InputError)) {
                           debugLog(error);
                           Notification.exception(error);
                       }
                    })
                    .finally(() => {
                        updatebutton.disabled = false;
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
                debugLog('save before submit');
                submitbutton.disabled = true;
                saveToServer()
                    .then(() => {
                        debugLog('Task is uploaded to server');
                        submitbutton.disabled = false;
                        submitbutton.onclick = realSubmitClick;
                        submitbutton.click();
                        /*                    Let uuid = document.querySelector("input[name='uuid']");
                                            uuid.disabled = false;*/
                        return true;
                    })
                    .catch(error => {
                        if (!(error instanceof InputError)) {
                            debugLog(error);
                            Notification.exception(error);
                        }
                    })
                    .finally(() => {
                        submitbutton.disabled = false;
                    });
            };
            /* Problem: in new questions the values are not submitted
             * to server so that UUID and proformaversion is missing
             */
            /*
            // Some of the form validation tests are executed on the Moodle server.
            // If the validation fails some of the changes must be
            // reverted.
            // Moodle has a form validation event that is triggered in that case
            // (hopefully)
            let form = submitbutton.closest('form');
            if (form) {
                form.addEventListener('core_form/fieldValidationFailed', (x) => {
                    console.log('core_form/fieldValidationFailed');
                    console.log(x);
                    // revertChangesForSubmission();
                }, false);
            }
 */
        } else {
            console.error('Could not find submit button');
        }
    }

    /**
     * Shows the task editor and loads the task of an existing question.
     */
    function showTaskeditor() {
        t0 = performance.now();
        draftitemid = document.querySelector("#id_task").value;
        let questionId = document.querySelector("input[name='id']").value;
        if (questionId === "") {
            // New question => finished.
            debugLog('new task');
            ModelSolutionWrapper.createFromTemplate();
            updateEnvironment();
            document.querySelector('.proforma-taskeditor').style.display = '';
            return;
        }

        let loadingElem = document.createElement('p');
        Templates.render('core/loading', {})
            .then((html) => {
                let head = document.getElementById('fitem_id_editdetails');
                // Center image
                loadingElem.align = 'center';
                loadingElem.innerHTML = html;
                head.parentNode.insertBefore(loadingElem, head);
                // Hide task editor
                document.querySelector('.proforma-taskeditor').style.display = 'None';
                // LoadingIcon.fadeIn(150);
                return 0;
                // Return new Promise(resolve => setTimeout(resolve, 105000));
            })
            .then(() => {
                return downloadTaskFromServer();
            })
            .then(taskresponse => {
                return displayTaskdata(taskresponse);
            })
            .then(() => {
                // Update environment
                updateEnvironment();
                loadingElem.remove();
                // Show task editor
                document.querySelector('.proforma-taskeditor').style.display = '';
                return true;
            })
            .catch((error) => {
                loadingElem.remove();
                Notification.exception(error);
            });
    }

    const questionId = document.querySelector("input[name='id']").value;
    // Hide editor if hidden 'taskeditor' input field is set to 0 (default)
    const taskeditorRequested = document.querySelector("input[name='taskeditor']");

    taskeditorconfig.initStrings()
        .then(() => {
            if (questionId === "" || (taskeditorRequested && taskeditorRequested.value === '1')) {
                debugLog('show editor');
                // Hide details button.
                document.getElementById(buttonid).style.display = 'none';
                // Show and fill editor
                showTaskeditor();
            } else {
                debugLog('hide editor');
                // Hide editor
                document.querySelector('.proforma-taskeditor').style.display = 'none';
                // Show editor on button click
                document.getElementById(buttonid).addEventListener('click', () => {
                    document.getElementById(buttonid).disabled = true;
                    showTaskeditor();
                });
            }
            return true;
        })
        .catch(Notification.exception);

    /*
            Let taskPromise = downloadTaskFromServer();

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
 * Fills a select element with the given values.
 *
 * @param {string} id id of the select element
 * @param {string[]} selectArray values
 * @param {string} textForError name of the values used in error messages (e.g. JUnit)
 */
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
                Notification.alert('', 'invalid ' + textForError + ' version ' + selectedVersion);
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
 * Get JUnit version from Moodle configuration and add to JUnit list
 * @param {string} id identifier of select element
 */
export const setJunitVersions = (id) => {
    // TODO: kann man die JUnit version nicht besser über eine Core-Funktion holen??
    getJunitVersions()
        .then(response => {
            fillSelectOptions(id, response.junitversions, 'JUnit');
            return true;
        })
        .fail(Notification.exception);
};

/**
 * Get Checkstyle version from Moodle server and add to select options
 * @param {string} id identifier of select element
 */
export const setCheckstyleVersions = (id) => {
    getCheckstyleVersions()
        .then(response => {
            fillSelectOptions(id, response.checkstyleversions, 'CheckStyle');
            return true;
        })
        .fail(Notification.exception);
};

/**
 * Creates a new test in the task editor.
 *
 * @param {Object} testconfig test configuration
 */
function createTestForm(testconfig) {
    TestWrapper.createFromTemplate(null,
        testconfig.mustacheTemplate, testconfig.getTemplateContext(), testconfig.withFileRef);
}

export const initproglang = (proglangdiv, buttondiv, langselect) => {

    /**
     * Adds the callbacks for the buttons creating new tests.
     */
    function addButtonCallbacks() {
        document.querySelector('#addJUnitTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoJavaJUnit);
        };

        document.querySelector('#addCheckStyleTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoCheckStyle);
        };

        document.querySelector('#addCompilerTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoJavaComp);
        };

        document.querySelector('#addGoogleTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoGoogleTest);
        };

        document.querySelector('#addCUnitTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoCUnit);
        };

        document.querySelector('#addPythonUnittest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoPythonUnittest);
        };

        document.querySelector('#addPythonDocTest').onclick = function(e) {
            e.preventDefault();
            createTestForm(taskeditorconfig.infoPythonDoctest);
        };
    }

    let langselectelem = document.getElementById(langselect);
    const lang = langselectelem.value;
    // Show versions
    document.querySelector('#xml_programming-language-' + lang).style.display = '';
    // Show buttons
    document.querySelectorAll('#' + buttondiv + ' .' + lang).forEach(
        e => {
            e.style.display = '';
        }
    );

    // Add change callback.
    langselectelem.onchange = function() {
        const lang = langselectelem.value;
        // Change syntax highlighting.
        let highlight = document.getElementById("id_programminglanguage");
        if (highlight) {
            highlight.value = lang;
        }

        // Show versions for this language
        // document.querySelector('#xml_programming-language-' + lang).style.display = '';
        let versionElement = document.getElementById("xml_programming-language-" + lang);
        versionElement.disabled = (versionElement.options.length === 0);
        versionElement.style.display = '';

        // Show buttons for this language
        document.querySelectorAll('#' + buttondiv + ' .' + lang).forEach(
            e => {
                e.style.display = '';
            }
        );
        // Hide other versions
        document.querySelectorAll('#' + proglangdiv + ' select:not(#xml_programming-language-' + lang + ')').forEach(
            e => {
                e.style.display = 'None';
            }
        );
        // Hide other buttons
        document.querySelectorAll('#' + buttondiv + ' :not(.' + lang + ')').forEach(
            e => {
                e.style.display = 'None';
            }
        );
    };

    // Add button callbacks (depend on initialisation of config).
    taskeditorconfig.initStrings()
        .then(() => addButtonCallbacks())
        .catch(Notification.exception);
};

export const download = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function(e) {
        e.preventDefault();
        convertToXML()
            .then((context) => zipme(context, true, taskmaxbytes))
            .catch(error => {
                if (!(error instanceof InputError)) {
                    console.error(error);
                }
            });
    };
};

export const downloadModelsolution = (buttonid) => {
    let button = document.getElementById(buttonid);

    /*    Let blob = new Blob([ TEXT_CONTENT ], {
            type : "application/zip"
        });
    */
    button.onclick = async function(e) {
        e.preventDefault();
        createModelSolutionZip()
            .then(zippedBlob => {
                debugLog(zippedBlob);
                const url = window.URL.createObjectURL(zippedBlob);
                let b = document.createElement("a");
                b.style = "display: none";
                b.download = 'modelsolution.zip';
                b.href = url;
                document.body.appendChild(b);
                b.click();
                return true;
            })
            .catch(Notification.exception);
    };
};

export const toggleFullscreen = (buttonid) => {
    let button = document.getElementById(buttonid);

    button.onclick = function(e) {
        e.preventDefault();
        let editor = document.querySelector('.proforma-taskeditor');
        if (document.fullscreenElement) {
            // If there is a fullscreen element, exit full screen.
            // editor.querySelector('.nav-tabs').classList.remove('fullscreen');
            document.exitFullscreen();
            return;
        }
        editor.requestFullscreen();
/*            .then(() =>
                editor.querySelector('.nav-tabs').classList.add('fullscreen')
            ) */
    };
};


/**
 * Creates the grading hints (test weights) from the tests in the task editor.
 *
 * @param {boolean} temporary true if the grading hints are not stored in the form
 * @returns {string} URI encoded grading hints
 */
function createGradingHints(temporary = false) {
    let doc = document.implementation.createDocument(null, null, null);
    let gh = doc.createElement("grading-hints");
    let root = doc.createElement("root");
    root.setAttribute('function', 'sum');
    gh.appendChild(root);

    TestWrapper.doOnAll(uiTest => {
        let test = doc.createElement("test-ref");
        root.appendChild(test);
        test.setAttribute('ref', uiTest.id);
        test.setAttribute('weight', uiTest.weight);
        let title = doc.createElement("title");
        title.innerHTML = uiTest.title;
        test.appendChild(title);
        let description = doc.createElement("description");
        description.innerHTML = uiTest.description;
        test.appendChild(description);
        let testtype = doc.createElement("test-type");
        testtype.innerHTML = uiTest.testtype;
        test.appendChild(testtype);
    });

    debugLog('create new grading hints');
    const gradinghints = document.querySelector('input[name="gradinghints"]');
    if (!gradinghints) {
        console.error('No gradinghints field found => ignore');
        return undefined;
    }
    let serializer = new XMLSerializer();
    let result = serializer.serializeToString(gh);

    if ((result.substring(0, 5) !== "<?xml")) {
        result = '<?xml version="1.0"?>' + result;
        // Result = "<?xml version='1.0' encoding='UTF-8'?>" + result;
    }
    debugLog(result);
    if (!temporary) {
        gradinghints.value = encodeURIComponent(result);
    }
    debugLog('grading hints are finished');
    return encodeURIComponent(result);
}

/**
 * Uploads the model solution files to a new draft area of the Moodle server.
 */
function uploadModelSolutionToServer() {
    // Instead of using the current draftid and delete all files
    // we use a new unused draftid.
    const draftitemid = modelsolrepositoryparams.newitemid;

    // Const draftitemid = document.querySelector("input[name='modelsol']").value;
    debugLog('draftid for model sol is ' + draftitemid);

    debugLog('now let us model solution in Moodle server');

    /**
     * Uploads a file synchronously to the Moodle repository.
     *
     * @param {FormData} formData upload data
     */
    function uploadFile(formData) {
        const url = Config.wwwroot + '/repository/repository_ajax.php';
        const action = 'upload';

        let request = new XMLHttpRequest();
        request.open('POST', url + '?action=' + action, false);
        debugLog('send');
        try {
            request.send(formData);
            if (request.status !== 200) {
                Notification.alert('', `Error ${request.status}: ${request.statusText}`);
            } else {
                debugLog(request.response);
            }
        } catch (err) { // Instead of onerror
            console.error(err);
            Notification.alert('', "Request failed");
        }
        debugLog('parse repsonse');
        const jsonResponse = JSON.parse(request.responseText);
        debugLog('response from Moodle');
        debugLog(jsonResponse);
        if (jsonResponse.error !== undefined) {
            console.error(request.responseText);
            Notification.alert('', jsonResponse.error);
        }
    }

    // Write model solutions
    ModelSolutionWrapper.doOnAll(function(ms) {
        let modelSolution = new TaskModelSolution();
        modelSolution.id = ms.id;
        let counter = 0;
        ModelSolutionFileReference.getInstance().doOnAll(function(id) {
            modelSolution.filerefs[counter++] = new TaskFileRef(id);
            let file = FileWrapper.constructFromId(id);
            const formData = new FormData();
            debugLog(fileStorages);
            formData.append('sesskey', Config.sesskey);
            formData.append('client_id', modelsolrepositoryparams.client_id);
            formData.append('overwrite', true);
            formData.append('repo_id', modelsolrepositoryparams.repo_id);
            formData.append('itemid', draftitemid);
            let filename = fileStorages[id].filename.split("/").pop();
            let length = fileStorages[id].filename.length - filename.length;
            let filepath = fileStorages[id].filename.substring(0, length);
            formData.append('title', filename);
            if (fileStorages[id].isBinary) {
                let blob = new Blob([fileStorages[id].content], {type: fileStorages[id].mimetype});
                formData.append('repo_upload_file', blob);
            } else {
                let content = file.text;
                formData.append('repo_upload_file', new Blob([content], {type: 'plain/text'}));
            }
            formData.append('filepath', '/');
            formData.append('savepath', filepath);
            debugLog(formData);
            uploadFile(formData);
        }, ms.root);
    });

    // Set draftitemid to new value
    document.querySelector("input[name='modelsol']").value = draftitemid;
}

/**
 * Creates a zip file containing all model solution files.
 *
 * @returns {Promise<Blob>} zip file
 */
async function createModelSolutionZip() {
    const zipFileWriter = new zip.BlobWriter("application/zip");
    const zipWriter = new zip.ZipWriter(zipFileWriter);

    // Create zipfile with model solutions
    ModelSolutionWrapper.doOnAll(function(ms) {
        let modelSolution = new TaskModelSolution();
        modelSolution.id = ms.id;
        ModelSolutionFileReference.getInstance().doOnAll(async function(id) {
            const filename = fileStorages[id].filename;
            let content = null;
            if (fileStorages[id].isBinary) {
                content = new Blob([fileStorages[id].content]);
            } else {
                let file = FileWrapper.constructFromId(id);
                content = new Blob([file.text], {type: 'plain/text'});
                // FormData.append('repo_upload_file', new Blob([content], { type : 'plain/text' }));
            }
            await zipWriter.add(filename, new zip.BlobReader(content));
        }, ms.root);
    });
    await zipWriter.close();
    return zipFileWriter.getData();
}

/**
 * Send task with model solution and grading hints to Moodle server in order let
 * the task run on grader. The result is shown in extra div element.
 *
 * @param {string} buttonid id of the button starting the check
 * @param {string} containerid id of the element showing the feedback
 */
export function checkModelsolution(buttonid, containerid) {
    let button = document.getElementById(buttonid);
    let container = document.getElementById(containerid);
    let blobtask;
    let defaultcursor = container.style.cursor;

    let htmlFeedback = '';
    let feedbackstarted = false;

    /**
     * Called when the feedback starts: resets the feedback.
     */
    function onFeedbackStart() {
        container.style.display = '';
        container.style.cursor = defaultcursor;
        htmlFeedback = '';
        feedbackstarted = true;
    }
    /**
     * Called for each feedback line: collects the feedback.
     *
     * @param {string} text feedback line
     */
    function onFeedbackData(text) {
        if (feedbackstarted) {
            htmlFeedback += text + '\n';
        } else {
            htmlFeedback += text + '<br>';
        }
    }
    /**
     * Called when the feedback is complete: displays the feedback.
     */
    function onFeedbackEnd() {
        container.innerHTML = htmlFeedback;
        document.querySelectorAll('#check-feedback-id .collapsibleregion')
            .forEach(element => {
                debugLog('create collapsible region for ' + element.id);
                M.util.init_collapsible_region(Y, element.id, '', 'EIN VERSUCH IST ES WERT');
            });
    }

    button.onclick = function(e) {
        e.preventDefault();
        // Clean old check feedback
        container.innerHTML = '';
        container.style.cursor = "wait";
        feedbackstarted = false;
        const aggstrategy = document.querySelector("select[name='aggregationstrategy']").value;
        button.disabled = true;

        // Create task zipfile
        convertToXML()
            .then((taskxml) => {
                // Zip task
                return zipme(taskxml, false, taskmaxbytes);
            })
            .then(blob => {
                // Task is zipped => zip model solution
                // (could be made in parallel but makes code a bit more complex
                // so I do not do this)
                debugLog('task zip created ');
                // Blob is the zipped version of the whole task
                blobtask = blob;
                return createModelSolutionZip();
            })
            .then(modelsolutionzip => {
                const gradinghints = createGradingHints(true);
                const proglang = document.getElementById("xml_programming-language").value;
                // Model solution is zipped => send to Moodle server
                debugLog('upload files to moodle');
                const url = Config.wwwroot + '/question/type/proforma/checksolution_ajax.php';
                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('task', blobtask, 'task.zip');
                formData.append('modelsolution', modelsolutionzip, 'modelsolution.zip');
                formData.append('itemid', modelsolrepositoryparams.checkitemid);
                formData.append('contextid', modelsolrepositoryparams.contextid);
                // CourseContextId is only required for security checks so that
                // not anybody can execute the function on the server.
                formData.append('coursecontextid', Config.courseContextId);
                // FormData.append('questionid', questionId);
                formData.append('gradinghints', gradinghints);
                formData.append('proglang', proglang);
                formData.append('aggregationstrategy', aggstrategy);
                return fetch(url, {
                    method: "POST",
                    body: formData,
                });
            })
            .then(response => {
                if (!response.ok) {
                    console.error(response);
                    throw new Error(response.statusText);
                }
                // Moodle server has received task with model solution
                // => convert to json
                return response.json();
            })
            .then(json => {
                // Forward json to logmonitor.
                if (json.error) {
                    debugLog(json);
                    throw new Error(json.error);
                }
                debugLog('run tests on grader and stream output');
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
                return json;
            })
            .catch(error => {
                if (!(error instanceof InputError)) {
                    debugLog(error);
                    Notification.exception(error);
                }
            })
            .finally(() => {
                button.disabled = false;
            });
    };
}

/**
 * Uploads currect task to Moodle server into the draft area prepared for the task
 * @returns {*}
 */
function saveToServer() {
    return convertToXML()
        .then((context) => {
            createGradingHints();
            uploadModelSolutionToServer();

            // Update values for UUID and proforma version in form input.
            let uuid = document.querySelector("input[name='uuid']");
            if (!uuid) {
                console.error('cannot find uuid element');
            } else {
                // The UUID is disabled and must be enabled in order to
                // submit the changed value.
                uuid.disabled = false;
                uuid.value = generateUUID();
            }
            let proformaversion = document.querySelector("input[name='proformaversion']");
            if (!proformaversion) {
                console.error('cannot find proformaversion element');
            } else {
                // The proformaversion is disabled and must be enabled in order to
                // submit the changed value.
                proformaversion.disabled = false;
            }

            return zipme(context, false, taskmaxbytes);
        })
        .then(blobtask => {
            debugLog('now let us update task in  Moodle server: ' + draftitemid);
            const url = Config.wwwroot + '/question/type/proforma/taskeditor_ajax.php';
            const formData = new FormData();
            formData.append('sesskey', Config.sesskey);
            formData.append('task', blobtask, taskTitleToFilename());
            // Use original itemid from task filemanager
            const itemid = document.querySelector("#id_task").value;
            formData.append('itemid', itemid); // Draftitemid);
            formData.append('contextid', taskrepositoryparams.contextid);
            formData.append('coursecontextid', Config.courseContextId);

            return fetch(url, {
                method: "POST",
                body: formData,
            });
        })
        .then(response => {
            debugLog(response);
            return response.json();
        })
        .then(json => {
            debugLog(json);
            return json;
        });
        // Do not catch here because error will not be detected in calling function!!
}

/**
 * Creates the task file and uploads it to the grader when the button is clicked.
 *
 * @param {string} buttonid id of the upload button
 */
export function uploadTaskToGrader(buttonid) {
    let button = document.getElementById(buttonid);
    if (!button) {
        console.error('invalid button id');
        return;
    }

    button.onclick = function(e) {
        e.preventDefault();
        button.disabled = true;
        convertToXML()
            .then((context) => {
                return zipme(context, false, taskmaxbytes);
            })
            .then(blobtask => {
                debugLog('upload file to Moodle');
                const url = Config.wwwroot + '/question/type/proforma/taskeditor_ajax.php';
                // Const questionId = document.querySelector("input[name='id']").value;
                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('task', blobtask, 'task.zip');
                // Which itemid???
                // Modelsolution parameters contain new (unused) draftarea itemids.
                // checkitemid is used for temporary files used for checks.
                formData.append('coursecontextid', Config.courseContextId);
                formData.append('itemid', modelsolrepositoryparams.checkitemid);
                // Context id is sent to Moodle in order to perform security checks:
                formData.append('contextid', modelsolrepositoryparams.contextid);
                // FormData.append('questionid', questionId);

                return fetch(url, {
                    method: "POST",
                    body: formData,
                });
            })
            .then(response => {
                if (!response.ok) {
                    console.error(response);
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .then(json => {
                if (json.error) {
                    debugLog(json);
                    throw new Error(json.error);
                }
                debugLog('upload file from Moodle to grader');
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
                // Taskupload.upload(null, json.itemid, json.contextid, json.filename);
                return json;
            })
            .catch(error => {
                if (!(error instanceof InputError)) {
                    debugLog(error);
                    Notification.exception(error);
                }
            })
            .finally(() => {
                button.disabled = false;
            });
    };
}

/*
Export const savetask = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        saveToServer();
    }
}
*/

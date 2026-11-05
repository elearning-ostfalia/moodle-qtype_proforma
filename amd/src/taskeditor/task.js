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
 * Helper functions for reading and writen task
 *
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

import {debugLog} from '../debug';
import $ from 'jquery';
import {TaskClass, TaskFile, TaskModelSolution, TaskFileRef, TaskTest, T_VISIBLE} from "./taskdata";
import {testIDs} from "./test";
import {setErrorMessage, clearErrorMessage, generateUUID} from "./helper";
import {FileWrapper} from "./file";
import {TestWrapper} from "./test";
import {ModelSolutionWrapper} from "./modelsol";
import * as taskeditorconfig from "./config";
import {relinkFiles} from "./zipper";
import {TestFileReference, FileReferenceList, ModelSolutionFileReference} from "./filereflist";
import * as Str from 'core/str';
import Notification from 'core/notification';

export class InputError extends Error {
    constructor(message) {
        super(message);
        this.name = 'InputError';
    }
}

/**
 * Switches to the tab with the given anchor.
 *
 * @param {string} hash anchor of the tab (e.g. #tab-tests)
 */
function switchToTab(hash) {
    const tab = document.querySelector('.nav-link[href="' + hash + '"]');
    if (tab) {
        tab.click();
    }
}

/**
 * Shows a message that the test configuration is ambiguous.
 *
 * @param {Object} params placeholders for the string (title, config)
 */
function alertAmbiguousTestConfig(params) {
    Str.get_string('errtestconfigambiguous', 'qtype_proforma', params)
        .then(content => Notification.alert('', content))
        .catch(Notification.exception);
}

/**
 * Marks an input element as missing required input.
 *
 * @param {HTMLElement} elem input element
 */
function addRequired(elem) {
    debugLog('missing input for required element found');
    debugLog(elem);
    elem.focus();
    elem.classList.add('is-invalid');
    elem.classList.add('form-control');

    let sibling = document.createElement('div');
    sibling.classList.add('form-control-feedback');
    sibling.classList.add('invalid-feedback');
    elem.after(sibling);

    return Str.get_string('err_required', 'form')
        .then((string) => {
            sibling.innerHTML = string;
            return string;
        });
}

/**
 * Checks whether all required input of the task editor is available.
 *
 * Missing input is marked in the form.
 *
 * @returns {boolean} true if input is complete
 */
function isInputComplete() {
    debugLog('check input');

    // Remove all previously set required hints
    document.querySelectorAll(".proforma-taskeditor .is-invalid").forEach(item => { // Check whether filenames are provided
        item.classList.remove('is-invalid');
        item.classList.remove('form-control');
    });
    document.querySelectorAll(".proforma-taskeditor .invalid-feedback").forEach(item => { // Check whether filenames are provided
        item.remove();
    });

    let incomplete = false;
    const inputField = document.querySelector("#id_name");
    if (inputField.value.trim() === '') {
        let header = document.querySelector('a[href="#id_generalheadercontainer"]');
        if (header) {
            // Expand general header in order to make name visible
            if (header.getAttribute('aria-expanded') === "false") {
                header.click();
            }
        }

        // Mark as required because the moodle validation is not reached
        // when this function fails, but if it does not fail
        // the question name is missing for successive execution.
        addRequired(inputField);
        incomplete = true;
    }

/*
    If ((typeof $("#proforma-model-solution-section .xml_file_id")[0] === "undefined") ||      //  check for missing form values
        ModelSolutionFileReference.getInstance().getCountFilerefs() === 0) {
        // (typeof $(".xml_model-solution_fileref")[0] === "undefined")) {
        setErrorMessage("Required elements are missing. " +
            "At least one model solution element and its " +
            "corresponding file element must be provided. ");
        return false;
    }
*/


    if (document.querySelectorAll('.xml_test').length < 1) {
        Str.get_string('errmissingtest', 'qtype_proforma')
            .then(localtext => Notification.alert('', localtext))
            .catch(Notification.exception);

        return false;
    }


    // Special handling for the response filename in the question input fields.
    // If the user chose 'editor' as response type a 'response filename'
    // is required. But this input field has no rule 'required'
    // as it is not required for other types. So this cannot be required when invisible.
    // In this case the whole input is lost (TODO: autosave without checking).
    if (document.querySelector('#id_responseformat')) {
        const format = document.querySelector('#id_responseformat').value;
        debugLog(format);
        if (format === 'editor') {
            const filename = document.querySelector('#id_responsefilename');
            if (filename) {
                debugLog(filename.value);
                if (filename.value.trim() === '') {
                    addRequired(filename);
                    incomplete = true;
                }
            } else {
                console.error('cannot find response filename');
            }
        }
    } else {
        console.error('cannot find response format select');
    }


    document.querySelectorAll(".xml_file_filename").forEach(item => { // Check whether filenames are provided
        if (!item.value) {
            switchToTab('#proforma-files-section');
            addRequired(item);
            incomplete = true;
        }
    });
    document.querySelectorAll(".xml_test_title").forEach(item => { // Check whether filenames are provided
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });


    let query = "#proforma-model-solution-section .xml_fileref_filename";
    document.querySelectorAll(query).forEach(item => { // Check whether referenced filenames exists
        if (!item.value) {
            switchToTab('#proforma-model-solution-section');
            addRequired(item);
            incomplete = true;
        }
    });

    // Check whether referenced filenames exists
    document.querySelectorAll('#proforma-tests-section .xml_test_weight').forEach(item => {
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });

    query = "#proforma-tests-section .xml_fileref_filename";
    document.querySelectorAll(query).forEach(item => { // Check whether referenced filenames exists
        if (!item.value) {
            // Only mandatory file lists (required icon in the label, see taskeditor_fileref_row).
            // Further rows contain a hidden copy of the label of the first row.
            let label = item.closest('tr').querySelector('label');
            if (label && label.querySelector('.proforma-required')) {
                switchToTab('#proforma-tests-section');
                addRequired(item);
                incomplete = true;
            }
        }
    });

    // Todo: this should be part of the configuration
    $.each($(".xml_entry_point"), function(index, item) { // Check whether main-class exists
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });
    document.querySelectorAll(".xml_pr_CS_warnings").forEach(item => {
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });

    if (incomplete) {
        return false;
    }

    let sumweight = 0.0;
    // Check whether referenced filenames exists
    document.querySelectorAll('#proforma-tests-section .xml_test_weight').forEach(item => {
        sumweight += parseFloat(item.value);
    });
    debugLog('sumweight = ' + sumweight);
    if (sumweight <= 0) {
        Str.get_string('sumweightzero', 'qtype_proforma')
            .then(content => {
                switchToTab('#proforma-tests-section');
                return Notification.alert('', content);
            })
            .catch(Notification.exception);
        incomplete = true;
    }


    return (!incomplete);
}


// On document ready...:

// /////////////////////////////////////////////////////// function: convertToXML


/**
 * writes data from UI elements to xml string
 */
export function convertToXML() {
    // Fake promise in order to be used in chain
    const promise = Promise.resolve(0);
    return promise.then(() => {
        const t0 = performance.now();
        clearErrorMessage();
        let taskXml;

        // Check input
        if (!isInputComplete()) {
            // With Promise reject the special type of error is not detected in the catch clause.
            // So a normal throw without reject is used here.
            // return Promise.reject(new InputError('invalid input => cannot create task.xml'));
            debugLog('input is incomplete => cannot create task.xml');
            throw new InputError('invalid input => cannot create task.xml');
            // Return null;
        }
        debugLog('input is ok => create task.xml');

        // PRE PROCESSING
        // copy data to task class
        let task = new TaskClass();
        task.title = $("#id_name").val();
        task.comment = '';
        task.description = $("#id_questiontext").val();
        task.proglang = $('#xml_programming-language').val();
        task.proglang = task.proglang.trim();
        task.proglangVersion = $("#xml_programming-language-" + task.proglang).val();
        task.parentuuid = null;
        // Task.uuid = $("#xml_uuid").val();
        // if (!task.uuid)
        task.uuid = generateUUID();
        task.lang = 'en'; // $("#xml_lang").val();
        task.sizeSubmission = 0; // $("#xml_submission_size").val();
        task.filenameRegExpSubmission = ''; // $(".xml_restrict_filename").first().val();


        /*
        task.title = $("#xml_title").val();
        task.comment = $("#xml_task_internal_description").find('.xml_internal_description').val();
        task.description = descriptionEditor.getValue();
        task.lang = $("#xml_lang").val();
        task.sizeSubmission = $("#xml_submission_size").val();
        task.filenameRegExpSubmission = $(".xml_restrict_filename").first().val();
         */

        // write files
        FileWrapper.doOnAllFiles(function(uiFile) {
            let taskfile = new TaskFile();
            taskfile.filename = uiFile.filename;
            taskfile.fileclass = uiFile.class;
            taskfile.id = uiFile.id;
            taskfile.filetype = uiFile.type;
            taskfile.comment = uiFile.comment;
            taskfile.content = uiFile.text;
            task.files[taskfile.id] = taskfile;
        });

        // Write model solutions
        ModelSolutionWrapper.doOnAll(function(ms) {
            let modelSolution = new TaskModelSolution();
            modelSolution.id = ms.id;
            modelSolution.comment = ms.comment;
            modelSolution.description = ms.description;
            let counter = 0;
            ModelSolutionFileReference.getInstance().doOnAll(function(id) {
                modelSolution.filerefs[counter++] = new TaskFileRef(id);
                task.files[id].visible = T_VISIBLE.DELAYED;
            }, ms.root);

            // ReadFileRefs(xmlReader, modelSolution, thisNode);
            task.modelsolutions[modelSolution.id] = modelSolution;
        });

        // Write tests
        TestWrapper.doOnAll(function(uiTest, index) {
            let test = new TaskTest();
            test.id = uiTest.id;
            test.title = uiTest.title;
            test.testtype = uiTest.testtype;
            test.comment = uiTest.comment;
            test.description = uiTest.description;
            test.weight = uiTest.weight;
            test.framework = uiTest.framework;

            let counter = 0;
            // TODO: geht über alle Test-Filerefs, sollte er nur über die
            // des entsprechenden Tests gehen?
            TestFileReference.getInstance().doOnAll(function(id) {
                if (id) {
                    test.filerefs[counter++] = new TaskFileRef(id);
                    task.files[id].usedByGrader = true;
                }
            }, uiTest.root);

            debugLog('*** look for test config');
            debugLog(test);
            $.each(taskeditorconfig.testInfos, function(index, configItem) {
                // Search for appropriate configuration instance
                if (!configItem.matches(test, task.proglang)) {
                    return;
                }

                debugLog(configItem);
                if (test.configItem !== undefined && test.configItem !== configItem) {
                    // Configuration already found
                    let params = {
                        'title': test.title,
                        'config': test.configItem.title
                    };
                    alertAmbiguousTestConfig(params);
                    return;
                }

                test.configItem = configItem;
                test.uiElement = uiTest;
            });
            debugLog('*** config lookup complete');
            if (test.configItem === undefined) {
                Notification.alert('', 'cannot determine test configuration for test "' + test.title + '"');
            }

            // ReadFileRefs(xmlReader, modelSolution, thisNode);
            // note that the test element is stored at the index position not at the test id position
            // (in order to keep the sort order from user interface)
            task.tests[index] = test;
        });

        /*
            SubmissionFileList.doOnAll(function(filename, regexp, optional) {
                let restrict = new TaskFileRestriction(filename, !optional, regexp?T_FILERESTRICTION_FORMAT.POSIX:null);
                task.fileRestrictions.push(restrict);
            });
        */
        /*
        if (USE_VISIBLES) {
            VisibleFileReference.getInstance().doOnAllIds(function(id, displayMode) {
                task.files[id].visible = T_VISIBLE.YES;
                task.files[id].usageInLms = displayMode;
            });
        } else {
            DownloadableFileReference.getInstance().doOnNonEmpty(function(id) {
                task.files[id].visible = T_VISIBLE.YES;
                task.files[id].usageInLms = T_LMS_USAGE.DOWNLOAD;
            });
            task.codeskeleton = codeskeleton.getValue();
        }*/


        taskXml = task.writeXml();
        const t1 = performance.now();
        debugLog("Call to convertToXML took " + (t1 - t0) + " milliseconds.");
        debugLog('Size of task is ' + taskXml.length);
        return taskXml;
    });
}


/**
 * Reads the task XML and creates the task editor content.
 *
 * @param {string} taskXml task.xml content
 * @returns {Promise}
 */
export async function readAndDisplayXml(taskXml) {
    let task = new TaskClass();

    /**
     * Creates the model solution in the task editor.
     *
     * @param {Object} item model solution
     * @returns {Promise}
     */
    function createMs(item) {
        return ModelSolutionWrapper.createFromTemplate(item.id, item.description, item.comment, item, task);
    }

    /**
     * Creates the file in the task editor.
     *
     * @param {Object} item task file
     * @returns {Promise}
     */
    function createFile(item) {
        // Let uiFile = FileWrapper.create(item.id);
        return FileWrapper.createFromTemplate(item.id)
            .then(uiFile => {
                uiFile.filename = item.filename;
                uiFile.class = item.fileclass;
                uiFile.type = item.filetype;
                uiFile.comment = item.comment;
                if (uiFile.type === 'embedded') {
                    uiFile.text = item.content;
                }
                if (item.id) {
                    relinkFiles();
                }
                return uiFile;
            });
    }

    /**
     * Creates the test in the task editor (using the matching test configuration).
     *
     * @param {Object} item test
     * @returns {Object|null} test wrapper resp. null if no configuration matches
     */
    function createTest(item) {
        testIDs[item.id] = 1;

        let uiTest;
        let theConfigitem;
        debugLog('iterate through all configured test templates, look for ' + item.testtype);
        $.each(taskeditorconfig.testInfos, function(index, configItem) {
            if (!configItem.matches(item, task.proglang)) {
                return;
            }
            if (uiTest) {
                let params = {
                    'title': item.title,
                    'config': theConfigitem.title
                };
                alertAmbiguousTestConfig(params);
                return;
            }
            debugLog('found ' + configItem.title);
            let context = configItem.getTemplateContext();
            context.testtitle = item.title;
            if (item.weight) {
                context.weight = item.weight;
            }
            context.description = item.description;
            context.comment = item.comment;

            task.readTestConfig(taskXml, item.id, configItem, context);

            theConfigitem = configItem;
            uiTest = TestWrapper.createFromTemplate(item.id,
                configItem.getMustacheTemplate(), context, true, item, task);
        });

        if (!uiTest) {
            setErrorMessage("Test '" + item.title + "' not imported, testtype and framework unsupported");
            testIDs[item.id] = 0;
            return null;
        } else {
            return uiTest;
        }
    }
/*
    Function createFileRestriction(item, index) {
        if (index > 0) {
            // create new row
            SubmissionFileList.getInstance().appendRow();
        }

        SubmissionFileList.getInstance().setLastRowContent(item.restriction, !item.required,
            item.format===T_FILERESTRICTION_FORMAT.POSIX);
    }
*/
    if (taskXml.length === 0) {
        setErrorMessage("Task.xml is empty.");
        return undefined;
    }

    /*
    Const templateroot = $("#templatedropzone");
    const multmediaroot = $("#multimediadropzone");
    const downloadroot = $("#downloaddropzone");
    const visibleroot = $("#visiblefiledropzone");
*/
    // TODO: check version
    // TODO: validate??
    task.readXml(taskXml);

/*
    $("#xml_task_internal_description").find('.xml_internal_description').val(task.comment);
    $("#xml_uuid").val(task.uuid);
    $("#xml_submission_size").val(task.sizeSubmission);
    $("#xml_restrict_filename").val(task.filenameRegExpSubmission);
 */

    let proglangElement = $("#xml_programming-language");
    proglangElement.val(task.proglang.toLowerCase());
    proglangElement.trigger('change');
    proglangElement.prop("disabled", true);
    let versionElement = document.getElementById("xml_programming-language-" + task.proglang.toLowerCase());
    if (!versionElement) {
        console.error('cannot find element #xml_programming-language-' + task.proglang.toLowerCase());
    } else if (versionElement.options.length > 0) {
        // If version element contains options then check version
        versionElement.value = task.proglangVersion;
        const noVersion = task.proglangVersion === undefined ||
            task.proglangVersion === null ||
            task.proglangVersion.trim() === '';
        if (versionElement.value !== task.proglangVersion && noVersion &&
            task.proglang.toLowerCase() === 'python') {
            // Set programming version to 3
            task.proglangVersion = '3';
        }
        if (versionElement.value !== task.proglangVersion) {
            Str.get_string('invalidproglang', 'qtype_proforma')
                .then(content => Notification.alert('', content + ' ' + task.proglangVersion))
                .catch(Notification.exception);
        }
    }

    let filepromises = [];
    let refpromises = [];
    task.files.forEach(file => {
        filepromises.push(createFile(file));
    });
    return Promise.all(filepromises)
        .then(() => {
            debugLog('** all files are created => create tests');
            task.tests.forEach(item =>
                refpromises.push(createTest(item))
            );
            debugLog('=> create model solution(s)');
            task.modelsolutions.forEach(item =>
                refpromises.push(createMs(item))
            );

            // Fill filename lists in empty file refences
            debugLog('=> wait');
            return Promise.all(refpromises);
        })
        .then(() => {
            debugLog('** all tests and model sols are created => add referenced files');
            FileReferenceList.updateAllFilenameLists();
            debugLog('=> finished');
            return true;
        });
//        .fail(Notification.exception);


    // task.fileRestrictions.forEach(createFileRestriction);

    // POST PROCESSING

    // special handling for visisble files:
    /*
    // add dummy file references
    let indexTemplate = 0;
    let indexDownload = 0;
    let indexMultmedia = 0;
    let indexVisible = 0;

    task.files.forEach(function(item) {
        if (item.visible === T_VISIBLE.YES) {
            if (USE_VISIBLES) {
                VisibleFileReference.getInstance().setFilenameOnCreation(visibleroot, indexVisible, item.filename);
                VisibleFileReference.getInstance().setDisplayMode(visibleroot, indexVisible++, item.usageInLms);
            } else {
                switch (item.usageInLms) {
                    case T_LMS_USAGE.EDIT:
                        //alert('??? hier sollte man nicht hinkommen');
                        if (indexTemplate === 0) {
                            codeskeleton.setValue(item.content);
                            indexTemplate++;
                            //$("#code_template").val('Hier kommt der Code rein');
                        } else
                            DownloadableFileReference.getInstance().setFilenameOnCreation(
                                downloadroot, indexDownload++, item.filename);
// TemplateFileReference.getInstance().setFilenameOnCreation(templateroot, indexTemplate++, item.filename);
                        break;
                    case T_LMS_USAGE.DISPLAY:
                        // create as download file
// MultimediaFileReference.getInstance().setFilenameOnCreation(multmediaroot, indexMultmedia++, item.filename);
//                        break;
                    case T_LMS_USAGE.DOWNLOAD:
                        DownloadableFileReference.getInstance().setFilenameOnCreation(downloadroot, indexDownload++, item.filename);
                        break;
                }
            }
        }
    });

     */

    // fill filename lists in empty file refences
    // FileReferenceList.updateAllFilenameLists();
}
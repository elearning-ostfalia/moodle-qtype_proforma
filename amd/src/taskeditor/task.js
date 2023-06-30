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
 * Helper functions for reading and writen task
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

import $ from 'jquery';
import {TaskClass, TaskFile, TaskModelSolution, TaskFileRef, TaskTest, T_VISIBLE} from "./taskdata";
import {testIDs} from "./test";
import {setErrorMessage, clearErrorMessage, generateUUID} from "./util";
import {FileWrapper} from "./file";
import {TestWrapper } from "./test";
import {ModelSolutionWrapper } from "./modelsol";
import * as taskeditorconfig from "./config";
import {relinkFiles} from "./zipper";
import {TestFileReference, FileReferenceList,ModelSolutionFileReference } from "./filereflist";
import * as Str from 'core/str';
import {readXMLWithLock} from "./helper";
import Notification, {exception as displayException} from 'core/notification';


export class InputError extends Error {
    constructor(message) {
        super(message);
        this.name = 'InputError';
    }
}

function switchToTab(hash) {
    const tab = document.querySelector('.nav-link[href="' + hash + '"]');
    if (tab) {
        tab.click();
    }
}

function addRequired(elem) {
    console.log('missing input for required element found');
    console.log(elem);
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
        });
}

function isInputComplete() {
    console.log('check input');

    // Remove all previously set required hints
    document.querySelectorAll(".proforma-taskeditor .is-invalid").forEach(item => {  // check whether filenames are provided
        item.classList.remove('is-invalid');
        item.classList.remove('form-control');
    });
    document.querySelectorAll(".proforma-taskeditor .invalid-feedback").forEach(item => {  // check whether filenames are provided
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
    if ((typeof $("#proforma-model-solution-section .xml_file_id")[0] === "undefined") ||      //  check for missing form values
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
            .then(localtext => {
                alert(localtext);
            });

        return false;
    }


    // Special handling for the response filename in the question input fields.
    // If the user chose 'editor' as response type a 'response filename'
    // is required. But this input field has no rule 'required'
    // as it is not required for other types. So this cannot be required when invisible.
    // In this case the whole input is lost (TODO: autosave without checking).
    if (document.querySelector('#id_responseformat')) {
        const format = document.querySelector('#id_responseformat').value;
        console.log(format);
        if (format === 'editor') {
            const filename = document.querySelector('#id_responsefilename');
            if (filename) {
                console.log(filename.value);
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


    document.querySelectorAll(".xml_file_filename").forEach(item => {  // check whether filenames are provided
        if (!item.value) {
            switchToTab('#proforma-files-section');
            addRequired(item);
            incomplete = true;
        }
    });
    document.querySelectorAll(".xml_test_title").forEach(item => {  // check whether filenames are provided
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });

    document.querySelectorAll(".xml_pr_CS_warnings").forEach(item => {
        // console.log(item.value);
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });

    let query = "#proforma-model-solution-section .xml_fileref_filename";
    document.querySelectorAll(query).forEach(item => {  // check whether referenced filenames exists
        if (!item.value) {
            console.log('filename in model solution is missing');
            switchToTab('#proforma-model-solution-section');
            addRequired(item);
            incomplete = true;
        }
    });

    document.querySelectorAll('#proforma-tests-section .xml_test_weight').forEach(item => {  // check whether referenced filenames exists
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });

    query = "#proforma-tests-section .xml_fileref_filename";
    document.querySelectorAll(query).forEach(item => {   // check whether referenced filenames exists
        if (!item.value) {
            let label = item.closest('tr').querySelector('label');
            if (label.querySelectorAll('.red').length !== 0) {
                switchToTab('#proforma-tests-section');
                addRequired(item);
                incomplete = true;
            }
        }
    });

    // todo: this should be part of the configuration
    $.each($(".xml_ju_mainclass"), function(index, item) {   // check whether main-class exists
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            incomplete = true;
        }
    });
    $.each($(".xml_u_mainclass"), function(index, item) {   // check whether main-class exists
        if (!item.value) {
            switchToTab('#proforma-tests-section');
            addRequired(item);
            // setErrorMessage("Run command is missing.");
            incomplete = true;
        }
    });

    if (incomplete)
        return false;

    let sumweight = 0.0;
    document.querySelectorAll('#proforma-tests-section .xml_test_weight').forEach(item => {  // check whether referenced filenames exists
        // console.log(item.value);
        sumweight += parseFloat(item.value);
    });
    console.log('sumweight = ' + sumweight);
    if (sumweight <= 0) {
        Str.get_string('sumweightzero', 'qtype_proforma')
            .then(content => {
                switchToTab('#proforma-tests-section');
                alert(content);
            });
        incomplete = true;
    }

    // console.log('result');
    // console.log(!incomplete);

    return (!incomplete);
}


// on document ready...:

///////////////////////////////////////////////////////// function: convertToXML





/**
 * writes data from UI elements to xml string
 */
export function convertToXML() {
    // Fake promise in order to be used in chain
    const promise = Promise.resolve(0);
    return promise.then(() => {
        const t0 = performance.now();
        clearErrorMessage();
        let taskXml = undefined;

        // check input
        if (!isInputComplete()) {
            // With Promise reject the special type of error is not detected in the catch clause.
            // So a normal throw without reject is used here.
            // return Promise.reject(new InputError('invalid input => cannot create task.xml'));
            console.log('input is incomplete => cannot create task.xml');
            throw new InputError('invalid input => cannot create task.xml');
            // return null;
        }
        console.log('input is ok => create task.xml');

        // PRE PROCESSING
        // copy data to task class
        let task = new TaskClass();
        task.title = $("#id_name").val();
        task.comment = '';
        task.description = $("#id_questiontexteditable").val();
        task.proglang = $('#xml_programming-language').val();
        task.proglang = task.proglang.trim();
        // console.log('READ FROM UI: ' + task.proglang);
        task.proglangVersion = $("#xml_programming-language-" + task.proglang).val();
        // console.log('READ FROM UI VERSION: ' + task.proglangVersion);
        task.parentuuid = null;
        //task.uuid = $("#xml_uuid").val();
        //if (!task.uuid)
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
        FileWrapper.doOnAllFiles(function(ui_file) {
            let taskfile = new TaskFile();
            taskfile.filename = ui_file.filename;
            taskfile.fileclass = ui_file.class;
            taskfile.id = ui_file.id;
            taskfile.filetype = ui_file.type;
            taskfile.comment = ui_file.comment;
            taskfile.content = ui_file.text;
            task.files[taskfile.id] = taskfile;
        });

        // write model solutions
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

            //readFileRefs(xmlReader, modelSolution, thisNode);
            task.modelsolutions[modelSolution.id] = modelSolution;
        })

        // write tests
        TestWrapper.doOnAll(function(uiTest, index) {
            let test = new TaskTest();
            test.id = uiTest.id;
            test.title = uiTest.title;
            test.testtype = uiTest.testtype;
            test.comment = uiTest.comment;
            test.description = uiTest.description;
            test.weight = uiTest.weight;

            let counter = 0;
            // TODO: geht über alle Test-Filerefs, sollte er nur über die
            // des entsprechenden Tests gehen?
            TestFileReference.getInstance().doOnAll(function(id) {
                if (id) {
                    test.filerefs[counter++] = new TaskFileRef(id);
                    console.log("Test ID" + id);
                    task.files[id].usedByGrader = true;
                }
            }, uiTest.root);

            console.log('*** look for test config');
            console.log(test);
            $.each(taskeditorconfig.testInfos, function(index, configItem) {
                // search for appropriate writexml function

                if (configItem.testType !== test.testtype) {
                    // testtype does not match
                    return;
                }
                // console.log('testtype match');
                // console.log(configItem);
                if (configItem.proglang !== undefined) {
                    // console.log('check proglang');
                    // console.log(task.proglang);
                    if (!configItem.proglang.includes(task.proglang)) {
                        // console.log('proglang does not match');
                        // Language does not match
                        return;
                    }
                }

                // console.log('everything matches');
                console.log(configItem);
                if (test.configItem !== undefined && test.configItem !== configItem) {
                    // configuration already found
                    let params = {
                        'title': test.title,
                        'config': test.configItem.title
                    };
                    Str.get_string('errtestconfigambiguous', 'qtype_proforma', params)
                        .then(content => alert(content));
/*                    console.log('Warning: test configuration for test "' + test.title + '" is not unique. \n' +
                        'Assume ' + test.configItem.title + ',\n' +
                        'but ' + configItem.title + ' is also matching.');*/
                    return;
                }

                test.configItem = configItem;
                test.uiElement = uiTest;
            });
            console.log('*** config lookup complete');
            if (test.configItem === undefined) {
                alert('cannot determine test configuration for test "' + test.title + '"');
            }

            //readFileRefs(xmlReader, modelSolution, thisNode);
            //console.log('convertToXML: create ' + test.title);
            // note that the test element is stored at the index position not at the test id position
            // (in order to keep the sort order from user interface)
            task.tests[index] = test;
        })

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
        console.log("Call to convertToXML took " + (t1 - t0) + " milliseconds.")
        console.log('Size of task is ' + taskXml.length);
        return taskXml;
    });
}


export async function readAndDisplayXml(taskXml) {
    // console.log(taskXml);
    let task = new TaskClass();

    function createMs(item, index) {
        return ModelSolutionWrapper.createFromTemplate(item.id, item.description, item.comment, item, task);
    }

    function createFile(item, index) {
        // let ui_file = FileWrapper.create(item.id);
        return FileWrapper.createFromTemplate(item.id)
            .then(ui_file => {
                // console.log('fileform ' + item.id + ' has been created');
                ui_file.filename = item.filename;
                ui_file.class = item.fileclass;
                ui_file.type = item.filetype;
                ui_file.comment = item.comment;
                if (ui_file.type === 'embedded')
                    ui_file.text = item.content;
                if (item.id) {
                    relinkFiles();
                }
                return ui_file;
            });
    }

    function createTest(item, index) {
        testIDs[item.id] = 1;

        let ui_test;
        let the_configitem;
        console.log('iterate through all configured test templates, look for ' + item.testtype);
        $.each(taskeditorconfig.testInfos, function(index, configItem) {
            // console.log(configItem);
            if (item.testtype === configItem.testType) {
                // Check if proglang is set in configured test. If true then compare
                // Check Programming language
                if (configItem.proglang !== undefined) {
                    if (!configItem.proglang.includes(task.proglang)) {
                        // Language does not match
                        // console.log('language does not match');
                        return;
                    }
                }
                // Distinguish between CUnit and Google test with C++
                if (configItem.frameworks !== undefined && item.framework !== undefined) {
                    if (!configItem.frameworks.includes(item.framework.toLowerCase())) {
                        console.log(item.framework + ' is not in ')
                        console.log(configItem.frameworks);
                        // Framework does not match
                        return;
                    }
                }
                if (ui_test) {
                    let params = {
                        'title': item.title,
                        'config': the_configitem.title
                    };
/*                    console.log('Warning: test configuration for test "' + item.title + '" is not unique. \n' +
                        'Assume ' + the_configitem.title + ',\n' +
                        'but ' + configItem.title + ' is also matching.');*/
                    Str.get_string('errtestconfigambiguous', 'qtype_proforma', params)
                        .then(content => alert(content));
                    return null;
                }
                console.log('found ' + configItem.title);
                let context = configItem.getTemplateContext();
                context['testtitle'] = item.title;
                if (item.weight) {
                    context['weight'] = item.weight;
                }
                context['description'] = item.description;
                context['comment'] = item.comment;

                task.readTestConfig(taskXml, item.id, configItem, context);
                // console.log('context for test template ');
                // console.log(context);

                the_configitem = configItem;
                ui_test = TestWrapper.createFromTemplate(item.id,
                    configItem.getMustacheTemplate(), context, true, item, task);
            }
        });


/*
        if (!ui_test) {
            // try alternative test types
            $.each(taskeditorconfig.testInfos, function(index, configItem) {
                $.each(configItem.alternativeTesttypes, function(index, alternative) {
                    if (!ui_test && item.testtype === alternative) {
                        ui_test = TestWrapper.create(item.id, item.title, configItem, item.weight);
                        task.readTestConfig(taskXml, item.id, configItem, ui_test.root);
                        ui_test.comment = item.comment;
                        ui_test.description = item.description;
                    }
                });
            });
        }
*/

        if (!ui_test) {
            setErrorMessage("Test '" + item.title + "' not imported, testtype and framework unsupported");
            testIDs[item.id] = 0;
            return null;
        } else {
            return ui_test;
        }
    }
/*
    function createFileRestriction(item, index) {
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
        return;
    }

    const templateroot = $("#templatedropzone");
    const multmediaroot = $("#multimediadropzone");
    const downloadroot = $("#downloaddropzone");
    const visibleroot = $("#visiblefiledropzone");

    // TODO: check version
    // TODO: validate??
    task.readXml(taskXml);

/*
    $("#xml_task_internal_description").find('.xml_internal_description').val(task.comment);
    $("#xml_uuid").val(task.uuid);
    $("#xml_submission_size").val(task.sizeSubmission);
    $("#xml_restrict_filename").val(task.filenameRegExpSubmission);
 */

    console.log(task.proglang);
    console.log(task.proglangVersion);
    let proglangElement = $("#xml_programming-language");
    proglangElement.val(task.proglang.toLowerCase());
    proglangElement.trigger('change');
    proglangElement.prop( "disabled", true );
    let versionElement = document.getElementById("xml_programming-language-" + task.proglang.toLowerCase());
    if (!versionElement) {
        console.error('cannot find element #xml_programming-language-' + task.proglang.toLowerCase());
    } else {
        if (versionElement.options.length > 0) {
            // If version element contains options then check version
            versionElement.value = task.proglangVersion;
            if (versionElement.value !== task.proglangVersion) {
                if (task.proglangVersion === undefined ||
                    task.proglangVersion === null ||
                    task.proglangVersion.trim() === ''
                ) {
                    switch (task.proglang.toLowerCase()) {
                        case 'python':
                            // Set programming version to 3
                            task.proglangVersion = '3';
                            break;
                    }
                }
            }
            if (versionElement.value !== task.proglangVersion) {
                Str.get_string('invalidproglang', 'qtype_proforma')
                    .then(content => alert(content + ' ' + task.proglangVersion));
            }
        }
    }

    let filepromises = [];
    let refpromises = [];
    task.files.forEach(file => {
        filepromises.push(createFile(file));
    });
    return Promise.all(filepromises)
        .then(() => {
            console.log('** all files are created => create tests');
            task.tests.forEach(item =>
                refpromises.push(createTest(item))
            );
            console.log('=> create model solution(s)');
            task.modelsolutions.forEach(item =>
                refpromises.push(createMs(item))
            );

            // fill filename lists in empty file refences
            console.log('=> wait');
            return Promise.all(refpromises);
        })
        .then(() => {
            console.log('** all tests and model sols are created => add referenced files');
            FileReferenceList.updateAllFilenameLists();
            console.log('=> finished');
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
                            DownloadableFileReference.getInstance().setFilenameOnCreation(downloadroot, indexDownload++, item.filename);
//                            TemplateFileReference.getInstance().setFilenameOnCreation(templateroot, indexTemplate++, item.filename);
                        break;
                    case T_LMS_USAGE.DISPLAY:
                        // create as download file
//                        MultimediaFileReference.getInstance().setFilenameOnCreation(multmediaroot, indexMultmedia++, item.filename);
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
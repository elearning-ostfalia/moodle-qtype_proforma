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
 * @copyright 2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @author   Karin Borm <k.borm@ostfalia.de>
 */

import $ from 'jquery';
import {TaskClass} from "./taskeditortaskdata";
import {testIDs} from "./taskeditortest";
import {FileWrapper} from "./taskeditorfile";
import {setErrorMessage, clearErrorMessage} from "./taskeditorutil";
import {TestWrapper } from "./taskeditortest";
import {TestFileReference, FileReferenceList,ModelSolutionFileReference } from "./filereflist";



function isInputComplete() {
    let inputField = $("#xml_description");
    if (!inputField.val()) {
        setErrorMessage("Task description is empty.");
        // switch to appropriate tab and set focus
        $("#tabs").tabs("option", "active",  tab_page.MAIN);
        inputField.focus();
        return false;
    }

    inputField = $("#xml_title");
    if (!inputField.val()) {
        setErrorMessage("Task title is empty.");
        // switch to appropriate tab and set focus
        $("#tabs").tabs("option", "active",  tab_page.MAIN);
        inputField.focus();
        return false;
    }

    if ((typeof $(".xml_file_id")[0] === "undefined") ||      //  check for missing form values
        ModelSolutionFileReference.getInstance().getCountFilerefs() === 0) {
        // (typeof $(".xml_model-solution_fileref")[0] === "undefined")) {
        setErrorMessage("Required elements are missing. " +
            "At least one model solution element and its " +
            "corresponding file element must be provided. ");
        return false;
    }

    let returnFromFunction = false;
    $.each($(".xml_file_filename"), function(index, item) {  // check whether filenames are provided
        if (!item.value) {
            setErrorMessage("Filename is empty.");
            $("#tabs").tabs("option", "active",  tab_page.FILES);
            item.focus();
            returnFromFunction = true;
        }
    });
    if (returnFromFunction)
        return false;

    $.each($("." + ModelSolutionFileReference.getInstance().getClassFilename()), function(index, item) {   // check whether referenced filenames exists
        if (!item.value) {
            $("#tabs").tabs("option", "active",  tab_page.MODEL_SOLUTION);
            setErrorMessage("Filename in model solution is missing.");
            item.focus();
            returnFromFunction = true;
        }
    });
    if (returnFromFunction)
        return false;



    $.each($("." + TestFileReference.getInstance().getClassFilename()), function(index, item) {   // check whether referenced filenames exists
        // check if file is optional or mandatory
        let mandatory = false;
        if (true) { //$(item).is(":visible") ) {
            // search label
            let label = $(item).closest('tr').find('label').first();
            
            if (label.find('.red').length > 0)
                mandatory = true;
        }

        if (mandatory && !item.value) {
            $("#tabs").tabs("option", "active",  tab_page.TESTS);

            //let title = $(item).closest('h3').first();

            setErrorMessage("Filename in test is missing.");
            item.focus();
            returnFromFunction = true;
        }
    });
    if (returnFromFunction)
        return false;

    // todo: this should be part of the configuration
    $.each($(".xml_ju_mainclass"), function(index, item) {   // check whether main-class exists
        if (!item.value) {
            $("#tabs").tabs("option", "active",  tab_page.TESTS);
            setErrorMessage("Entry point is missing.");
            item.focus();
            returnFromFunction = true;
        }
    });
    $.each($(".xml_u_mainclass"), function(index, item) {   // check whether main-class exists
        if (!item.value) {
            $("#tabs").tabs("option", "active",  tab_page.TESTS);
            setErrorMessage("Run command is missing.");
            item.focus();
            returnFromFunction = true;
        }
    });

    if (returnFromFunction)
        return false;

    return true;
}


// on document ready...:

///////////////////////////////////////////////////////// function: convertToXML


/**
 * writes data from UI elements to xml string
 */
export function convertToXML(topLevelDoc, rootNode) {

    const t0 = performance.now();
    clearErrorMessage();
    let taskXml = undefined;
    descriptionEditor.save();

    // check input
    if (!isInputComplete()) {
        return;
    }

    // PRE PROCESSING
    // copy data to task class
    let task = new TaskClass();
    task.title = $("#xml_title").val();
    task.comment = $("#xml_task_internal_description").find('.xml_internal_description').val();
    task.description = descriptionEditor.getValue();
    let proglangAndVersion = $("#xml_programming-language").val();
    let proglangSplit = proglangAndVersion.split("/");

    task.proglang = proglangSplit[0]; // proglangAndVersion.substr(0, proglangAndVersion.indexOf("/"));
    if (proglangSplit.length > 1)
        task.proglangVersion = proglangSplit[1]; // proglangAndVersion.substr(proglangAndVersion.indexOf("/")+1);
    else
        task.proglangVersion = '';

    task.parentuuid = null;
    //task.uuid = $("#xml_uuid").val();
    //if (!task.uuid)
        task.uuid = generateUUID();
    task.lang = $("#xml_lang").val();
    task.sizeSubmission = $("#xml_submission_size").val();
    task.filenameRegExpSubmission = $(".xml_restrict_filename").first().val();

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

        $.each(config.testInfos, function(index, configItem) {
            // search for appropriate writexml function
            if (configItem.testType === test.testtype) {
                if (configItem.proglang !== undefined) {
                    if (!configItem.proglang.includes(task.proglang)) {
                        // Language does not match
                        return;
                    }
                }

                test.configItem = configItem;
                test.uiElement = uiTest;
            }
        });


        //readFileRefs(xmlReader, modelSolution, thisNode);
        //console.log('convertToXML: create ' + test.title);
        // note that the test element is stored at the index position not at the test id position
        // (in order to keep the sort order from user interface)
        task.tests[index] = test;
    })


    SubmissionFileList.doOnAll(function(filename, regexp, optional) {
        let restrict = new TaskFileRestriction(filename, !optional, regexp?T_FILERESTRICTION_FORMAT.POSIX:null);
        task.fileRestrictions.push(restrict);
    });

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
    }


    taskXml = task.writeXml(topLevelDoc, rootNode);
    const t1 = performance.now();
    console.log("Call to convertToXML took " + (t1 - t0) + " milliseconds.")
    return taskXml;
}

/*
function resetInputFields() {
    console.log('Reset all input fields?? Not actually needed in Moodle');
    return;
/////TODO     gradingHintCounter = 1;                            // variable initialisation

    clearErrorMessage();
    FileWrapper.deleteAllFiles();

    $("#modelsolutionsection")[0].textContent = "";
    $("#testsection")[0].textContent = "";
    $("#files_restriction")[0].textContent = "";
    $("#files_restriction").append(SubmissionFileList.getInstance().getTableString());

    codeskeleton.setValue('');

    // initialise other sections
    if (USE_VISIBLES) FileReferenceList.init("#visiblefiledropzone", '#visiblesection', VisibleFileReference);

    //FileReferenceList.init("#multimediadropzone", '#multimediasection', MultimediaFileReference);
    FileReferenceList.init("#downloaddropzone", '#downloadsection', DownloadableFileReference);
    //FileReferenceList.init("#templatedropzone", '#templatesection', TemplateFileReference);
///// TODO    modelSolIDs = {};
    testIDs = {};
}

 */

export function readAndDisplayXml(taskXml) {
    let task = new TaskClass();

    function createMs(item, index) {
        let ms = ModelSolutionWrapper.create(item.id, item.description, item.comment);
        let counter = 0;
        item.filerefs.forEach(function(itemFileref, indexFileref) {
            let filename = task.findFilenameForId(itemFileref.refid);
            ModelSolutionFileReference.getInstance().setFilenameOnCreation(ms.root, counter++, filename);
        });
    }

    function createFile(item, index) {
        let ui_file = FileWrapper.create(item.id);
        //let ui_file = FileWrapper.constructFromId(item.id);
        ui_file.filename = item.filename;
        ui_file.class = item.fileclass;
        ui_file.type = item.filetype;
        ui_file.comment = item.comment;
        if (ui_file.type === 'embedded')
            ui_file.text = item.content;
    }

    function createTest(item, index) {
        testIDs[item.id] = 1;

        let ui_test = undefined;
        console.log(item.testtype);

        let context = {

        };
/*        console.log('found ' + configItem.title);
        ui_test = TestWrapper.create(item.id, item.title, configItem, item.weight);
        task.readTestConfig(taskXml, item.id, configItem, ui_test.root);
        ui_test.comment = item.comment;
        ui_test.description = item.description;
*/
        switch(task.proglang.toLowerCase()) {
            case 'python':
                switch(item.testtype) {
                    case 'unittest':
                        context['testname'] = 'Python UnitTest';
                        context['filenamelabel'] = 'Python unittest file(s)';
                        console.log('create Python UnitTest');
                        ui_test = TestWrapper.createFromTemplate(item.id,
                            'qtype_proforma/taskeditor_pythonunit', context, true);
                        break;
                    default:
                        console.error('unsupported test type: ' + item.testtype);
                        break;
                }
                break;
            case 'java':
                switch(item.testtype) {
                    case 'unittest':
                        console.log('create JUnitTest');
                        context['testname'] = 'JUnit Test';
                        context['filenamelabel'] = 'Junit and other file(s)';
                        ui_test = TestWrapper.createFromTemplate(item.id,
                            'qtype_proforma/taskeditor_junit', context, true);
                        break;
                    case 'checkstyle':
                        console.log('create Checkstyle');
                        context['testname'] = 'Checkstyle Test';
                        context['filenamelabel'] = 'Configuration file';
                        ui_test = TestWrapper.createFromTemplate(item.id,
                            'qtype_proforma/taskeditor_checkstyle', context, true);
                        break;
                    default:
                        console.error('unsupported test type: ' + item.testtype);
                        break;
                }
                break;
            case 'c++':
                console.error('unsupported test type: ' + item.testtype);
                break;
            case 'c':
                console.error('unsupported test type: ' + item.testtype);
                break;
            default:
                console.error('unsupported programming language: ' + task.proglang.toLowerCase());
                break;
        }
/*
        $.each(config.testInfos, function(index, configItem) {
            if (!ui_test && item.testtype === configItem.testType) {
                // Check if proglang is set in configured test. If true then compare
                // Check Programming language
                if (configItem.proglang !== undefined) {
                    if (!configItem.proglang.includes(task.proglang)) {
                        // Language does not match
                        return;
                    }
                }
                console.log('found ' + configItem.title);
                ui_test = TestWrapper.create(item.id, item.title, configItem, item.weight);
                task.readTestConfig(taskXml, item.id, configItem, ui_test.root);
                ui_test.comment = item.comment;
                ui_test.description = item.description;
            }
        });
        if (!ui_test) {
            // try alternative test types
            $.each(config.testInfos, function(index, configItem) {
                $.each(configItem.alternativeTesttypes, function(index, alternative) {
                    if (!ui_test && item.testtype === alternative) {
                        ui_test = TestWrapper.create(item.id, item.title, configItem, item.weight);
                        task.readTestConfig(taskXml, item.id, configItem, ui_test.root);
                        ui_test.comment = item.comment;
                        ui_test.description = item.description;
                    }

                });
            });


            if (!ui_test) {
                setErrorMessage("Test " + item.testtype + " not imported");
                testIDs[item.id] = 0;
                return; // wrong test-type
            }
        }
*/

        if (ui_test) {
            let counter = 0;
            item.filerefs.forEach(function(itemFileref, indexFileref) {
                let filename = task.findFilenameForId(itemFileref.refid);
                TestFileReference.getInstance().setFilenameOnCreation(ui_test.root, counter++, filename);
            });
        } else {
            setErrorMessage("Test " + item.testtype + " not imported");
            testIDs[item.id] = 0;
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

    // resetInputFields();

    const templateroot = $("#templatedropzone");
    const multmediaroot = $("#multimediadropzone");
    const downloadroot = $("#downloaddropzone");
    const visibleroot = $("#visiblefiledropzone");

    // TODO: check version
    // TODO: validate??
    task.readXml(taskXml);


/*    descriptionEditor.setValue(task.description);
    $("#xml_title").val(task.title);
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
    let versionElement = $("#xml_programming-language-" + task.proglang.toLowerCase());
    if (!versionElement) {
        console.error('cannot find element #xml_programming-language-' + task.proglang.toLowerCase());
    } else {
        versionElement.val(task.proglangVersion);
        if (versionElement.val() !== task.proglangVersion) {
            alert('check programming language version (' + task.proglangVersion + ')');
        }
    }


    /*

    codeskeleton.setValue(task.codeskeleton);
*/

    task.files.forEach(createFile);
    task.tests.forEach(createTest);
    // task.modelsolutions.forEach(createMs);
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
    FileReferenceList.updateAllFilenameLists();
}
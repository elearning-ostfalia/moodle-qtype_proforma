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
 * functions for uploading a task.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
// import {get_string as getString} from 'core/str';
// import Str from 'core/str';
import {get_strings as getStrings} from 'core/str';
import Notification, {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import {TestWrapper } from "./taskeditortest";
import {downloadTask, getCheckstyleVersions, getJunitVersions} from "./repository";
import {getExtension} from "./taskeditorutil";
import {taskeditorconfig} from "./taskeditorconfig";
import {unzipme, zipme} from "./zipper";
import {readXMLWithLock} from "./taskeditorhelper";
import {convertToXML} from "./taskeditortask";
import Config from 'core/config';

var draftitemid = null;
var draftfilename = null;
var repositoryparams = null;

/**
 * edit task
 *
 * @param {string} buttonid: button id
 * @returns {undefined}
 */
export async function edit(buttonid, context, repoparams, inline) {

    console.log(context);
    repositoryparams = repoparams;
    console.log(repositoryparams);

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

    function downloadTaskFromServer() {
        // Find file from {files} where itemid = value of #id_task

        // let questionId = document.querySelector("input[name='id']").value;
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
            console.log('No gradinghints field found => ignore');
            return;
        }

        const aggregationstrategy = document.querySelector('#id_aggregationstrategy');
        console.log('aggregationstrategy ' + aggregationstrategy.value);

        console.log(gradinghints.value);
        const parser = new DOMParser();
        const doc = parser.parseFromString(gradinghints.value, "application/xml");
        doc.querySelectorAll('test-ref').forEach(test => {
            let ui_test = TestWrapper.constructFromId(test.getAttribute('ref'));
            if (aggregationstrategy.value === 2) {
                ui_test.weight = test.getAttribute('weight');
            }
            ui_test.title = test.querySelector('title').innerHTML;
            ui_test.description = test.querySelector('description').innerHTML;
            if (test.querySelector('test-type').innerHTML != ui_test.testtype) {
                console.error('Testtype for test ' + ui_test.id + ' does not match value from grading hints')
            }
        });
    }

    function displayTaskdata(taskresponse) {
        const extension = getExtension(taskresponse.url);
        const isZipped = (extension === 'zip');
        if (isZipped) {
            console.log('task file is zipped! => extract');
            return taskresponse.blob()
                .then(blob => {
                    console.log('blob is');
                    console.log(blob);
                    unzipme(blob, function(text) {
                        readXMLWithLock(text)
                            .then(() => mergeWithGradingHints());
                    });
                });
        } else {
            console.log('task file is not zipped');
            taskresponse.text()
                .then(text => {
                    readXMLWithLock(text)
                        .then(() => mergeWithGradingHints());
                });
        }
    }

    async function newsave() { await saveBackToServer();}

    console.log('change submit function');
    let form = document.getElementById('id_submitbutton').closest('form');

    console.log(form);
    var realSubmit = form.submit;
    console.log(realSubmit);
/*    try {
        var wrappedSubmit = form.submit = function () {
            alert('do new submit');
            newsave();
            alert('do old submit');
            form.submit = realSubmit;
            form.submit();
            form.submit = wrappedSubmit;
        };
    } catch(e) {}

 */

    if (form) {
        console.log('change submit button');
        // In student review there will be no button!
        form.onsubmit  = (event) => {
            // event.preventDefault();
            console.log('save before submit');
            saveBackToServer(); // synchronous action!
            // alert('button submit');
            console.log('saveBackToServer triggered');
        };
    }
/*
        let submitbutton = document.getElementById('id_updatebutton');
        if (submitbutton !== null) {
            console.log('change update button');
            // In student review there will be no button!
            submitbutton.onclick = async (event) => {
                event.preventDefault();
                console.log('save before submit');
                // alert('button submit');
                await saveBackToServer() // synchronous action!
                console.log('saveBackToServer triggered');
            };
        }
    */


    if (inline) {
        downloadTaskFromServer()
            .then(taskresponse => displayTaskdata(taskresponse))
            .fail(Notification.exception);
        return;
    }

    document.getElementById(buttonid).addEventListener('click', function (e) {
        console.log('edit task');

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
                /* console.log(root);
                console.log(header);
                console.log(header.querySelector('button'));*/
                header.querySelector('button').style.display = 'none';

                modal.show();
                if (js) {
                    Templates.runTemplateJS(js);
                }

                // Fill modal with data
                console.log('response from fetch is');
                console.log(taskresponse);
                displayTaskdata(taskresponse);
                /*
                const extension = getExtension(taskresponse.url);
                const isZipped = (extension === 'zip');
                if (isZipped) {
                    return taskresponse.blob()
                        .then(blob => {
                            console.log('blob is');
                            console.log(blob);
                            unzipme(blob, function(text) {
                                readXMLWithLock(text);
                            });
                        });
                } else {
                    readXMLWithLock(response.text());
                }

                 */

                return modal;
        }).fail(Notification.exception);
    });

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
        document.querySelector('#xml_programming-language-' + lang).style.display = '';
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


function createGradingHints() {
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
        console.log('No gradinghints field found => ignore');
        return;
    }
    let serializer = new XMLSerializer();
    let result = serializer.serializeToString (gh);

    if ((result.substring(0, 5) !== "<?xml")){
        result = '<?xml version="1.0"?>' + result;
        // result = "<?xml version='1.0' encoding='UTF-8'?>" + result;
    }
    console.log(result);
    gradinghints.value = result;
    console.log('grading hints are finished');
}

function saveBackToServer() {
    const zipname = $("#id_name").val();
    const context = convertToXML();
    if (context) {
        return zipme(context, zipname, false)
            .then(blob => {
                createGradingHints();
                console.log('now let us update task in  Moodle server');
                const url = Config.wwwroot + '/repository/repository_ajax.php';
                const action = 'upload';

                const formData = new FormData();
                formData.append('sesskey', Config.sesskey);
                formData.append('repo_upload_file', blob);
                formData.append('filepath', '/');
                formData.append('client_id', repositoryparams['client_id']);
                formData.append('title', draftfilename);
                formData.append('overwrite', true);
                // formData.append('maxbytes', -1);
                // since we are uploading the file to the 'draft area',
                // there is no point in limiting the size of the file area.
                // The draft area is used for all users.
                // formData.append('areamaxbytes', this.options['areamaxbytes']);
                formData.append('savepath', '/');
                formData.append('repo_id', repositoryparams['repo_id']);
                formData.append('itemid', draftitemid);

                /*
                                    const url = Config.wwwroot + '/repository/repository_ajax.php';
                                    const formData = new FormData();
                                    formData.append('sesskey', Config.sesskey);
                                    formData.append('repo_upload_file', blob);
                                    formData.append('filepath', '/');
                                    // formData.append('client_id', this.options['client_id']);
                                    formData.append('title', draftfilename);
                                    formData.append('overwrite', true);
                                    // formData.append('maxbytes', this.options['maxbytes']);
                                    // since we are uploading the file to the 'draft area',
                                    // there is no point in limiting the size of the file area.
                                    // The draft area is used for all users.
                                    // formData.append('areamaxbytes', this.options['areamaxbytes']);
                                    formData.append('savepath', '/');
                                    // formData.append('repo_id', this.options['repo_id']);
                                    formData.append('itemid', draftitemid);

                                    // formData.append('file', blob, 'readme.txt');
                */
                let request = new XMLHttpRequest();
                request.open('POST', url + '?action=' + action, false);
                request.send(formData);
                const jsonResponse = JSON.parse(request.responseText);
                console.log('response from Moodle');
                console.log(jsonResponse);
                alert('response from Moodle');
                if (jsonResponse.error !== undefined) {
                    console.error(request.responseText);
                    alert(jsonResponse.error);
                }
            });
    }
}

export const savetask = (buttonid) => {
    let button = document.getElementById(buttonid);
    button.onclick = function (e) {
        e.preventDefault();
        saveBackToServer();

    }
}
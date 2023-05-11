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
import {config} from "./taskeditorconfig";
import {unzipme} from "./zipper";
import {readXMLWithLock} from "./taskeditorhelper";


/**
 * edit task
 *
 * @param {string} buttonid: button id
 * @returns {undefined}
 */
export async function edit(buttonid, context, inline) {

    console.log(context);

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

    function downloadTaskFromServer() {
        let questionId = document.querySelector("input[name='id']").value;
        console.log('download task ' + questionId);
        return downloadTask(questionId)
            .then(response => {
                console.log(response.fileurl);
                if (!response.fileurl) {
                    reject(new Error('invalid fileurl ' + response.fileurl));
                }
                return response.fileurl;
            })
            .then(url => fetch(url, {method: 'GET'}));
/*
            .then(response => {
                console.log('response from fetch is');
                console.log(response);
                const extension = getExtension(response.url);
                const isZipped = (extension === 'zip');
                if (isZipped) {
                    return response.blob()
                        .then(blob => {
                            console.log('blob is');
                            console.log(blob);
                            return unzipme(blob, undefined);
                        });
                } else {
                    return response.text();
                }
            });*/
            // .then(text => readXMLWithLock(text))
/*            .catch( error => {
                console.error('error:', error);
                alert(error);
            });*/
    }

    function displayTaskdata(taskresponse) {
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
    }

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
};


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
                    response['junitversions'].forEach(version => {
                        let option = document.createElement("option");
                        option.text = version;
                        selectElem.add(option);
                    });
                }
            );
        })
        .fail(Notification.exception);
}


export const setCheckstyleVersions = () => {
    // console.log('setJunitVersions');
    getCheckstyleVersions()
        .then(response => {
            // console.log(response['junitversions']);
            document.querySelectorAll('.xml_pr_CS_version').forEach(
                selectElem => {
                    response['checkstyleversions'].forEach(version => {
                        let option = document.createElement("option");
                        option.text = version;
                        selectElem.add(option);
                    });
                }
            );
        })
        .fail(Notification.exception);
}

export const initproglang = (proglangdiv, buttondiv, langselect) => {

    function addButtonCallbacks() {
        document.querySelector('#addJUnitTest').onclick = function (e) {
            e.preventDefault();
            config.infoJavaJUnit.createTestForm();
        }

        document.querySelector('#addCheckStyleTest').onclick = function (e) {
            e.preventDefault();
            config.infoCheckStyle.createTestForm();
        }

        document.querySelector('#addCompilerTest').onclick = function (e) {
            e.preventDefault();
            config.infoJavaComp.createTestForm();
        }

        document.querySelector('#addGoogleTest').onclick = function (e) {
            e.preventDefault();
            config.infoGoogleTest.createTestForm();
        }

        document.querySelector('#addCUnitTest').onclick = function (e) {
            e.preventDefault();
            config.infoCUnit.createTestForm();
        }

        document.querySelector('#addPythonUnittest').onclick = function (e) {
            e.preventDefault();
            config.infoPython.createTestForm();
        }

        document.querySelector('#addPythonDocTest').onclick = function (e) {
            e.preventDefault();
            config.infoPythonDoctest.createTestForm();
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
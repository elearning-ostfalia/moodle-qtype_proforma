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


import config from 'core/config';
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
import {unzipme} from "./zipper";
import {readXMLWithLock} from "./taskeditorhelper";


/**
 * edit task
 *
 * @param {string} buttonid: button id
 * @returns {undefined}
 */
export const edit = (buttonid, context) => {

    console.log(context);

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

    async function downloadTaskFromServer() {
        let questionId = document.querySelector("input[name='id']").value;
        console.log('download task ' + questionId);
        const response = await downloadTask(questionId);
        console.log(response.fileurl);
        if (!response.fileurl) {
            console.error('invalid fileurl ' + response.fileurl);
            return;
        }

        const extension = getExtension(response.fileurl);
        const isZipped = (extension === 'zip');

        if (isZipped) {
            fetch(response.fileurl, { method: 'GET' })
                .then( response => response.blob())
                .then( responseblob => {
                    unzipme(responseblob, function (text) {
                        // console.log('readycallback');
                        readXMLWithLock(text);
                    });
                })
                .catch( error => {
                    console.error('error:', error);
                    alert(error);
                });
        } else {
            fetch(response.fileurl, { method: 'GET' })
                .then( response => response.text())
                .then( responsetext => {
                    console.log(responsetext);
                    readXMLWithLock(responsetext);
                })
                .catch( error => {
                    console.error('error:', error);
                    alert(error);
                });
            
        }
    }

    // Initialise.
    console.log('edit task');
    downloadTaskFromServer();
    document.getElementById(buttonid).addEventListener('click', function (e) {
        console.log('click');

        var stringsPromise = getStrings([
            {
                // All string beginning with taskeditor.
                key: 'taskeditor',
                component: 'qtype_proforma'
            }
        ]);
        var modalPromise = ModalFactory.create(
            {
                type: ModalFactory.types.SAVE_CANCEL,
                large: true
            }
        );

        context['tests'] = '';
        context['files'] = '';
        var bodyPromise = Templates.renderForPromise('qtype_proforma/taskeditor', context);

        $.when(stringsPromise, modalPromise, bodyPromise).then(function(strings, modal, {html, js}) {
            console.log(html);
            console.log(js);

            modal.getRoot().on(ModalEvents.hidden, modal.destroy.bind(modal));
            modal.setTitle(strings[0]);

            modal.setBody(html);
            // Change size (TODO: actually do with css)
            modal.getModal().css('min-width', '70%');
            modal.getModal().css('min-height', '90%');

            modal.getRoot().on(ModalEvents.save, function() {
            });

            modal.show();
            if (js) {
                Templates.runTemplateJS(js);
            }
            return modal;
        }).fail(Notification.exception);
    });
};


/**
 * get JUnit version from Moodle configuration and add to JUnit list
 */
export const setJunitVersions = () => {
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
        // Add Junit testcase
        document.querySelector('#addJUnitTest').onclick = function (e) {
            e.preventDefault();
            let context = {
                'testname' : 'JUnit Test',
                'filenamelabel' : 'Junit and other file(s)'
            };
            TestWrapper.createFromTemplate(null,
                'qtype_proforma/taskeditor_junit', context, true);
        }

        // Add Junit testcase
        document.querySelector('#addCheckStyleTest').onclick = function (e) {
            e.preventDefault();
            let context = {
                'testname' : 'Checkstyle Test',
                'filenamelabel' : 'Configuration file'
            };
            TestWrapper.createFromTemplate(null,
                'qtype_proforma/taskeditor_checkstyle', context, true);
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

    // Add button callbacks.
    addButtonCallbacks();

}
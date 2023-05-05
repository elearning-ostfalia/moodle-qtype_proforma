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
import {CustomTest, setcounter, DEBUG_MODE, getDescriptionHtmlString} from "./taskeditorutil";

/**
 * edit task
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const edit = (buttonid, task) => {

    var modalroot = null;

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

    /*
    async function performUpload() {
        let questionId = document.querySelector("input[name='id']").value;
        console.log('upload task ' + questionId);
        const promise = await uploadTask(questionId);
        console.log('upload task finished, handle result 1');
        window.console.log(promise);
        console.log('upload task finished, handle result 2');
        // alert(response.message);
    }*/

    // Initialise.
    console.log('edit task');
    init();
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

        // var bodyPromise = Templates.render('tool_analytics/export_options', {});
        // var bodyPromise = Templates.render('core_user/send_bulk_message', {});
        // var bodyPromise = Templates.render('qtype_proforma/setting_configproformagrader', context);
        let context = {
            "tests": "todo tests",
            "files": "todo files"
        }
        // const bodyPromise = Templates.renderForPromise('qtype_proforma/taskeditor', context);
        var bodyPromise = Templates.render('qtype_proforma/taskeditor', context);

/*
        // Wait for the content to be ready, and for the transition to be complet.
        Promise.all([stringsPromise, modalPromise, bodyPromise])
            .then(([{html, js}]) => {
                modal.getRoot().on(ModalEvents.hidden, modal.destroy.bind(modal));

                modal.setTitle(strings[0]);
                // modal.setSaveButtonText(strings[0]);
                // modal.setBody(bodyPromise);
                Templates.appendNodeContents(modal.getRoot(), html, js);
                // Templates.replaceNodeContents(help, html, js)

                modal.getRoot().on(ModalEvents.save, function() {
                });
                modal.show();
                return modal;

            })
            .catch(Notification.exception);
            
 */

        $.when(stringsPromise, modalPromise, bodyPromise).then(function(strings, modal, body) {

            modal.getRoot().on(ModalEvents.hidden, modal.destroy.bind(modal));
            modal.setTitle(strings[0]);
            modal.setBody(body);
            // Change size (TODO: actually do with css)
            modal.getModal().css('max-width', '70%');
            modal.getModal().css('max-height', '90%');

            modal.getRoot().on(ModalEvents.save, function() {
            });

            modal.show();
            return modal;
        }).fail(Notification.exception);
    });
};


export const initproglang = (proglangdiv, buttondiv, langselect) => {
    function addButtonCallbacks() {
        // Add Junit testcase
        document.querySelector('#addJUnitTest').onclick = function (e) {
            e.preventDefault();
            let config = new CustomTest("JUnit_Default_Title", "unittest", "", ['java']);
            let ui_test = TestWrapper.create(null, 'JUnit Test', config, 1);

/*
            const context = { 'testname': 'JUnit Test'};
            Templates.renderForPromise('qtype_proforma/taskeditor_junit', context)
                .then(({html, js}) => {
                    Templates.appendNodeContents('#proforma-tests-section', html, js);
                })
                .catch((error) => { alert('error');displayException(error); });

 */
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
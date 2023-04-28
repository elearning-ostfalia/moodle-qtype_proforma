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


// import {uploadTask} from './repository';
import config from 'core/config';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
// import {get_string as getString} from 'core/str';
// import Str from 'core/str';
import {get_strings as getStrings} from 'core/str';
import Notification from 'core/notification';
import Templates from 'core/templates';

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const edit = (buttonid, task) => {

    var source = null;
    var modalroot = null;
    var closeString = 'close';


    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        // closeString = await getString('close', 'editor');
    }

/*    function closeSse() {
        source.close();
        let dialog = document.querySelector(".modal");
        if (dialog) {
            let button = dialog.querySelector(".btn-secondary");
            if (button) {
                // Change cancel button to close button
                button.innerHTML = closeString;
            }
        }
    }*/
    
    /**
     * upload current question to grader
     * @returns {Promise<void>}
     */
/*    async function uploadWithSse() {
        // Get question id from form fields.
        const questionId = document.querySelector("input[name='id']").value;
        let url = config.wwwroot + '/question/type/proforma/upload_sse.php';
        url += '?sesskey=' + config.sesskey + '&id=' + questionId;

        // Create Eventsource with callbacks
        source = new EventSource(url);
        source.onmessage = function(event) {
            // console.log(event.data);
            let dialog = document.querySelector("#proforma-modal-message");
            if (dialog != null) {
                let message = event.data.trim();
                // handle binary prefix (direct output from Popen)
                if (message.startsWith("b'") || message.startsWith("b\"")) {
                    message = '<b>' + message.substring(2, message.length - 3) + '</b>';
                }
                if (message.endsWith('####') && !message.startsWith('####')) { // got line ending with special keys
                    closeSse();
                } else {
                    // Append new message
                    dialog.innerHTML += message + "<br>";
                }
            }
        };
        source.onerror = function(event) {
            // Upload is complete (with or without error)
            closeSse();
        };
    }*/

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
                // All string beginning with export.
                key: 'export',
                component: 'tool_analytics'
            }
        ]);
        var modalPromise = ModalFactory.create({type: ModalFactory.types.SAVE_CANCEL});

/*        let context = {
            "name": "test",
            "value": "/my-super-secret-path/file",
            "id": "test0",
            "readonly": true,
            "showvalidity": true,
            "valid": false,
            "response": "OK"
        };
*/
        let context = {
            "tabs": [
            {
                "id": "tab-tests",
                "name": "Tab Tests",
                "content": "This is tab 1 content <a href=\"#\">test</a>"
            },
            {
                "id": "tab-files",
                "name": "Tab Files",
                "content": "This is tab 2 content <a href=\"#\">test</a>"
            },
        ]
        }

        // var bodyPromise = Templates.render('tool_analytics/export_options', {});
        // var bodyPromise = Templates.render('core_user/send_bulk_message', {});
        // var bodyPromise = Templates.render('qtype_proforma/setting_configproformagrader', context);
        var bodyPromise = Templates.render('qtype_proforma/taskeditor', context);

        $.when(stringsPromise, modalPromise).then(function(strings, modal) {

            modal.getRoot().on(ModalEvents.hidden, modal.destroy.bind(modal));

            modal.setTitle(strings[0]);
            modal.setSaveButtonText(strings[0]);
            modal.setBody(bodyPromise);

            modal.getRoot().on(ModalEvents.save, function() {
/*
                var exportOption = $("input[name='exportoption']:checked").val();

                if (exportOption == 'exportdata') {
                    a.attr('href', a.attr('href') + '&action=exportdata');

                } else {
                    a.attr('href', a.attr('href') + '&action=exportmodel');
                    if ($("#id-includeweights").is(':checked')) {
                        a.attr('href', a.attr('href') + '&includeweights=1');
                    } else {
                        a.attr('href', a.attr('href') + '&includeweights=0');
                    }
                }

                window.location.href = a.attr('href');
                return;*/
            });
            modal.show();
            return modal;
        }).fail(Notification.exception);

/*
        // Create Moodle modal dialog.
        ModalFactory.create({
            type: ModalFactory.types.SAVE_CANCEL,
            title: 'Task editor',
            body: '<span id ="proforma-task-editor">Here it goes</span>',
            large: true
        }).then(function(modal) {
            modalroot = modal.getRoot();
            modalroot.on(ModalEvents.cancel, function() {
//                source.close();
//                source = null;
                modalroot.remove();
            });
            modal.show();
        }).fail(Notification.exception);*/
    });
};

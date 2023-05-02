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
import {get_string as getString} from 'core/str';

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const upload = (buttonid, task) => {

    // const msgBoxId = 'proforma-notification-bar';
    var source = null;
    var modalroot = null;
    var closeString = 'close';

    /*
    function showMessageBar(buttonid, message) {
        const duration = 1500;
        const txtColor = "#101010";
        let height = "100px";

        let box = document.getElementById(msgBoxId);
        if (box === null) {
            let node = document.createElement('div');
            node.style.width = "100%";
            // node.style.height = height;
            node.style.position = "fixed";
            node.style.zIndex = "10000";
            node.style.background = "#E0E2E4";
            node.style.border = "1px solid " + txtColor;
            node.style.transition = "all 2s ease-in-out";
            node.style.height  = "0px"; // => height
            document.body.prepend(node);
            console.log('appended');

            let span = document.createElement('span');
            span.id = msgBoxId;
            span.innerHTML = message;
            node.appendChild(span);

            node.style.height = height;

        } else {
            // Delete output
            box.innerHTML = message;
        }
    }
     */

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        closeString = await getString('close', 'editor');
    }

    function closeSse() {
        source.close();
        let dialog = document.querySelector(".modal");
        if (dialog) {
            let button = dialog.querySelector(".btn-secondary");
            if (button) {
                // Change cancel button to close button
                button.innerHTML = closeString;
            }
        }
    }

    /**
     * upload current question to grader
     * @returns {Promise<void>}
     */
    async function uploadWithSse() {
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
    init();
    document.getElementById(buttonid).addEventListener('click', function (e) {
        // Create Moodle modal dialog.
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: 'Upload Log',
            body: '<span><code id ="proforma-modal-message"></code></span>',
            large: true
        }).then(function(modal) {
            // close eventsource on cancel
            modalroot = modal.getRoot();
            modalroot.on(ModalEvents.cancel, function() {
                source.close();
                source = null;
                modalroot.remove();
            });
            modal.show();
            uploadWithSse();
        });
    });
};

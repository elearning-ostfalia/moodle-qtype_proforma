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
 * functions for running a test with the model solution.
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
import {get_string as getString} from 'core/str';

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} itemid: itemid of task on Moodle server
 * @param {string} contextid: contextid of task on Moodle server
 * @param {string} filename: filename of task on Moodle server
 * @returns {undefined}
 */
export const show = (json, questionId, callbackstart, callbackdata, callbackend) => {

    let source = null;
    let modalroot = null;
    let closeString = 'close';
    const itemid = json.itemid;
    const contextid = json.contextid;
    const taskfilename = json.taskfilename;
    const modelsolutionfilename = json.modelsolutionfilename;
    const questionid = questionId;

    let url = config.wwwroot + '/question/type/proforma/checksolution_ajax.php?runtest=1';
    url += '&sesskey=' + config.sesskey + '&questionid=' + questionid;
    url += '&itemid=' + itemid + '&contextid=' + contextid +
        '&taskfilename=' + taskfilename +  '&modelsolutionfilename=' + modelsolutionfilename;


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
    async function requestEventSource() {
        // Create Eventsource with callbacks
        source = new EventSource(url);
        let feedbackstarted = false;
        source.onmessage = function(event) {
            // console.log(event.data);
            let dialog = document.querySelector("#proforma-modal-message");
            if (dialog != null) {
                let message = event.data.trim();
                // handle binary prefix (direct output from Popen)
                if (message.startsWith('RESPONSE START####')) {
                    feedbackstarted = true;
                    callbackstart()
                } else if (message.endsWith('RESPONSE END####')/* && !message.startsWith('####')*/) { // got line ending with special keys
                    console.log('end of response found => close connection');
                    callbackend();
                    closeSse();
                } else {
                    if (feedbackstarted) {
                        callbackdata(message);
                    } else {
                        // Append new message
                        if (message.startsWith("b'") || message.startsWith("b\"")) {
                            message = '<b>' + message.substring(2, message.length - 3) + '</b>';
                        }
                        dialog.innerHTML += message + "<br>";
                    }
                }
            }
        };
        source.onerror = function(event) {
            // Upload is complete (with or without error)
            closeSse();
        };
    }

    function showUploadDialog() {
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: 'Check Log',
            body: '<span><code id ="proforma-modal-message"></code></span>',
            large: true
        }).then(function (modal) {
            // close eventsource on cancel
            modalroot = modal.getRoot();
            modal.getModal().css('min-width', '50%');
            modalroot.on(ModalEvents.cancel, function () {
                source.close();
                source = null;
                modalroot.remove();
            });
            modal.show();
            requestEventSource();
        });
    }

    // Initialise.
    init();

    showUploadDialog();

};

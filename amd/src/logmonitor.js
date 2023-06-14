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
 * function for displaying server sent events.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';
import Config from 'core/config';

/**
 * shows a modal dialog with the server sent events
 *
 * @param title string identifier for localised title
 * @param url SSE url
 * @param callbackstart callback for begin feedback (Proforma XML response after grading)
 * @param callbackdata callback for end feedback (Proforma XML response after grading)
 * @param callbackend callback for handling Proforma XML response after grading
 * @returns {Promise<void>}
 */
export async function show(title, url, callbackstart, callbackdata, callbackend) {

    let source = null;
    let modalroot = null;
    let closeString = 'close';
    let titleString = title;

    /**
     * get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        closeString = await getString('close', 'editor');
        titleString = await getString(title, 'qtype_proforma');
    }

    function fade(element, button) {
        let op = 1;  // initial opacity
        let timer = setInterval(function () {
            if (op <= 0.1){
                clearInterval(timer);
                button.click();
/*                element.style.display = 'none';
                modalroot.remove();
                if (source) {
                    source.close();
                    source = null;
                }*/
            } else {
                element.style.opacity = op;
                element.style.filter = 'alpha(opacity=' + op * 100 + ")";
                op -= op * 0.1;
            }
        }, 50);
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
            if (callbackstart) {
                // If there is a callback for feedback finished then we fade the dialog.
                // Das Schließen des Dialogs scheint nicht zu funktionieren.
                // Der Hintergrund bleibt grau.
                fade(dialog, button);
            }
        }
    }

    /**
     * opens connection and handles events
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
                let message = event.data;
                if (!feedbackstarted) {
                    message = message.trim();
                }

                // handle binary prefix (direct output from Popen)
                if (message.startsWith('RESPONSE START####')) {
                    feedbackstarted = true;
                    callbackstart();
                    return;
                }
                if (message.endsWith('RESPONSE END####')/* && !message.startsWith('####')*/) { // got line ending with special keys
                    console.log('end of response found => close connection');
                    callbackend();
                    closeSse();
                    return;
                }
                if (message.endsWith('####') && !message.startsWith('####')) { // got line ending with special keys
                    closeSse();
                    return;
                }
                if (feedbackstarted) {
                    callbackdata(message);
                    return;
                }
                // Default handling: append new message
                if (message.startsWith("b'") || message.startsWith("b\"")) {
                    message = '<b>' + message.substring(2, message.length - 3) + '</b>';
                }
                dialog.innerHTML += message + "<br>";
            }
        };
        source.onerror = function(event) {
            // Complete (with or without error)
            closeSse();
        };
    }

    /**
     * shows dialog window
     */
    function showDialog() {
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: titleString,
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
    await init();

    showDialog();
}


/**
 * uploads the currently stored task for the currently 'open' question
 * to the grader
 * (it is not previously recreated so it might not be up-to-date)
 *
 * @param buttonid identifier of button to click on
 */
export const uploadToGrader = (buttonid) => {
    const button = document.getElementById(buttonid);
    if (button) {
        button.addEventListener('click', function (e) {
            // Create Moodle modal dialog.
            const questionId = document.querySelector("input[name='id']").value;
            let url = Config.wwwroot + '/question/type/proforma/upload_sse.php';
            url += '?sesskey=' + Config.sesskey + '&id=' + questionId;
            show('uploadlog', url);
        });
    } else {
        console.error('could not find button ' + buttonid);
    }
}
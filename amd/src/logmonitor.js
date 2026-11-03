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
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

import {debugLog} from './debug';
import CancelModal from 'core/modal_cancel';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';
import Config from 'core/config';

/**
 * Shows a modal dialog with the server sent events
 *
 * @param {string} title string identifier for localised title
 * @param {string} url SSE url
 * @param {Function} callbackstart callback for begin feedback (Proforma XML response after grading)
 * @param {Function} callbackdata callback for end feedback (Proforma XML response after grading)
 * @param {Function} callbackend callback for handling Proforma XML response after grading
 * @returns {Promise<void>}
 */
export async function show(title, url, callbackstart, callbackdata, callbackend) {

    let source = null;
    let modalroot = null;
    let closeString = 'close';
    let titleString = title;

    /**
     * Get localized string for cancel/close button
     * @returns {Promise<void>}
     */
    async function init() {
        closeString = await getString('close', 'editor');
        titleString = await getString(title, 'qtype_proforma');
    }

    /**
     * Fades out the element and clicks the button afterwards.
     *
     * @param {HTMLElement} element element to fade out
     * @param {Object} button button to click
     */
    /*
    function fade(element, button) {
        let op = 1; // Initial opacity
        let timer = setInterval(function() {
            if (op <= 0.1) {
                clearInterval(timer);
                let timer1 = setInterval(function() {
                    button.click();
                    clearInterval(timer1);
                }, 50);
            } else {
                element.style.opacity = op;
                element.style.filter = 'alpha(opacity=' + op * 100 + ")";
                op -= op * 0.1;
            }
        }, 40);
    }
*/
    /**
     * Closes the event source and changes the cancel button to a close button.
     */
    function closeSse() {
        source.close();
        if (modalroot) {
            let button = modalroot.find(".btn-secondary");
            if (button) {
                // Change cancel button to close button
                button.html(closeString);
                button.show();
            }

            if (callbackstart) {
                // If there is a callback for feedback finished then we fade the dialog.
                // Das Schließen des Dialogs scheint nicht zu funktionieren.
                // Der Hintergrund bleibt grau.
                // fade(dialog, button);
            }
        }
    }
    /**
     * Opens connection and handles events
     * @returns {Promise<void>}
     */
    async function requestEventSource() {
        // Create Eventsource with callbacks
        source = new EventSource(url);
        let feedbackstarted = false;
        source.onmessage = function(event) {
            let dialog = document.querySelector("#proforma-modal-message");
            if (dialog !== null) {
                let message = event.data;
                if (!feedbackstarted) {
                    message = message.trim();
                }

                // Handle binary prefix (direct output from Popen)
                if (message.startsWith('RESPONSE START####')) {
                    feedbackstarted = true;
                    callbackstart();
                    return;
                }
                if (message.endsWith('RESPONSE END####')/* && !message.startsWith('####')*/) { // Got line ending with special keys
                    debugLog('end of response found => close connection');
                    callbackend();
                    closeSse();
                    return;
                }
                if (message.endsWith('####') && !message.startsWith('####')) { // Got line ending with special keys
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
        source.onerror = function() {
            // Complete (with or without error)
            closeSse();
        };
    }

    /**
     * Shows dialog window
     */
    function showDialog() {
        CancelModal.create({
            title: titleString,
            body: '<span><code id ="proforma-modal-message"></code></span>',
            large: true,
            removeOnClose: true
        /*
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: titleString,
            body: '<span><code id ="proforma-modal-message"></code></span>',
            large: true */
        }).then(function(modal) {
            // Close eventsource on cancel
            modalroot = modal.getRoot();
            modalroot.css('min-width', '50%');
            modalroot.on(ModalEvents.hidden, function() {
                source.close();
                source = null;
                modalroot.remove();
                modalroot = null;
            });
            modal.show();
            // Hide button
            modalroot.find(".btn-secondary").hide();
            requestEventSource();
        });
    }

    // Initialise.
    await init();

    showDialog();
}


/**
 * Uploads the currently stored task for the currently 'open' question
 * to the grader
 * (it is not previously recreated so it might not be up-to-date)
 *
 * @param {string} buttonid identifier of button to click on
 */
export const uploadToGrader = (buttonid) => {
    const button = document.getElementById(buttonid);
    if (button) {
        button.addEventListener('click', function() {
            // Create Moodle modal dialog.
            const questionId = document.querySelector("input[name='id']").value;
            let url = Config.wwwroot + '/question/type/proforma/upload_sse.php';
            url += '?sesskey=' + Config.sesskey + '&id=' + questionId;
            show('uploadlog', url);
        });
    } else {
        console.error('could not find button ' + buttonid);
    }
};
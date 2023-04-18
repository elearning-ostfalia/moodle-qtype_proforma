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


import {uploadTask} from './repository';
import config from 'core/config';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const upload = (buttonid, task) => {

    const msgBoxId = 'proforma-notification-bar';
    var source = null;
    var modalroot = null;
    const maxLogLines = 20;

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

            let button = document.createElement('button');
            button.innerHTML = "close";
            button.addEventListener('click', function() {
                if (source != null) {
                    source.close();
                }
                node.remove();
            });
            node.appendChild(button);

            // Transistion
            node.style.height = height;

        } else {
            // Delete output
            box.innerHTML = message;

        }
    }


    async function uploadWithSse() {
        const questionId = document.querySelector("input[name='id']").value;
        let url = config.wwwroot + '/question/type/proforma/upload_sse.php';
        url += '?sesskey=' + config.sesskey + '&id=' + questionId;

        source = new EventSource(url);
        source.onmessage = function(event) {
            console.log(event.data);
            let dialog = document.querySelector(".modal-body");
            // console.log(dialog);
            if (dialog != null) {
                dialog.innerHTML += event.data.trim() + "<br>";
            }

            // document.getElementById(msgBoxId).innerHTML += event.data + "<br>";
        };
        source.onerror = function(event) {
            // Unfortunately, we cannot see on this level whether an error has occurred or
            // whether the upload was simply completed successfully.
            // console.error("An error occured");
            // console.log(event);
            // console.log(source);
/*            switch (source.readyState) {
                case EventSource.CLOSED:
                    console.log('Close connection');
                    break;
                case EventSource.OPEN:
                    console.log('Open connection');
                    break;
                case EventSource.CONNECTING:
                    console.log('Connecting connection');
                    break;
                default:
                    console.log('Unknown connection state ' + source.readyState);
                    break;
            }*/
            source.close();
            let dialog = document.querySelector(".modal");
            if (dialog) {
                let button = dialog.querySelector(".btn-secondary");
                if (button) {
                    button.innerHTML = 'Close';
                }
            }
            // source = null;
            // document.getElementById("result").innerHTML += event.data + "<br>";
        };
        source.onopen = function(event) {
            // console.log("The connection has been established. ");
            // document.getElementById("result").innerHTML += event.data + "<br>";
        };
    }

    async function performUpload() {
        let questionId = document.querySelector("input[name='id']").value;
        console.log('upload task ' + questionId);

        const promise = await uploadTask(questionId);
        console.log('upload task finished, handle result 1');

        window.console.log(promise);

        console.log('upload task finished, handle result 2');

        // alert(response.message);
    }

    // Initialise.
    document.getElementById(buttonid).addEventListener('click', function (e) {
        // Create Moodle modal dialog.
        ModalFactory.create({
            type: ModalFactory.types.CANCEL,
            title: 'Upload Log',
            body: '',
        })
            .then(function(modal) {
                modalroot = modal.getRoot();
                modalroot.on(ModalEvents.cancel, function() {
                    source.close();
                    source = null;
                    modalroot.remove();
                });
                modal.show();
                uploadWithSse();
            });

        // showMessageBar(buttonid, '');
        // uploadWithSse();
    });
};

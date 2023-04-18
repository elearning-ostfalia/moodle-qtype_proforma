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

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const upload = (buttonid, task) => {

    const msgBoxId = 'proforma-notification-bar';

    function showMessageBar(buttonid, message) {
        console.log('showNotificationBar');

        const duration = 1500;
        const txtColor = "#101010";
        let height = 100;

        let box = document.getElementById(msgBoxId);
        console.log(box);

        if (box === null) {
            console.log('create messagebox');
            let boxHtml = "<div id='" + msgBoxId + "' style='width:100%; height:" + height +
            "px; position: fixed; z-index: 10000; background-color: #E0E2E4; border: 1px solid " + txtColor + "'>" + message + "</div>";

            let node = document.createElement('div');
            node.innerHTML = boxHtml;
            node.style.transition = "all 2s ease-in-out";
            node.style.height  = "0px"; // => height

            document.body.prepend(node); // appendChild(node);
            let button = document.getElementById(buttonid);
            console.log('appended');
        } else {
            // Delete output
            box.innerHTML = message;
        }
    }


    async function uploadWithSse() {
        const questionId = document.querySelector("input[name='id']").value;
        let url = config.wwwroot + '/question/type/proforma/upload_sse.php';
        url += '?sesskey=' + config.sesskey + '&id=' + questionId;

        var source = new EventSource(url);
        source.onmessage = function(event) {
            console.log(event.data);
            // console.log(event);
            document.getElementById(msgBoxId).innerHTML += event.data + "<br>";
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
    document.getElementById(buttonid).addEventListener('click', function () {
        showMessageBar(buttonid, '');
        uploadWithSse();
    });
};

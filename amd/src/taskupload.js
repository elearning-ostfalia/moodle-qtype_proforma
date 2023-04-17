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

/**
 * upload task to grader
 *
 * @param {string} buttonid: button id
 * @param {string} task: name of task
 * @returns {undefined}
 */
export const upload = (buttonid, task) => {
    async function noAjaxUpload() {
        let questionId = document.querySelector("input[name='id']").value;

        var that = this;
        var page_url = 'http://10.235.1.41/moodle/lib/ajax/service.php?info=qtype_proforma_upload_task';

        let ajaxRequestData = [];
        // for (i = 0; i < requests.length; i++) {
            ajaxRequestData.push({
                index: 0,
                methodname: 'qtype_proforma_upload_task',
                args: {questionId, }
            });
        // }
        ajaxRequestData = JSON.stringify(ajaxRequestData);

        var req = new XMLHttpRequest();
        req.open("POST", page_url, true);
        req.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        req.addEventListener("progress", function (evt) {
            console.log(evt);
        }, false);

        req.responseType = "blob";
        req.onreadystatechange = function () {
            console.log('onreadystatechange');
        };
        req.send(JSON.stringify(ajaxRequestData));
    }

    async function performUpload() {
        let questionId = document.querySelector("input[name='id']").value;
        console.log('upload task ' + questionId);

        const promise = await uploadTask(questionId);
        console.log('upload task finished, handle result 1');

        window.console.log(promise);

        console.log('upload task finished, handle result 2');

/*
        promise.then((response) => {
            window.console.log(response);
        })
        .catch( error => {
            window.console.error(error);
            // console.error('error:', error);
            // alert(error);
        });
*/

        // alert(response.message);
    }

    // Initialise.
    document.getElementById(buttonid).addEventListener('click', function () {
        noAjaxUpload();
        // performUpload();
    });
};

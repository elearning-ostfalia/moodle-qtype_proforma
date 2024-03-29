<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This PHP file is used for uploading a task to the grader
 * forwarding server sent events to the browser.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

require_once(__DIR__.'/../../../config.php');
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/externallib.php');

// Create events.
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

try {
    require_sesskey();
    if (!isloggedin()) {
        throw new Exception('user is not logged in');
    }

    $itemid    = optional_param('itemid', -1, PARAM_INT);
    if ($itemid > 0) {
        // Task comes from Javascript taskeditor and is only created in draft area
        // and may be without a question.
        global $USER;
        // Item id is set which means that there is a temporary task
        // in the draft area that shall be uploaded to the grader.
        $contextid = required_param('contextid', PARAM_INT);
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);
        if (!isset($context)) {
            throw new invalid_parameter_exception('invalid context');
        }
        external_api::validate_context($context);
        if ($context->contextlevel == CONTEXT_COURSE) {
            require_capability('moodle/question:editmine', $context);
        }

        // Since we're in the context of the user, it does not make sense checking course-level rights.
        // But in order to block other users we check the coursecontextid.
        // The $coursecontextid value is not used at anywhere. Just for security checks.
        $coursecontextid = required_param('coursecontextid', PARAM_INT);
        $coursecontext = \context::instance_by_id($coursecontextid);
        if (!isset($coursecontext)) {
            throw new moodle_exception('invalid course context');
        }
        external_api::validate_context($coursecontext);
        require_capability('moodle/question:editmine', $coursecontext);

        $filename = required_param('filename', PARAM_FILE);
        $fs = get_file_storage();
        $task = $fs->get_file($contextid, 'user', 'draft', $itemid, '/', $filename);
        if ($task === null) {
            throw new invalid_parameter_exception('No task file available');
        }

        // Use default grader and set upload URI
        $protocolhost = trim(get_config('qtype_proforma', 'graderuri_host'));
        $path = trim(get_config('qtype_proforma', 'uploaduri_path'));
        $uri = $protocolhost . $path;

        $grader = new \qtype_proforma_grader_2($uri);
    } else {
        // Get and validate question id.
        $id = required_param('id', PARAM_INT);

        $question = question_bank::load_question($id);
        // Consistency checks.
        if ($question == null) {
            throw new invalid_parameter_exception('no question');
        }
        if (get_class($question) != 'qtype_proforma_question') {
            throw new invalid_parameter_exception('invalid question type');
        }

        // Security checks
        global $DB;
        $contextid = $DB->get_field('question_categories', 'contextid', array('id' => $question->category));
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);
        if (isset($context)) {
            external_api::validate_context($context);
            if ($context->contextlevel == CONTEXT_COURSE) {
                require_capability('moodle/question:editmine', $context);
            }
        }
        $task = $question->get_task_file();
        if (!$task instanceof stored_file) {
            throw new coding_exception("task variable has wrong class");
        }
        $path = trim(get_config('qtype_proforma', 'uploaduri_path'));
        $grader = new \qtype_proforma_grader_2($question->get_uri($path));
    }

    list($graderoutput, $httpcode) = $grader->upload_task_to_grader($task);
    if ($graderoutput === true) {
        $graderoutput = 'successfully started';
    }

} catch (Exception $ex) {
    // Send error message as event data
    echo "data: $ex\n\n";
}





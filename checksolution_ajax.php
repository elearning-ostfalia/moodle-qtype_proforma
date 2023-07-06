<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * PHP file for dealing with running tests for the model solution
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../../lib/filelib.php');
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/externallib.php');
require_once(__DIR__ . '/renderer.php');



$err = new stdClass();

// Parameters
// $questionid = required_param('questionid', PARAM_INT); // Question id
$coursecontextid = required_param('coursecontextid', PARAM_INT); // Question id
$proglang = required_param('proglang', PARAM_TEXT); // Aggregation strategy
$maxbytes  = optional_param('maxbytes', 0, PARAM_INT);          // Maxbytes
$areamaxbytes  = optional_param('areamaxbytes', FILE_AREA_MAX_BYTES_UNLIMITED, PARAM_INT); // Area max bytes.
$gradinghints = optional_param('gradinghints', '', PARAM_TEXT); // Grading hints
$aggregationstrategy = optional_param('aggregationstrategy', '', PARAM_INT); // Aggregation strategy


$runtest  = optional_param('runtest', 0, PARAM_BOOL); // Files are already available, run test.
$taskfilename  = optional_param('taskfilename', '', PARAM_FILE);
$modelsolutionfilename  = optional_param('modelsolutionfilename', '', PARAM_FILE);
$contextid = required_param('contextid', PARAM_INT); // Context ID
$itemid    = required_param('itemid', PARAM_INT);            // Itemid




// Create events.
if ($runtest) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');
}

// If uploaded file is larger than post_max_size (php.ini) setting, $_POST content will be empty.
if (!$runtest && empty($_POST)) {
    $err->error = get_string('errorpostmaxsize', 'repository');
    die(json_encode($err));
}

if (!confirm_sesskey()) {
    $err->error = get_string('invalidsesskey', 'error');
    die(json_encode($err));
}

if (!isloggedin()) {
    throw new Exception('user is not logged in');
}

switch ($proglang) {
    case 'c':
        $proglang = 'clang';
        break;
    case 'java':
    case 'clang':
    case 'cpp':
    case 'python':
    case 'setlx': // ????
        break;
    default:
        throw new Exception('invalid programming language');
}

// Security checks
global $DB, $USER;
// Context is user context.
$context = \context::instance_by_id($contextid, IGNORE_MISSING);
if (!isset($context)) {
    throw new moodle_exception('invalid context');
}
external_api::validate_context($context);
if ($context->contextlevel != CONTEXT_USER) {
    throw new moodle_exception('invalid context level');
}

$usercontext = context_user::instance($USER->id);
if ($context->id != $usercontext->id) {
    throw new moodle_exception('context is no user context');
}

// Since we're in the context of the user, it does not make sense checking
// course-level rights.
// But in order to block other users we check the coursecontextid.
// The $coursecontextid value is not used at anywhere. Just for security checks.
$coursecontext = \context::instance_by_id($coursecontextid);
if (!isset($coursecontext)) {
    throw new moodle_exception('invalid course context');
}
external_api::validate_context($coursecontext);
require_capability('moodle/question:editmine', $coursecontext);


// Callback function for server sent events by grader.
function on_grader_response($graderoutput, $grader, $question, $gradinghints) {
    $ok = false;
    $message = "";
    $feedback = "";
    $class = 'fail';
    $quiet = false;

    list($state, $fraction, $error, $feedback1, $feedbackformat) =
        $grader->extract_grade($graderoutput, 200, $question);
    global $PAGE;
    $renderer = new qtype_proforma_renderer($PAGE, null);
    $fbrenderer = new feedback_renderer($renderer, $question);
    $feedback = $fbrenderer->render_proforma2_message($feedback1);

    if (!$quiet) {
        $message .= html_writer::tag('p', $graderoutput, array('class' => $class));
    }

    $output = html_writer::nonempty_tag('div', $feedback,
        array('class' => 'specificfeedback'));

    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        echo "data: " . $line . "\n\n";
    }
}

if (!$runtest) {
    // STEP 1 (upload files):
    if (!isset($_FILES['task'])) {
        throw new moodle_exception('no task file');
    }

    if (!isset($_FILES['modelsolution'])) {
        throw new moodle_exception('no model solution file');
    }
    global $CFG;

    // Check size of each uploaded file and scan for viruses.
    $maxsize = get_max_upload_file_size($CFG->maxbytes);
    $maxbytes = get_user_max_upload_file_size($context, $maxsize,
        get_config('qtype_proforma', 'taskmaxbytes'));
    foreach ($_FILES as $uploadedfile) {
        $filename = clean_param($uploadedfile['name'], PARAM_FILE);
        if ($uploadedfile['size'] > $maxsize) {
            throw new moodle_exception('file is too large');
        }
        \core\antivirus\manager::scan_file($uploadedfile['tmp_name'], $filename, true);
    }

    $fs = get_file_storage();
    $record = array(
        'contextid' => $context->id,
        'component' => 'user',
        'filearea' => 'draft',
        'itemid' => $itemid, // $contentid,
        'filepath' => '/',
        'userid'    => $USER->id
    );

    // Delete old files from last attempt
    $fs->delete_area_files($context->id, 'user', 'draft', $itemid);

    $record['filename'] = clean_param($_FILES['task']['name'], PARAM_FILE);
    $task_file = $fs->create_file_from_pathname($record, $_FILES['task']['tmp_name']);

    $record['filename'] = clean_param($_FILES['modelsolution']['name'], PARAM_FILE);
    $ms_file = $fs->create_file_from_pathname($record, $_FILES['modelsolution']['tmp_name']);

    $record['filename'] = 'gradinghints.txt';
    $gh_file = $fs->create_file_from_string($record, urldecode($gradinghints));


    $data = [
        'taskfilename' => $_FILES['task']['name'],
        'modelsolutionfilename' => $_FILES['modelsolution']['name'],
        'runtest' => 1,
        'itemid' => $itemid,
        'proglang' => $proglang,
        'contextid' => $context->id,
    ];
    echo json_encode( $data );

} else {
    // STEP 2 (execute tests)

    $fs = get_file_storage();

    $task_file = $fs->get_file($contextid, 'user', 'draft', $itemid, '/', $taskfilename);
    if (!$task_file) {
        throw new moodle_exception('no task file');
    }
    $ms_file = $fs->get_file($contextid, 'user', 'draft', $itemid, '/', $modelsolutionfilename);
    if (!$ms_file) {
        throw new moodle_exception('no model solution file');
    }
    $gh_file = $fs->get_file($contextid, 'user', 'draft', $itemid, '/', 'gradinghints.txt');
    if (!$gh_file) {
        throw new moodle_exception('no grading hints file');
    }
    $gh = $gh_file->get_content();

    // Wait as long as it takes for this script to finish
    core_php_time_limit::raise();

    $question = new qtype_proforma_question();
    // Override grading hints with temporary grading hints from client.
    // (needed for correct feedback)
    $question->gradinghints = $gh;
    $question->aggregationstrategy = $aggregationstrategy;
    $question->contextid = $contextid;
    // Set programming language from client.
    // Needed for determining grader host.
    $question->programminglanguage = $proglang;

    // The question returns the appropriate grader uri.
    $path = trim(get_config('qtype_proforma', 'runtest_path'));
    $grader = new \qtype_proforma_grader_2($question->get_uri($path));
    $files = [];
    $files[$modelsolutionfilename] = $ms_file;

    list($graderoutput, $httpcode) = $grader->send_files_with_task_to_grader_and_stream_result($files,
        $task_file, 'on_grader_response', $question, $gh);
    if ($graderoutput === true) {
        $graderoutput = 'successfully started';
    }
}

/*
// Version without server sent events and with direct output of feedback

list($graderoutput, $httpcode) = $grader->send_files_with_task_to_grader($files, $task_file);

$ok = false;
$message = "";
$feedback = "";
$class = 'fail';
$quiet = false;

// Override grading hints with temporary grading hints from client.
// (needed for correct feedback)
$question->gradinghints = urldecode($gradinghints);

if ($httpcode != 200) {
    $result = get_string('failed', 'qtype_proforma');
    $feedback .= html_writer::tag('p', 'HTTP-Code ' . $httpcode);
    $feedback .= html_writer::tag('small', html_writer::tag('xmp', $graderoutput));
} else {
    list($state, $fraction, $error, $feedback, $feedbackformat) =
        $grader->extract_grade($graderoutput, $httpcode, $question);
    global $PAGE;
    $renderer = new qtype_proforma_renderer($PAGE, null);
    $fbrenderer = new feedback_renderer($renderer, $question);
    $feedback = $fbrenderer->render_proforma2_message($feedback);
}
if (!$quiet) {
    $message .= html_writer::tag('p', $result, array('class' => $class));
}


$output = html_writer::nonempty_tag('div', $feedback,
    array('class' => 'specificfeedback'));

echo $output;
*/

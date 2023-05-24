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
 * The Web service script that is called from the taskeditor javascript
 *
 * @since Moodle 2.0
 * @package    repository
 * @copyright  2009 Dongsheng Cai {@link http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
$questionid = required_param('questionid', PARAM_INT); // Question id
$contextid = optional_param('ctx_id', SYSCONTEXTID, PARAM_INT); // Context ID
$source    = optional_param('source', '', PARAM_RAW);           // File to download
$sourcekey = optional_param('sourcekey', '', PARAM_RAW);        // Used to verify the source.
$itemid    = optional_param('itemid', 0, PARAM_INT);            // Itemid
$maxbytes  = optional_param('maxbytes', 0, PARAM_INT);          // Maxbytes
$areamaxbytes  = optional_param('areamaxbytes', FILE_AREA_MAX_BYTES_UNLIMITED, PARAM_INT); // Area max bytes.

// list($context, $course, $cm) = get_context_info_array($contextid);
// require_login($course, false, $cm, false, true);
// $PAGE->set_context($context);

// echo $OUTPUT->header(); // send headers

// If uploaded file is larger than post_max_size (php.ini) setting, $_POST content will be empty.
if (empty($_POST)) {
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


$question = question_bank::load_question($questionid);
// Consistency checks.
if ($question == null) {
    throw new invalid_parameter_exception('no question');
}
if (get_class($question) != 'qtype_proforma_question') {
    throw new invalid_parameter_exception('invalid question type');
}

// Security checks
global $DB;
$contextid = $DB->get_field('question_categories', 'contextid', array('id'=>$question->category));
$context = \context::instance_by_id($contextid, IGNORE_MISSING);
if (isset($context)) {
    external_api::validate_context($context);
    require_capability('moodle/question:editmine', $context);
}




// Get repository instance information
/*
$repooptions = array(
    'ajax' => true,
    'mimetypes' => $accepted_types
);

// ajax_capture_output();
$repo = repository::get_repository_by_id($repo_id, $contextid, $repooptions);

// Check permissions
$repo->check_capability();

$coursemaxbytes = 0;
if (!empty($course)) {
    $coursemaxbytes = $course->maxbytes;
}

// Make sure maxbytes passed is within site filesize limits.
$maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes, $coursemaxbytes, $maxbytes);
*/


if (!isset($_FILES['task'])) {
    throw new moodle_exception('no task file');
}

if (!isset($_FILES['modelsolution'])) {
    throw new moodle_exception('no model solution file');
}
global $CFG;
$maxsize = get_max_upload_file_size($CFG->maxbytes);
// Check size of each uploaded file and scan for viruses.
foreach ($_FILES as $uploadedfile) {
    $filename = clean_param($uploadedfile['name'], PARAM_FILE);
    if ($uploadedfile['size'] > $maxsize) {
        throw new moodle_exception('file is too large');
    }
    \core\antivirus\manager::scan_file($uploadedfile['tmp_name'], $filename, true);
}

global $USER;
$context = context_user::instance($USER->id);

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


// Wait as long as it takes for this script to finish
core_php_time_limit::raise();

// We need the programming language in order to find the correct grader
$grader = new \qtype_proforma_grader_2();
$files = [];
$files[] = $ms_file;
list($graderoutput, $httpcode) = $grader->send_files_with_task_to_grader($files, $task_file);

$ok = false;
$message = "";
$feedback = "";
$class = 'fail';
$quiet = false;

if ($httpcode != 200) {
    $result = get_string('failed', 'qtype_proforma');
    $feedback .= html_writer::tag('p', 'HTTP-Code ' . $httpcode);
    $feedback .= html_writer::tag('small', html_writer::tag('xmp', $graderoutput));
} else {
    list($state, $fraction, $error, $feedback, $feedbackformat) =
        $grader->extract_grade($graderoutput, $httpcode, $question);

    if ($fraction < 1) {
        $result = get_string('failed', 'qtype_proforma');
        if ($feedbackformat != qtype_proforma_grader::FEEDBACK_FORMAT_PROFORMA2) {
            $result .= html_writer::tag('xmp', $feedback, array('class' => 'proforma_testlog'));
        } else {
            global $PAGE;
            $renderer = new qtype_proforma_renderer($PAGE, null);
            $fbrenderer = new feedback_renderer($renderer, $question);
            $feedback = $fbrenderer->render_proforma2_message($feedback);
        }
    } else {
        $class = 'pass';
        $result = get_string('passed', 'qtype_proforma');
        $ok = true;
    }
}
if (!$quiet) {
    $message .= html_writer::tag('p', $result, array('class' => $class));
}

// return array($ok, $message, $feedback);

$output = html_writer::nonempty_tag('div', $feedback,
    array('class' => 'specificfeedback'));

echo $output;

// $result = $repo->upload($saveas_filename, $maxbytes);
// ajax_check_captured_output();
// echo json_encode($result);

// echo 'alles paletti';
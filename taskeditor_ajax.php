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
 * This PHP file is used for uploading a task from Javascript taskeditor into the draft area
 * for later upload to grader.
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
$contextid = required_param('contextid', PARAM_INT); // Context ID
$itemid    = optional_param('itemid', 0, PARAM_INT);            // Itemid of task (draft)
// $maxbytes  = optional_param('maxbytes', 0, PARAM_INT);          // Maxbytes
// $areamaxbytes  = optional_param('areamaxbytes', FILE_AREA_MAX_BYTES_UNLIMITED, PARAM_INT); // Area max bytes.

// list($context, $course, $cm) = get_context_info_array($contextid);

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

$context = \context::instance_by_id($contextid, IGNORE_MISSING);
if (!isset($context)) {
    throw new moodle_exception('invalid context');
}

external_api::validate_context($context);
if ($context->contextlevel != CONTEXT_USER) {
    throw new moodle_exception('invalid context level');
}

// Since we're in the context of the user, it does not make sense checking course-level rights.
// require_capability('moodle/question:editmine', $context);



// Get repository instance information
/*
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
if ($context->id != $contextid) {
    throw new moodle_exception('invalid context');
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

$result = array(
    'itemid' => $itemid,
    'contextid' => $context->id,
    'filename' => $_FILES['task']['name']
);
ajax_check_captured_output();
echo json_encode($result);
die();


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
 * Plugin library
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

// phpcs:disable Squiz.PHP.CommentedOutCode.Found


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/filearea.php');

/**
 * Checks file access for proforma questions.
 *
 * Files belonging to a question attempt (or preview) are served via the standard
 * question_pluginfile() mechanism. Access is then checked by the module that owns the
 * question usage (e.g. quiz) and by qtype_proforma_question::check_file_access().
 *
 * Exception: task file and model solution files are only linked in the question edit form
 * (no question usage available). They are served to users who can view the question.
 *
 * @package  qtype_proforma
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function qtype_proforma_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');

    if ($filearea === qtype_proforma::FILEAREA_TASK || $filearea === qtype_proforma::FILEAREA_MODELSOL) {
        qtype_proforma_send_question_file($course, $cm, $context, $filearea, $args, $options);
        send_file_not_found();
    }

    question_pluginfile($course, $context, 'qtype_proforma', $filearea, $args, $forcedownload, $options);
}

/**
 * Sends a file that is linked outside a question attempt (edit form).
 * The user must be allowed to view the question.
 *
 * URL format: pluginfile.php/<contextid>/qtype_proforma/<filearea>/<questionid>/<filepath+filename>
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param array $options additional options affecting the file serving
 * @return void (does not return if the file is sent)
 */
function qtype_proforma_send_question_file($course, $cm, $context, $filearea, $args, array $options) {
    require_login($course, false, $cm);

    $questionid = (int)array_shift($args);
    if (!$questionid || !$args) {
        return;
    }
    question_require_capability_on($questionid, 'view');

    $filename = implode('/', $args);
    $fs = get_file_storage();
    // For historical reasons we use two approaches for retrieving the file
    // (I want to avoid updating database values to fit the new model).
    // New approach:
    // Properly split filename into path and basename.
    [$filepath, $basename] = qtype_proforma_filearea::split_filename($filename);
    $file = $fs->get_file($context->id, 'qtype_proforma', $filearea, $questionid, $filepath, $basename);
    if (!$file) {
        // Old approach:
        // Filename also contains path part.
        $file = $fs->get_file($context->id, 'qtype_proforma', $filearea, $questionid, '/', $filename);
    }
    if (!$file || $file->is_directory()) {
        return;
    }

    // We can now send the file back to the browser.
    send_stored_file($file, 0, 0, true, $options); // Download MUST be forced - security!
}

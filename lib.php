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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin library
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

/**
 * Checks file access for proforma questions.
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
function qtype_proforma_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG;

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_COURSE && $context->contextlevel != CONTEXT_COURSECAT) {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, false, $cm);

    // Make sure the filearea is one of those used by the plugin and:
    // Check the relevant capabilities - these vary depending on the filearea being accessed.
    switch ($filearea) {
        case qtype_proforma::FILEAREA_MODELSOL:
        case qtype_proforma::FILEAREA_TASK:
            if (!has_capability('moodle/question:editmine', $context)) {
                return false;
            }
            break;
        default:
            $pass = false;
            foreach (qtype_proforma::fileareas() as $proformafilearea => $value) {
                if ($proformafilearea === $filearea) {
                    if (!has_capability('moodle/question:flag', $context)) {
                        return false;
                    } else {
                        $pass = true;
                    }
                }
            }

            if (!$pass) {
                return false;
            }
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    // $filename = array_pop($args); // The last item in the $args array.

    if (!$args) {
        // echo 'no file given';
        return false;
    }

    $filename = implode('/', $args); // $args contains elements of the filepath
    $filepath = '/';

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'qtype_proforma', $filearea, $itemid, $filepath , $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser
    send_stored_file($file, 0, 0, true, $options); // download MUST be forced - security!
}

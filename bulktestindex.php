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
 * This script provdies an index for running the question tests in bulk.
 *
 * @package   qtype_proforma
 * @copyright 2021 Ostfalia University of Applied Sciences
 * based on same file for STACK (the Open University)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once(__DIR__.'/../../../config.php');

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/bulktester.php');

// Login and check permissions.
$context = context_system::instance();
require_login();
// Do not check access rights for system context here in order to allow
// managers or even teachers to check all questions within
// their course context or course.
// require_capability('qtype/proforma:runbulktest', $context);
$PAGE->set_url('/question/type/proforma/bulktestindex.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('bulktestindextitle', 'qtype_proforma'));


// Create the helper class.
$bulktester = new proforma_bulk_tester();

// Display.
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('replacedollarsindex', 'qtype_proforma'));

// Print link for courses with required capabilities.
echo html_writer::start_tag('ul');
foreach ($bulktester->get_proforma_questions_by_context() as $record) {
    // Check capability for course resp. course context.
    $numproformaquestions = $record->numproformaquestions;
    $contextid = $record->id;
    $contextinst = context::instance_by_id($contextid);
    // var_dump($coursecontext);
    if (has_capability('qtype/proforma:runbulktest', $contextinst)) {
        // Print course name.
        $coursename = $contextinst->get_context_name(true, false);
        if ($contextinst->get_course_context(false) !== false and isset($record->courseid)) {
            $coursename = '<a href="'. new moodle_url('/course/view.php', ['id' => $record->courseid]) . '">'.$coursename.'</a>';
        }
        echo html_writer::tag('li', $coursename . html_writer::link(
                new moodle_url('/question/type/proforma/bulktest.php', array('contextid' => $contextid)),
                ' (' . $numproformaquestions . ')'));
        if ($contextinst->get_course_context(false) !== false) {
            $courseid = $record->courseid;
            foreach ($bulktester->get_teachers($courseid) as $teacher) {
                echo html_writer::tag('small', $teacher->firstname . ' ' . $teacher->lastname . '<br>');
            }
        }
        // var_dump($coursecontext);
    } else {
        // No access rights.
        if (has_capability('moodle/site:config', context_system::instance())) {
            // Print course context name only if user is admin.
            echo html_writer::tag('li',
                    $contextinst->get_context_name(true, false) . ' NOT ENOUGH CAP');
        }
    }
}
echo html_writer::end_tag('ul');

// Print link for all questions in the system.
if (has_capability('moodle/site:config', context_system::instance())) {
    echo html_writer::tag('p', html_writer::link(
            new moodle_url('/question/type/proforma/bulktestall.php'), get_string('bulktestrun', 'qtype_proforma')));
}

echo $OUTPUT->footer();

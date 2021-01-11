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

require_once(__DIR__ . '/../../../config.php');

require_once(__DIR__ . '/classes/bulktester.php');

// Get the parameters from the URL. This is an option to restart the process
// in the middle. Useful if it crashes.
$startfromcontextid = optional_param('startfromcontextid', 0, PARAM_INT);

// Login and check permissions.
$context = context_system::instance();
require_login();
require_capability('moodle/site:config', $context);
$PAGE->set_url('/question/type/proforma/bulktestall.php',
        array('startfromcontextid' => $startfromcontextid));
$PAGE->set_context($context);
$title = get_string('bulktesttitle', 'qtype_proforma', $context->get_context_name());
$PAGE->set_title($title);

require_login();

// Create the helper class.
$bulktester = new proforma_bulk_tester();
$allpassed = true;
$allfailing = array();
$skipping = $startfromcontextid != 0;

// Release the session, so the user can do other things while this runs.
\core\session\manager::write_close();

// Display.
echo $OUTPUT->header();
echo $OUTPUT->heading($title, 1);

// Run the tests.
foreach ($bulktester->get_proforma_questions_by_context() as $contextid => $numproformaquestions) {
    if ($skipping && $contextid != $startfromcontextid) {
        continue;
    }
    $skipping = false;
    $testcontext = context::instance_by_id($contextid);

    echo $OUTPUT->heading(get_string('bulktesttitle', 'qtype_proforma', $testcontext->get_context_name()));
    echo html_writer::tag('p', html_writer::link(
            new moodle_url('/question/type/proforma/bulktestall.php',
                array('startfromcontextid' => $testcontext->id)),
            get_string('bulktestcontinuefromhere', 'qtype_proforma')));

    list($passed, $failing) = $bulktester->run_all_tests_for_context($testcontext);
    $allpassed = $allpassed && $passed;
    foreach ($failing as $key => $arrvals) {
        // Guard clause here to future proof any new fields from the bulk tester.
        if (!array_key_exists($key, $allfailing)) {
            $allfailing[$key] = array();
        }
        $allfailing[$key] = array_merge($allfailing[$key], $arrvals);
    }
}

// Display the final summary.
$bulktester->print_overall_result($allpassed, $allfailing);
echo $OUTPUT->footer();

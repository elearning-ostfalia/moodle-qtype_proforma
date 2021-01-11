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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/classes/feedback_renderer.php');
require_once($CFG->dirroot . '/question/type/proforma/renderer.php');


class proforma_bulk_tester  {

    /**
     * Get all the contexts that contain at least one ProFormA question, with a
     * count of the number of those questions.
     *
     * @return array context id => number of ProFormA questions.
     */
    public function get_proforma_questions_by_context() {
        global $DB;

        return $DB->get_records_sql_menu("
            SELECT ctx.id, COUNT(q.id) AS numproformaquestions
              FROM {context} ctx
              JOIN {question_categories} qc ON qc.contextid = ctx.id
              JOIN {question} q ON q.category = qc.id
             WHERE q.qtype = 'proforma'
          GROUP BY ctx.id, ctx.path
          ORDER BY ctx.path
        ");
    }

    /**
     * Run all the question tests for all variants of all questions belonging to
     * a given context.
     *
     * Does output as we go along.
     *
     * @param context $context the context to run the tests for.
     * @return array with two elements:
     *              bool true if all the tests passed, else false.
     *              array of messages relating to the questions with failures.
     */
    public function run_all_tests_for_context(context $context) {
        global $DB, $OUTPUT;

        // Load the necessary data.
        $categories = question_category_options(array($context));
        $categories = reset($categories);
        $questiontestsurl = new moodle_url('/question/question.php');
        switch ($context->contextlevel) {
            case CONTEXT_COURSE:
            case CONTEXT_COURSECAT:
                $questiontestsurl->param('courseid', $context->instanceid);
                break;
            case CONTEXT_MODULE:
                $questiontestsurl->param('cmid', $context->instanceid);
                break;
            default:
                debugging('unexpected contextlevel: ' . $context->contextlevel);
        }
        $allpassed = true;
        $failingtests = array();
        $notaskfile = array();
        $nomodelsolution = array();

        foreach ($categories as $key => $category) {
            list($categoryid) = explode(',', $key);
            echo $OUTPUT->heading(get_string('questioncategory', 'question'). ': ' . $category, 4);

            $questionids = $DB->get_records_menu('question',
                    array('category' => $categoryid, 'qtype' => 'proforma'), 'name', 'id,name');
            if (!$questionids) {
                // No ProFormA questions found.
                echo html_writer::tag('p', get_string('replacedollarscount', 'qtype_proforma', 0));
                continue;
            }

            // Write number of questions found.
            echo html_writer::tag('p', get_string('replacedollarscount', 'qtype_proforma', count($questionids)));

            foreach ($questionids as $questionid => $name) {

                $question = question_bank::load_question($questionid);
                $questionname = format_string($name);
                $questionnamelink = html_writer::link(new moodle_url($questiontestsurl,
                        array('id' => $questionid)), format_string($name));

                $questionproblems = array();
                // Load model solution.
                $modelsolution = self::load_model_solution($questionid, $context->id);
                if (empty($modelsolution)) {
                    $nomodelsolution[] = $questionnamelink;
                    $questionproblems[] = html_writer::tag('li', get_string('bulktestnomodelsolution', 'qtype_proforma'));
                }

                // Load ProFormA task file.
                $proformafile = self::load_proforma_file($questionid, $context->id);
                if (!isset($proformafile)) {
                    $notaskfile[] = $questionnamelink;
                    $questionproblems[] = html_writer::tag('li', get_string('bulktestnofile', 'qtype_proforma'));
                }

                if ($questionproblems !== array()) {
                    // Cannot run tests.
                    echo $OUTPUT->heading($questionnamelink, 5);
                    echo html_writer::tag('ul', implode("\n", $questionproblems));
                } else {
                    // Run tests.
                    $previewurl = new moodle_url($questiontestsurl, array('id' => $questionid));
                    $questionnamelink = html_writer::link($previewurl, $questionname);
                    echo $OUTPUT->heading($questionnamelink, 5);

                    list($ok, $message, $feedback) = $this->run_test_question($question, $proformafile, $modelsolution);
                    if (!$ok) {
                        $allpassed = false;
                        // Create text for summary.
                        $failingtests[] = $questionnamelink  . ': ' . $feedback;
                    }
                    echo html_writer::tag('div', $message);

                    // Do not write specific feedback here.
                    // It is not rendered properly while waiting for further
                    // responses of grader.
                    flush(); // Force output to prevent timeouts and to make progress clear.
                }
            }
        }
        $failing = array(
            'failingtests'      => $failingtests,
            'notests'           => $notaskfile,
            'nomodelsolution'   => $nomodelsolution);
        return array($allpassed, $failing);
    }

    /**
     * Run the tests for one variant of one question and display the results.
     *
     * @param qtype_proforma_question $question the question to test.
     * @param $proformafile proforma file to run.
     * @return array with two elements:
     *              bool true if the tests passed, else false.
     *              sring message summarising the number of passes and fails.
     */
    public function run_test_question($question, $proformafile, $modelsolution, $quiet = false) {
        $grader = new qtype_proforma_grader_2();

        $ok = false;
        $message = "";
        $feedback = "";
        $class = 'fail';
        core_php_time_limit::raise(60); // Prevent PHP timeouts.
        list($graderoutput, $httpcode) = $grader->send_files_with_task_to_grader(
                $modelsolution, $proformafile);
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
                    $fbrenderer = new feedback_renderer($renderer);
                    $feedback = $fbrenderer->render_proforma2_message($feedback, $question);
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

        return array($ok, $message, $feedback);
    }

    /**
     * Print an overall summary, with a link back to the bulk test index.
     *
     * @param bool $allpassed whether all the tests passed.
     * @param array $failingtests list of the ones that failed.
     */
    public function print_overall_result($allpassed, $failing) {
        global $OUTPUT;
        echo $OUTPUT->heading(get_string('overallresult', 'qtype_proforma'), 2);
        if ($allpassed) {
            echo html_writer::tag('p', get_string('proformaInstall_testsuite_pass', 'qtype_proforma'),
                    array('class' => 'overallresult pass'));
        } else {
            echo html_writer::tag('p', get_string('proformaInstall_testsuite_fail', 'qtype_proforma'),
                    array('class' => 'overallresult fail'));
        }

        foreach ($failing as $key => $failarray) {
            if (!empty($failarray)) {
                echo $OUTPUT->heading(get_string('proformaInstall_testsuite_' . $key, 'qtype_proforma'), 3);
                echo html_writer::start_tag('ul');
                foreach ($failarray as $message) {
                    echo html_writer::tag('li', $message);
                }
                echo html_writer::end_tag('ul');
            }
        }

        echo html_writer::tag('p', html_writer::link(new moodle_url('/question/type/proforma/bulktestindex.php'),
                get_string('back')));
    }


    public static function load_proforma_file($questionid, $contextid) {
        $fs = get_file_storage();
        $taskfiles = $fs->get_area_files($contextid, 'qtype_proforma',
            qtype_proforma::FILEAREA_TASK, $questionid, false, false);
        if (count($taskfiles) == 1) {
            return reset($taskfiles); // Get first item in array.
        } else {
            if (count($taskfiles) > 1) {
                debugging('old taskfile not unique');
            }
        }

        return null;
    }

    public static function load_model_solution($questionid, $contextid) {
        $fs = get_file_storage();
        return $fs->get_area_files($contextid, 'qtype_proforma',
            qtype_proforma::FILEAREA_MODELSOL, $questionid, false, false);
    }
}

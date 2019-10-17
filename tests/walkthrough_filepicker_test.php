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
// along with ProFormA Question Type for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains tests that walks a question using the filepicker (unit tests)
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2013 The Open University
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/tests/walkthrough_test_base.php');


class qtype_proforma_walkthrough_filepicker_testcase extends qtype_proforma_walkthrough_test_base {

    protected function check_answer_text($content = null, $isReadonly = false) {
        // has no answer field
    }

    public function run_test_one_try($preferredbehaviour) {

        // TODO : show that there is no feeback for students??

        // file attachments need a current user id. So this test must
        // be associated with a user!
        $this->setAdminUser();

        // Create a proforma question.
        $q = test_question_maker::make_question('proforma', 'filepicker');
        $this->start_attempt_at_question($q, $preferredbehaviour, 1);
        $this->prepare_test($preferredbehaviour, $q);

        // Process a response and check the expected result.
        $file = $this->upload_file(self::CORRECT_RESPONSE);
        $this->save_with_attachment(null, array($file));
        $this->check_not_yet_graded();

        // Submit blank (no file attached).
        $this->press_submit('', null);
        // verify, not accepted as valid response
        $this->check_invalid();

        // Submit correct answer as attachment
        $this->press_submit('', array($file));
        $this->check_graded_right();

        // Finish the attempt
        $this->finish_attempt();
        $this->check_graded_right(1.0);

        //        $this->assertRegExp('/' . preg_quote($response, '/') . '/', $this->currentoutput);
    }

    public function test_immediatefeedback() {
        $this->run_test_one_try('immediatefeedback');
    }

    public function test_deferred_feedback() {
        $this->run_test_one_try('deferredfeedback');
    }



/* TODO: check what test is actually doing ...
    public function test_deferredfeedback_attempt_on_last() {
        global $PAGE;

        $preferredbehaviour = 'deferredfeedback';
        $this->resetAfterTest(true);
        // user is needed for upload
        $this->setAdminUser();
        $PAGE->set_url('/');
        //$usercontextid = context_user::instance($USER->id)->id;
        //$fs = get_file_storage();

        // Create a proforma question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('proforma', 'filepicker', array('category' => $cat->id));

        // Start attempt at the question.
        $q = question_bank::load_question($question->id);
        $this->start_attempt_at_question($q, $preferredbehaviour, 1);
        $this->prepare_test($preferredbehaviour, $q);

        // Process a response and check the expected result.
        $file = $this->upload_file(self::CORRECT_RESPONSE);
        $this->save_with_attachment(null, array($file));
        $this->check_not_yet_graded();

        $steps = $this->expected_step_counter;
        $this->save_to_database();
        // Save the same response again, and verify no new step is created.
        $this->load_from_database();
        $this->check_step_count($steps);

        $this->save_with_attachment(null, array($file));

        $this->check_not_yet_graded();
        $this->check_step_count($steps);

        // Now submit all and finish.
        $this->finish_attempt();
        $this->check_graded_right(1.0);
        $this->save_to_database();

        // Now start a new attempt based on the old one.
        $this->load_from_database();
        $oldqa = $this->get_question_attempt();

        $q = question_bank::load_question($question->id);
        $this->quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());
        $this->quba->set_preferred_behaviour($preferredbehaviour);
        // SLOT = number of question within quiz (1.question)
        $this->slot = $this->quba->add_question($q, 1);
        $this->question = $q;
        $this->quba->start_question_based_on($this->slot, $oldqa);
        $this->prepare_test($preferredbehaviour, $this->quba->get_question(1));

        $this->check_step_count(1);
        $this->save_to_database();

        // Now save the same response again, and ensure that a new step is not created.
        $this->load_from_database();

        $this->save_with_attachment(null, array($file));

        $this->check_not_yet_graded();
        $this->check_current_mark(null);
        $this->check_step_count(2);

        // Finish the attempt
        $this->finish_attempt();
        $this->check_graded_right(1.0);
    }


    public function test_deferred_feedback_attempt_on_last_no_files_uploaded() {
        global $CFG, $USER, $PAGE;

        $preferredbehaviour = 'deferredfeedback';
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // user is needed for upload
        $PAGE->set_url('/');
        //$usercontextid = context_user::instance($USER->id)->id;
        //$fs = get_file_storage();

        // Create a proforma question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('proforma', 'filepicker', array('category' => $cat->id));

        // Start attempt at the question.
        $q = question_bank::load_question($question->id);
        $this->start_attempt_at_question($q, $preferredbehaviour, 1);

        $this->prepare_test($preferredbehaviour, $q);

        // Process a response and check the expected result.
        // First we need to get the draft item ids.
        $draftid = $this->determine_draftid();


        $this->save_with_attachment(null, array($draftid));
        $this->check_not_yet_graded();

        $this->save_to_database();
        $this->check_not_yet_graded();

        // Now submit all and finish.
        $this->finish_attempt();
        $this->check_gave_up();

        $this->save_to_database();

        // Now start a new attempt based on the old one.
        $this->load_from_database();
        $oldqa = $this->get_question_attempt();

        $q = question_bank::load_question($question->id);
        $this->quba = question_engine::make_questions_usage_by_activity('unit_test',
                context_system::instance());
        $this->quba->set_preferred_behaviour($preferredbehaviour);
        $this->slot = $this->quba->add_question($q, 1);
        $this->quba->start_question_based_on($this->slot, $oldqa);

        $this->prepare_test($preferredbehaviour, $q);
        $this->check_not_yet_graded();

        $this->save_to_database();

        // Check the display.
        $this->load_from_database();
        $this->check_not_yet_graded();
    }
*/

    public function run_test_multiple_tries_1($preferredbehaviour, $no_of_tries) {
        // user is needed for upload
        $this->setAdminUser();
        // Create a proforma question.
        $q = test_question_maker::make_question('proforma', 'filepicker');
        $this->start_attempt_at_question($q, $preferredbehaviour, 1);
        $this->prepare_test($preferredbehaviour, $q);

        // Process a response and check the expected result.
        $file1 = $this->upload_file(self::WRONG_RESPONSE);
        $this->save_with_attachment(null, array($file1));
        $this->check_not_yet_graded();


        $this->press_submit(null,  array($file1));
        $this->check_graded_wrong();

        // Try again.
        $this->press_try_again();

        // Submit blank.
        //$this->press_submit(null);
        //$this->check_invalid();

        // Submit correct answer.
        $file2 = $this->upload_file(self::CORRECT_RESPONSE);
        $this->press_submit(null, array($file2));
        $this->check_graded_right($preferredbehaviour != 'adaptivenopenalty'?0.8:1.0);

        // Finish the attempt
        $this->finish_attempt();
        // Verify  => response is graded
        $this->check_graded_right($preferredbehaviour != 'adaptivenopenalty'?0.8:1.0);
    }

    public function test_interactive_editor_multiple_tries_1()
    {
        $this->run_test_multiple_tries_1('interactive', self::interactive_tries);
    }

    public function test_adaptive_editor_multiple_tries_1()
    {
        $this->run_test_multiple_tries_1('adaptive', self::interactive_tries);
    }

    public function test_adaptivenopenalty_editor_multiple_tries_1()
    {
        $this->run_test_multiple_tries_1('adaptivenopenalty', self::adaptivenopenalty_tries);
    }
}

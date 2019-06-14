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
 * This file contains tests that walks a question using the editor
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2013 The Open University*
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die();


// TODO: finish with wrong response for one attempt
// TODO: finish with no response


global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/tests/walkthrough_test_base.php');



/*
 * state machine 'adaptive with feedback:
 * - start: invalid(?)
 * - submit: complete (= mark = 1.0) or todo
 * - finish: state from grader??
 */

class qtype_proforma_walkthrough_editor_testcase extends qtype_proforma_walkthrough_test_base {

    private $behaviours_multiple_tries = array(
            array('interactive', self::interactive_tries),
            array('adaptive', self::interactive_tries),
            array('adaptivenopenalty', self::adaptivenopenalty_tries)
    );


    protected function run_on_all_behaviours($testfunction) {
        // easier for debugging:
        print('adaptivenopenalty' .PHP_EOL);flush();
        $testfunction('adaptivenopenalty');
        print('adaptive'.PHP_EOL);flush();
        $testfunction('adaptive');
        print('immediatefeedback'.PHP_EOL);flush();
        $testfunction('immediatefeedback');
        print('deferredfeedback'.PHP_EOL);flush();
        $testfunction('deferredfeedback');
        print('interactive'.PHP_EOL);flush();
        $testfunction('interactive');
        print('interactivecountback'.PHP_EOL);flush();
        $testfunction('interactivecountback');
        print('immediatecbm'.PHP_EOL);
        $testfunction('immediatecbm');

    }

    protected function run_on_mulitiple_tries_behaviours($testfunction) {
        // easier for debugging:
        $testfunction('adaptivenopenalty', self::adaptivenopenalty_tries);
        $testfunction('adaptive', self::interactive_tries);
        $testfunction('interactive', self::interactive_tries);

        // todo: müsste interactivecountback nicht auch funktionieren????
        //$testfunction('interactivecountback', self::interactive_tries);

        //$testfunction('immediatefeedback');
        //$testfunction('immediatecbm', self::interactive_tries);
    }


    /**
     * tests a correct answer
     */
    public function test_correct() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // TODO : show that there is no feeback for students??

            // The current text editor depends on the users profile setting - so it needs a valid user.
            // $this->setAdminUser();

            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::CORRECT_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded(self::CORRECT_RESPONSE);

            // Submit blank.
            $this->press_submit('');
            // verify, not accepted as valid response
            $this->check_invalid();

            // Submit correct answer.
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right();

            // Finish the attempt
            $this->finish_attempt();
            $this->check_graded_right(1.0);
        });
    }


    /* tests a wrong answer with finishing attempt  */
    public function test_wrong() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // TODO : show that there is no feeback for students??

            // The current text editor depends on the users profile setting - so it needs a valid user.
            // $this->setAdminUser();

            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit wrong answer.
            $this->press_submit(self::WRONG_RESPONSE);
            $this->check_graded_wrong();

            // Finish the attempt
            $this->finish_attempt();

            // Verify  => response is graded
            $this->check_graded_wrong();

            //        $this->assertRegExp('/' . preg_quote($response, '/') . '/', $this->currentoutput);
        });
    }


    public function test_correct_but_internal_grading_error() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::CORRECT_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Submit correct answer
            $this->force_internal_grading_error = true;
            $this->press_submit(self::CORRECT_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(true, true);

            // Finish the attempt
            $this->force_internal_grading_error = true;
            $this->finish_attempt();
            $this->check_needs_grading(true);
        });
    }

    public function test_correct_but_internal_grading_error_1() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
                // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::CORRECT_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Submit correct answer
            $this->force_internal_grading_error = true;
            $this->press_submit(self::CORRECT_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(true, true);

            // Finish the attempt
            $this->force_internal_grading_error = false;
            $this->finish_attempt();
            $this->check_graded_right();
        });
    }

    public function test_wrong_but_internal_grading_error_1() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::WRONG_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Submit correct answer
            $this->force_internal_grading_error = true;
            $this->press_submit(self::WRONG_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(true, true);

            // Finish the attempt
            $this->force_internal_grading_error = false;
            $this->finish_attempt();
            $this->check_graded_wrong();
        });
    }

    public function test_wrong_with_internal_grading_error() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::WRONG_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Submit blank.
            $this->force_internal_grading_error = true;
            $this->press_submit(self::WRONG_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(true, true);

            // Finish the attempt
            $this->force_internal_grading_error = true;
            $this->finish_attempt();
            $this->check_needs_grading(true);
        });
    }

    public function test_correct_after_wrong_internal_error() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit wrong response
            $this->press_submit(self::WRONG_RESPONSE);
            $this->check_graded_wrong(0.0);

            // Submit correct answer
            $this->force_internal_grading_error = true;
            $this->press_submit(self::CORRECT_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(false, true);

            // Finish the attempt
            $this->force_internal_grading_error = true;
            $this->finish_attempt();
            $this->check_needs_grading(false);
        });
    }

    public function test_wrong_after_correct_internal_error() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit correct response
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right(1.0);

            // Submit wrong answer
            $this->force_internal_grading_error = true;
            $this->press_submit(self::WRONG_RESPONSE);
            // verify, not accepted as valid response
            $this->check_invalid(false, true);

            // Finish the attempt
            $this->force_internal_grading_error = true;
            $this->finish_attempt();
            $this->check_needs_grading(false);
        });
    }


    /* tests a wrong answer using weighted sum  */
    public function test__weighted_sum_wrong() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'weightedsum');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit wrong answer.
            $this->press_submit(self::WRONG_RESPONSE);
            $this->check_graded_wrong(0.6);

            // Finish the attempt
            $this->finish_attempt();

            // Verify  => response is graded
            $this->check_graded_wrong(0.6);

            //        $this->assertRegExp('/' . preg_quote($response, '/') . '/', $this->currentoutput);
        });
    }


    /* tests a completely wrong answer with finishing attempt  */
    public function test_weighted_sum_completely_wrong() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'weightedsum');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit wrong answer.
            $this->press_submit(self::WRONG_RESPONSE_2);
            $this->check_graded_wrong(0.0);

            // Finish the attempt
            $this->finish_attempt();

            // Verify  => response is graded
            $this->check_graded_wrong(0.0);
        });
    }

    /* tests a correct answer with finishing attempt  */
    public function test_weighted_sum_correct() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'weightedsum');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit answer.
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right(1.0);
            // Finish the attempt
            $this->finish_attempt();
            // Verify  => response is graded
            $this->check_graded_right(1.0);
        });
    }

    /**
     * tests what happens if the student types a correct answer and finishes the
     * question without submission => the answer is graded right
     */
    public function test_correct_finish_without_submit() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::CORRECT_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Finish the attempt
            $this->finish_attempt();
            // Verify  => response is graded
            $this->check_graded_right( 1.0);
        });
    }

    /**
     * save correct, submit blank, submit correct, finish
     */
    public function test_test_multiple_tries_1() {
        $this->run_on_mulitiple_tries_behaviours(function($preferredbehaviour, $no_of_tries) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Save a correct response.
            $this->save(self::CORRECT_RESPONSE);
            // Verify, not yet graded
            $this->check_not_yet_graded();

            // Submit blank.
            $this->press_submit('');
            $this->check_invalid();

            // Submit correct answer.
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right();

            // Finish the attempt
            $this->finish_attempt();
            // Verify  => response is graded
            $this->check_graded_right(1.0);
        });
    }

    /**
     * submit wrong, submit blank, finish
     */
    public function test_test_multiple_tries_2() {
        $this->run_on_mulitiple_tries_behaviours(function($preferredbehaviour, $no_of_tries) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // submit a wrong response .
            $this->press_submit(self::WRONG_RESPONSE);
            $this->check_graded_wrong();

            // Try again.
            $this->press_try_again();

            // Submit blank.
            $this->press_submit('');
            $this->check_invalid(false);

            $this->check_current_output($this->get_does_not_contain_try_again_button_expectation());
            //        $this->press_try_again();


            /*        // Submit correct answer.
                    $this->press_submit(self::CORRECT_RESPONSE);
                    $this->check_graded_right();
                    $this->check_remaining_tries(self::tries_remaining_correct);
            */
            // Finish the attempt
            $this->finish_attempt();
            // Verify  => response is somehow ionvalid (gave up state)
            $this->check_gave_up();
        });


/*        foreach($this->behaviours_multiple_tries as $behaviour) {
            $this->run_test_multiple_tries_2($behaviour[0], $behaviour[1]);
        }*/
    }


    /**
     * - try again button
     * - multiple tries possible
     * - penalty for wrong responses
     */
    public function test_test_multiple_tries_3() {
        $this->run_on_mulitiple_tries_behaviours(function($preferredbehaviour, $no_of_tries) {
            // Create a proforma question.
            $q = test_question_maker::make_question('proforma', 'editor');
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Submit wrong answer.
            $this->press_submit(self::WRONG_RESPONSE);
            $this->check_graded_wrong();

            // Try again.
            $this->press_try_again();
            // Submit correct answer.
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right($preferredbehaviour != 'adaptivenopenalty'?0.8:1.0);

            // Finish the attempt
            $this->finish_attempt();

            // Verify  => response is graded
            $this->check_graded_right($preferredbehaviour != 'adaptivenopenalty'?0.8:1.0); // , true);
        });
    }

    // tests bases on similar essay test idea
    // - save andd finish answer
    // - reload from database
    public function test_attempt_on_last() {
        $this->run_on_all_behaviours(function($preferredbehaviour) {

            global $CFG, $USER;

            $this->resetAfterTest(true);
            $this->setAdminUser();
            $usercontextid = context_user::instance($USER->id)->id;

            // Create a proforma question in the DB.
            $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
            $cat = $generator->create_question_category();
            $question = $generator->create_question('proforma', 'editor', array('category' => $cat->id));

            // Start attempt at the question.
            $q = question_bank::load_question($question->id);
            $this->start_attempt_at_question($q, $preferredbehaviour, 1);
            $this->prepare_test($preferredbehaviour, $q);

            // Process a response and check the expected result.

            // Submit correct answer.
            $this->press_submit(self::CORRECT_RESPONSE);
            $this->check_graded_right();

            // save without finishing
            $this->save_to_database();
            $this->check_graded_right();

            // Finish.
            $this->finish_attempt(); // finish();
            $this->check_graded_right(1.0); // , true);
            $this->save_to_database();

            // Now start a new attempt based on the old one.
            $this->load_from_database();
            $oldqa = $this->get_question_attempt();

            $q = question_bank::load_question($question->id);
            $this->quba = question_engine::make_questions_usage_by_activity('unit_test',
                    context_system::instance());
            $this->quba->set_preferred_behaviour($preferredbehaviour);
            $this->slot = $this->quba->add_question($q, 1);
            $this->question = $q;
            $this->quba->start_question_based_on($this->slot, $oldqa);


            $this->check_current_state(question_state::$complete);

            $this->check_current_mark(null);
            $this->check_step_count(1);
            $this->save_to_database();

            // Check the display.
            $this->load_from_database();
            $this->render();
            $this->check_answer_text(self::CORRECT_RESPONSE);
            $this->check_current_mark(null);
            $this->check_step_count(1);

            // Test for the hash of an empty file area.
            $this->assertNotContains('d41d8cd98f00b204e9800998ecf8427e', $this->currentoutput);
        });
    }


}

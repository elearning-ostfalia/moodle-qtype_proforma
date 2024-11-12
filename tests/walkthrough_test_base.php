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
 * support function for walkthrough tests
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2013 The Open University
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use PHPUnit\Runner\Version as PHPUnitVersion;

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

class qtype_proforma_walkthrough_test_base extends qbehaviour_walkthrough_test_base {

    const EXPECTED_BEHAVIOUR = "adaptiveexternalgrading";
    //const EXPECTED_BEHAVIOUR = "interactivewithfeedback";

    /**  true: do not use actual grader (use test double) */
    const USE_TEST_DOUBLE = true;

    // string constants
    const CORRECT_RESPONSE = 'public class MyString
{
	static public String flip( String aString)
	{
		StringBuilder sb = new StringBuilder();

		for (int i = 0; i < aString.length(); i++)
			sb.append(aString.charAt(aString.length()-1-i));

		return sb.toString();
	}
}';
    const WRONG_RESPONSE = 'attempt 1';
    const WRONG_RESPONSE_2 = 'attempt 2';

    const GRADER_OUTPUT_CORRECT = array('<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <submission-feedback-list>
      <student-feedback level="debug">
        <title>title1</title>
        <content format="html">content1</content>
        <filerefs>
        </filerefs>
      </student-feedback>
      <teacher-feedback level="debug">
        <title>title1</title>
        <content format="plaintext">content4</content>
        <filerefs>
        </filerefs>
      </teacher-feedback>
    </submission-feedback-list>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>1.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="error">
              <title>MyString cannot be resolved to a variable</title>
              <content format="plaintext">Sample.java	line 55</content>
              <filerefs>
              </filerefs>
            </student-feedback>
            <student-feedback level="error">
                <title>Inline cannot be resolved</title>
              <content format="plaintext">Sample.java	line 56</content>
              <filerefs>
              </filerefs>
            </student-feedback>
            <teacher-feedback level="debug">
              <title>Java-Compilation (teacher)</title>
              <content format="html">content11</content>
              <filerefs>
              </filerefs>
            </teacher-feedback>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>1.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="debug">
              <title>JUnit</title>
              <content format="html">content15</content>
              <filerefs>
              </filerefs>
            </student-feedback>
            <teacher-feedback level="debug">
              <title>JUnit</title>
              <content format="plaintext">content18</content>
              <filerefs>
              </filerefs>
            </teacher-feedback>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data>
    <grader-engine name="praktomat" version="xyz" />
  </response-meta-data>
</response>', 200);

    const GRADER_OUTPUT_INCORRECT = array('<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <submission-feedback-list>
      <student-feedback level="debug">
        <title>title1</title>
        <content format="html">content1</content>
        <filerefs>
        </filerefs>
      </student-feedback>
    </submission-feedback-list>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="error">
              <title>MyString cannot be resolved to a variable</title>
              <content format="plaintext">Sample.java	line 55</content>
              <filerefs>
              </filerefs>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>1.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="debug">
              <title>JUnit</title>
              <content format="html">content15</content>
              <filerefs>
              </filerefs>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data>
    <grader-engine name="praktomat" version="xyz" />
  </response-meta-data>
</response>', 200);


    const GRADER_OUTPUT_COMPLETELY_INCORRECT = array('<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <submission-feedback-list>
    </submission-feedback-list>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="error">
              <title>MyString cannot be resolved to a variable</title>
              <content format="plaintext">Sample.java	line 55</content>
              <filerefs>
              </filerefs>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="debug">
              <title>JUnit</title>
              <content format="html">content15</content>
              <filerefs>
              </filerefs>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data>
    <grader-engine name="praktomat" version="xyz" />
  </response-meta-data>
</response>', 200);

    const GRADER_OUTPUT_ERROR_NO_SUBMISSION = array('<loncapagrade>
        <awarddetail>ERROR</awarddetail>
        <message><![CDATA[No path to student submission or submission attached]]></message>
        <awarded></awarded>
        </loncapagrade>', 200);

    const GRADER_OUTPUT_INTERNAL_ERROR = array('internal grader error', 404);

    const interactive_tries = 5;
    const adaptivenopenalty_tries = 15;

    const STATE_CORRECT = 'Correct';
    const STATE_INVALID = 'Invalid';
    const STATE_WRONG = 'Incorrect';
    const STATE_WEIGHTED_SUM_WRONG = 'Partially correct';

    const STATE_INCOMPLETE = 'Incomplete answer';

    protected $force_internal_grading_error = false;

    // track question relevant data
    protected $expected_step_counter = 1;
    protected $last_state = null;
    protected $current_state = null;

    protected $last_response = null;
    protected $current_response = null;

    protected $last_attachments = null;
    protected $current_attachments = null;

    protected $is_graded = false;
    protected $is_finished = false;
    protected $finish_pending = false;
    protected $preferredbehaviour = null;
    protected $remainingtries = 0;
    protected $question = null;

    /** @var array stores the uploaded files  data */
    protected $files = []; // could be static??


    protected function prepare_test($preferredbehaviour, &$q,
            $responsetemplate = qtype_proforma_test_helper::QUESTION_TEMPLATE) {
        $this->expected_step_counter = 1;

        $this->last_state = null;
        $this->current_state = null;

        $this->last_response = null;
        $this->current_response = null;

        $this->last_attachments = null;
        $this->current_attachments = null;

        $this->is_graded = false;
        $this->is_finished = false;

        $this->remainingtries = 0;

        $this->force_internal_grading_error = false;



        $this->preferredbehaviour = $preferredbehaviour;
        switch ($preferredbehaviour) {
            case 'interactive': $this->remainingtries = self::interactive_tries; break;
            case 'adaptive': $this->remainingtries = self::interactive_tries; break;
            case 'adaptivenopenalty': $this->remainingtries = self::adaptivenopenalty_tries; break;
            default: $this->remainingtries = 1; break;
        }
        $this->question = $q;
        // Create question category.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $q->contextid = $generator->create_question_category(array())->contextid;

        // check the behaviour is changed
        $this->assertEquals(self::EXPECTED_BEHAVIOUR,
                $this->quba->get_question_attempt($this->slot)->get_behaviour_name());
        // Check the initial state.
        $this->check_not_yet_graded($responsetemplate);

        // $files = null; //??
    }


    protected function set_mockbuilder_for_grader(qtype_proforma_question $q) {
        $response = $this->get_response_text();
        if (self::USE_TEST_DOUBLE) {
            // Create a stub for the grader
            $stub = $this->getMockBuilder(qtype_proforma_grader_2::class)
                    ->setMethods(['send_code_to_grader', 'send_files_to_grader'])
                    ->getMock();

            // Configure the stub.
            if ($this->force_internal_grading_error) {
                $stub->method('send_code_to_grader')
                        ->willReturn(self::GRADER_OUTPUT_INTERNAL_ERROR);
                $stub->method('send_files_to_grader')
                        ->willReturn(self::GRADER_OUTPUT_INTERNAL_ERROR);
                $this->force_internal_grading_error = false;
            } else {
                if ($this->is_graded && $this->finish_pending) {
                    // expect stub never to be called
                    $stub->expects($this->never())->method('send_code_to_grader');
                    $stub->expects($this->never())->method('send_files_to_grader');
                } else {
                    //$stub->expects($this->atLeastOnce());
                    switch ($response) {
                        case self::CORRECT_RESPONSE:
                            $stub->method('send_code_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_CORRECT);
                            $stub->method('send_files_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_CORRECT);
                            break;
                        case self::WRONG_RESPONSE:
                            $stub->method('send_code_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_INCORRECT);
                            $stub->method('send_files_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_INCORRECT);
                            break;
                        case self::WRONG_RESPONSE_2:
                            $stub->method('send_code_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_COMPLETELY_INCORRECT);
                            $stub->method('send_files_to_grader')
                                    ->willReturn(self::GRADER_OUTPUT_COMPLETELY_INCORRECT);
                            break;
                        case '':
                            // no grading
                            break;
                        default:
                            $this->assert(true, false);
                            break;
                    }
                }

            }
            $q->grader = $stub;
        }
    }

    /**
     * Helper method: Store a test file with a given name and contents in a
     * draft file area.
     *
     * @param int $usercontextid user context id.
     * @param int $draftitemid draft item id.
     * @param string $filename filename.
     * @param string $contents file contents.
     */
    private function save_file_to_draft_area($usercontextid, $draftitemid, $filename, $contents) {
        $fs = get_file_storage();

        $filerecord = new stdClass();
        $filerecord->contextid = $usercontextid;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = $draftitemid;
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;
        $fs->create_file_from_string($filerecord, $contents);
    }

    protected function save_response($response, $sequencecheck = '1') {
        $prefix = $this->quba->get_field_prefix($this->slot);
        $fieldname = $prefix . 'answer';
        $this->quba->process_all_actions(null, array(
                'slots'                    => $this->slot,
                $fieldname                 => $response,
                $fieldname . 'format'      => FORMAT_HTML,
                $prefix . ':sequencecheck' => $sequencecheck,
        ));
    }

    /** iterates through array and removes all attached ids that do not match a file  */
    private function get_attachments_with_content($attachment) {
        if (is_null($attachment)) {
            return $attachment;
        }

        $new_array = [];

        foreach ($attachment as $id) {
            if (array_key_exists($id, $this->files)) {
                $new_array[] = $id;
            }
        }

        return $new_array;
    }


    private function is_same_response() {
        if (($this->last_response != $this->current_response)) {
            return false;
        }

        $last = $this->get_attachments_with_content($this->last_attachments);
        $current = $this->get_attachments_with_content($this->current_attachments);
        if (empty($last) && empty($current)) {
            // no attachments and same editor conent
            return true;
        }

        // => at least one array is not empty
        if (!empty($last) && !empty($current)) {
            // => both arrays have content
            return (count(array_diff($last, $current)) == 0);
        }

        return false;
    }

    private function store_response_data($response, $attachedfiles) {
        if (isset($attachedfiles) && count($attachedfiles) > 1)
            // prohibit crashing in process_submission
            throw new coding_exception('Moodle *test* environment does not support arrays for attachements, sorry!');

        $this->last_response = $this->current_response;
        $this->current_response = $response;

        $this->last_attachments = $this->current_attachments;
        $this->current_attachments = $attachedfiles;
    }

    private function get_response_text() {
        // TODO: handle response with file and text
        if ($this->current_response)
            return $this->current_response;

        if ($this->current_attachments) {
            if (!array_key_exists($this->current_attachments[0], $this->files)) {
                return '';
            }

            $file = $this->files[$this->current_attachments[0]];
            return $file['content'];
        }

        return '';
    }


    protected function get_contains_specific_feedback_expectation() {
        return new question_pattern_expectation('/class="specificfeedback"/');
    }
    protected function get_contains_feedback_expectation() {
        return new question_pattern_expectation('/class="feedback"/');
    }


    // CHECKS

    protected function check_contains_class_and_content($classname, $content, $fullcontent = true) {
        $xmlDoc = new DOMDocument();
        // use of '&nbsp' prevents loadXML from loading a document!
        $output = str_replace('&nbsp', ' ', $this->currentoutput);
        // remove <xmp ...* ...</xmp>
        $output = preg_replace('/\<xmp[\s\S]*?\<\/xmp\>/m', '', $output);


        // $this->assertTrue($xmlDoc->loadXML($output, LIBXML_NOERROR ), 'invalid XML output from renderer');
        $this->assertTrue($xmlDoc->loadXML($output  ), 'invalid XML output from renderer');
        // LIBXML_NOERROR is set in order to ignore errors in result

        $xpath = new DomXPath($xmlDoc);

        $elements = $xpath->query("//div[@class='$classname']");
        $this->assertTrue(count($elements) == 1);

        if ($content) {
            $content1 = preg_replace('/\r\n?/', "\n", $content);

            if ($fullcontent) {
                $element = $elements[0]->nodeValue;
                $element1 = preg_replace('/\r\n?/', "\n", $element);
                $this->assertSame($content1, $element1);
            } else {
                $element = $elements[0]; // leave DOM element
                $elementFull = $element->ownerDocument->saveXML($element);
                $element1 = preg_replace('/\r\n?/', "\n", $elementFull);
                if (PHPUnitVersion::id() >= 9) {
                    $this->assertMatchesRegularExpression('/' . preg_quote(s($content1), '/') . '/', $element1);
                } else {
                    $this->assertRegExp('/' . preg_quote(s($content1), '/') . '/', $element1);
                }
            }
        } else {
            $this->assertTrue(empty($elementFull));
        }
    }

/*    protected function check_contains_submit() {
        $this->assertTag(array('tag' => 'input', 'attributes' => array('type' => 'submit')),
                $this->currentoutput);
    }
*/
    protected function check_contains_textarea($name, $content = '', $height = 10) {
        $fieldname = $this->quba->get_field_prefix($this->slot) . $name;

        $this->assertTag(array('tag' => 'textarea',
                'attributes' => array('cols' => '60', 'rows' => $height,
                        'name' => $fieldname)),
                $this->currentoutput);

        if (!is_null($content)) {
            if (PHPUnitVersion::id() >= 9) {
                $this->assertMatchesRegularExpression('/' . preg_quote(s($content), '/') . '/', $this->currentoutput);
            } else {
                $this->assertRegExp('/' . preg_quote(s($content), '/') . '/', $this->currentoutput);
            }
        }
    }

    protected function check_verify_button_enabled($exists = true, $enabled = true) {
        $doc = new DOMDocument();
        $doc->loadHTML($this->currentoutput, LIBXML_NOERROR);
        $options = array();
        // $options['tag'] = 'button';
        $options['class'] = 'submit';
        // $options['attributes'] = 'disabled';
        $result = $this->findNodes($doc, $options);
        if (!$exists) {
            $this->assertFalse($result);
            return;
        }
        $this->assertNotFalse($result);
        $this->assertEquals(1, count($result));
        $element = $result[0];
        $this->assertNotEquals(null, $element);
        $value = $element->getAttribute('disabled');
        if ($enabled)
            $this->assertEquals('', $value);
        else
            $this->assertEquals('disabled', $value);
    }

    protected function check_answer_text($content = null, $isReadonly = false) {
        if (is_null($content))
            $content = $this->current_response;

        if (!$isReadonly) {
            $this->check_contains_textarea('answer', $content);
        } else {
            $fieldname = $this->quba->get_field_prefix($this->slot) . 'answer';
            $this->assertNotTag(array('tag' => 'textarea',
                    'attributes' => array('name' => $fieldname)),
                    $this->currentoutput);
        }

        $doc = new DOMDocument();
        $doc->loadHTML($this->currentoutput, LIBXML_NOERROR);
        $xpath = new DOMXpath($doc);

        // alle <a href="..."> innerhalb von <div class="blog">
        // CSS-Selektor: div.blog a[href]
        // z.B. JavaScript: document.querySelector('div.blog a[href]')
        $textareas = $xpath->query('//div[@class="answer"]//textarea');
        $answertext = '???';
        foreach ($textareas as $textarea) {
            //echo $textarea->nodeValue,PHP_EOL;
            $answertext = $textarea->nodeValue;
        }

        $this->assertEquals($content, $answertext);
        //$this->check_contains_class_and_content('answer', $content);
    }

//    protected function check_remaining_tries($numberOfTries, $incorrect = false) {
    protected function check_remaining_tries($state = "Not complete", $numberOfTries = null) {
        if (!is_null($numberOfTries)) {
            switch (self::EXPECTED_BEHAVIOUR) {
                case "adaptiveexternalgrading":
                    //if ($incorrect)
                        $this->check_contains_class_and_content('state',
                                $state); //self::TRIES_REMAINING_WRONG);
                    //else
                    //    $this->check_contains_class_and_content('state',
                    //            "Not complete");
                    break;
                case "interactivewithfeedback":
                    $this->check_contains_class_and_content('state',
                            'Tries remaining: ' . $numberOfTries);
                    break;
                default:
                    $this->assertFalse(true); // ???
            }
        } else {
            $this->check_contains_class_and_content('state', $state);
        }
    }

    protected function check_specific_feedback_text($content) {
        $this->check_contains_class_and_content('specificfeedback', $content, false);
    }





    protected function check_current_state($state) {
        // remember state
        $this->last_state = $this->current_state;
        $this->current_state = $state;
        parent::check_current_state($state);
    }

    protected function check_graded_right($mark = 1.0) {
        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                $this->check_current_state(question_state::$gradedright);
                break;
            case "adaptiveexternalgrading":
                if ($this->is_finished) {
                    $this->check_current_state(question_state::$gradedright);
                }
                else {
                    $this->check_current_state(question_state::$complete);
                }
                break;
            default:
                $this->assertFalse(true);
        }
        $this->check_step_count($this->expected_step_counter);
        $this->check_current_mark($mark);
        $this->render();
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback' && !$this->is_finished;
        $this->check_verify_button_enabled($verify_exists);
        $this->check_answer_text($this->current_response, // self::CORRECT_RESPONSE,
                (self::EXPECTED_BEHAVIOUR == "interactivewithfeedback" or $this->is_finished)?true:false);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                //            $this->get_contains_general_feedback_expectation($q),
                $this->get_contains_specific_feedback_expectation());
        // $this->check_specific_feedback_text('Your answer is correct.');
        $this->check_remaining_tries(self::STATE_CORRECT);
    }


    // adaptive: final grade if finished
    // interactive: final grade on submit
    protected function check_graded_wrong($mark = 0.0) {
        $mustbegraded = true;
        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                // answer must be graded if it is the last try
                $mustbegraded = ($this->remainingtries == 0);
                break;
            case "adaptiveexternalgrading":
                // answer is graded if it is finished
                $mustbegraded = $this->is_finished;
                break;
            default:
                $this->assertFalse(true);
        }
        if ($mustbegraded) {
            if ($mark === 0.0)
                $this->check_current_state(question_state::$gradedwrong);
            else
                $this->check_current_state(question_state::$gradedpartial);
            $this->check_current_mark($mark);
        } else {
            $this->check_current_state(question_state::$todo);
            // mark is set for adaptive but not for interactive
            switch (self::EXPECTED_BEHAVIOUR) {
                case "interactivewithfeedback":
                    // state is todo
                    $this->check_current_mark(null);
                    // $this->check_current_mark(0.0);
                    break;
                case "adaptiveexternalgrading":
                    //$this->check_current_mark(null);
                    $this->check_current_mark($mark);
                    break;
                default:
                    $this->assertFalse(true);
            }
        }

        $this->render();
        // answer field is disabled because you have to press 'Try again' Button
        // $this->check_answer_text($response, true); //????
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback' && !$this->is_finished;
        $this->check_verify_button_enabled($verify_exists);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                // $this->get_contains_general_feedback_expectation($q),
                $this->get_contains_specific_feedback_expectation());
        // $this->check_specific_feedback_text('Your answer is not completely correct.');
        $remainingtries = null;

        if (!$this->is_finished && $this->remainingtries) {
            $remainingtries = $this->remainingtries;
        }
        if ($mark === 0.0)
            $this->check_remaining_tries(self::STATE_WRONG, $remainingtries);
        else
            $this->check_remaining_tries(self::STATE_WEIGHTED_SUM_WRONG, $remainingtries);

        $this->check_step_count($this->expected_step_counter);
    }

    protected function is_deferred() : bool {
        return ($this->preferredbehaviour == 'deferredfeedback');
    }
    protected function check_not_yet_graded($answer = null) {
        // $this->step_counter
        $this->check_step_count($this->expected_step_counter);
        $expectedstate = question_state::$todo;
        if ($this->is_deferred()) {
            if (!empty($this->current_response) || !empty($this->current_attachments)) {
                $expectedstate = question_state::$complete;
            }
        }
        $this->check_current_state($expectedstate);
        $this->check_current_mark(null);
        $this->render();
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback';
        $this->check_verify_button_enabled($verify_exists);
        $this->check_answer_text($answer);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_specific_feedback_expectation());
        $expectedstate = "Not complete";
        if ($this->is_deferred()) {
            if (empty($this->current_response) && empty($this->current_attachments)) {
                $expectedstate = "Not yet answered";
            } else {
                $expectedstate = "Answer saved";
            }
        }
        $this->check_remaining_tries($expectedstate, $this->remainingtries);
    }


    protected function check_gave_up()
    {
        $this->check_current_state(question_state::$gaveup);
        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                $this->check_current_mark(0.0); // should be 0 // null);
                break;
            case "adaptiveexternalgrading":
                // evtl. wird der Wert nicht geändert, so
                // dass der letzte Wert erhalten bleibt ???
                $this->check_current_mark(0.0);
                break;
            default:
                $this->assertFalse(true);
        }

        $this->render();
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback' && !$this->is_finished;
        $this->check_verify_button_enabled($verify_exists);
        $this->check_answer_text(null, true);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                //$this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_specific_feedback_expectation());
        $this->check_remaining_tries("Not answered");
    }

    protected function check_invalid($initialstate = true, $internalerror = false) {

        if ($internalerror) {
            //if ($this->is_finished)
            //    $this->check_current_state(question_state::$needsgrading) ; // $needsgrading);
            //else
                $this->check_current_state(question_state::$invalid); // $invalid); // $todo);
        }
        else {
            // empty response or start state
            $this->check_current_state(question_state::$invalid);
        }

        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                $this->check_current_mark(null);
                break;
            case "adaptiveexternalgrading":
                // evtl. wird der Wert nicht geändert, so
                // dass der letzte Wert erhalten bleibt ???
                if ($internalerror)
                    $this->check_current_mark(null);
                else {
                    //if (!$initialstate)
                    //    $this->check_current_mark(0.0);
                    //else
                        $this->check_current_mark(null);
                }
                break;
            default:
                $this->assertFalse(true);
        }

        $this->render();
        $this->check_answer_text();
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback';
        $this->check_verify_button_enabled($verify_exists);
//        if (!$this->is_finished)
//            $this->check_contains_submit();
/*        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                $this->get_does_not_contain_feedback_expectation(),
                $this->get_does_not_contain_specific_feedback_expectation());*/
        $this->check_current_output($this->get_contains_question_text_expectation($this->question));
        if ($internalerror)
            $this->check_output_contains('INTERNAL ERROR');
        else {
            $this->check_current_output($this->get_does_not_contain_feedback_expectation());
            $this->check_current_output($this->get_does_not_contain_specific_feedback_expectation());
        }

//        if ($this->is_finished) {
//            $this->check_remaining_tries('Requires grading');
//        } else
            $this->check_remaining_tries(self::STATE_INCOMPLETE);
    }

    /**
     * checks state after finishing an attempt that was not graded (internal grading error)
     * for last response
     * @param bool $initialstate not yet graded
     */
    protected function check_needs_grading($initialstate = true) {
        if ($this->is_finished)
            $this->check_current_state(question_state::$needsgrading) ; // $needsgrading);
        else
            $this->check_current_state(question_state::$invalid); // $invalid); // $todo);

        $this->check_current_mark(null);

        $this->render();
        $verify_exists = $this->preferredbehaviour != 'deferredfeedback' && !$this->is_finished;
        $this->check_verify_button_enabled($verify_exists);
        $this->check_answer_text(null, true);
        $this->check_current_output(
                $this->get_contains_question_text_expectation($this->question),
                $this->get_contains_feedback_expectation()
        );

        global $COURSE, $USER;
        $context = context_course::instance($COURSE->id);


        global $COURSE, $USER;
        if (has_capability('moodle/grade:viewhidden', $context, $USER->id)) {
            $this->check_current_output($this->get_contains_specific_feedback_expectation());
        } else {
            $this->check_current_output($this->get_does_not_contain_specific_feedback_expectation());
        }

        $this->check_current_output($this->get_contains_question_text_expectation($this->question));
        $this->check_output_contains('INTERNAL ERROR');

        $this->check_remaining_tries('Requires grading');
    }


    // ACTIONS


    protected function save_to_database() {
        $this->save_quba();
    }

    protected function load_from_database() {
        $this->load_quba();
        // now a new question object will be created that we need to store (???)
        $this->question = $this->quba->get_question(1);
    }

    protected function finish_attempt() {
        $this->finish_pending = true;
        $increment_step = false;
        /*
                if (strcmp($this->current_response, $response) != 0) {
                    $this->last_response = $this->current_response;
                    $this->current_response = $response;
                    $this->expected_step_counter++;
                    $increment_step = true;
                }
        */

        $this->set_mockbuilder_for_grader($this->question);
        $this->quba->finish_all_questions();

        if (!$increment_step and !$this->is_graded) {
            $this->expected_step_counter++;
            $increment_step = true;
        }

        if (!$increment_step and $this->current_state == question_state::$invalid) {
            $this->expected_step_counter++;
            $increment_step = true;
        }

        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                break;
            case "adaptiveexternalgrading":
                if (!$increment_step)
                    $this->expected_step_counter++;
                break;
            default:
                $this->assertFalse(true);
        }

//        $this->check_step_count($this->expected_step_counter);
        $this->is_graded = true;
        $this->is_finished = true;
        $this->finish_pending = false;

    }

    protected function press_submit($response = null, $attachedfiles = null) {

        if (is_null($response) && is_null($attachedfiles))
            throw new coding_exception('editor content or attachment must be set');

        $this->store_response_data($response, $attachedfiles);

        $this->check_step_count($this->expected_step_counter);

        $this->is_graded = !$this->force_internal_grading_error;
        $this->set_mockbuilder_for_grader($this->question);
        if ($attachedfiles) {
            if (!is_null($response)) {
                $this->process_submission(array(
                        '-submit' => 1,
                        'answer' => $response,
                        'attachments' => $attachedfiles[0]));
            } else {
                $this->process_submission(array(
                        '-submit' => 1,
                        'attachments' => $attachedfiles[0]));
            }
        }
        else {
            $this->process_submission(array('-submit' => 1, 'answer' => $response));
        }

        $this->expected_step_counter++;
        $this->check_step_count($this->expected_step_counter);
        if ($this->quba->get_question_state($this->slot) != question_state::$invalid) {
            $this->assertGreaterThan(0, $this->remainingtries);
            $this->remainingtries--;
        }
    }

    protected function press_try_again()
    {
        switch (self::EXPECTED_BEHAVIOUR) {
            case "interactivewithfeedback":
                $this->check_step_count($this->expected_step_counter);
                $this->check_current_output($this->get_contains_try_again_button_expectation(true));
                $this->process_submission(array('-tryagain' => 1));
                //        $this->check_current_output($this->get_contains_try_again_button_expectation(false));
                $this->expected_step_counter++;
                $this->check_step_count($this->expected_step_counter);
                $this->is_graded = false;
                break;
            case "adaptiveexternalgrading":
                $this->check_current_output($this->get_does_not_contain_try_again_button_expectation());
                break;
            default:
                $this->assertFalse(true);
        }
    }

    protected function save($response, $sequencecheck = '1')
    {
        $this->check_step_count($this->expected_step_counter);
        $this->store_response_data($response, null);
/*
        $this->last_response = $this->current_response;
        $this->current_response = $response;
        $this->last_attachments = $this->current_attachments;
        $this->current_attachments = null;
*/
        $this->save_response($response, $sequencecheck);
        if (!$this->is_same_response()) {
            $this->expected_step_counter++;
        }
        $this->check_step_count($this->expected_step_counter);
        $this->is_graded = false;
    }


    // difference to save??
    protected function save_with_attachment($response = null, $attachedfiles = null)
    {
        $this->check_step_count($this->expected_step_counter);
        $this->store_response_data($response, $attachedfiles);

        if ($attachedfiles) {
            if (!is_null($response)) {
                $this->process_submission(array(
                        'answer' => $response,
                        'attachments' => $attachedfiles[0]));
            } else {
                $this->process_submission(array(
                        'attachments' => $attachedfiles[0]));
            }
        } else {
            $this->process_submission(array(
                    'answer' => $response));
        }

        // check if response has been changed
        if (!$this->is_same_response()) {
            $this->expected_step_counter++;
        }

        $this->check_step_count($this->expected_step_counter);
        $this->is_graded = false;
    }

    protected function determine_draftid() {
        $this->render();
        if (!preg_match('/env=filemanager&amp;action=browse&amp;.*?itemid=(\d+)&amp;/', $this->currentoutput, $matches)) {
            throw new coding_exception('File manager draft item id not found.');
        }
        $attachementsdraftid = $matches[1];
        return $attachementsdraftid;
    }

    protected function upload_file($response, $filename = 'MyString.java') {
        global $USER;
        $usercontextid = context_user::instance($USER->id)->id;
        // we need to get the draft item ids.
        $this->render();
        if (!preg_match('/env=filemanager&amp;action=browse&amp;.*?itemid=(\d+)&amp;/', $this->currentoutput, $matches)) {
            throw new coding_exception('File manager draft item id not found.');
        }
        $attachementsdraftid = $matches[1];

        // save to draft area
        $this->save_file_to_draft_area($usercontextid, $attachementsdraftid, $filename, $response);

        // update storage for uploaded files
        $this->files[$attachementsdraftid] = array(
                'filename' => $filename,
                'content' => $response);

        //$this->last_attachments = $this->current_attachments;
        //$this->current_attachments = $response;

        $this->is_graded = false;

        return $attachementsdraftid;
    }
}


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
 * This file contains unit tests for class qtype_proforma_grader_2
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/classes/grader_2.php');
require_once($CFG->dirroot . '/question/type/proforma/tests/walkthrough_test_base.php');


class qtype_proforma_grader_test extends qtype_proforma_walkthrough_test_base {

    const GRADINGHINTS_1 = '<grading-hints>'.
    '<root function="sum">'.
    '<test-ref ref="1" weight="2">
            <title>TEST 1</title>
            <test-type>TEST-CONFIG 1</test-type>
            <description>DESCRIPTION 1</description>
        </test-ref>'.
    '<test-ref ref="2" weight="3">
            <title>TEST 2</title>
            <test-type>TEST-CONFIG 2</test-type>
            <description>DESCRIPTION 2</description>
        </test-ref>'.
    '</root>'.
    '</grading-hints>';

    const RESPONSE_1 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
            <validity>0.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="error">
              <title>MyString cannot be resolved to a variable</title>
              <content format="plaintext">Sample.java	line 55</content>
              <filerefs/>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="true">
            <score>0.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="debug">
              <title>JUnit</title>
              <content format="html">Fake Message</content>
              <filerefs/>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;


    const RESPONSE_2 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>0.0</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;


    const RESPONSE_3 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>1.0</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>1.0</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;


    const RESPONSE_4 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result is-internal-error="false">
            <score>0.3</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
        <test-result>
          <result is-internal-error="false">
            <score>0.4</score>
          </result>
          <feedback-list>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;


    const RESPONSE_5 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
    <separate-test-feedback>
        <submission-feedback-list>
            <student-feedback level="debug">
                <title>title1</title>
                <content format="html">Fake Result</content>
            </student-feedback>
            <teacher-feedback level="debug">
                <title>title2</title>
                <content format="plaintext">Teacher Message 1</content>
            </teacher-feedback>
        </submission-feedback-list>
        <tests-response>
            <test-response id="1">
                <test-result>
                    <result is-internal-error="false">
                        <score>0.0</score>
                        <validity>0.0</validity>
                    </result>
                    <feedback-list>
                        <student-feedback level="error">
                            <title>MyString cannot be resolved to a variable</title>
                            <content format="plaintext">Sample.java	line 55</content>
                        </student-feedback>
                    </feedback-list>
                </test-result>
            </test-response>
            <test-response id="2">
                <subtests-response>
                    <subtest-response id="junit1">
                        <test-result>
                            <result>
                                <score>1.0</score>
                            </result>
                            <feedback-list>
                                <student-feedback level="info">
                                    <title>Even Number Of Characters</title>
                                </student-feedback>
                            </feedback-list>
                        </test-result>
                    </subtest-response>
                    <subtest-response id="junit2">
                        <test-result>
                            <result>
                                <score>0.0</score>
                            </result>
                            <feedback-list>
                                <student-feedback level="error">
                                    <title>Failes Always</title>
                                    <content format="plaintext">testet Erwartungswert expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;</content>
                                </student-feedback>
                                <teacher-feedback>
                                    <title>Exception</title>
                                    <content format="plaintext">testFailesAlways(reverse_task.MyStringTest): liefert immer einen Fehler expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;
            org.junit.ComparisonFailure: liefert immer einen Fehler expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;&#13;
                at org.junit.Assert.assertEquals(Assert.java:115)&#13;
                                    </content>
                                </teacher-feedback>
                            </feedback-list>
                        </test-result>
                    </subtest-response>
                    <subtest-response id="junit3">
                        <test-result>
                            <result>
                                <score>1.0</score>
                            </result>
                            <feedback-list>
                                <student-feedback level="info">
                                    <title>Empty String</title>
                                </student-feedback>
                            </feedback-list>
                        </test-result>
                    </subtest-response>
                    <subtest-response id="junit4">
                        <test-result>
                            <result>
                                <score>1.0</score>
                            </result>
                            <feedback-list>
                                <student-feedback level="info">
                                    <title>Odd Number Of Characters</title>
                                </student-feedback>
                            </feedback-list>
                        </test-result>
                    </subtest-response>
                </subtests-response>    
             </test-response>
         </tests-response>
    </separate-test-feedback>
    <files></files>
    <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;



    const RESPONSE_6 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
    <separate-test-feedback>
        <submission-feedback-list>
            <student-feedback level="debug">
                <title>title1</title>
                <content format="html">Fake Result</content>
            </student-feedback>
            <teacher-feedback level="debug">
                <title>title2</title>
                <content format="plaintext">Teacher Message 1</content>
            </teacher-feedback>
        </submission-feedback-list>
        <tests-response>
            <test-response id="1">
                <test-result>
                    <result is-internal-error="false">
                        <score>0.0</score>
                        <validity>0.0</validity>
                    </result>
                    <feedback-list>
                        <student-feedback level="error">
                            <title>MyString cannot be resolved to a variable</title>
                            <content format="plaintext">Sample.java	line 55</content>
                        </student-feedback>
                    </feedback-list>
                </test-result>
            </test-response>
            <test-response id="2">
                <subtests-response>
                </subtests-response>    
             </test-response>
         </tests-response>
    </separate-test-feedback>
    <files></files>
    <response-meta-data><grader-engine name="praktomat" version="1.2.3" /></response-meta-data>
</response>
EOD;


    private function assert_grade($response, $gradinghints, $exstate, $exfraction, $exerror, $exfeedback,
            $feedbackformat = qtype_proforma_grader_2::FEEDBACK_FORMAT_PROFORMA2) {

        // create a question (grading hints are important)
        $q = test_question_maker::make_question('proforma', 'editor');
        if ($gradinghints != null) {
            $q->gradinghints = $gradinghints;
            $q->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;
        } else {
            $q->aggregationstrategy = qtype_proforma::ALL_OR_NOTHING;
        }

        $grader = new qtype_proforma_grader_2();
        list($state, $fraction, $error, $feedback, $feedbackformat) =
                $grader->extract_grade($response, 200, $q);
        $this->assertEquals($exstate, $state);
        $this->assertEquals($exfraction, $fraction);
        $this->assertEquals($exerror, $error);
        $this->assertEquals($exfeedback, $exfeedback);
    }


    // Weighted Sum.
    public function test_1_WS() {
        $this->assert_grade(self::RESPONSE_1, self::GRADINGHINTS_1,
                question_state::$needsgrading, 0.0, 'Internal error in a test', '');
    }
    
    public function test_2_WS() {
        $this->assert_grade(self::RESPONSE_2, self::GRADINGHINTS_1,
                question_state::$gradedwrong, 0.0, '', '');
    }

    public function test_3_WS() {
        $this->assert_grade(self::RESPONSE_3, self::GRADINGHINTS_1,
                question_state::$gradedright, 1.0, '', '');
    }

    public function test_4_WS() {
        $this->assert_grade(self::RESPONSE_4, self::GRADINGHINTS_1,
                question_state::$gradedpartial, 0.36, '', '');
    }

    public function test_5_WS() {
        // fraction = (2*0)+ (3*(1+0+1+1)/4)/5
        $this->assert_grade(self::RESPONSE_5, self::GRADINGHINTS_1,
                question_state::$gradedpartial, 0.45, '', '');
    }

    public function test_6_WS() {
        $this->assert_grade(self::RESPONSE_6, self::GRADINGHINTS_1,
                question_state::$gradedwrong, 0.0, '', '');
    }

    // All or nothing.
    public function test_1_AON() {
        $this->assert_grade(self::RESPONSE_1, null,
                question_state::$needsgrading, 0.0, 'Internal error in a test', '');
    }

    public function test_2_AON() {
        $this->assert_grade(self::RESPONSE_2, null,
                question_state::$gradedwrong, 0.0, '', '');
    }

    public function test_3_AON() {
        $this->assert_grade(self::RESPONSE_3, null,
                question_state::$gradedright, 1.0, '', '');
    }

    public function test_4_AON() {
        $this->assert_grade(self::RESPONSE_4, null,
                question_state::$gradedwrong, 0.0, '', '');
    }

    public function test_5_AON() {
        $this->assert_grade(self::RESPONSE_5, null,
                question_state::$gradedwrong, 0.0, '', '');
    }

    public function test_6_AON() {
        $this->assert_grade(self::RESPONSE_6, null,
                question_state::$gradedwrong, 0.0, '', '');
    }
}

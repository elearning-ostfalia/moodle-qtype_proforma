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
 * This file contains unit tests for class qtype_proforma_renderer
 * (most of all for rendering specific feedback i.e. grader feedback)
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/renderer.php');
require_once($CFG->dirroot . '/question/type/proforma/tests/walkthrough_test_base.php');


// TODO
// - check student versus teacher feedback

class qtype_proforma_renderer_test extends qtype_proforma_walkthrough_test_base {

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
    <submission-feedback-list>
      <student-feedback level="debug">
        <title>title1</title>
        <content format="html">Fake Message</content>
        <filerefs/>
      </student-feedback>
      <teacher-feedback level="debug">
        <title>title2</title>
        <content format="plaintext">Teacher Message</content>
        <filerefs/>
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
              <filerefs/>
            </student-feedback>
            <student-feedback level="error">
                <title>Inline cannot be resolved</title>
              <content format="plaintext">Sample.java	line 56</content>
              <filerefs/>
            </student-feedback>
            <teacher-feedback level="debug">
              <title>Java-Compilation (teacher)</title>
              <content format="html">content11</content>
              <filerefs/>
            </teacher-feedback>
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
            <teacher-feedback level="debug">
              <title>JUnit</title>
              <content format="plaintext">content18</content>
              <filerefs/>
            </teacher-feedback>
          </feedback-list>
        </test-result>
      </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
  </files>
  <response-meta-data>
    <grader-engine name="praktomat" version="1.2.3" />
  </response-meta-data>
</response>
EOD;

    const LOGS_1_1 = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
    );
    const LOGS_1_2 = array(
            array('JUnit', 'Fake Message')
    );
    const LOGS_1_1_TEACHER = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
            array('Java-Compilation (teacher)', 'content11'),
    );
    const LOGS_1_2_TEACHER = array(
            array('JUnit', 'Fake Message'),
            array('JUnit', 'content18')
    );

    const RESPONSE_2 = <<<'EOD'
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
                        <student-feedback level="error">
                            <title>Inline cannot be resolved</title>
                            <content format="plaintext">Sample.java	line 56</content>
                        </student-feedback>            
                        <teacher-feedback level="debug">
                            <title>Java-Compilation (teacher)</title>
                            <content format="html">content11</content>
                        </teacher-feedback>
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
	at reverse_task.MyStringTest.testFailesAlways(MyStringTest.java:30)&#13;
	at sun.reflect.NativeMethodAccessorImpl.invoke0(Native Method)&#13;
	at sun.reflect.NativeMethodAccessorImpl.invoke(Unknown Source)&#13;
	at sun.reflect.DelegatingMethodAccessorImpl.invoke(Unknown Source)&#13;
	at java.lang.reflect.Method.invoke(Unknown Source)&#13;
	at org.junit.runners.model.FrameworkMethod$1.runReflectiveCall(FrameworkMethod.java:50)&#13;
	at org.junit.internal.runners.model.ReflectiveCallable.run(ReflectiveCallable.java:12)&#13;
	at org.junit.runners.model.FrameworkMethod.invokeExplosively(FrameworkMethod.java:47)&#13;
	at org.junit.internal.runners.statements.InvokeMethod.evaluate(InvokeMethod.java:17)&#13;
	at org.junit.runners.ParentRunner.runLeaf(ParentRunner.java:325)&#13;
	at org.junit.runners.BlockJUnit4ClassRunner.runChild(BlockJUnit4ClassRunner.java:78)&#13;
	at org.junit.runners.BlockJUnit4ClassRunner.runChild(BlockJUnit4ClassRunner.java:57)&#13;
	at org.junit.runners.ParentRunner$3.run(ParentRunner.java:290)&#13;
	at org.junit.runners.ParentRunner$1.schedule(ParentRunner.java:71)&#13;
	at org.junit.runners.ParentRunner.runChildren(ParentRunner.java:288)&#13;
	at org.junit.runners.ParentRunner.access$000(ParentRunner.java:58)&#13;
	at org.junit.runners.ParentRunner$2.evaluate(ParentRunner.java:268)&#13;
	at org.junit.runners.ParentRunner.run(ParentRunner.java:363)&#13;
	at org.junit.runners.Suite.runChild(Suite.java:128)&#13;
	at org.junit.runners.Suite.runChild(Suite.java:27)&#13;
	at org.junit.runners.ParentRunner$3.run(ParentRunner.java:290)&#13;
	at org.junit.runners.ParentRunner$1.schedule(ParentRunner.java:71)&#13;
	at org.junit.runners.ParentRunner.runChildren(ParentRunner.java:288)&#13;
	at org.junit.runners.ParentRunner.access$000(ParentRunner.java:58)&#13;
	at org.junit.runners.ParentRunner$2.evaluate(ParentRunner.java:268)&#13;
	at org.junit.runners.ParentRunner.run(ParentRunner.java:363)&#13;
	at org.junit.runner.JUnitCore.run(JUnitCore.java:137)&#13;
	at org.junit.runner.JUnitCore.run(JUnitCore.java:115)&#13;
	at org.junit.runner.JUnitCore.run(JUnitCore.java:105)&#13;
	at org.junit.runner.JUnitCore.run(JUnitCore.java:94)&#13;
	at de.ostfalia.zell.praktomat.JunitProFormAListener.main(JunitProFormAListener.java:264)&#13;
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
    <files>
    </files>
    <response-meta-data>
               <grader-engine name="praktomat" version="5.6.7" />
    </response-meta-data>
</response>
EOD;

    const LOGS_2_1 = array(
        array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
        array('Inline cannot be resolved', 'Sample.java	line 56'),
    );
    const SUBTEST_2_1 = array(
            array('Even Number Of Characters', 1),
            array('Failes Always', 0, 'testet Erwartungswert expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;'),
            array('Empty String', 1),
            array('Odd Number Of Characters', 1),
    );
    const LOGS_2_1_TEACHER = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
            array('Java-Compilation (teacher)', 'content11')
    );
    const SUBTEST_2_1_TEACHER = array(
            array('Even Number Of Characters', 1),
            array('Failes Always', 0, 'testet Erwartungswert expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;'),
            array('Empty String', 1),
            array('Odd Number Of Characters', 1),
    );

    private function assert_same_xml($expectedxml, $xml) {
        // remove comments
        $xml = preg_replace('/<!--(.|\s)*?-->/', '', $xml);
        $expectedxml = preg_replace('/<!--(.|\s)*?-->/', '', $expectedxml);
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    private function render_title($title) {
        return '<div>' . $title .'</div>';
    }

    private function render_log($log) {
        return '<pre class="proforma_testlog">' . $log .'</pre>';
    }

    private function render_graderinfo($number, $graderinfo, $response) {
        $idprefix = '{COLLAPSE_ID}';
        $id = $idprefix.'-'.$number;

        $output = '</div></div>
<small></small><p></p>['.$graderinfo.']';
        $output .= '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
';
        $output .= '<div id="'.$id.'_caption" class="collapsibleregioncaption">raw response </div>
<div id="'.$id.'_inner" class="collapsibleregioninner"><xmp class="proforma_testlog"><?xml version="1.0" encoding="utf-8"?>
';
        // strip prolog
        $response = substr($response, 40);
        $output .= $response . '</xmp></div>
</div></div>';
        return $output;
    }


    private function render_collapsible_region_score($number, $score, $total, $title, $description, $logs, $internalerror = false) {
        $idprefix = '{COLLAPSE_ID}';
        $id = $idprefix.'-'.$number;

        $output = '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
<div id="'.$id.'_caption" class="collapsibleregioncaption">
<i class="icon fa fa-remove text-danger fa-fw "  title="Incorrect" aria-label="Incorrect"></i> '.$title;

        if (isset($total)) {
            $output .= ' ('.($score*100) .'/'.($total*100).' %)';
        }
        $output .= ' </div>
<div id="'.$id.'_inner" class="collapsibleregioninner">';

        $output .= '<span class="proforma_testlog_description">'.$description.'</span>';

        if ($internalerror) {
            $output .= '<p><b>INTERNAL ERROR IN GRADER!!</b></p>';
        }
        foreach ($logs as $log) {
            $output .= '<div class="proforma_testlog_title">'.$log[0].'</div>';
            $output .= '<pre class="proforma_testlog">'.$log[1].'</pre>';
        }
        $output .= '</div></div></div>';

        return $output;
    }

    private function render_collapsible_region_subtests($number, $score, $total, $title, $description, $subtests, $internalerror = false) {
        $idprefix = '{COLLAPSE_ID}';
        $id = $idprefix.'-'.$number;

        $output = '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
<div id="'.$id.'_caption" class="collapsibleregioncaption">';

        if ($score == 0) {
            $output .= '<i class="icon fa fa-remove text-danger fa-fw "  title="Incorrect" aria-label="Incorrect"></i> '.$title;
        } else if ($score == 1.0) {
            $output .= '<i class="icon fa fa-check text-success fa-fw " title="Correct" aria-label="Correct"></i> '.$title;
        } else {
            $output .= '<i class="icon fa fa-check-square fa-fw " title="Partially correct" aria-label="Partially correct"></i> '.$title;
        }

        if (isset($total)) {
            $output .= ' ('.($score*100) .'/'.($total*100).' %)';
        }
        $output .= ' </div>
<div id="'.$id.'_inner" class="collapsibleregioninner">';
        $output .= '<span class="proforma_testlog_description">'.$description.'</span>';

        if ($internalerror) {
            $output .= '<p><b>INTERNAL ERROR IN GRADER!!</b></p>';
        }
        foreach ($subtests as $subtest) {
            $output .= '<div class="proforma_subtest_title">
';
            if ($subtest[1] == 1) {
                $output .= '<i class="icon fa fa-check text-success fa-fw " title="Correct" aria-label="Correct"></i>';
            } else {
                $output .= '<i class="icon fa fa-remove text-danger fa-fw " title="Incorrect" aria-label="Incorrect"></i>';
            }
            $output .= $subtest[0].'</div>';
            if (count($subtest) > 2)
                $output .= '<pre class="proforma_subtest_testlog">'.$subtest[2].'</pre>';
        }
        $output .= '</div></div></div>';

        return $output;
    }

    private function assert_same_feedback($response, $errormsg, $gradinghints, $expected) {
        // create a question (grading hints are important)
        $q = test_question_maker::make_question('proforma', 'editor');
        if ($gradinghints != null) {
            $q->gradinghints = $gradinghints;
            $q->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;
        }
        $this->start_attempt_at_question($q, 'adaptivenopenalty', 1);
        $qa = $this->get_question_attempt();
        global $PAGE;
        $renderer = $PAGE->get_renderer('qtype_proforma');
        $output = $renderer->render_proforma2_message($response, $errormsg, $qa);

        // pretty print for comparison
        $dom = new DOMDocument();
        // Initial block (must before load xml string)
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        // End initial block

        $qaid = (empty($qa->get_database_id()) ? 'x' : $qa->get_database_id()) . '-' .
                (empty($qa->get_usage_id()) ? 'y' : $qa->get_usage_id());
        $expected = str_replace('{COLLAPSE_ID}', 'm-id-test-proforma-' . $qaid, $expected);

        //$dom->loadHTML($output);
        $dom->loadHTML($output, LIBXML_NOERROR | LIBXML_NOXMLDECL );
        $output_pretty = $dom->saveHTML();
        $dom->loadHTML($expected, LIBXML_NOERROR | LIBXML_NOXMLDECL );
        $expected_pretty = $dom->saveHTML();

        $this->assert_same_xml($expected_pretty, $output_pretty);
        // $this->assert_same_xml($expected, $output);
    }

    /**
     * internal error
     * all or nothing, student feedback
     */
    public function test_specific_feedback_internal_error_AON() {
        $expected =
            '<p>'.
            $this->render_title('title1').
            $this->render_log('Fake Message').
            '</p>'.
            $this->render_collapsible_region_score(1, 0.0, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_1_1).
            $this->render_collapsible_region_score(2, 0.0, null, 'TEST 2', 'DESCRIPTION 2', self::LOGS_1_2, true).
'<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';

        $this->assert_same_feedback(self::RESPONSE_1, '', null, $expected);
    }

    /**
     * subtest
     * all or nothing, student feedback
     */
    public function test_specific_feedback_subtest_partially_correct_AON() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Result').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1).
                $this->render_collapsible_region_subtests(2, 0.5, null, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1).
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->assert_same_feedback(self::RESPONSE_2, '', null, $expected);
    }

    /**
     * internal error
     * weighted sum, student feedback
     */
    public function test_specific_feedback_internal_error_WS() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Message').
                '</p>'.
                $this->render_collapsible_region_score(1, 0.0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_1_1).
                $this->render_collapsible_region_score(2, 0.0, 0.6, 'TEST 2', 'DESCRIPTION 2', self::LOGS_1_2, true).
                '<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';

        $this->assert_same_feedback(self::RESPONSE_1, '', self::GRADINGHINTS_1, $expected);
    }

    /**
     * subtest
     * weighted sum, student feedback
     */
    public function test_specific_feedback_subtest_partially_correct_WS() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Result').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1).
                $this->render_collapsible_region_subtests(2, 0.45, 0.6, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1).
                '<p></p>
<p>Your answer is not completely correct.</p>';

        // $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_2, '', self::GRADINGHINTS_1, $expected);
    }

    /**
     * internal error
     * all or nothing, teacher feedback
     */
    public function test_specific_feedback_internal_error_AON_TEACHER() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Message').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_log('Teacher Message').
                '</p>'.
                $this->render_collapsible_region_score(1, 0.0, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_1_1_TEACHER).
                $this->render_collapsible_region_score(2, 0.0, null, 'TEST 2', 'DESCRIPTION 2', self::LOGS_1_2_TEACHER, true).
                $this->render_graderinfo(3, 'praktomat 1.2.3', self::RESPONSE_1) .
                '<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_1, '', null, $expected);
    }

    /**
     * subtest
     * all or nothing, teacher feedback
     */
    public function test_specific_feedback_subtest_partially_correct_AON_ADMIN() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Result').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_log('Teacher Message 1').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1_TEACHER).
                $this->render_collapsible_region_subtests(2, 0.5, null, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1_TEACHER).
                $this->render_graderinfo(3, 'praktomat 5.6.7', self::RESPONSE_2) .
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_2, '', null, $expected);
    }

    /**
     * internal error
     * weighted sum, teacher feedback
     */
    public function test_specific_feedback_internal_error_WS_ADMIN() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Message').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_log('Teacher Message').
                '</p>'.
                $this->render_collapsible_region_score(1, 0.0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_1_1_TEACHER).
                $this->render_collapsible_region_score(2, 0.0, 0.6, 'TEST 2', 'DESCRIPTION 2', self::LOGS_1_2_TEACHER, true).
                $this->render_graderinfo(3, 'praktomat 1.2.3', self::RESPONSE_1) .
                '<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_1, '', self::GRADINGHINTS_1, $expected);
    }

    /**
     * subtest
     * weighted sum, teacher feedback
     */
    public function test_specific_feedback_subtest_partially_correct_WS_ADMIN() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_log('Fake Result').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_log('Teacher Message 1').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1_TEACHER).
                $this->render_collapsible_region_subtests(2, 0.45, 0.6, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1_TEACHER).
                $this->render_graderinfo(3, 'praktomat 5.6.7', self::RESPONSE_2) .
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_2, '', self::GRADINGHINTS_1, $expected);
    }

}

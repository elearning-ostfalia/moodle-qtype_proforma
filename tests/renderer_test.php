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



class qtype_proforma_renderer_test extends qtype_proforma_walkthrough_test_base {

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
    private function render_collapsible_region($qa, $number, $title, $description, $logs, $internalerror = false) {
        $qaid = (empty($qa->get_database_id()) ? 'x' : $qa->get_database_id()) . '-' .
                (empty($qa->get_usage_id()) ? 'y' : $qa->get_usage_id());
        $idprefix = 'm-id-test-proforma-' . $qaid;
        $id = $idprefix.'-'.$number;

        $output = '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
<div id="'.$id.'_caption" class="collapsibleregioncaption">
<i class="icon fa fa-remove text-danger fa-fw "  title="Incorrect" aria-label="Incorrect"></i>'.$title.'</div>
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

    public function test_specific_feedback_1() {
        // create a question (grading hints are important)
        $q = test_question_maker::make_question('proforma', 'editor');
        $q->gradinghints = '<grading-hints>'.
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

        $this->start_attempt_at_question($q, 'adaptivenopenalty', 1);
        $qa = $this->get_question_attempt();
        global $PAGE;
        $renderer = $PAGE->get_renderer('qtype_proforma');

        $response = <<<'EOD'
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
        <title>title1</title>
        <content format="plaintext">content4</content>
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
    <grader-engine name="praktomat" version="xyz" />
  </response-meta-data>
</response>
EOD;

        $errormsg = '';
        $output = $renderer->render_proforma2_message($response, $errormsg, $qa);

        // pretty print for comparison
        $dom = new DOMDocument();
        // Initial block (must before load xml string)
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = true;
        // End initial block

        //$dom->loadHTML($output);
        $dom->loadHTML($output, LIBXML_NOERROR | LIBXML_NOXMLDECL );
        $output_pretty = $dom->saveHTML();

        $logs1 = array(
                array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
                array('Inline cannot be resolved', 'Sample.java	line 56'),
        );
        $logs2 = array(
                array('JUnit', 'Fake Message')
        );
        $expected =
            '<p>'.
            $this->render_title('title1').
            $this->render_log('Fake Message').
            '</p>'.
            $this->render_collapsible_region($qa, 1, 'TEST 1', 'DESCRIPTION 1', $logs1).
            $this->render_collapsible_region($qa, 2, 'TEST 2', 'DESCRIPTION 2', $logs2, true).
'<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';
        $dom->loadHTML($expected, LIBXML_NOERROR | LIBXML_NOXMLDECL );
        $expected_pretty = $dom->saveHTML();


        $this->assert_same_xml($expected_pretty, $output_pretty);
        $this->assert_same_xml($expected, $output);

    }

}

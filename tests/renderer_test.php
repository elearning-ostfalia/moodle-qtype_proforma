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
              <content format="html"><![CDATA[content11 <b>sample</b>]]></content>
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

    // Format:
    // array (array(titel 1, content 1), array(titel 2, content 2)...)
    const LOGS_1_1 = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
    );
    const LOGS_1_2 = array(
            array('JUnit', ['Fake Message', 'html'])
    );
    const LOGS_1_1_TEACHER = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
            array('Java-Compilation (teacher)', ['content11 <b>sample</b>', 'html']),
    );
    const LOGS_1_2_TEACHER = array(
            array('JUnit', ['Fake Message', 'html']),
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
                            <content format="html"><![CDATA[content11 <b>sample</b>]]></content>
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
            array(1,  array('Even Number Of Characters')),
            array(0,  array('Failes Always', 'testet Erwartungswert expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;')),
            array(1,  array('Empty String')),
            array(1,  array('Odd Number Of Characters')),
    );
    const LOGS_2_1_TEACHER = array(
            array('MyString cannot be resolved to a variable', 'Sample.java	line 55'),
            array('Inline cannot be resolved', 'Sample.java	line 56'),
            array('Java-Compilation (teacher)', ['content11 <b>sample</b>', 'html'])
    );
    const SUBTEST_2_1_CALLSTACK = 'testFailesAlways(reverse_task.MyStringTest): liefert immer einen Fehler expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;
org.junit.ComparisonFailure: liefert immer einen Fehler expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;
	at org.junit.Assert.assertEquals(Assert.java:115)
	at reverse_task.MyStringTest.testFailesAlways(MyStringTest.java:30)
	at sun.reflect.NativeMethodAccessorImpl.invoke0(Native Method)
	at sun.reflect.NativeMethodAccessorImpl.invoke(Unknown Source)
	at sun.reflect.DelegatingMethodAccessorImpl.invoke(Unknown Source)
	at java.lang.reflect.Method.invoke(Unknown Source)
	at org.junit.runners.model.FrameworkMethod$1.runReflectiveCall(FrameworkMethod.java:50)
	at org.junit.internal.runners.model.ReflectiveCallable.run(ReflectiveCallable.java:12)
	at org.junit.runners.model.FrameworkMethod.invokeExplosively(FrameworkMethod.java:47)
	at org.junit.internal.runners.statements.InvokeMethod.evaluate(InvokeMethod.java:17)
	at org.junit.runners.ParentRunner.runLeaf(ParentRunner.java:325)
	at org.junit.runners.BlockJUnit4ClassRunner.runChild(BlockJUnit4ClassRunner.java:78)
	at org.junit.runners.BlockJUnit4ClassRunner.runChild(BlockJUnit4ClassRunner.java:57)
	at org.junit.runners.ParentRunner$3.run(ParentRunner.java:290)
	at org.junit.runners.ParentRunner$1.schedule(ParentRunner.java:71)
	at org.junit.runners.ParentRunner.runChildren(ParentRunner.java:288)
	at org.junit.runners.ParentRunner.access$000(ParentRunner.java:58)
	at org.junit.runners.ParentRunner$2.evaluate(ParentRunner.java:268)
	at org.junit.runners.ParentRunner.run(ParentRunner.java:363)
	at org.junit.runners.Suite.runChild(Suite.java:128)
	at org.junit.runners.Suite.runChild(Suite.java:27)
	at org.junit.runners.ParentRunner$3.run(ParentRunner.java:290)
	at org.junit.runners.ParentRunner$1.schedule(ParentRunner.java:71)
	at org.junit.runners.ParentRunner.runChildren(ParentRunner.java:288)
	at org.junit.runners.ParentRunner.access$000(ParentRunner.java:58)
	at org.junit.runners.ParentRunner$2.evaluate(ParentRunner.java:268)
	at org.junit.runners.ParentRunner.run(ParentRunner.java:363)
	at org.junit.runner.JUnitCore.run(JUnitCore.java:137)
	at org.junit.runner.JUnitCore.run(JUnitCore.java:115)
	at org.junit.runner.JUnitCore.run(JUnitCore.java:105)
	at org.junit.runner.JUnitCore.run(JUnitCore.java:94)
	at de.ostfalia.zell.praktomat.JunitProFormAListener.main(JunitProFormAListener.java:264)
                        ';
    const SUBTEST_2_1_TEACHER = array(
            array(1, array ('Even Number Of Characters')),
            array(0, array('Failes Always', 'testet Erwartungswert expected:&lt;[cba]&gt; but was:&lt;[hallo]&gt;'),
                    array('Exception', self::SUBTEST_2_1_CALLSTACK)),
            array(1,  array('Empty String')),
            array(1,  array('Odd Number Of Characters')),
    );


    const RESPONSE_3 = <<<'EOD'
<?xml version="1.0" encoding="UTF-8"?>
<response xmlns="urn:proforma:v2.0" xmlns:praktomat="urn:proforma:praktomat:v2.0"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" >
    <separate-test-feedback>
        <submission-feedback-list>
        </submission-feedback-list>
        <tests-response>
            <test-response id="1">
                <test-result>                
                    <result >
                        <score>1</score>
                    </result>
                    <feedback-list>
                        <student-feedback level="info">
                            <title>JUnit Test: Junit Test #1</title>
                        </student-feedback>
                    </feedback-list>
                </test-result>
            </test-response>
        
            <test-response id="2">
                <test-result>                
                    <result >
                        <score>0</score>
                    </result>
                    <feedback-list>
                        
                        <student-feedback level="info">
                            <title>JUnit Test: Junit Test de/ostfalia/DoSomethingTest</title>
                            <content format="html"><![CDATA[<pre>

======== Test Results ======

</pre><br/>

<div>1 <tt>Java</tt> user-submitted files found for compilation:  DoSomethingTest.java &nbsp; </div>

<div>Java compiler output:</div>
<pre><b>de/ostfalia/DoSomethingTest.java:21: error: duplicate class: de.ostfalia.DoSomethingTest</b>
<b>  location: class DoSomethingTest</b>
xx errors
1
</pre>
]]></content>
                        </student-feedback>
                        
                    </feedback-list>
                </test-result>
            </test-response>
        </tests-response>
    </separate-test-feedback>
    <files>
    </files>
    <response-meta-data>
    </response-meta-data>
</response>    
EOD;

    const LOGS_3_1 = array(array('JUnit Test: Junit Test #1', null));
    const LOGS_3_2 = array(
            array('JUnit Test: Junit Test de/ostfalia/DoSomethingTest', ['<pre>

======== Test Results ======

</pre><br/>

<div>1 <tt>Java</tt> user-submitted files found for compilation:  DoSomethingTest.java &nbsp; </div>

<div>Java compiler output:</div>
<pre><b>de/ostfalia/DoSomethingTest.java:21: error: duplicate class: de.ostfalia.DoSomethingTest</b>
<b>  location: class DoSomethingTest</b>
xx errors
1
</pre>
', 'html']),
    );

    const RESPONSE_4 = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response xmlns="urn:proforma:v2.0" xmlns:praktomat="urn:proforma:praktomat:v2.0"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" >
    <separate-test-feedback>
        <submission-feedback-list>
        </submission-feedback-list>
        <tests-response>
            <test-response id="1">
                <test-result>                
                    <result> >
                        <score>0</score>
                    </result>
                    <feedback-list>
    <student-feedback level="info">
        <title>1 Java user-submitted file(s) found for compilation</title>
        <content format="plaintext">de/ostfalia/zell/isPalindromTask/MyString.java</content>
    </student-feedback>
    <student-feedback level="error">
        <title>Compilation failed</title>
        <content format="plaintext">de/ostfalia/zell/isPalindromTask/MyString.java:8: error: not a statement
            was
            ^
de/ostfalia/zell/isPalindromTask/MyString.java:8: error: &#39;;&#39; expected
            was
               ^
de/ostfalia/zell/isPalindromTask/MyString.java:12: error: class, interface, or enum expected
}          2
           ^
3 errors
1
</content>
    </student-feedback>
                    </feedback-list>
                </test-result>
            </test-response>
            <test-response id="2">
                <test-result>                
                    <result> >
                        <score>0</score>
                    </result>
                    <feedback-list>
    <student-feedback level="info">
        <title>1 Java user-submitted file(s) found for compilation</title>
        <content format="plaintext">de/ostfalia/zell/isPalindromTask/MyString.java</content>
    </student-feedback>
    <student-feedback level="error">
        <title>Compilation failed</title>
        <content format="plaintext">de/ostfalia/zell/isPalindromTask/MyString.java:8: error: not a statement
            was
            ^
de/ostfalia/zell/isPalindromTask/MyString.java:8: error: &#39;;&#39; expected
            was
               ^
de/ostfalia/zell/isPalindromTask/MyString.java:12: error: class, interface, or enum expected
}          2
           ^
3 errors
1
</content>
    </student-feedback>
                    </feedback-list>
                </test-result>
            </test-response>
        </tests-response>
    </separate-test-feedback>
    
    <files>
    </files>
    <response-meta-data>
        <grader-engine name="praktomat" version="Version 4.5.1 | 20200803"/>
    </response-meta-data>
</response>
EOD;

/*
<praktomat:response-meta-data>
<praktomat:response-datetime>2020-08-04T13:29:42.570139</praktomat:response-datetime>

</praktomat:response-meta-data>
*/
    const LOGS_4_1 = array(
            array('1 Java user-submitted file(s) found for compilation', 'de/ostfalia/zell/isPalindromTask/MyString.java'),
            array('Compilation failed', 'de/ostfalia/zell/isPalindromTask/MyString.java:8: error: not a statement
            was
            ^
de/ostfalia/zell/isPalindromTask/MyString.java:8: error: \';\' expected
            was
               ^
de/ostfalia/zell/isPalindromTask/MyString.java:12: error: class, interface, or enum expected
}          2
           ^
3 errors
1
'),
    );
    const LOGS_4_2 = self::LOGS_4_1;

    


    const RESPONSE_EMPTY = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response xmlns="urn:proforma:v2.0" xmlns:praktomat="urn:proforma:praktomat:v2.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <separate-test-feedback>
    <submission-feedback-list>
        </submission-feedback-list>
    <tests-response>
      <test-response id="1">
        <test-result>
          <result>
            <score>1</score>
          </result>
          <feedback-list>
            <student-feedback level="info">
              <content format="plaintext">Beginne Pr&#252;fung...
Pr&#252;fung beendet.
</content>
            </student-feedback>
          </feedback-list>
        </test-result>
      </test-response>
      <test-response id="2">
            </test-response>
    </tests-response>
  </separate-test-feedback>
  <files>
    </files>
  <response-meta-data>
    <grader-engine name="praktomat" version="Version 4.5.1 | 20200803"/>
    <praktomat:response-meta-data>
      <praktomat:response-datetime>2020-10-26T16:01:03.745332</praktomat:response-datetime>
      <praktomat:version-control-system name="SVN" submission-uri="https://svn.ostfalia.de/src" submission-revision="1234"/>
    </praktomat:response-meta-data>
  </response-meta-data>
</response>    
EOD;    

    
    const LOGS_EMPTY_1 = array(
            array('', 'Beginne Prüfung...
Prüfung beendet.
', ''),
    );
    const LOGS_EMPTY_2 = array(
            array(null, ['Response format error: no test result available', 'plaintext']),
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

    private function render_log($log, $format = 'plaintext') {
        switch ($format) {
            case 'html':         
                return '<p class="proforma_testlog proforma_html">' . $log .'</p>';
            case 'plaintext':
                return '<pre class="proforma_testlog">' . $log .'</pre>';            
            default:
                throw new Exception('invalid format: "' . $format . '"');
        }
    }

    private function render_general_log($log, $format = 'plaintext') {
        switch ($format) {
            case 'html':         
                return '<p class="proforma_general proforma_html">' . $log .'</p>';
            case 'plaintext':
                return '<pre class="proforma_general">' . $log .'</pre>';            
            default:
                throw new Exception('invalid format: "' . $format . '"');
        }
    }    
    
    /**
     * Generate expected grader info
     * @param $number number for collapsible region (sequence)
     * @param $graderinfo grader info string
     * @param $response test response string
     * @return string
     */
    private function render_graderinfo($number, $graderinfo, $response, $svn = null) {
        $idprefix = '{COLLAPSE_ID}';
        $id = $idprefix.'-'.$number;

        // Add small tag to svn uri.
        if (isset($svn)) {
            $svn = '<small>' . $svn . '</small>';
        } else {
            $svn = '';
        }

        $output = '</div></div>
'. $svn .'<small></small><p></p>['.$graderinfo.']';
        $output .= '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
';
        $output .= '<div id="'.$id.'_caption" class="collapsibleregioncaption">raw response </div>
<div id="'.$id.'_inner" class="collapsibleregioninner"><xmp class="proforma_testlog"><?xml version="1.0" encoding="utf-8"?>
';
        // strip prolog
        //$response = str_replace('\r\n', '\n', $response);
        $response = substr($response, 39);
        $output .= $response . '</xmp></div>
</div></div>';
        return $output;
    }

    /** Generate output for a complete test
     * @param $number (sequence)
     * @param $score expected score for this test
     * @param $total
     * @param $title Title of this test (from grading hints)
     * @param $description Description of the test (from grading hints)
     * @param $logs
     * @param bool $internalerror
     * @return string
     */
    private function render_collapsible_region_score($number, $score, $total, $title, $description, $logs, $internalerror = false) {
        $idprefix = '{COLLAPSE_ID}';
        $id = $idprefix.'-'.$number;

        $iconpassed = 'class="icon fa fa-check text-success fa-fw "  title="Correct" aria-label="Correct"';
        $iconfailed = 'class="icon fa fa-remove text-danger fa-fw "  title="Incorrect" aria-label="Incorrect"';
        $iconinternalerror = 'class="icon fa fa-exclamation text-warning fa-fw " title="info" aria-label="info"';
                  
        if (isset($total)) {
            // with subtests
            $icon = $iconpassed;
            if ($internalerror) {
                $icon = $iconinternalerror;                                
            } else if (($score/$total) < 1) {
                $icon = $iconfailed;                
            }
            $output = '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
<div id="'.$id.'_caption" class="collapsibleregioncaption">
<i ' . $icon . '></i> '.$title;
            if ($internalerror and !isset($score)) {
                $output .= ' ( ? /'.($total*100).' %)';                
            } else {
                $output .= ' ('.($score*100) .'/'.($total*100).' %)';                
            }
        } else {
            // without subtests
            $icon = $iconpassed;
            if ($internalerror) {
                $icon = $iconinternalerror;                                
            } else if ($score < 1) {
                $icon = $iconfailed;                
            }
          
            $output = '<div id="'.$id.'" class="collapsibleregion  collapsed"><div id="'.$id.'_sizer">
<div id="'.$id.'_caption" class="collapsibleregioncaption">
<i ' . $icon . '></i> '.$title;
        }
             
        
        $output .= ' </div>
<div id="'.$id.'_inner" class="collapsibleregioninner">';

        $output .= '<span class="proforma_testlog_description">'.$description.'</span>';

        if ($internalerror) {
            $output .= '<p class="proforma_testlog_description">An internal error occured during test execution:</p>';
        }
        foreach ($logs as $log) {
            if (isset($log[0])) {
                $output .= '<div class="proforma_testlog_title">'.$log[0].'</div>';
            }
            if (isset($log[1])) {
                $content = $log[1];
                if (is_array($content)) {
                    // Log with format infomation:
                    $output .= $this->render_log($content[0], $content[1]);
                } else {
                    $output .= $this->render_log($content);                    
                }
            }
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
            if ($subtest[0] == 1) {
                $output .= '<i class="icon fa fa-check text-success fa-fw " title="Correct" aria-label="Correct"></i>';
            } else {
                $output .= '<i class="icon fa fa-remove text-danger fa-fw " title="Incorrect" aria-label="Incorrect"></i>';
            }
            $output .= $subtest[1][0].'</div>';
            if (count($subtest[1]) > 1)  {
                $output .= '<pre class="proforma_subtest_testlog">'.$subtest[1][1].'</pre>';
            }

            $index = 2;
            while (isset($subtest[$index])) {
                $output .= '<div class="proforma_subtest_title_2">';
                $output .= $subtest[$index][0].'</div>';
                if (count($subtest[$index]) > 1) {
                    $output .= '<pre class="proforma_subtest_testlog">'.$subtest[$index][1].'</pre>';
                }
                $index ++;
            }
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
        $dom->loadHTML($output, LIBXML_NOERROR | LIBXML_NOXMLDECL | LIBXML_NOWARNING);
        $output_pretty = $dom->saveHTML();
        $dom->loadHTML($expected, LIBXML_NOERROR | LIBXML_NOXMLDECL | LIBXML_NOWARNING);
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
            $this->render_general_log('Fake Message', 'html').
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
                $this->render_general_log('Fake Result', 'html').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1).
                $this->render_collapsible_region_subtests(2, 0.5, null, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1).
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->assert_same_feedback(self::RESPONSE_2, '', null, $expected);
    }

    /**
     * subtest
     * all or nothing, student feedback
     */
    public function test_specific_feedback_log_error_AON() {
        $expected =
                $this->render_collapsible_region_score(1, 1, null, 'TEST 1', 'DESCRIPTION 1', self::LOGS_3_1).
                $this->render_collapsible_region_score(2, 0, null, 'TEST 2', 'DESCRIPTION 2', self::LOGS_3_2).
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->assert_same_feedback(self::RESPONSE_3, '', null, $expected);
    }

    /**
     * internal error
     * weighted sum (WS), student feedback
     */
    public function test_specific_feedback_internal_error_WS() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_general_log('Fake Message', 'html').
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
                $this->render_general_log('Fake Result', 'html').
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
     * all or nothing (AON), teacher feedback
     */
    public function test_specific_feedback_internal_error_AON_ADMIN() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_general_log('Fake Message', 'html').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_general_log('Teacher Message').
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
                $this->render_general_log('Fake Result', 'html').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_general_log('Teacher Message 1').
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
     * weighted sum (WS), teacher feedback
     */
    public function test_specific_feedback_internal_error_WS_ADMIN() {
        $expected =
                '<p>'.
                $this->render_title('title1').
                $this->render_general_log('Fake Message', 'html').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_general_log('Teacher Message').
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
                $this->render_general_log('Fake Result', 'html').
                '<p></p>'.
                $this->render_title('title2').
                $this->render_general_log('Teacher Message 1').
                '</p>'.
                $this->render_collapsible_region_score(1, 0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_2_1_TEACHER).
                $this->render_collapsible_region_subtests(2, 0.45, 0.6, 'TEST 2', 'DESCRIPTION 2', self::SUBTEST_2_1_TEACHER).
                $this->render_graderinfo(3, 'praktomat 5.6.7', self::RESPONSE_2) .
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_2, '', self::GRADINGHINTS_1, $expected);
    }

    /**
     * compilation error
     */
    public function test_compilation_error() {
        $expected =
                $this->render_collapsible_region_score(1, 0, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_4_1).
                $this->render_collapsible_region_score(2, 0, 0.6, 'TEST 2', 'DESCRIPTION 2', self::LOGS_4_2).
                $this->render_graderinfo(3, 'praktomat Version 4.5.1 | 20200803', self::RESPONSE_4) .
                '<p></p>
<p>Your answer is not completely correct.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_4, '', self::GRADINGHINTS_1, $expected);
    }

    /**
     * empty test result
     */
    public function test_empty_response() {
        $expected =
                $this->render_collapsible_region_score(1, 0.4, 0.4, 'TEST 1', 'DESCRIPTION 1', self::LOGS_EMPTY_1).
                $this->render_collapsible_region_score(2, null, 0.6, 'TEST 2', 'DESCRIPTION 2', self::LOGS_EMPTY_2, true).
                $this->render_graderinfo(3, 'praktomat Version 4.5.1 | 20200803', self::RESPONSE_EMPTY, 
                     'SVN: https://svn.ostfalia.de/src Revision 1234') .
                '<p></p>
<p>Your answer could not be graded due to an internal error in the grading system.</p>';

        $this->setAdminUser();
        $this->assert_same_feedback(self::RESPONSE_EMPTY, '', self::GRADINGHINTS_1, $expected);
    }    
    
}

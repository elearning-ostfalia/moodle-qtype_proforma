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
 * Grader that returns a fake response (can be used for tests)
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K. Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/classes/grader2.php');

class qtype_proforma_testgrader extends  qtype_proforma_grader_2 {
    private function set_dummy_result() {
        $dummyresult = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
  <separate-test-feedback>
    <submission-feedback-list>
      <student-feedback level="debug">
        <title>title1</title>
        <content format="html">Fake Message</content>
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
            <score>0.0</score>
            <validity>0.0</validity>
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
          <result is-internal-error="true">
            <score>0.0</score>
            <validity>1.0</validity>
          </result>
          <feedback-list>
            <student-feedback level="debug">
              <title>JUnit</title>
              <content format="html">Fake Message</content>
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
</response>
EOD;

        return $dummyresult;
    }


    private function set_dummy_result2() {
        $dummyresult = <<<'EOD'
<?xml version="1.0" encoding="utf-8"?>
<response lang="en" xmlns="urn:proforma:v2.0">
    <separate-test-feedback>
        <submission-feedback-list>
            <student-feedback level="debug">
                <title>title1</title>
                <content format="html">Fake Result</content>
            </student-feedback>
            <teacher-feedback level="debug">
                <title>title1</title>
                <content format="plaintext">content4</content>
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
               <grader-engine name="praktomat" version="xyz" />
    </response-meta-data>
</response>
EOD;

        return $dummyresult;
    }

    protected function post_to_grader(&$postfields, qtype_proforma_question $question) {
        return array($this->set_dummy_result2(), 200); // fake
    }
}
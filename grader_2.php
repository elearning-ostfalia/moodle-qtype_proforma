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
 * Interface to Grader  ProFormA 2.0
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.arin Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/grader.php');
require_once($CFG->dirroot . '/question/type/proforma/simplexmlwriter.php');


class qtype_proforma_grader_2 extends  qtype_proforma_grader {

    private function set_dummy_result() {
        $dummyresult =
'<?xml version="1.0" encoding="utf-8"?>
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
</response>';

        return $dummyresult;
    }


    private function set_dummy_result2() {
        $dummyresult =
                '<?xml version="1.0" encoding="utf-8"?>
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
</response>';

        return $dummyresult;
    }


    private function create_submission_xml($code, $files, $filename, qtype_proforma_question $question) {
        global $CFG;

        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(1);
        $res = $xw->setIndentString(' ');

        $xw->startDocument('1.0', 'UTF-8');

        // ----------------
        $xw->startElement('submission');

        // Attributes for submission
        $xw->createAttribute('xmlns', 'urn:proforma:v2.0');
//        $xw->createAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
//        $xw->createAttribute('xsi:schemaLocation', 'urn:proforma:v2.0 schema.xsd');

        // task
        if ($question->taskstorage == qtype_proforma::INTERNAL_STORAGE) { // do not use === here!
            // external task in http field
            $xw->startElement('external-task');
            $xw->createAttribute('uuid', $question->uuid);
            $xw->text('http-file:'.$question->taskfilename);
//            $xw->text('http-file:task-file');
            $xw->endElement(); // lms
            //
            // $xw->createChildElementWithText('inline-task-zip', $question->taskfiletask-file = {stored_file} [4]name);
        } else {
            throw new coding_exception('tasks stored outside Moodle are not supported');
            // external TODO???
        }

        if (isset($files)) {
            if (count($files) == 1) {
                $file = array_values($files)[0];
                $xw->createChildElementWithText('external-submission', 'http-file:' . $file->get_filename());
            } else {
                $http_filename = '';
                foreach ($files as $file) {
                    if (strlen($http_filename) == 0) {
                        $http_filename = $file->get_filename();
                    } else {
                        $http_filename = $http_filename . ',' . $file->get_filename();
                    }
                }
                $xw->createChildElementWithText('external-submission', 'http-file:' . $http_filename);
            }
        } else if (isset($code)) {
            //$xw->createChildElementWithText('external-submission', 'http-text:'.$filename);

           // Start a child element
            $xw->startElement('files');
                $xw->startElement('file');
                    $xw->startElement('embedded-txt-file');
                    $xw->createAttribute('filename', $filename);
                    //$xw->startCdata();
                    $xw->writeCData($code);
                    //$xw->endCdata();
                    $xw->endElement(); // embedded-txt-file
                $xw->endElement(); // file
            $xw->endElement(); // files
        }
        else
            debugging('got neither code nor file for submission.xml');

            // lms
            $xw->startElement('lms');
            $xw->createAttribute('url', $CFG->wwwroot);

            $xw->createChildElementWithText('submission-datetime', date('c', time()));
            $xw->createChildElementWithText('user-id', $this->get_user());
            $xw->createChildElementWithText('course-id', $this->get_course());

            $xw->endElement(); // lms

            // result-spec
            $xw->startElement('result-spec');
            // Attributes for submission
            $xw->createAttribute('format', 'xml');
            $xw->createAttribute('structure', 'separate-test-feedback');
            $xw->createAttribute('lang', 'de');

            $xw->createChildElementWithText('student-feedback-level', 'debug' /*'info'*/);
            $xw->createChildElementWithText('teacher-feedback-level', 'debug');

            $xw->endElement(); // result-spec

         $xw->endElement(); // submission

         $xw->endDocument();
         $submission = $xw->outputMemory();
            //echo $submission;

         //debugging($submission);
         return $submission;
    }

    private function post_to_grader(&$post_fields, qtype_proforma_question $question) {

         if ($question->taskstorage == qtype_proforma::INTERNAL_STORAGE) { // do not use === here!
             $task =  $question->get_task_file();
             if (!$task instanceof stored_file) {
                 throw new coding_exception("wrong class");
             }

             $post_fields['task-file'] = $task;
         }

         $protocolhost = get_config('qtype_proforma', 'graderuri_host');

         $path = get_config('qtype_proforma', 'graderuri_path');
         $uri = $protocolhost . $path;

//return array($this->set_dummy_result(), 200); // fake
        // return array($this->set_dummy_result2(), 200); // fake

         $curl = new curl();
         $options['CURLOPT_TIMEOUT'] = get_config('qtype_proforma', 'grading_timeout');
         $output = $curl->post($uri, $post_fields, $options);
         $info = $curl->get_info();
         $http_code = $info["http_code"];
         return array($output, $http_code) ;
    }


    /** sends a student's uploaded file to the grader. Exactly one file is supported.
     *
     * @param $file
     * @param $question
     * @return mixed|string
     */
//    public function send_file_to_grader($file, qtype_proforma_question $question) {
//
//        if (!$file instanceof stored_file) {
//            throw new coding_exception("wrong class");
//        }
//
////        debugging("VERSION 2");
//        $files = array($file);
//
//
//        $submission = $this->create_submission_xml(null, $files, $question->responsefilename, $question);
//
//        //echo $submission;
//
//        $post_fields = array(
//                'submission.xml' => $submission,
//                'submission-file' => $file,
//        );
//
//        return $this->post_to_grader($post_fields, $question);
//    }

    public function send_files_to_grader($files, qtype_proforma_question $question) {
        // check files
        foreach($files as $file) {
            if (!$file instanceof stored_file) {
                throw new coding_exception("wrong class for file");
            }
        }

        $submission = $this->create_submission_xml(null, $files, $question->responsefilename, $question);

        //debugging($submission);

        $post_fields = array('submission.xml' => $submission);

        foreach($files as $file) {
            $post_fields[$file->get_filename()] = $file;
        }

        return $this->post_to_grader($post_fields, $question);
    }


    /** sends the sudent's submitted source code to the grader
     *
     * @param $code
     * @param $question
     * @return mixed|string
     */
    public function send_code_to_grader($code, qtype_proforma_question $question) {
        if (empty($code)) {
            throw new coding_exception('send_code_to_grader with empty code');
        }

        $filename = $question->responsefilename;
/*        if (get_config('qtype_proforma', 'javafile_without_package')) {
            $array = explode('/', $filename);
            $array= explode('\\', end($array)); // normally not needed
            $filename = end($array);
        }
*/
        $submissionxml = $this->create_submission_xml($code, null, $filename, $question);

        $post_fields = array(
                'submission.xml' => $submissionxml,
                $filename => $code // ????
//                'submission-text' => $code, //??
//                'submission-filename' => $question->responsefilename //??
        );

        return $this->post_to_grader($post_fields, $question);
    }


    private function update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc) {

        switch ($question->aggregationstrategy) {
            case qtype_proforma::WEIGHTED_SUM:
                $id = (string)$test['id'];
                $ghtest = $gradingtests->xpath("//test-ref[@ref='" . $id . "']");
                if (count($ghtest) == 0)
                    throw new moodle_exception('Cannot find appropriate grading hints for test "' . $id.'"');

                $ghtest = $ghtest[0];
                $weight = floatval((string)$ghtest['weight']) / $totalweight;

                //$weightscore = number_format($score * $weight, 2);
                $weightscore = $score * $weight;
                $gradecalc += $weightscore;
                break;
            case qtype_proforma::ALL_OR_NOTHING:
                if ($score < 1.0) {
                    $gradecalc = 0;
                }
                break;
            default:
                throw new moodle_exception('Unsupported aggregation strategy ' . $question->aggregationstrategy);
        }
        return $gradecalc;
    }


    public static function calc_score_for_test($test) {
        if (count($test->{'subtests-response'}) == 0) {
            throw new moodle_exception('Subtest results not found');
        }

        $counttests = 0.0;
        $score = 0.0;
        $internalError = false;

        foreach ($test->{'subtests-response'}->{'subtest-response'} as $subtest) {
            $testresult = $subtest->{'test-result'}->result;
            if ((string)$testresult['is-internal-error'] === 'true')
                $internalError = true;
            $score += floatval((string)$testresult->score); // 0.0 or 1.0
            $counttests ++;
        }

        return array($score / $counttests, $internalError);
    }

    public function extract_grade($result, $httpcode, qtype_proforma_question $question) {
        $questionstate = question_state::$needsgrading;
                // question_state::$invalid; // $needsgrading; // $invalid;
        // needsgrading is an inactive state that does not allow the
        // student to change his or her submission
        // => the question behaviour converts the state to invalid during attempt
        // (when finishing the state it is kept)
        $grade = null;

        if (isset($httpcode) && $httpcode != 200) {
            return array($questionstate, $grade, 'HTTP Status Code ' . $httpcode, $result, self::FEEDBACK_FORMAT_HTTP_ERROR);
        }
        if ($result === false) {
            // ERROR: no result
            // TODO: improve error handling with returning array with multible values
            // - return only error text and calling function must convert???
            // - or throw exception??
            // - or use if-else
            return array($questionstate, $grade, 'No result from grader (maybe grader is unreachable)', '', self::FEEDBACK_FORMAT_ERROR);
        }
        $response = '';
        try {
            $response = new SimpleXMLElement($result);
        } catch (Exception $e) {
            // echo 'NO XML: ' . $result . '<br>';
            return array($questionstate, $grade, 'Unsupported feedback from grader, no valid XML: ' . $e->getMessage(),
                    $result, self::FEEDBACK_FORMAT_ERROR);
//            return array($questionstate, $grade, 'Unsupported feedback from grader, no valid XML: ' . $e->getMessage(),
//                    $result, self::FEEDBACK_FORMAT_ERROR);
        }

        if (!isset($response->{'separate-test-feedback'})) {
            // invalid response format
            // echo 'NO PROFORMA: ' . $result . '<br>';
            return array($questionstate, $grade, 'Unsupported feedback format', $result, self::FEEDBACK_FORMAT_INVALID);
        }

        $feedbackformat = self::FEEDBACK_FORMAT_PROFORMA2;
        $testsWithInternalError = false;

        try {
            $gradecalc = 0;
            $totalweight = 0;
            $gradingtests = null;
            switch ($question->aggregationstrategy) {
                case qtype_proforma::WEIGHTED_SUM:
                    // evaluate total weight
                    $gh = new SimpleXMLElement($question->gradinghints);
                    $gradingtests = $gh->root;
                    foreach($gradingtests->{'test-ref'} as $test){
                        $totalweight += floatval((string)$test['weight']);
                    }
                    break;
                case qtype_proforma::ALL_OR_NOTHING:
                    // default: everything is ok
                    $gradecalc = 1;
                    break;
            }

            foreach ($response->{'separate-test-feedback'}->{'tests-response'}->{'test-response'} as $test) {
                // handle test with score
                if (count($test->{'test-result'}) > 0) {
                    $testresult = $test->{'test-result'}->result;
                    $internalError = ((string)$testresult['is-internal-error'] === 'true');
                    if ($internalError) {
                        // in case of an internal error do not calculate an actual grade
                        // todo: what do we do with internal error in test?
                        $testsWithInternalError = true;
                        // throw new moodle_exception('Internal error during grading in test');
                    }
                    $score = floatval((string)$testresult->score);

                    $gradecalc = $this->update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc);

                } else {
                    // handle test with subtest scores => calculate total score
                    list($score, $internalError) = qtype_proforma_grader_2::calc_score_for_test($test);
                    if ($internalError) {
                        $testsWithInternalError = true;
                        // throw new moodle_exception('Internal error in subtest');
                    }

                    $gradecalc = $this->update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc);
                }
            }
        } catch (Exception $e) {
            return array($questionstate, $grade, $e->getMessage(), $result, $feedbackformat);
        }


        // convert grade fraction to state, make sure approx. 1.0 is exactely 1.0
        if (abs($gradecalc - 1.0) < 0.001) {
            $grade = 1.0;
            $questionstate = question_state::$gradedright;
        } else if (abs($gradecalc - 0.0) < 0.001) {
                $grade = 0.0;
                $questionstate = question_state::$gradedwrong;
        } else {
            // exact value does not matter since it is always not right
            $grade = $gradecalc;
            if ($question->aggregationstrategy == qtype_proforma::WEIGHTED_SUM) { // do not use === !!!
                // only use weighted mean in case of appropriate aggregation strategy
                $questionstate = question_state::$gradedpartial;
            } else {
                // sorry
                $grade = 0.0;
                $questionstate = question_state::$gradedwrong;
            }
        }

        if ($testsWithInternalError) {
            return array(question_state::$needsgrading, null, 'internal error in one subtest', $result, $feedbackformat);
        }

        return array($questionstate, $grade, '', $result, $feedbackformat);
    }
}

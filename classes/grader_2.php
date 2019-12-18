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
 * Interface to Grader  ProFormA 2.0
 *
 * @package    qtype_proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K. Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/grader.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/simplexmlwriter.php');


class qtype_proforma_grader_2 extends  qtype_proforma_grader {

    /**
     * Creates the submission.xml according to Proforma specification
     * @param $code
     * @param $files
     * @param $filename
     * @param qtype_proforma_question $question question object
     * @return string submission.xml string
     * @throws coding_exception
     */
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
        $xw->create_attribute('xmlns', 'urn:proforma:v2.0');
        // $xw->createAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        // $xw->createAttribute('xsi:schemaLocation', 'urn:proforma:v2.0 schema.xsd');

        // task
        if ($question->taskstorage == qtype_proforma::PERSISTENT_TASKFILE or
                $question->taskstorage == qtype_proforma::VOLATILE_TASKFILE) { // do not use === here!
            // external task in http field
            $xw->startElement('external-task');
            $xw->create_attribute('uuid', $question->uuid);
            $xw->text('http-file:'.$question->taskfilename);
            // $xw->text('http-file:task-file');
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
                $xw->create_childelement_with_text('external-submission', 'http-file:' . $file->get_filename());
            } else {
                $httpfilename = '';
                foreach ($files as $file) {
                    if (strlen($httpfilename) == 0) {
                        $httpfilename = $file->get_filename();
                    } else {
                        $httpfilename = $httpfilename . ',' . $file->get_filename();
                    }
                }
                $xw->create_childelement_with_text('external-submission', 'http-file:' . $httpfilename);
            }
        } else if (isset($code)) {
            // $xw->createChildElementWithText('external-submission', 'http-text:'.$filename);

            // Start a child element
            $xw->startElement('files');
                $xw->startElement('file');
                    $xw->startElement('embedded-txt-file');
                    $xw->create_attribute('filename', $filename);
                    // $xw->startCdata();
                    $xw->writeCData($code);
                    // $xw->endCdata();
                    $xw->endElement(); // embedded-txt-file
                $xw->endElement(); // file
            $xw->endElement(); // files
        } else {
            debugging('got neither code nor file for submission.xml');
        }

        // lms
        $xw->startElement('lms');
        $xw->create_attribute('url', $CFG->wwwroot);

        $xw->create_childelement_with_text('submission-datetime', date('c', time()));
        $xw->create_childelement_with_text('user-id', $this->get_user());
        $xw->create_childelement_with_text('course-id', $this->get_course());

        $xw->endElement(); // lms

        // result-spec
        $xw->startElement('result-spec');
        // Attributes for submission
        $xw->create_attribute('format', 'xml');
        $xw->create_attribute('structure', 'separate-test-feedback');
        $xw->create_attribute('lang', 'de');

        $xw->create_childelement_with_text('student-feedback-level', 'debug' /*'info'*/);
        $xw->create_childelement_with_text('teacher-feedback-level', 'debug');

        $xw->endElement(); // result-spec

        $xw->endElement(); // submission

        $xw->endDocument();
        $submission = $xw->outputMemory();
        // debugging($submission);
        return $submission;
    }

    protected function post_to_grader(&$postfields, qtype_proforma_question $question) {

        if ($question->taskstorage == qtype_proforma::PERSISTENT_TASKFILE or
                $question->taskstorage == qtype_proforma::VOLATILE_TASKFILE) { // do not use === here!
            $task = $question->get_task_file();
            if (!$task instanceof stored_file) {
                throw new coding_exception("task variable has wrong class");
            }
            // debugging($task->get_content());
            $postfields['task-file'] = $task;
        }

        $protocolhost = get_config('qtype_proforma', 'graderuri_host');

        $path = get_config('qtype_proforma', 'graderuri_path');
        $uri = $protocolhost . $path;

        // return array($this->set_dummy_result3(), 200); // fake

        $curl = new curl();
        $options['CURLOPT_TIMEOUT'] = get_config('qtype_proforma', 'grading_timeout');
        $output = $curl->post($uri, $postfields, $options);
        $info = $curl->get_info();
        $httpcode = $info["http_code"];
        return array($output, $httpcode);
    }

    public function send_files_to_grader($files, qtype_proforma_question $question) {
        // check files
        foreach ($files as $file) {
            if (!$file instanceof stored_file) {
                throw new coding_exception("wrong class for file");
            }
        }

        $submission = $this->create_submission_xml(null, $files, $question->responsefilename, $question);

        // debugging($submission);

        $postfields = array('submission.xml' => $submission);

        foreach ($files as $file) {
            $postfields[$file->get_filename()] = $file;
        }

        return $this->post_to_grader($postfields, $question);
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

        $submissionxml = $this->create_submission_xml($code, null, $filename, $question);

        $postfields = array(
                'submission.xml' => $submissionxml,
                $filename => $code // ????
        );

        return $this->post_to_grader($postfields, $question);
    }

    private function update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc) {

        switch ($question->aggregationstrategy) {
            case qtype_proforma::WEIGHTED_SUM:
                $id = (string)$test['id'];
                $ghtest = $gradingtests->xpath("//test-ref[@ref='" . $id . "']");
                if (count($ghtest) == 0) {
                    throw new moodle_exception('Cannot find appropriate grading hints for test "' . $id.'"');
                }

                $ghtest = $ghtest[0];
                $weight = floatval((string)$ghtest['weight']) / $totalweight;

                // $weightscore = number_format($score * $weight, 2);
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
        $internalerror = false;

        foreach ($test->{'subtests-response'}->{'subtest-response'} as $subtest) {
            $testresult = $subtest->{'test-result'}->result;
            if ((string)$testresult['is-internal-error'] === 'true') {
                $internalerror = true;
            }
            $score += floatval((string)$testresult->score); // 0.0 or 1.0
            $counttests ++;
        }

        return array($score / $counttests, $internalerror);
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
        }

        if (!isset($response->{'separate-test-feedback'})) {
            // invalid response format
            // echo 'NO PROFORMA: ' . $result . '<br>';
            return array($questionstate, $grade, 'Unsupported feedback format', $result, self::FEEDBACK_FORMAT_INVALID);
        }

        $feedbackformat = self::FEEDBACK_FORMAT_PROFORMA2;
        $testswithinternalerror = false;

        try {
            $gradecalc = 0;
            $totalweight = 0;
            $gradingtests = null;
            switch ($question->aggregationstrategy) {
                case qtype_proforma::WEIGHTED_SUM:
                    // evaluate total weight
                    $gh = new SimpleXMLElement($question->gradinghints);
                    $gradingtests = $gh->root;
                    foreach ($gradingtests->{'test-ref'} as $test) {
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
                    $internalerror = ((string)$testresult['is-internal-error'] === 'true');
                    if ($internalerror) {
                        // in case of an internal error do not calculate an actual grade
                        // todo: what do we do with internal error in test?
                        $testswithinternalerror = true;
                        // throw new moodle_exception('Internal error during grading in test');
                    }
                    $score = floatval((string)$testresult->score);

                    $gradecalc = $this->update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc);

                } else {
                    // handle test with subtest scores => calculate total score
                    list($score, $internalerror) = self::calc_score_for_test($test);
                    if ($internalerror) {
                        $testswithinternalerror = true;
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

        if ($testswithinternalerror) {
            // TODO: get language string on display not here
            return array(question_state::$needsgrading, null,
                    get_string('internaltesterror', 'qtype_proforma'), $result, $feedbackformat);
        }

        return array($questionstate, $grade, '', $result, $feedbackformat);
    }
}

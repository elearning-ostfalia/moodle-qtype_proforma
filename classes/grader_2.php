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

    /** @var programming|null programming language (for selecting grader) */
    protected $_uri = null;

    /**
     * qtype_proforma_grader_2 constructor.
     *
     * @param $uri programming language (for selecting grader)
     */
    public function __construct($uri = null) {
        parent::__construct();
        $this->_uri = $uri;
    }

    /**
     * Creates the submission.xml according to Proforma specification
     * @param $code
     * @param $files
     * @param $filename
     * @param qtype_proforma_question $question question object
     * @return string submission.xml string
     * @throws coding_exception
     */
    private function create_submission_xml($code, $files, $filename, $uri, $question, $taskfile = null) {
        global $CFG;

        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(1);
        $xw->setIndentString(' ');

        $xw->startDocument('1.0', 'UTF-8');

        $xw->startElement('submission');

        $version = get_config('qtype_proforma', 'submissionproformaversion');
        // Namespace for Proforma Version.
        switch ($version) {
            /* case "2.0":
                $xw->create_attribute('xmlns', 'urn:proforma:v2.0');
                break;*/
            case "2.1_new":
                $xw->create_attribute('xmlns', 'urn:proforma:v2.1');
                $xw->create_attribute('xmlns:praktomat', 'urn:proforma:praktomat:v2.3');
                break;
            case "2.1_old":
                $xw->create_attribute('xmlns', 'urn:proforma:v2.1');
                break;
        }

        // $xw->createAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        // $xw->createAttribute('xsi:schemaLocation', 'urn:proforma:v2.0 schema.xsd');

        // Task as external task in http field.
        $xw->startElement('external-task');
        if (isset($taskfile)) {
            $xw->create_attribute('uuid', 'x-y-z');
        } else {
            $xw->create_attribute('uuid', $question->uuid);
        }
        if (isset($taskfile)) {
            $xaskfiletext = 'http-file:' . $taskfile->get_filename();
        } else {
            $xaskfiletext = 'http-file:' . $question->taskfilename;
        }

        if ($version == "2.1_new") {
            $xw->create_childelement_with_text('uri', $xaskfiletext);
        } else {
            $xw->text($xaskfiletext);
        }
        $xw->endElement(); // End tag external-task.

        if (isset($uri)) {
            if (qtype_proforma::VCS_GIT == $question->vcssystem) {
                if ($version == '2.1_old' or $version == '2.0') {
                    throw new coding_exception('Version control system Git is not supported in ' .
                        'Proforma version ' . $version);
                }
            }
            // Version control system.
            if ($version == "2.1_new") {
                $xw->startElement('external-submission');
                $xw->create_childelement_with_text('uri', $uri);
                $xw->startElement('praktomat:meta-data');
                switch ($question->vcssystem) {
                    case qtype_proforma::VCS_GIT:
                        $xw->startElement('praktomat:git');
                        $xw->endElement(); // End tag praktomat:git.
                        break;
                    case qtype_proforma::VCS_SVN:
                        $xw->startElement('praktomat:svn');
                        $xw->endElement(); // End tag praktomat:git.
                        break;
                    default:
                        throw new coding_exception('invalid vcs system');
                }
                $xw->endElement(); // End tag praktomat::meta-data.

                $xw->endElement(); // End tag external-submission.
            } else {
                $xw->create_childelement_with_text('external-submission', $uri);
            }
        } else if (isset($files)) {
            $xw->startElement('external-submission');
            if ($version == "2.1_new") {
                $xw->startElement('uri');
            }
            // File upload.
            if (count($files) == 1) {
                $file = array_values($files)[0];
                $xw->text('http-file:' . $file->get_filename());
            } else {
                $httpfilename = '';
                foreach ($files as $file) {
                    if (strlen($httpfilename) == 0) {
                        $httpfilename = $file->get_filename();
                    } else {
                        $httpfilename = $httpfilename . ',' . $file->get_filename();
                    }
                }
                $xw->text('http-file:' . $httpfilename);
            }
            if ($version == "2.1_new") {
                $xw->endElement(); // End tag uri.
            }
            $xw->endElement(); // End tag external-submission.
        } else if (isset($code)) {
            // Editor.
            $xw->startElement('files');
                $xw->startElement('file');
                    // Code is sent as base64 encoded text because it might contain illegal
                    // characters which could lead to some problems otherwise.
                    $xw->startElement('embedded-bin-file');
                    $xw->create_attribute('filename', $filename);
                    $xw->text(base64_encode($code));
                    $xw->endElement(); // End tag embedded-bin-file.
                $xw->endElement(); // End tag file.
            $xw->endElement(); // End tag files.
        } else {
            debugging('got neither code nor file for submission.xml');
        }

        // Tag lms.
        $xw->startElement('lms');
        $xw->create_attribute('url', $CFG->wwwroot);

        $xw->create_childelement_with_text('submission-datetime', date('c', time()));
        $xw->create_childelement_with_text('user-id', $this->get_user());
        $xw->create_childelement_with_text('course-id', $this->get_course());

        $xw->endElement(); // End tag lms.

        // Tag result-spec.
        $xw->startElement('result-spec');
        // Attributes for submission.
        $xw->create_attribute('format', 'xml');
        $xw->create_attribute('structure', 'separate-test-feedback');
        $xw->create_attribute('lang', 'de');
        $xw->create_childelement_with_text('student-feedback-level', 'debug' /*'info'*/);
        $xw->create_childelement_with_text('teacher-feedback-level', 'debug');
        $xw->endElement(); // End tag result-spec.

        $xw->endElement(); // End tag submission.

        $xw->endDocument();
        $submission = $xw->outputMemory();
        return $submission;
    }

    /**
     * send grading request via HTTP post
     *
     * @param $postfields
     * @param qtype_proforma_question $question
     * @return array
     * @throws coding_exception
     */
    protected function post_to_grader(&$postfields, $question, $taskfile = null, $test = false) {
        // Get timeout.
        $options['CURLOPT_TIMEOUT'] = get_config('qtype_proforma', 'grading_timeout');
        if ($test) {
            // Reduce timeout for testing conection because user is waiting.
            $options['CURLOPT_TIMEOUT'] = 5;
        }
        // Add task file.
        if (isset($question)) {
            $task = $question->get_task_file();
            if (!$task instanceof stored_file) {
                throw new coding_exception("task variable has wrong class");
            }
            $postfields['task-file'] = $task;
        } else {
            if (!isset($taskfile)) {
                throw new coding_exception('task is missing');
            }
            if (!$taskfile instanceof stored_file) {
                throw new coding_exception("task variable has wrong class");
            }
            $postfields['task-file'] = $taskfile;
        }

        // Get URI.
        if (!isset($this->_uri)) {
            $protocolhost = trim(get_config('qtype_proforma', 'graderuri_host'));
            $path = trim(get_config('qtype_proforma', 'graderuri_path'));
            $uri = $protocolhost . $path;
        } else {
            $uri = $this->_uri;
        }

        // debugging($uri);
        // return array($this->set_dummy_result3(), 200); // Fake.

        // Send task and submission to grader with Curl with a configured timeout.
        $curl = new curl();
        $output = $curl->post($uri, $postfields, $options);
        $info = $curl->get_info();
        $httpcode = $info["http_code"];
        if ($output === false) {
            // In case of a curl error get the erro number.
            $msg = 'curl request failed: Errno = ' . $curl->get_errno() . ', error = "' . $curl->error . '"';
            return array($msg, $httpcode);
        } else {
            return array($output, $httpcode);
        }
    }

    private $chunks = [];
    private $data = '';
    private $response = '';
    private $responsestarted = false;
    function write_data() {
        $this->data = $this->data . implode($this->chunks);
        $this->chunks = [];

        $lines = explode("\n\n", $this->data);
        if (substr( $this->data, -2) === "\n\n") {
            // Remove last element as is may be empty when last 2 characters are \n\n
            $this->data = array_pop($lines);
        } else {
            $this->data = '';
        }

        foreach ($lines as $line) {
            // Ends with \n\n.
            if (substr( $line, 0, strlen("data: RESPONSE START####")) === "data: RESPONSE START####") {
                // Response starts.
                $this->responsestarted = true;
                echo $line . "\n\n";
                continue;
            }
            if (!$this->responsestarted) {
                // No response.
                echo $line . "\n\n";
                continue;
            }
            if (substr( $line, 0, strlen("data: RESPONSE END####")) === "data: RESPONSE END####") {
                // Response is complete => execute callback
                if (isset($this->callback)) {
                    call_user_func($this->callback, $this->response, $this, $this->question, $this->gradinghints);
                }
                echo $line . "\n\n";
            } else {
                $this->response .= substr( $line, strlen("data: ")) . "\n";
            }
        }

        ob_flush();
        flush();
    }

    /**
     * CURLOPT_WRITEFUNCTION which flushes the output buffer.
     *
     * @param resource $curl_handle
     * @param string   $chunk
     */
    function curl_write_flush($curl_handle, $chunk) {
        $len = strlen($chunk);
        $this->chunks[] = $chunk;
        $this->write_data();
        return $len; // Tell curl how much data was handled.
    }

    protected function post_to_grader_and_stream_result(&$postfields, $task, $uri) {
        $this->chunks = [];
        $this->data = null;
        $this->responsestarted = false;
        // Get timeout.
        $options['CURLOPT_TIMEOUT'] = get_config('qtype_proforma', 'upload_timeout');
        // $options['CURLOPT_SUPPRESS_CONNECT_HEADERS'] = True;
        $options['CURLOPT_WRITEFUNCTION'] = array(&$this, 'curl_write_flush');

        // Add task file.
        if (!$task) {
            throw new coding_exception('task is missing');
        }
        if (!$task instanceof stored_file) {
            throw new coding_exception("task variable has wrong class");
        }
        $postfields['task-file'] = $task;

        debugging($uri);

        // Send task and submission to grader with Curl with a configured timeout.
        $curl = new curl();
        $output = $curl->post($uri, $postfields, $options);
        $info = $curl->get_info();
        $httpcode = $info["http_code"];
        if ($output === false) {
            // In case of a curl error get the erro number.
            $msg = 'curl request failed: Errno = ' . $curl->get_errno() . ', error = "' . $curl->error . '"';
            return array($msg, $httpcode);
        } else {
            return array($output, $httpcode);
        }
    }

    /**
     * send file upload submission to grader
     *
     * @param $files
     * @param qtype_proforma_question $question
     * @return array
     * @throws coding_exception
     */
    public function send_files_to_grader($files, qtype_proforma_question $question) {
        // Check file classes.
        foreach ($files as $file) {
            if (!$file instanceof stored_file) {
                throw new coding_exception("wrong class for file");
            }
        }

        // Create submission.
        $submission = $this->create_submission_xml(null, $files, $question->responsefilename, null, $question);
        $postfields = array('submission.xml' => $submission);
        foreach ($files as $file) {
            $postfields[$file->get_filename()] = $file;
        }

        // Send POST request to grader.
        return $this->post_to_grader($postfields, $question);
    }

    /**
     * send file upload submission to grader
     *
     * @param $files
     * @param qtype_proforma_question $question
     * @return array
     * @throws coding_exception
     */
    public function send_files_with_task_to_grader($files, $task) {
        // Check file classes.
        foreach ($files as $file) {
            if (!$file instanceof stored_file) {
                throw new coding_exception("wrong class for file");
            }
        }

        // Create submission.
        $submission = $this->create_submission_xml(null, $files, null, null, null, $task);
        $postfields = array('submission.xml' => $submission);
        foreach ($files as $file) {
            // debugging(print_r($file));
            $postfields[$file->get_filename()] = $file;
        }

        // debugging($submission);
        // Send POST request to grader.
        return $this->post_to_grader($postfields, null, $task);
    }


    /**
     * send files and task to grader and expect result to be streamed
     * @param $files
     * @param $task
     * @return array
     * @throws coding_exception
     */
    public function send_files_with_task_to_grader_and_stream_result($files, $task,
                                                                     $callback, $question, $gradinghints) {
        $this->callback = $callback;
        $this->question = $question;
        $this->gradinghints = $gradinghints;

        // Check file classes.
        foreach ($files as $file) {
            if (!$file instanceof stored_file) {
                throw new coding_exception("wrong class for file");
            }
        }

        // Create submission.
        $submission = $this->create_submission_xml(null, $files, null, null, null, $task);
        $postfields = array('submission.xml' => $submission);
        foreach ($files as $file) {
            // debugging(print_r($file));
            $postfields[$file->get_filename()] = $file;
        }

        // Get URI.
        // $protocolhost = trim(get_config('qtype_proforma', 'graderuri_host'));
        // $path = trim(get_config('qtype_proforma', 'runtest_path'));
        // $uri = $protocolhost . $path;
        if (!isset($this->_uri)) {
            throw new moodle_exception('SSE uri is not set in grader');
        }

        // debugging($submission);
        // Send POST request to grader.
        return $this->post_to_grader_and_stream_result($postfields, $task, $this->_uri);
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

        $submissionxml = $this->create_submission_xml($code, null, $filename, null, $question);

        $postfields = array(
                'submission.xml' => $submissionxml,
                $filename => $code
        );

        return $this->post_to_grader($postfields, $question);
    }

    /**
     * Function uploads the task associated to question to the grader.
     * It is assumed that this may take longer. So the response is not buffered.
     *
     * @param qtype_proforma_question $question
     * @return array
     * @throws coding_exception
     */
    public function upload_task_to_grader($task) {
        // $filename = $question->responsefilename;

        $submissionxml = $this->create_submission_xml('no source code available',
            null, 'nofilenameneeded', null, null, $task);

        $postfields = array(
            'submission.xml' => $submissionxml,
            'nofilenameneeded' => ''
        );

        // Get URI.
        $protocolhost = trim(get_config('qtype_proforma', 'graderuri_host'));
        $path = trim(get_config('qtype_proforma', 'uploaduri_path'));
        $uri = $protocolhost . $path;

        return $this->post_to_grader_and_stream_result($postfields, $task, $uri);
    }
    /*
    public function upload_task_to_grader(qtype_proforma_question $question) {
        $filename = $question->responsefilename;

        $submissionxml = $this->create_submission_xml('no source code available',
            null, $filename, null, $question);

        $postfields = array(
            'submission.xml' => $submissionxml,
            $filename => ''
        );

        return $this->post_to_grader_and_stream_result($postfields, $question);
    }
    */

    /**
     * checks if the connection to the grader is valid.
     * This is done by sending a simple task with some piece of code.
     *
     * @return http code and grader message, in case of http code = 0
     * a curl error message might be returned instead of the grader message.
     */
    public function test_connection() {
        $task = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb">
    <title>ProFormA question</title>
    <description>description</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="MS" used-by-grader="false" visible="no">
            <embedded-txt-file filename="modelsolution.java">// no model solution available </embedded-txt-file>
        </file>
    </files>
    <model-solutions>
        <model-solution id="1">
            <filerefs>
                <fileref refid="MS"/>
            </filerefs>
        </model-solution>
    </model-solutions>
    <tests>
        <test id="compiler">
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        // Create task file in draft area.
        global $USER;
        $fs = get_file_storage();
        $itemid = file_get_unused_draft_itemid();
        $filerecord = array(
                'contextid' => context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $itemid,
                'filepath' => '/',
                'filename' => 'task.xml',
        );
        $taskfile = $fs->create_file_from_string($filerecord, $task);

        $code = "class X {};";
        $filename = "X.java";
        $submissionxml = $this->create_submission_xml($code, null, $filename, null, null, $taskfile);

        $postfields = array(
                'submission.xml' => $submissionxml,
                $filename => $code
        );

        return $this->post_to_grader($postfields, null, $taskfile, true);
    }

    /**
     * send external code (from Source Control) to grader
     *
     * @param type $uri
     * @param qtype_proforma_question $question
     * @return type
     * @throws coding_exception
     */
    public function send_external_submission_to_grader($uri, qtype_proforma_question $question) {
        if (empty($uri)) {
            throw new coding_exception('send_external_submission_to_grader with empty uri');
        }

        $submissionxml = $this->create_submission_xml(null, null, null, $uri, $question);

        $postfields = array(
                'submission.xml' => $submissionxml
        );

        return $this->post_to_grader($postfields, $question);
    }


    /**
     * updates the grade result for this specifix test
     * @param $test
     * @param $score
     * @param $question
     * @param $totalweight
     * @param $gradingtests
     * @param $gradecalc
     * @return float|int
     * @throws moodle_exception
     */
    private function update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc) {

        switch ($question->aggregationstrategy) {
            case qtype_proforma::WEIGHTED_SUM:
                $id = (string)$test['id'];
                $ghtest = $gradingtests->xpath("//test-ref[@ref='" . $id . "']");
                if (count($ghtest) == 0) {
                    throw new moodle_exception('Cannot find appropriate grading hints for test "' . $id.'"');
                }

                $ghtest = $ghtest[0];
                if ($totalweight == 0) {
                    debugging('Division by zero: ' . $question->name);
                    $gradecalc = 0;
                } else {
                    $weight = floatval((string)$ghtest['weight']) / $totalweight;
                    $weightscore = $score * $weight;
                    $gradecalc += $weightscore;
                }
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

    /**
     * calc score for this specifix test resp. subtest
     *
     * @param $test
     * @return array
     * @throws moodle_exception
     */
    public static function calc_score_for_test($test) {
        if (count($test->{'subtests-response'}) == 0) {
            throw new moodle_exception('Format error: Subtest results not found');
        }

        $counttests = 0.0;
        $score = 0.0;
        $internalerror = false;
        $testsfound = false;

        foreach ($test->{'subtests-response'}->{'subtest-response'} as $subtest) {
            $testsfound = true;
            $testresult = $subtest->{'test-result'}->result;
            if ((string)$testresult['is-internal-error'] === 'true') {
                $internalerror = true;
            }
            $score += floatval((string)$testresult->score);
            // Note: $testresult-score is 0.0 or 1.0.
            $counttests ++;
        }

        // Avoid division by zero.
        if (!$testsfound) {
            return array(0.0, $internalerror);
        }

        return array($score / $counttests, $internalerror);
    }

    /**
     * calculate or extract grade from grader response
     *
     * @param $result
     * @param $httpcode
     * @param qtype_proforma_question $question
     * @return array
     */
    public function extract_grade($result, $httpcode, qtype_proforma_question $question) {
        $questionstate = question_state::$needsgrading;
        // State 'needsgrading' is an inactive state that does not allow the
        // student to change his or her submission
        // => the question behaviour converts the state to 'invalid' during attempt
        // (when finishing the state it is kept).
        $grade = null;

        if (isset($httpcode) && $httpcode != 200) {
            return array($questionstate, $grade, 'HTTP Status Code ' . $httpcode, $result, self::FEEDBACK_FORMAT_HTTP_ERROR);
        }
        if ($result === false) {
            // ERROR: no result
            // TODO: improve error handling with returning array with multible values
            // - return only error text and calling function must convert???
            // - or throw exception??
            // - or use if-else.
            return array($questionstate, $grade, 'No result from grader (maybe grader is unreachable)', '',
                self::FEEDBACK_FORMAT_ERROR);
        }
        $response = '';
        try {
            $response = new SimpleXMLElement($result);
        } catch (Exception $e) {
            return array($questionstate, $grade, 'Unsupported feedback from grader, no valid XML: ' . $e->getMessage(),
                    $result, self::FEEDBACK_FORMAT_ERROR);
        }

        if (!isset($response->{'separate-test-feedback'})) {
            // Invalid response format.
            return array($questionstate, $grade, 'Unsupported feedback format',
                $result, self::FEEDBACK_FORMAT_INVALID);
        }

        $feedbackformat = self::FEEDBACK_FORMAT_PROFORMA2;
        $testswithinternalerror = false;

        $resultfound = false;
        try {
            $gradecalc = 0;
            $totalweight = 0;
            $gradingtests = null;
            switch ($question->aggregationstrategy) {
                case qtype_proforma::WEIGHTED_SUM:
                    // Evaluate total weight.
                    $gh = new SimpleXMLElement($question->gradinghints);
                    $gradingtests = $gh->root;
                    foreach ($gradingtests->{'test-ref'} as $test) {
                        $totalweight += floatval((string)$test['weight']);
                    }
                    break;
                case qtype_proforma::ALL_OR_NOTHING:
                    // Default: everything is ok.
                    $gradecalc = 1;
                    break;
            }

            foreach ($response->{'separate-test-feedback'}->{'tests-response'}->{'test-response'} as $test) {
                $resultfound = true;
                // Handle test with score.
                if (count($test->{'test-result'}) > 0) {
                    $testresult = $test->{'test-result'}->result;
                    $internalerror = ((string)$testresult['is-internal-error'] === 'true');
                    if ($internalerror) {
                        // In case of an internal error do not calculate an actual grade
                        // todo: what do we do with internal error in test?
                        $testswithinternalerror = true;
                    }
                    $score = floatval((string)$testresult->score);

                    $gradecalc = $this->update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc);

                } else {
                    // Handle test with subtest scores => calculate total score.
                    list($score, $internalerror) = self::calc_score_for_test($test);
                    if ($internalerror) {
                        $testswithinternalerror = true;
                    }

                    $gradecalc = $this->update_grade($test, $score, $question, $totalweight, $gradingtests, $gradecalc);
                }
            }
        } catch (Exception $e) {
            return array($questionstate, $grade, $e->getMessage(), $result, $feedbackformat);
        }

        if ($resultfound) {
            // Convert grade fraction to state, make sure approx. 1.0 is exactely 1.0.
            if (abs($gradecalc - 1.0) < 0.001) {
                $grade = 1.0;
                $questionstate = question_state::$gradedright;
            } else if (abs($gradecalc - 0.0) < 0.001) {
                $grade = 0.0;
                $questionstate = question_state::$gradedwrong;
            } else {
                // Exact value does not matter since it is always not right.
                $grade = $gradecalc;
                if ($question->aggregationstrategy == qtype_proforma::WEIGHTED_SUM) { // Do not use === here.
                    // Only use weighted mean in case of appropriate aggregation strategy.
                    $questionstate = question_state::$gradedpartial;
                } else {
                    // Sorry.
                    $grade = 0.0;
                    $questionstate = question_state::$gradedwrong;
                }
            }
        } else {
            // No test result could be evaluated, this is treated as an internal error.
            return array(question_state::$needsgrading, null,
                '' /*get_string('internaltesterror', 'qtype_proforma')*/, $result, $feedbackformat);

        }

        if ($testswithinternalerror) {
            // TODO: get language string on display not here.
            // debugging('internaltesterror');
            return array(question_state::$needsgrading, null,
                    get_string('internaltesterror', 'qtype_proforma'), $result, $feedbackformat);
        }

        return array($questionstate, $grade, '', $result, $feedbackformat);
    }
}

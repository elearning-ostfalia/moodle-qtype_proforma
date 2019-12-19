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
 * Interface to Grader
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

// TODO: cleanup: LON-CAPA format is no longer needed!

/*
 * base class for graders (and this one is for LON-CAPA feedback)
 */
class qtype_proforma_grader {

    // different message formats

    /**
     * ProFormA 2 format
     */
    const FEEDBACK_FORMAT_PROFORMA2 = 0;
    /**
     * LON-CAPA format (no longer supported)
     */
    const FEEDBACK_FORMAT_LC = 1; // LON CAPA
    /**
     * invalid
     */
    const FEEDBACK_FORMAT_INVALID = -2;
    /**
     * error
     */
    const FEEDBACK_FORMAT_ERROR = -1;

    /**
     * no feedback
     */
    const FEEDBACK_FORMAT_NONE = -3;
    /**
     * HTTP error
     */
    const FEEDBACK_FORMAT_HTTP_ERROR = -4;

    /**
     * returns (encrypted) course identifier
     * @return string
     */
    protected function get_course() {
        global $COURSE;

        /* Sie sollten das vollständige Ergebnis von crypt() als Salt zum
           Passwort-Vergleich übergeben, um Problemen mit unterschiedlichen
           Hash-Algorithmen vorzubeugen. (Wie bereits ausgeführt, verwendet
           ein Standard-DES-Passwort-Hash einen 2-Zeichen-Salt, ein
           MD5-basierter hingegen nutzt 12 Zeichen. */

        $courseid = crypt($COURSE->id, 'pro#forma');

        if (crypt($COURSE->id, $courseid) != $courseid) {
            debugging("course_id does not match!");
        }

        return $courseid;
    }

    /**
     * returns encrypted user identifier
     *
     * @return string
     */
    protected function get_user() {
        global $USER;

        /* Sie sollten das vollständige Ergebnis von crypt() als Salt zum
           Passwort-Vergleich übergeben, um Problemen mit unterschiedlichen
           Hash-Algorithmen vorzubeugen. (Wie bereits ausgeführt, verwendet
           ein Standard-DES-Passwort-Hash einen 2-Zeichen-Salt, ein
           MD5-basierter hingegen nutzt 12 Zeichen. */

        $userid = crypt($USER->id, 'pro#forma');
        if (crypt($USER->id, $userid) != $userid) {
            debugging("user_id does not match!");
        }

        return $userid;
    }

    /**
     * create fake response in LON-CAPA format
     *
     * @return string
     */
    private function set_dummy_result() {
        $dummyresult = '<loncapagrade>' .
            '<awarddetail>INCORRECT</awarddetail>' .
            '<message>' .
            '<taskresult grade="failed">' .
            '<tasktitle>reverse string</tasktitle>' .
            '<testresult grade="failed">' .
            '<testname>FAKE JUnit Test: Java JUnit Test</testname>' .
            '<testlog><![CDATA[<pre>testet, ob die Funktion das macht, was sie machen soll' .
            ' ' . PHP_EOL .
            '                ======== Test Results ======' .
            ' ' . PHP_EOL .
            '                </pre><br/>' .
            '<div>1 <tt>Java</tt> user-submitted files found for compilation:  MyString.java &nbsp;' . '</div>' .
            ' ' .
            '<div>Java compiler output:</div>' .
            '<pre><b>MyString.java:1: error: reached end of file while parsing</b>' .
            'dfgdfg' .
            '^' .
            '1 error' .
            '1' .
            '</pre>' .
            ']]></testlog>' .
            '</testresult>'.
            ' ' .
            '<testresult grade="passed">' .
            '<testname>FAKE CheckStyle Test</testname>' .
            '<testlog><![CDATA[<pre>' .
            ' ' .
            '======== Test Results ======' .
            ' ' .
            '</pre><br/><pre>Starting audit...' .
            'Audit done.' .
            '</pre>]]></testlog>' .
            '</testresult>' .
            ' ' .
            '<filename>MyString.java</filename>' .
            ' ' .
            '</taskresult>' .
            '</message>' .
            '<awarded></awarded>' .
            '</loncapagrade>';
        return $dummyresult;
    }

    // override!

    /**
     * generates the grade from the given input parameters
     *
     * @param $result
     * @param $httpcode
     * @param qtype_proforma_question $question
     * @return array
     */
    public function extract_grade($result, $httpcode, qtype_proforma_question $question) {
        return $this->extract_grade_from_lon_capa_format_result($result);
    }

    /**
     * @param $result
     * @return array with:
     * 1. question state
     * 2. grade fraction [0...1]
     * 3. error message (or '' in case of no error)
     * 4. grading message (or '' in case of error)
     * @throws coding_exception
     */
    private function extract_grade_from_lon_capa_format_result($result) {
        $questionstate = question_state::$invalid;
        $grade = 0;
        $feedbackformat = self::FEEDBACK_FORMAT_LC;

        // ERROR: no result
        if ($result === false) {
            // TODO: improve error handling with returning array with multible values
            // - return only error text and calling function must convert???
            // - or throw exception??
            // - or use if-else
            return array($questionstate, $grade, 'no result from grader (maybe grader is unreachable)', '', $feedbackformat);
        }

        if (empty($result)) {
            debugging('extract_grade_from_LON_CAPA_format_result with empty result');
        }
        // result is expected to be XML. So read DOM tree
        $xmldoc = new DOMDocument();
        if (!$xmldoc->loadXML($result, LIBXML_NOERROR )) {
            // LIBXML_NOERROR is set in order to ignore errors in result
            // which would trigger Moodle to stop in development mode (i.e. showing all errors)
            // ERROR: no xml format
            return array($questionstate, $grade, $result, '', $feedbackformat);
        }

        $awarddetails = $xmldoc->getElementsByTagName('awarddetail');
        $resultawarded = $xmldoc->getElementsByTagName('awarded');
        $messages = $xmldoc->getElementsByTagName('message');
        if (count($awarddetails) != 1 || count($resultawarded) != 1 || count($messages) != 1) {
            // ERROR: XML result does not contain element awarddetail
            // (invalid format?)
            // TODO handle unknown format
            return array($questionstate, $grade, $result, '', $feedbackformat);
        }

        // get expected elements
        $awarded = $resultawarded[0]->nodeValue;
        $award = $awarddetails[0]->nodeValue;
        $message = $messages[0]; // leave DOM element

        // evaluate grading result
        switch (strtoupper($award)) {
            case 'ERROR':
                return array($questionstate, $grade, $message->nodeValue, '', $feedbackformat);
            case 'INCORRECT':
                $grade = 0;
                $questionstate = question_state::$gradedwrong;
                break;
            case 'CORRECT':
            case 'EXACT_ANS':
                $grade = 1;
                $questionstate = question_state::$gradedright;
                break;
            case 'APPROX_ANS':
                $questionstate = question_state::$gradedpartial;
                break;
            default:
                // return array($questionstate, $grade, $result);
                throw new coding_exception("invalid award in qtype_proforma_grader::extract_grade_from_LON_CAPA_format_result: " .
                    $award);
        }

        // override $grade with result from grader (if any)
        if (!empty($awarded)) {
            $grade = $awarded;
        }

        // nodeValue does not contain all xml tags.
        // But we need the full message content. This is done
        // by saveXML
        $feedback = $message->ownerDocument->saveXML($message);
        return array($questionstate, $grade, '', $feedback, $feedbackformat);
    }

    /**
     * send grading request via HTTP post
     * @param $postfields
     * @param qtype_proforma_question $question
     * @return array
     * @throws coding_exception
     */
    private function post_to_grader(&$postfields, qtype_proforma_question $question) {
        global $USER, $COURSE;

        if ($question->taskstorage == qtype_proforma::PERSISTENT_TASKFILE) { // do not use === here!
            $task = $question->get_task_file();
            if (!$task instanceof stored_file) {
                throw new coding_exception("no task file available");
            }
            $postfields['task-file'] = $task;
        } else {
            $postfields['task-repo'] = $question->taskrepository;
            $postfields['task-path'] = $question->taskpath;
        }

        // debugging('send user=' . $USER->id . '='. $user_id .', course='. $COURSE->id . '='.$course_id);
        $postfields['course'] = $this->get_course();
        $postfields['user'] = $this->get_user();

        $protocolhost = get_config('qtype_proforma', 'graderuri_host');

        /*
        if (!empty(strstr($protocolhost, '2.2.2.2' ))) {
         // no actual host configured (debug host name)
         // => return fake result (in order to avoid waiting for timeout
         // of unreachable host)
         return $this->set_dummy_result(); // fake
        }
        */
        $path = get_config('qtype_proforma', 'graderuri_path');
        $uri = $protocolhost . $path;

        $curl = new curl();
        $output = $curl->post($uri, $postfields);
        $info = $curl->get_info();

        return array($output, $info->httpcode === 200 ? null : $info->httpcode);
    }

    /** sends a student's uploaded file to the grader. Exactly one file is supported.
     *
     * @param $file
     * @param $filename
     * @param $taskrepo
     * @param $taskpath
     */
    public function send_file_to_grader($file, qtype_proforma_question $question) {

        if (!$file instanceof stored_file) {
            throw new coding_exception("wrong class");
        }

        $postfields = array(
                'submission-file' => $file,
                'submission-filename' => $question->responsefilename);

        return $this->post_to_grader($postfields, $question);
    }

    /**
     * handles grading request for file upload with more than one file
     * @param $file
     * @param qtype_proforma_question $question
     * @throws coding_exception
     */
    public function send_files_to_grader($file, qtype_proforma_question $question) {
        throw new coding_exception('send_files_to_grader is not implemented');
    }

    /** sends the sudent's submitted source code to the grader
     *
     * @param $code
     * @param $classname
     * @param $taskrepo
     * @param $taskpath
     * @return mixed|string
     */
    public function send_code_to_grader($code, qtype_proforma_question $question) {
        if (empty($code)) {
            throw new coding_exception('send_code_to_grader with empty code');
        }

        $filename = $question->responsefilename;

        $postfields = array(
            'submission' => $code,
            'submission-filename' => $filename);

        return $this->post_to_grader($postfields, $question);
    }
}
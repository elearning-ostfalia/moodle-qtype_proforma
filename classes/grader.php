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
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
        throw new coding_exception('extract_grade is not implemented');
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

    /**
     * sends a grading request for a submission located in a version control system
     * @param $uri
     * @param qtype_proforma_question $question
     * @throws coding_exception
     */
    public function send_external_submission_to_grader($uri, qtype_proforma_question $question) {
        throw new coding_exception('send_external_submission_to_grader is not implemented');
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
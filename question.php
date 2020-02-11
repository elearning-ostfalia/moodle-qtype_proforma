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
// along with ProFormA Question Type for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The ProFormA Question definition
 *
 * @package    qtype_proforma
 * @copyright  2009 The Open University
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
               The Open University (for essay code base)
 */



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/grader_2.php');



/**
 * Represents the proforma question.
 */
class qtype_proforma_question extends question_graded_automatically {

    // defines the maximum number of char shown in the response summary
    const SUMMARY_LENGTH = 500;

    /** @var  text The UUID of the associated ProFormA task. */
    public $uuid;
    public $taskrepository;
    public $taskpath;
    /** @var string filename of the ProFormA task file stored in Moodle  */
    public $taskfilename;

    // additional files for download in student view. All filenames are comma separated
    public $templates;

    public $downloads;
    public $displayfiles; // not used so far!!!


    public $modelsolfiles;

    public $responsefilename;

    public $programminglanguage;
    /** @var  string Initial text in response editor */
    public $responsetemplate;

    /** @var  string Whether there is an editor or an editor with filepicker..  */
    public $responseformat;
    /** @var  int The number of lines in the editor. */
    public $responsefieldlines;
    /** @var int The maximum number of attachments allowed. */
    public $attachments;
    /** @var int The number of bytes allowed for submission by file upload */
    public $maxbytes;
    /** @var  string allowed filetypes for upload */
    public $filetypes;

    public $comment;
    public $commentformat;

    public $aggregationstrategy;
    public $gradinghints;


    /** @var  int Whether the task is created by import of a task.zip file (default) */
    public $taskstorage; // source? 1=internal 2=LON-CAPA

    /** @var null is made a member variable in oder to use test doubles */
    public $grader = null;

    public $proformaversion;

    /** @var string The URI template for accessing a version control system with submissions. */
    public $vcsuritemplate;
    /** @var string The label for an input field (if any). */
    public $vcslabel;

    /**
     * creates the grader object (why function???)
     *
     * @return null|qtype_proforma_grader_2
     */
    private function get_grader() {
        if ($this->grader == null) {
            $this->grader = new qtype_proforma_grader_2();
            // $this->grader = new qtype_proforma_grader();
        }

        return $this->grader;
    }

    /**
     * make the behaviour needed for proforma:
     * always adaptiveexternalgrading
     *
     * @param question_attempt $qa
     * @param string $preferredbehaviour
     * @return question_behaviour
     */
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        switch($preferredbehaviour) {
            /* The question behaviour must be changed because we need the grader feedback
               stored in the database !!
               If there the original behaviour is desired we need to overload
               these classes (see interactivewithfeedback). Otherwise the grader feedback is lost.
            case 'deferredfeedback':
            case 'deferredcbm':
                // keep preferred behaviour
            //    return question_engine::make_behaviour($preferredbehaviour, $qa, $preferredbehaviour);
            */
            default:
                return question_engine::make_behaviour('adaptiveexternalgrading', $qa, $preferredbehaviour);
        }
    }


    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     * @return string the message.
     */
    public function get_validation_error(array $response) {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return "TODO: get_validation_error";
    }


    /**
     * @param moodle_page the page we are outputting to.
     * @return qtype_proforma_format_renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_proforma', 'format_' . $this->responseformat);
    }


    /** BASIS KOMMENTAR AUS question_definition (abstract)
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *      that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *      meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data() {
        $expecteddata = array();
        switch ($this->responseformat) {
            /*  case 'editorfilepicker':
                $expecteddata['answer'] = PARAM_RAW;
                $expecteddata['attachments'] = question_attempt::PARAM_FILES;
                break;*/
            case qtype_proforma::RESPONSE_EDITOR:
                $expecteddata['answer'] = PARAM_RAW;
                break;
            case qtype_proforma::RESPONSE_FILEPICKER:
                $expecteddata['attachments'] = question_attempt::PARAM_FILES;
                break;
            case qtype_proforma::RESPONSE_VERSION_CONTROL:
                $expecteddata['answer'] = PARAM_RAW;
                break;
            default:
                throw new coding_exception('unsupported responseformat '. $this->responseformat);
        }
        return $expecteddata;
    }

    // für question_with_responses (interface question_manually_gradable)
    /**
     * Produce a plain text summary of a response.
     * @param $response a response, as might be passed to {@link grade_response()}.
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            // return 'das ist ein Rückgabewert'.PHP_EOL.'von summarise response'.PHP_EOL.'guckst Du!';
            $code = $response['answer']; // return $response['answer'];
            $text = '';
            if (is_a($code, 'question_file_loader')) {
                $text = $code->__toString();
            } else {
                $text = $code;
            }
            if (strlen($text) > self::SUMMARY_LENGTH) {
                return mb_substr($text, 0, self::SUMMARY_LENGTH) . '...';
            }
            return $text;

            // return question_utils::to_plain_text($response['answer'],
            // $response['answerformat'], array('para' => false));
        } else if (isset($response['attachments'])) {

            if (is_a($response['attachments'], 'question_file_loader')) {
                $files = $response['attachments']->get_files();
                if (!$files) {
                    throw new coding_exception("no files attached");
                }

                if (count($files) > 1) {
                    // more than one file: return filenames
                    $filenames = array();
                    foreach ($files as $file) {
                        $filenames[] = $file->get_filename();
                    }

                    return implode(', ', $filenames);
                }
                // only one file: get first file
                $file = array_values($files)[0];
                if (!$file instanceof stored_file) {
                    throw new coding_exception("wrong class");
                }
                // do not return content of file because we do not know if it is binary!
                return $file->get_filename();
            }

            return '(uploaded file)';
        } else {
            // response data could be extracted from question step which
            // could be grader feedback without any user repsonse
            // debugging('nothing to output ...');
            return null;
        }
    }

    /** BASIS KOMMENTAR AUS question_definition (abstract)
     * What data would need to be submitted to get this question correct.
     * If there is more than one correct answer, this method should just
     * return one possibility. If it is not possible to compute a correct
     * response, this method should return null.
     *
     * @return array|null parameter name => value.
     */
    public function get_correct_response() {
        return null;
    }

    // für question_with_responses (interface question_manually_gradable)
    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete. That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response) {
        // Determine if the given response has online text and attachments.
        $hasinlinetext = array_key_exists('answer', $response) &&
                (trim($response['answer']) !== '');
        if (!empty($this->responsetemplate != '' && $hasinlinetext)) {
            // inline text equals to response template?
            if ($this->responsetemplate == $response['answer']) {
                // yes => no input in editor
                $hasinlinetext = false;
            }
        }
        $hasattachments = array_key_exists('attachments', $response)
            && $response['attachments'] instanceof question_response_files;

        // Determine the number of attachments present.
        if ($hasattachments) {
            $attachcount = count($response['attachments']->get_files());
            // if ($attachcount == 0) {
            // throw new coding_exception('no attachments found');
            // }
        } else {
            $attachcount = 0;
        }

        // Determine if we have /some/ content to be graded.
        $hascontent = $hasinlinetext || ($attachcount > 0);

        $meetsconentreq = true;

        // Determine if we meet the optional requirements.
        switch ($this->responseformat) {
            case qtype_proforma::RESPONSE_FILEPICKER:
                $meetsconentreq = ($attachcount > 0);
                break;
            case qtype_proforma::RESPONSE_EDITOR:
                $meetsconentreq = $hasinlinetext;
                break;
            case qtype_proforma::RESPONSE_VERSION_CONTROL:
                $meetsconentreq = $hasinlinetext;
                break;
            default:
                throw new coding_exception("invalid responseformat");
        }

        // The response is complete iff all of our requirements are met.
        return $hascontent && $meetsconentreq;
    }

    // für question_with_responses
    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed. This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *      as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     * @return bool whether the two sets of responses are the same - that is
     *      whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse) {
        switch ($this->responseformat) {
            case qtype_proforma::RESPONSE_EDITOR:
                if (array_key_exists('answer', $prevresponse) && $prevresponse['answer'] !== $this->responsetemplate) {
                    $value1 = (string) $prevresponse['answer'];
                } else {
                    $value1 = '';
                }
                if (array_key_exists('answer', $newresponse) && $newresponse['answer'] !== $this->responsetemplate) {
                    $value2 = (string) $newresponse['answer'];
                } else {
                    $value2 = '';
                }
                return $value1 === $value2;
                break;
            case qtype_proforma::RESPONSE_FILEPICKER:
                if (!question_utils::arrays_same_at_key_missing_is_blank(
                        $prevresponse, $newresponse, 'attachments')) {
                    return false;
                }
                // todo check file content
                return true;
                break;
            case qtype_proforma::RESPONSE_VERSION_CONTROL:
                // we cannot decide if the student's response has changed
                // since it is located somewhere else
                return false;
            default:
                throw new coding_exception('invalid response format "'. $this->responseformat . '"');

        }
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and get_max_fraction(), and the corresponding {@link question_state}
     * right, partial or wrong.
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return array (float, integer) the fraction, and the state.
     */
    public function grade_response(array $response) {

        if (!$this->is_complete_response($response)) {
            throw new coding_exception('complete response expected');
        }

        // create grader
        $grader = $this->get_grader();

        if ($this->responseformat == qtype_proforma::RESPONSE_VERSION_CONTROL) {
            $uri = str_replace('{input}', $response['answer'], $this->vcsuritemplate);
            list($graderoutput, $httpcode) = $grader->send_external_submission_to_grader($uri, $this);
        } else {
            // quite complex determination of grading function
            // (we might simply use the response format)
            $hasinlinetext = array_key_exists('answer', $response) && ($response['answer'] !== '');
            $hasattachments = array_key_exists('attachments', $response)
                    && $response['attachments'] instanceof question_response_files;

            // Determine the number of attachments present.
            if ($hasattachments) {
                $attachcount = count($response['attachments']->get_files());
            } else {
                $attachcount = 0;
            }

            $graderoutput = "";
            $code = "";
            if ($hasinlinetext) {
                $code = $response['answer'];
                if (!is_string($code)) {
                    if (is_a($code, 'question_file_loader')) {
                        $newcode = $code->__toString();
                        $code = $newcode;
                    } else {
                        throw new coding_exception('invalid datatype for grade_response');
                    }
                }
            }

            if ($attachcount > 0) {
                $files = $response['attachments']->get_files();
                if (!$files) {
                    throw new coding_exception("no files attached");
                }

                // get first file
                $file = array_values($files)[0];
                if (!$file instanceof stored_file) {
                    throw new coding_exception("wrong class");
                }

                list($graderoutput, $httpcode) = $grader->send_files_to_grader(
                        $files,
                        $this);
            } else if (!empty($code)) {
                list($graderoutput, $httpcode) = $grader->send_code_to_grader(
                        $code,
                        $this);
            } else {
                throw new coding_exception('no attachments and no code available');
            }
        }

        list($state, $fraction, $error, $feedback, $feedbackformat) =
                $grader->extract_grade($graderoutput, $httpcode, $this);

        return array($fraction, $state,
            array ('_feedback' => $feedback, '_errormsg' => $error, '_feedbackformat' => $feedbackformat));
    }



    /**
     * Checks whether the users is allow to be served a particular file.
     * @param question_attempt $qa the question attempt being displayed.
     * @param question_display_options $options the options that control display of the question.
     * @param string $component the name of the component we are serving files for.
     * @param string $filearea the name of the file area.
     * @param array $args the remaining bits of the file path.
     * @param bool $forcedownload whether the user must be forced to download the file.
     * @return bool true if the user can access this file.
     */
    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        // TODO: response_attachments gibt es nicht mehr...
        if ($component == 'question' && $filearea == 'response_attachments') {
            // Response attachments visible if the question has them.
            return $this->attachments != 0;

            /* } else if ($component == 'question' && $filearea == 'response_answer') {
            // Response attachments visible if the question has them.
            return $this->responseformat === 'editorfilepicker';
            */
        } else if ($component == 'qtype_proforma' && $filearea == qtype_proforma::FILEAREA_COMMENT) {
            return $options->manualcomment && $args[0] == $this->id;

        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);
        }
    }

    /**
     * returns the task file object.
     *
     * @return bool|null|stored_file
     */
    public function get_task_file() {
        if ($this->taskstorage != qtype_proforma::PERSISTENT_TASKFILE and
                $this->taskstorage != qtype_proforma::VOLATILE_TASKFILE) {
            return null;
        }

        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo = array(
                'component' => 'qtype_proforma',
                'filearea' => qtype_proforma::FILEAREA_TASK,
                'itemid' => $this->id,           // question id
                'contextid' => $this->contextid, // category id
                'filepath' => '/',
                'filename' => $this->taskfilename);

        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        return $file;
    }
}

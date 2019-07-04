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
 * The ProFormA Question renderer (code bases upon essay question renderer from Moodle core)
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2009 The Open University 
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 *             (The Open University for essay base)
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');


/**
 * Generates the output for proforma questions.
 */
class qtype_proforma_renderer extends qtype_renderer {

    private $collapse_id = 0;

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {

        $question = $qa->get_question();
        $files = '';

        // get default renderer
        $responseoutput = $question->get_format_renderer($this->page);

        // Answer field.
        $step = $qa->get_last_step_with_qt_var('answer');

        if (!$step->has_qt_var('answer') && empty($options->readonly)) {
            // Question has never been answered, fill it with response template.
            $step = new question_attempt_step(array('answer'=>$question->responsetemplate));
        }

        
        if (empty($options->readonly)) {
            // editor
            $answer = $responseoutput->response_area_input('answer', $qa,
                    $step, $question->responsefieldlines, $options->context);

        } else {
            // readonly for review
            // => we cannot use default renderer from question settings
            // since the teacher could have changed them in the meantime
            // => try and figure out what renderer to use
            $stepfiles = $qa->get_last_step_with_qt_var('attachments');

            $SHOW_EDITOR = 1;
            $SHOW_FILES = 2;
            $showWhat = 0;
            if ($step->get_id()) {
                $showWhat = $SHOW_EDITOR;
            }
            if ($stepfiles->get_id()) {
                $showWhat = $SHOW_FILES;
            }
            if ($step->get_id() && $stepfiles->get_id()) {
                if ($step->get_timecreated() < $stepfiles->get_timecreated()) {
                    $showWhat = $SHOW_FILES; // 'FILES (und Editor)';
                } else {
                    if ($step->get_timecreated() > $stepfiles->get_timecreated()) {
                        $showWhat = $SHOW_EDITOR; // 'EDITOR (und FILES)';
                    } else
                        $showWhat = $SHOW_EDITOR;; // BOTH!!'EDITOR und FILES';
                }
            }

            //echo 'EDITOR = ' . $submitEditor . '<br>';
            switch($showWhat) {
                case $SHOW_EDITOR:
                    $responseoutput = $this->page->get_renderer('qtype_proforma', 'format_editor');
                    break;
                case $SHOW_FILES:
                    $responseoutput = $this->page->get_renderer('qtype_proforma', 'format_filepicker');
                    $files = $this->files_read_only($qa, $options);
                    break;
            }
            $answer = $responseoutput->response_area_read_only('answer', $qa,
                    $step, $question->responsefieldlines, $options->context);

        }

        if ($responseoutput->can_have_attachments() && $question->attachments) {
            if (empty($options->readonly)) {
                $files = $this->files_input($qa, $question, $options);

            } else {
                $files = $this->files_read_only($qa, $options);
            }
        }
        $downloadtext = $this->question_downloads($question, $qa);

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa) . $downloadtext,
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => 'answer'));
        $result .= html_writer::tag('div', $files, array('class' => 'attachments'));
        $result .= html_writer::end_tag('div');

        return $result;
    }


    private function get_download_uri($question, $filearea, $filename) {
        if (empty($filename))
            return '';
        $url = moodle_url::make_pluginfile_url($question->contextid, 'qtype_proforma',
                $filearea, $question->id, '/', $filename);
        return '<a href="' . $url->out().'">'. $filename .'</a> ';
    }

    protected function question_downloads($question, $qa) {
        $result = '';

        // Default Template
        //if (!empty($question->responsetemplate)) {
        //    $result = $result. $this->get_download_uri($question, qtype_proforma::FILEAREA_TEMPLATE,
        //                    $question->responsefilename); // Model Solution instead of template
        //}

        foreach (qtype_proforma::fileareas() as $filearea => $value) { // WITHOUT model solution!!!
            $property = $value['questionlist'];
            foreach (explode(',',$question->$property) as $download) {
                $result = $result. $this->get_download_uri($question, $filearea, $download);
            }
        }

        // downloads
        //foreach (explode(',',$question->downloads) as $download) {
        //    $result = $result.'<a href="@@PLUGINFILE@@/'.$download.'">'. $download .'</a> ';
        //}

        // for debugging:
        // $result = $result.'<a href="@@PLUGINFILE@@/'.$question->taskfilename.'">'. $question->taskfilename .'</a> ';

        // evaluate links
        //$result = $question->format_text($result, FORMAT_HTML, $qa, 'question', 'questiontext', $question->id);
        if (!empty($result)) {
            return html_writer::tag('p', html_writer::tag('div',
                    get_string('attachments', 'qtype_proforma') . $result,
                    array('class' => 'downloads')));
        }
        return $result;
    }

    /**
     * Displays the input control for when the student should upload a single file.
     * @param question_attempt $qa the question attempt to display.
     * @param int $numallowed the maximum number of attachments allowed. -1 = unlimited.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_input(question_attempt $qa, qtype_proforma_question $question,
            question_display_options $options) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $question->attachments;
        //$pickeroptions->areamaxbytes = $question->maxbytes;
        $pickeroptions->maxbytes = $question->maxbytes;
        //$pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
        //        'attachments', $options->context->id);
        $pickeroptions->context = $options->context;
        $pickeroptions->accepted_types = $question->filetypes;
        $pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;

        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'attachments', $options->context->id);

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');
        return $filesrenderer->render($fm). html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $pickeroptions->itemid));
    }

    /**
     * Displays any attached files when the question is in read-only mode.
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    public function files_read_only(question_attempt $qa, question_display_options $options) {
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $output = array();

        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                    $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                    'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    /**
     * overriden funstion shows extra feedback for error messages even if
     * no feedback is shown (i.e. $options->feedback === false)
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
    public function feedback(question_attempt $qa, question_display_options $options) {
        $output = ''; // 'feedback: ';

        // always show error message (if any) even if no specific feedback shall be reported!
        $question = $qa->get_question();
        $error = $qa->get_last_qt_var('_errormsg');
        $format = $qa->get_last_qt_var('_feedbackformat');

        if (!empty($error)) {
            $output .= ''; // 'Error occured: ';
            switch ($format) {
                case qtype_proforma_grader::FEEDBACK_FORMAT_INVALID:
                    // $output .= $this->notification($error, 'error');
                    // break;
                case qtype_proforma_grader::FEEDBACK_FORMAT_ERROR:
                case qtype_proforma_grader::FEEDBACK_FORMAT_NONE:
                case qtype_proforma_grader::FEEDBACK_FORMAT_HTTP_ERROR:
                default:
                    $output .= $this->notification('<b>INTERNAL ERROR: </b><p>'. $error.'</p>', 'error');
                    if ($this->is_teacher()) {
                        $options->feedback = true;
                    } else {
                        // the student shall not see any error text!
                        $options->feedback = false;
                    }
                    break;
            }
        } else {
            // $output .= 'No error occured: ';
            if (!empty($format) && $this->is_teacher()) {
                // force feedback to true for teachers so that they can see the actual feedback
                // (deferred feedback still needs no feedback for students)
                $options->feedback = true;
            }

        }

        $output .= parent::feedback($qa, $options);

        return $output;
    }

    /**
     * Generate the specific feedback. This is feedback that varies according to
     * the response the student gave.
     * Function is used to show the grader feedback.
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    public function specific_feedback(question_attempt $qa) {
        global $DB, $CFG, $PAGE;

        $result = ''; // 'specific feedback: ';
        $question = $qa->get_question ();

        list($feedback, $errormsg, $feedbackformat) = $this->get_feedback_for_last_answer($qa);

        // TODO: bei den anderen Fragetypen wird bei einer falschen Antwort
        // die Antwort entsprechend farbig (grün/rot) hinterlegt. (nur eine Idee)
        // das sieht man nicht bei jedem Theme!

        switch ($feedbackformat) {
            case qtype_proforma_grader::FEEDBACK_FORMAT_ERROR: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_INVALID: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_NONE: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_HTTP_ERROR: // no feedback
                if (!empty($feedback) && $this->is_teacher()) {
                    return  html_writer::tag('xmp', $feedback, array('class' => 'proforma_testlog'));
                }
                else {
                    return $result;
                }

            case qtype_proforma_grader::FEEDBACK_FORMAT_LC:
                if (empty($feedback))
                    return $result . '<no feedback, maybe internal error>';
                return $result . $this->convert_LC_message_to_html($feedback, $qa);
            case qtype_proforma_grader::FEEDBACK_FORMAT_PROFORMA2:
                if (empty($feedback))
                    return $result . '<no feedback, maybe internal error>';
                return $result . $this->convert_proforma2_message_to_html($feedback, $errormsg, $qa);
            default:
                return $result . "INTERNAL ERROR: unsupported feedback format: " . $feedbackformat;
        }
    }

    /**
     * gets feedback for last answer. This function prevents getting
     * feedback for previous answers.
     * @param question_attempt $qa
     * @return array
     * @throws coding_exception
     */
    private function get_feedback_for_last_answer(question_attempt $qa) {

        $feedbackstep = null;

        foreach ($qa->get_reverse_step_iterator() as $step) {
            $answerresponse = $step->get_qt_data();
            if (array_key_exists ( 'answer' , $answerresponse ))
            {
                // last answer found!
                // check if feedback for last answer is present
                if (!array_key_exists ( '_feedback' , $answerresponse )) {
                    // no feedback yet
                    return array("",'', qtype_proforma_grader::FEEDBACK_FORMAT_NONE);
                }
            }
            // feedback found (answer may be in current or previous steps)
            if (array_key_exists ( '_feedback' , $answerresponse )) {
                if (!array_key_exists ( '_feedbackformat' , $answerresponse ))
                    throw new coding_exception("feedback format is missing");
                return array($answerresponse['_feedback'], $answerresponse['_errormsg'], $answerresponse['_feedbackformat']);
            }
        }

        return array("", "", qtype_proforma_grader::FEEDBACK_FORMAT_NONE);
    }


    public function convert_proforma2_message_to_html($message, $errormsg, question_attempt $qa) {
        $result = '';
        //if (strlen($errormsg) > 0) {
        //    $result = '<p>' . $errormsg . '</p>';
        //    // return  html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
        //}

        $question = $qa->get_question();
        $gh = new SimpleXMLElement($question->gradinghints);
        $gradingtests = $gh->root;

        $totalweight = 0;
        foreach($gradingtests->{'test-ref'} as $test){
            $totalweight += floatval((string)$test['weight']);
        }

        $response = $message;
        try {
            $response = new SimpleXMLElement($message, LIBXML_PARSEHUGE);
            if (!isset($response->{'separate-test-feedback'})) {
                return $result .'UNSUPPORTED FEEDBACK FORMAT: ' . html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
            }
        } catch (Exception $e) {
            return $result .'UNSUPPORTED FEEDBACK FORMAT: ' . html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
        }

        // Work out visual feedback for answer correctness.
        $truefeedbackimg = $this->feedback_image((int) 1);
        $falsefeedbackimg = $this->feedback_image((int) 0);
        $partfeedbackimg = $this->feedback_image(0.5);


        // TODO: find better way to create a unique identifier
        // (background: often more than one question is displayed per page. In this case
        // the collapsible regions do not waork if the identifier is not unique)
        //$qa_id = (empty($qa->get_database_id())?'x':$qa->get_database_id()) . '-' .
        //        (empty($qa->get_usage_id())?'y':$qa->get_usage_id());

        //$xpath = new DOMXPath($xmldoc);
        // todo: check namespace!
        //$xpath->registerNamespace('dns','urn:proforma:v2.0');

        foreach($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'student-feedback'} as $feedback)
        {
            $result .= html_writer::tag('p', $this->print_proforma_single_feedback($feedback, false,
                    false, false, false, true));
        }
        if ($this->is_teacher()) {
            foreach($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'teacher-feedback'} as $feedback)
            {
                $result .= html_writer::tag('p', $this->print_proforma_single_feedback($feedback, true,
                        false, false, false, true));
            }
        }

        $allcorrect = true;
        $contains_internal_error = false;
        foreach ($response->{'separate-test-feedback'}->{'tests-response'}->{'test-response'} as $test) {
            if (count($test->{'test-result'}) > 0) {
                // handle test with score
                $testresult = $test->{'test-result'}->result;
                $internalError = ((string) $testresult['is-internal-error'] === 'true');
                if ($internalError)
                    $contains_internal_error = true;
                $score = floatval((string) $testresult->score);

                $this->render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight,
                        $score, $internalError, $result, $allcorrect);

                $this->render_proforma_test_with_score($test, $result);
            } else {
                // handle tests with subtest results
                list($score, $internalError)  = qtype_proforma_grader_2::calc_score_for_test($test);
                if ($internalError)
                    $contains_internal_error = true;
                $this->render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight,
                        $score, $internalError, $result, $allcorrect);

                $this->render_proforma_test_with_subtests($test, $result, $qa);
            }
            $result .= print_collapsible_region_end(true);
        }

        if (!$result)
            return html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
        else {
            if ($allcorrect) {
                $result .= '<p></p>' . html_writer::tag('p', get_string('gradepassed', 'qtype_proforma'));
            } else {
                if ($contains_internal_error)
                    $result .= '<p></p>' .html_writer::tag('p', get_string('gradeinternalerror', 'qtype_proforma'));
                else
                    $result .= '<p></p>' .html_writer::tag('p', get_string('gradefailed', 'qtype_proforma'));
            }
        }

        return  $result;
        //. PHP_EOL . 'ORIGINAL: ' . html_writer::tag('xmp', $result, array('class' => 'proforma_testlog'));
        //return 'Grader output: ' . $message;
        //return $result; // $message;
    }

    private function is_teacher() {
        global $COURSE, $USER;
        $context = context_course::instance($COURSE->id);
        return has_capability('moodle/question:editmine', $context, $USER->id);
    }

    private function print_proforma_single_feedback($feedback, $teacher = false, $subtest = false,
            $printpassedinfo = false, $passed = false, $general = false) {

        if ($teacher && !$this->is_teacher()) {
            return '';
        }

        $level = (string) $feedback['level'];
        $title = (string) $feedback->title;
        $content = (string) $feedback->content;
        //$content_xml = $feedback->content->asXML();
        $format = (string) $feedback->content['format'];
        $result = '';

        if ($general) {
            $csstitle = array();
            $csscontent = array('class' => 'proforma_testlog');

        } else if ($subtest) {
            $csstitle = array('class' => 'proforma_subtest_title');
            $csscontent = array('class' => 'proforma_subtest_testlog');
            if ($printpassedinfo) {
                $truefeedbackimg = $this->feedback_image((int) 1);
                $falsefeedbackimg = $this->feedback_image((int) 0);
                //$cssicon = array('class' => 'proforma_subtest_title', 'style' => 'font-size: 10%; background-size:10px');
                //$result .= html_writer::tag('div', ($passed?$truefeedbackimg:$falsefeedbackimg), $cssicon);
                $title = ($passed ? $truefeedbackimg : $falsefeedbackimg) . $title;
            } else {
                // adjust left space
                $csstitle = array('class' => 'proforma_subtest_title_2');
            }
        } else {
            $csstitle = array('class' => 'proforma_testlog_title');
            $csscontent = array('class' => 'proforma_testlog');
        }

        //$result .= html_writer::start_tag('div');
        switch ($level) {
            case 'error':
                //$result .= html_writer::tag('b', $level);
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'debug':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'warn':
            case 'info':
            default:
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
        }

        if (strlen($content) > 0) {
            switch ($format) {
                case 'plaintext':
                    //$result .= html_writer::tag('div',
                    //        html_writer::tag('pre', $content_xml, $csscontent));
                    $result .= html_writer::tag('pre', htmlspecialchars($content), $csscontent);
                    break;
                //$result .= html_writer::tag('div', $content_xml, $csscontent);
                case 'html':
                    $result .= html_writer::tag('pre', $content, $csscontent);
                    break;
                default:
                    debugging('missing or invalid format for feedback (student/teacher):' . $format);
                    break;
            }
        }
        //$result .= html_writer::end_tag('div');
        return $result;
    }


    private function create_collapsible_region_id(question_attempt $qa) {
        $qa_id = (empty($qa->get_database_id())?'x':$qa->get_database_id()) . '-' .
                (empty($qa->get_usage_id())?'y':$qa->get_usage_id());
        /*        if (empty($qa_id))
                    $qa_id = $qa->get_usage_id();
                if (empty($qa_id))
                    throw new Exception("no identifier for question available");
        */
        $this->collapse_id++;
        return 'm-id-test-proforma-' . $qa_id . '-' . $this->collapse_id;

    }

    private function print_proforma_subtest_result($testresult, $qa) {
        $passed = (string)$testresult->result->score === '1.0';
        $result = '';
        foreach ($testresult->{'feedback-list'} as $feedbacklist) {
            $count = 0;
            foreach ($feedbacklist->{'student-feedback'} as $feedback) {
                $result .= $this->print_proforma_single_feedback($feedback, false, true,
                    $count === 0, $passed);
                $count++;
            }
            // print further teacher feedback if any
            if ($this->is_teacher() && count($feedbacklist->{'teacher-feedback'})) {
                //$result .= print_collapsible_region_start('', $this->create_collapsible_region_id($qa), '', '', true, true);
                foreach ($feedbacklist->{'teacher-feedback'} as $feedback) {
                    $result .= $this->print_proforma_single_feedback($feedback, true, true);
                }
                //$result .= print_collapsible_region_end(true);
            }
        }

        return $result;
    }


    public function convert_LC_message_to_html($message, question_attempt $qa) {
        $xmldoc = new DOMDocument();
        if (!$xmldoc->loadXML($message, LIBXML_NOERROR )) {
            return 'RAW FEEDBACK' . $message; // 'INTERNAL ERROR: $taskresult is not XML';
        }


        // Work out visual feedback for answer correctness.
        //$trueclass = ' ' . $this->feedback_class((int) 1);
        $truefeedbackimg = $this->feedback_image((int) 1);
        //$falseclass = ' ' . $this->feedback_class((int) 0);
        $falsefeedbackimg = $this->feedback_image((int) 0);


        $taskresult = $xmldoc->getElementsByTagName('taskresult')[0];
        $taskgrade = $taskresult->getAttribute('grade');
        $tasktitle = $taskresult->getElementsByTagName('tasktitle')[0]->nodeValue;

        $output = '';
        switch ($taskgrade) {
            case 'failed': $output .= get_string('gradefailed', 'qtype_proforma'); break;
            case 'passed': $output .= get_string('gradepassed', 'qtype_proforma'); break;
            default: $output .= get_string('gradepartialpassed', 'qtype_proforma'); break;
        }
        $output .= html_writer::empty_tag('p');
        // $output = 'Grader Output for Question ';
        // $output .= html_writer::tag('span', $tasktitle, array('class' => 'tasktitle'));

        $testresults = $taskresult->getElementsByTagName('testresult');


        // TODO: find better way to create a unique identifier
        // (background: often more than one question is displayed per page. In this case
        // the collapsible regions do not waork if the identifier is not unique)
        //$qa_id = (empty($qa->get_database_id())?'x':$qa->get_database_id()) . '-' .
        //    (empty($qa->get_usage_id())?'y':$qa->get_usage_id());
/*        if (empty($qa_id))
            $qa_id = $qa->get_usage_id();
        if (empty($qa_id))
            throw new Exception("no identifier for question available");
*/
        foreach ($testresults as $testresult) {
            $this->collapse_id++;
            $testname = $testresult->getElementsByTagName('testname')[0]->nodeValue;
            $testlog = $testresult->getElementsByTagName('testlog')[0]->nodeValue;
            $testgrade = $testresult->getAttribute('grade');
            // create unique identifier for each region
            // since there can be multiple regions per page!
            $id = $this->create_collapsible_region_id($qa);
            //$id = 'm-id-test-proforma-' . $qa_id . '-' . $this->collapse_id;

            switch ($testgrade) {
                case 'failed':
                    $output .= print_collapsible_region_start('', $id, $testname  . ' ' . $falsefeedbackimg,
                        '', true, true);
                    break;
                case 'passed':
                    $output .= print_collapsible_region_start('', $id, $testname . ' ' .$truefeedbackimg,
                        '', true, true);
                    break;
                default:
                    $output .= print_collapsible_region_start('', $id, $testname . ' ' .' Result???',
                        '', true, true);
                    break;
            }
            $output .= html_writer::tag('div', $testlog, array('class' => 'proforma_testlog'));
            $output .= print_collapsible_region_end(true);
        }

        return $output;
    }

    public function manual_comment(question_attempt $qa, question_display_options $options) {
        if ($options->manualcomment != question_display_options::EDITABLE) {
            return '';
        }

        $question = $qa->get_question();
        return html_writer::nonempty_tag('div', $question->format_text(
                $question->comment, $question->comment, $qa, 'qtype_proforma',
                'comment', $question->id), array('class' => 'comment'));
    }

    /**
     * Gereate an automatic description of the correct response to this question.
     * If it is not possible, this method
     * should just return an empty string.
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    protected function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        if (empty($question->modelsolfiles)) // $question->modelsolution))
            return '';

        // todo: find better way for id...
        $qa_id = 'm-modelsolution-'.(empty($qa->get_database_id())?'x':$qa->get_database_id()) . '-' .
            (empty($qa->get_usage_id())?'y':$qa->get_usage_id());

        $output = print_collapsible_region_start('', $qa_id,
            get_string('modelsolution', 'qtype_proforma'),
            '', true, true);

        // read model solution from file(s)
        foreach (explode(',',$question->modelsolfiles) as $ms) {
            // $output .= $this->get_download_uri($question, qtype_proforma::FILEAREA_MODELSOL, $ms);

            // Note! The model solution files are made inline in order to
            // avoid offering a download link for them.
            // Access rules for Downloads must ensure that the student cannot see the
            // model solution before he or she should see it!!! This can be difficult.

            $output .= html_writer::tag('div', 'File '. $ms,
                    array('class' => 'proforma_testlog_title'));

            $output .= html_writer::tag('div', '<pre>'.
                    qtype_proforma::read_file_content($question->contextid,
                            qtype_proforma::FILEAREA_MODELSOL, $ms, $question->id) .
                    '</pre>', array('class' => 'proforma_testlog'));
        }

        // $output .= html_writer::tag('div', '<pre>'. $question->modelsolution .'</pre>', array('class' => 'proforma_testlog'));

        $output .= print_collapsible_region_end(true);


        return $output;
    }

    private function render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight, $score, $internalError, &$result, &$allcorrect) {
        $truefeedbackimg = $this->feedback_image((int) 1);
        $falsefeedbackimg = $this->feedback_image((int) 0);

        $id = (string) $test['id'];
        $ghtest = $gradingtests->xpath("//test-ref[@ref='" . $id . "']");
        if (count($ghtest) == 0) {
            throw new moodle_exception('cannot find appropriate grading hints for test "' . $id . '""');
        }

        $ghtest = $ghtest[0];
        $testtitle = $ghtest->title;
        if (!isset($testtitle)) {
            $testtitle = 'Test ' . $id;
        }

        // create unique identifier for each region
        // since there can be multiple regions per page!
        $collid = $this->create_collapsible_region_id($qa);

        //$this->collapse_id++;
        //$collid = 'm-id-test-proforma-' . $qa_id . '-' . $this->collapse_id;
        $visiblescore = '';
        if ($question->aggregationstrategy == qtype_proforma::WEIGHTED_SUM) {
            $weight = floatval((string) $ghtest['weight']) / $totalweight;
            $weightscore = number_format($score * $weight / 1 * 100, 0);
            $visiblescore = ' (' . $weightscore . '/' . number_format($weight * 100, 0) . ' %)';
        }

        if ($score === 1.0) {
            $result .= print_collapsible_region_start('', $collid,
                    //                        $testtitle . ' ' . $truefeedbackimg . $score . ' Weight=' . $weight . ' => ' . $weightscore,
                    $truefeedbackimg . ' '. $testtitle . $visiblescore,
                    '', true, true);
        } else {
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    //                        $testtitle . ' ' . $falsefeedbackimg . $score . ' Weight=' . $weight . ' => ' . $weightscore,
                    $falsefeedbackimg . ' '. $testtitle . $visiblescore,
                    '', true, true);
        }

        if (isset($ghtest->description) and strlen($ghtest->description) > 0) {
            $result .= html_writer::tag('span', $ghtest->description, array('class' => 'proforma_testlog_description'));
        }

        if ($internalError) {
            //$csscontent = array('class' => 'proforma_testlog');
            $result .= html_writer::tag('p', '<b>INTERNAL ERROR IN GRADER!!</b>');
        }
    }


    /**
     * @param $test
     * @param $result
     * @throws moodle_exception
     */
    private function render_proforma_test_with_score($test, &$result) {

        foreach ($test->{'test-result'}->{'feedback-list'}->{'student-feedback'} as $feedback) {
            $result .= $this->print_proforma_single_feedback($feedback);
        }
        if ($this->is_teacher()) {
            foreach ($test->{'test-result'}->{'feedback-list'}->{'teacher-feedback'} as $feedback) {
                $result .= $this->print_proforma_single_feedback($feedback, true);
            }
        }
    }

    private function render_proforma_test_with_subtests($test, &$result, $qa) {

        foreach ($test->{'subtests-response'}->{'subtest-response'} as $response) {
            $result .= $this->print_proforma_subtest_result($response->{'test-result'}, $qa);
        }
    }
}



/**
 * A renderer for questions where the student needs to upload files
 */
class qtype_proforma_format_filepicker_renderer extends plugin_renderer_base {

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return '';
    }

    public function response_area_input($name, $qa, $step, $lines, $context) {
        return '';
    }

    public function can_have_attachments() {
        return true;
    }

    protected function class_name() {
        return 'qtype_proforma_filepicker';
    }

}


/**
 * A renderer for questions where the student enters text into editor
 */
class qtype_proforma_format_editor_renderer extends plugin_renderer_base {

    const READONLY = 1;
    const WRITABLE = 0;

    protected $textarea_id = null;

    public function response_area_input($name, $qa, $step, $lines, $context) {
        global $PAGE;
        global $CFG;

        $question = $qa->get_question();
        $mode = $question->programminglanguage;

        // Prevent JS caching in Debug-Mode
        // $CFG->cachejs = false; // set in config.php
        $input = $this->set_response_area_input($name, $qa, $step, $lines, $context);
        // convert textarea to codemirror editor
        //self::load_codemirror_modes();
        if (get_config('qtype_proforma', 'usecodemirror')) {
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                array($this->textarea_id, self::WRITABLE, $mode));
        }
        return $input;
    }

    private function set_response_area_input($name, $qa, $step, $lines, $context) {
        $inputname = $qa->get_qt_field_name($name);
        $id = $this->set_textarea_id($qa);

        return $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,
                'id' => $id)) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
    }

    protected function set_textarea_id($qa) {
        $responsefieldname = $qa->get_qt_field_name('answer');
        $this->textarea_id = 'id_' . $responsefieldname;
        return $this->textarea_id;
    }

    /**
     * @return string the HTML for the textarea.
     */
    protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    protected function class_name() {
        return 'qtype_proforma_editor';
    }

    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        global $PAGE;
        global $CFG;

        $question = $qa->get_question();
        $mode = $question->programminglanguage;


        // Prevent JS caching in Debug-Mode
        // $CFG->cachejs = false; // set in config.php
        $input = $this->set_response_area_read_only($name, $qa, $step, $lines, $context);
        // convert textarea to codemirror editor
        //self::load_codemirror_modes();
        if (get_config('qtype_proforma', 'usecodemirror')) {
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                array($this->textarea_id, self::READONLY, $mode));
        }

        return $input;
    }

    private function set_response_area_read_only($name, $qa, $step, $lines, $context) {
        $id = $this->set_textarea_id($qa);

        return $this->textarea($step->get_qt_var($name), $lines, array('readonly' => 'readonly',
            'id' => $id));
    }

    public function can_have_attachments() {
        return false;
    }
}




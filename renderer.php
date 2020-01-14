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
 * The ProFormA Question renderer (code bases upon essay question renderer from Moodle core)
 *
 * @package    qtype_proforma
 * @copyright  2009 The Open University
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 *             (The Open University for essay base)
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

// load JQuery for CodeMirror resizing. This cannot be done
// inside a function.

global $PAGE;
if (!$PAGE->requires->is_head_done()) {
    // this is_head_done check avoids a debugging error message telling us
    // that we cannot add jquery after starting page output.

    // But: if the jquery calls are missing then Codemirror resizing does not work.
    // This happens for the preview window for a specific step in the grades history :-(
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');
}


/**
 * Generates the output for proforma questions.
 */
class qtype_proforma_renderer extends qtype_renderer {

    /**
     * For creating a collapsible a unique identifier is needed.
     * Therefore a counter is used that is incremented
     * for each collapsible instance (kind of sequence counter).
     *
     * @var int
     */
    private $collapseid = 0;

    /**
     * overridden function for creating the output
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string outout as html fragment
     */
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
            $step = new question_attempt_step(array('answer' => $question->responsetemplate));
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

            $showeditor = 1; // constant
            $showfiles = 2; // constant
            $showwhat = 0;
            if ($step->get_id()) {
                $showwhat = $showeditor;
            }
            if ($stepfiles->get_id()) {
                $showwhat = $showfiles;
            }
            if ($step->get_id() && $stepfiles->get_id()) {
                if ($step->get_timecreated() < $stepfiles->get_timecreated()) {
                    $showwhat = $showfiles; // 'FILES (and Editor)';
                } else {
                    if ($step->get_timecreated() > $stepfiles->get_timecreated()) {
                        $showwhat = $showeditor; // 'EDITOR (and FILES)';
                    } else {
                        $showwhat = $showeditor; // BOTH!!'EDITOR and FILES';
                    };
                }
            }

            switch ($showwhat) {
                case $showeditor:
                    $responseoutput = $this->page->get_renderer('qtype_proforma', 'format_editor');
                    break;
                case $showfiles:
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

    /**
     * returns the download URI for a given file (identfied by filename and filearea)
     * @param $question
     * @param $filearea
     * @param $filename
     * @return string
     */
    private function get_download_uri($question, $filearea, $filename) {
        if (empty($filename)) {
            return '';
        }
        $url = moodle_url::make_pluginfile_url($question->contextid, 'qtype_proforma',
                $filearea, $question->id, '/', $filename);
        return '<a href="' . $url->out() . '">' . $filename . '</a> ';
    }

    /**
     * returns the html fragment for download links
     * @param $question
     * @param $qa
     * @return string
     */
    protected function question_downloads($question, $qa) {
        $result = '';

        foreach (qtype_proforma::fileareas_for_studentfiles() as $filearea => $value) { // WITHOUT model solution!!!
            $property = $value['questionlist'];
            foreach (explode(',', $question->$property) as $download) {
                $result = $result . $this->get_download_uri($question, $filearea, $download);
            }
        }

        // prefix
        if (!empty($result)) {
            return html_writer::tag('p', html_writer::tag('div',
                    get_string('attachments', 'qtype_proforma') . ' '. $result,
                    array('class' => 'downloads')));
        }
        return $result;
    }

    /**
     * Displays the input control for when the student should upload a single file.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param int $numallowed the maximum number of attachments allowed. -1 = unlimited.
     * @param question_display_options $options controls what should and should
     *      not be displayed. Used to get the context.
     */
    protected function files_input(question_attempt $qa, qtype_proforma_question $question,
            question_display_options $options) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $question->attachments;
        // $pickeroptions->areamaxbytes = $question->maxbytes;
        $pickeroptions->maxbytes = $question->maxbytes;
        // $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        $pickeroptions->context = $options->context;
        $pickeroptions->accepted_types = $question->filetypes;
        $pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;

        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
                'attachments', $options->context->id);

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');
        return $filesrenderer->render($fm) . html_writer::empty_tag(
                        'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                        'value' => $pickeroptions->itemid));
    }

    /**
     * Displays any attached files when the question is in read-only mode.
     *
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
     *
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
                    $output .= $this->notification('<b>INTERNAL ERROR: </b><p>' . $error . '</p>', 'error');
                    if (qtype_proforma\lib\is_teacher()) {
                        $options->feedback = true;
                    } else {
                        // the student shall not see any error text!
                        $options->feedback = false;
                    }
                    break;
            }
        } else {
            // $output .= 'No error occured: ';
            if (!empty($format) && qtype_proforma\lib\is_teacher()) {
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
     *
     * @param question_attempt $qa the question attempt to display.
     * @return string HTML fragment.
     */
    public function specific_feedback(question_attempt $qa) {
        $result = ''; // 'specific feedback: ';

        list($feedback, $errormsg, $feedbackformat) = $this->get_feedback_for_last_answer($qa);
        switch ($feedbackformat) {
            case qtype_proforma_grader::FEEDBACK_FORMAT_ERROR: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_INVALID: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_NONE: // no feedback
            case qtype_proforma_grader::FEEDBACK_FORMAT_HTTP_ERROR: // no feedback
                if (!empty($feedback) && qtype_proforma\lib\is_teacher()) {
                    return html_writer::tag('xmp', $feedback, array('class' => 'proforma_testlog'));
                } else {
                    return $result;
                }
            case qtype_proforma_grader::FEEDBACK_FORMAT_PROFORMA2:
                if (empty($feedback)) {
                    return $result . '<no feedback, maybe internal error>';
                }
                return $result . $this->render_proforma2_message($feedback, $errormsg, $qa);
            default:
                return $result . "INTERNAL ERROR: unsupported feedback format: " . $feedbackformat;
        }
    }

    /**
     * gets feedback for last answer. This function prevents getting
     * feedback for previous answers.
     *
     * @param question_attempt $qa
     * @return array
     * @throws coding_exception
     */
    private function get_feedback_for_last_answer(question_attempt $qa) {

        $feedbackstep = null;

        foreach ($qa->get_reverse_step_iterator() as $step) {
            $answerresponse = $step->get_qt_data();
            if (array_key_exists('answer', $answerresponse)) {
                // last answer found!
                // check if feedback for last answer is present
                if (!array_key_exists('_feedback', $answerresponse)) {
                    // no feedback yet
                    return array("", '', qtype_proforma_grader::FEEDBACK_FORMAT_NONE);
                }
            }
            // feedback found (answer may be in current or previous steps)
            if (array_key_exists('_feedback', $answerresponse)) {
                if (!array_key_exists('_feedbackformat', $answerresponse)) {
                    throw new coding_exception("feedback format is missing");
                }
                return array($answerresponse['_feedback'], $answerresponse['_errormsg'], $answerresponse['_feedbackformat']);
            }
        }

        return array("", "", qtype_proforma_grader::FEEDBACK_FORMAT_NONE);
    }

    /**
     * converts the ProFormA response to html
     *
     * @param $message
     * @param $errormsg
     * @param question_attempt $qa
     * @return string
     */
    public function render_proforma2_message($message, $errormsg, question_attempt $qa) {
        $result = '';
        $question = $qa->get_question();
        $gh = new SimpleXMLElement($question->gradinghints);
        $gradingtests = $gh->root;

        $totalweight = 0;
        foreach ($gradingtests->{'test-ref'} as $test) {
            $totalweight += floatval((string) $test['weight']);
        }

        try {
            $response = new SimpleXMLElement($message, LIBXML_PARSEHUGE);
            if (!isset($response->{'separate-test-feedback'})) {
                return $result . 'UNSUPPORTED FEEDBACK FORMAT: ' .
                        html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
            }
        } catch (Exception $e) {
            return $result . 'UNSUPPORTED FEEDBACK FORMAT: ' .
                    html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
        }

        // $xpath = new DOMXPath($xmldoc);
        // todo: check namespace!
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');

        foreach ($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'student-feedback'} as $feedback) {
            $result .= html_writer::tag('p', $this->print_proforma_single_feedback($feedback, false,
                    false, false, false, true));
        }
        if (qtype_proforma\lib\is_teacher()) {
            foreach ($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'teacher-feedback'} as $feedback) {
                $result .= html_writer::tag('p', $this->print_proforma_single_feedback($feedback, true,
                        false, false, false, true));
            }
        }

        $allcorrect = true;
        $containsinternalerror = false;

        $tests = $response->{'separate-test-feedback'}->{'tests-response'};
        /*
         * we want to avoid using namespaces in the response!
        if (!$tests->registerXPathNamespace('dns', 'urn:proforma:v2.0')) {
            $containsinternalerror = true;
            $result .= '<p><b>INTERNAL ERROR</b>: unknown response namespace</p>';
        } else {
        */
        // iterate through all tests in the grading hints instead of
        // iterating through all tests in the response.
        // This guarantees that we detect missing tests in the response and
        // we can reorder the result.
        foreach ($gradingtests->{'test-ref'} as $ghtest) {
            $testid = (string)$ghtest['ref'];
            // Look for test in message:
            // Using xpath is more elagant but requires registering the default namespace.
            // $lookup = 'dns::test-response[@id="'.$testid.'"]';
            // In order to be more flexible with the namespace version we just iterate over
            // all tests in the response and seach for the appropriate one.
            $test = null;
            foreach ($tests->{'test-response'} as $resptest) {
                if ($testid == (string)$resptest['id']) {
                    $test = $resptest;
                    break;
                }
            }

            if ($test == null) {
                $containsinternalerror = true;
                $result .= '<p><b>INTERNAL ERROR</b>: Result for Test "' . $testid . '" is missing</p>';
            } else {
                list($internalerror, $result, $correct) =
                        $this->render_test($qa, $test[0], $gradingtests, $question, $totalweight, $result);
                if ($internalerror) {
                    $containsinternalerror = true;
                }
                if (!$correct) {
                    $allcorrect = false;
                }
            }
        }

        if (qtype_proforma\lib\is_admin()) {
            // infos for admins are displayed as small text
            $result .= html_writer::start_tag('small', null);
            // show grader info
            $gradertext = "Grader ???";
            try {
                $graderinfo = $response->{'response-meta-data'}->{'grader-engine'};
                $gradertext = $graderinfo['name'] . ' ' . $graderinfo['version'];
            } catch (Exception $e) {
                // ignore exception.
            }
            $result .= '<p></p>' . '[' . $gradertext . ']';

            // debugging: show raw response
            $qaid = $this->create_collapsible_region_id($qa);
            $result .= print_collapsible_region_start('', $qaid,
                    'raw response', '', true, true);
            $result .= html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
            $result .= print_collapsible_region_end(true);

            $result .= html_writer::end_tag('small');
        }

        if ($allcorrect) {
            $result .= '<p></p>' . html_writer::tag('p', get_string('gradepassed', 'qtype_proforma'));
        } else {
            if ($containsinternalerror) {
                $result .= '<p></p>' . html_writer::tag('p', get_string('gradeinternalerror', 'qtype_proforma'));
            } else {
                $result .= '<p></p>' . html_writer::tag('p', get_string('gradefailed', 'qtype_proforma'));
            }
        }


        return $result;
    }



    /**
     * creates the html fragmenbt for a single freedback element
     * @param $feedback
     * @param bool $teacher
     * @param bool $subtest
     * @param bool $printpassedinfo
     * @param bool $passed
     * @param bool $general
     * @return string
     */
    private function print_proforma_single_feedback($feedback, $teacher = false, $subtest = false,
            $printpassedinfo = false, $passed = false, $general = false) {

        if ($teacher && !qtype_proforma\lib\is_teacher()) {
            return '';
        }

        $level = (string) $feedback['level'];
        $title = (string) $feedback->title;
        $content = (string) $feedback->content;
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
                // smaller font?
                // $cssicon = array('class' => 'proforma_subtest_title', 'style' => 'font-size: 10%; background-size:10px');
                // $result .= html_writer::tag('div', ($passed?$truefeedbackimg:$falsefeedbackimg), $cssicon);
                $title = ($passed ? $truefeedbackimg : $falsefeedbackimg) . $title;
            } else {
                // adjust left space
                $csstitle = array('class' => 'proforma_subtest_title_2');
            }
        } else {
            $csstitle = array('class' => 'proforma_testlog_title');
            $csscontent = array('class' => 'proforma_testlog');
        }

        // todo: show different level
        switch ($level) {
            case 'error':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'debug':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'warn':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'info':
                // do not display 'info title' for score tests (title is set by LMS and can be changed by teacher)
                // because the title is displayed twice
                if ($subtest or $general) {
                    $result .= html_writer::tag('div', $title, $csstitle);
                }
                break;
            default:
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
        }

        if (strlen($content) > 0) {
            switch ($format) {
                case 'plaintext':
                    $result .= html_writer::tag('pre', htmlspecialchars($content), $csscontent);
                    break;
                case 'html':
                    $result .= html_writer::tag('pre', $content, $csscontent);
                    break;
                default:
                    debugging('missing or invalid format for feedback (student/teacher):' . $format);
                    break;
            }
        }

        return $result;
    }


    /**
     * creates a collapsible region identifier
     *
     * background: often more than one question is displayed per page. In this case
     * the collapsible regions do not work if the identifier is not unique
     *
     * @param question_attempt|null $qa
     * @return string
     */
    private function create_collapsible_region_id(question_attempt $qa = null) {
        if ($qa != null) {
            $qaid = (empty($qa->get_database_id()) ? 'x' : $qa->get_database_id()) . '-' .
                    (empty($qa->get_usage_id()) ? 'y' : $qa->get_usage_id());
        } else {
            $qaid = '0';
        }

        $this->collapseid++;
        return 'm-id-test-proforma-' . $qaid . '-' . $this->collapseid;
    }

    /**
     * creates the html fragment for a subtest result
     * @param $testresult
     * @param $qa
     * @return string
     */
    private function print_proforma_subtest_result($testresult) {
        $passed = (string) $testresult->result->score === '1.0';
        $result = '';
        foreach ($testresult->{'feedback-list'} as $feedbacklist) {
            $count = 0;
            foreach ($feedbacklist->{'student-feedback'} as $feedback) {
                $result .= $this->print_proforma_single_feedback($feedback, false, true,
                        $count === 0, $passed);
                $count++;
            }
            // print further teacher feedback if any
            if (qtype_proforma\lib\is_teacher() && count($feedbacklist->{'teacher-feedback'})) {
                foreach ($feedbacklist->{'teacher-feedback'} as $feedback) {
                    $result .= $this->print_proforma_single_feedback($feedback, true, true);
                }

            }
        }

        return $result;
    }

    /**
     * returns the comment text
     *
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
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
        if (empty($question->modelsolfiles)) {
            return '';
        }

        $qaid = $this->create_collapsible_region_id($qa);

        $output = print_collapsible_region_start('', $qaid,
                get_string('modelsolution', 'qtype_proforma'),
                '', true, true);

        // read model solution from file(s)
        foreach (explode(',', $question->modelsolfiles) as $ms) {
            // $output .= $this->get_download_uri($question, qtype_proforma::FILEAREA_MODELSOL, $ms);

            // Note! The model solution files are made inline in order to
            // avoid offering a download link for them.
            // Access rules for Downloads must ensure that the student cannot see the
            // model solution before he or she should see it!!! This can be difficult.

            $output .= html_writer::tag('div',
                    get_string('msfilename', 'qtype_proforma') . ': ' .$ms,
                    array('class' => 'proforma_testlog_title'));

            $output .= html_writer::tag('div', '<xmp>' .
                    qtype_proforma::read_file_content($question->contextid,
                            qtype_proforma::FILEAREA_MODELSOL, $ms, $question->id) .
                    '</xmp>', array('class' => 'proforma_testlog'));
        }

        // $output .= html_writer::tag('div', '<pre>'. $question->modelsolution .'</pre>', array('class' => 'proforma_testlog'));

        $output .= print_collapsible_region_end(true);

        return $output;
    }

    /**
     * creates the html fragment for a test title
     * @param $test
     * @param $gradingtests
     * @param $qa
     * @param $question
     * @param $totalweight
     * @param $score
     * @param $internalerror
     * @param $result
     * @param $allcorrect
     * @throws moodle_exception
     */
    private function render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight, $score, $internalerror,
            &$result, &$allcorrect) {
        $successimg = $this->feedback_image((int) 1);
        $failimg = $this->feedback_image((int) 0);
        $partimg = $this->feedback_image(0.1);

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
        $visiblescore = '';
        if ($question->aggregationstrategy == qtype_proforma::WEIGHTED_SUM) {
            $weight = floatval((string) $ghtest['weight']) / $totalweight;
            if ($weight > 0.0) {
                // only display percentage if this test counts more than 0
                $weightscore = number_format($score * $weight / 1 * 100, 0);
                $visiblescore = ' (' . $weightscore . '/' . number_format($weight * 100, 0) . ' %)';
            }
        }

        if ($score === 1.0) {
            $result .= print_collapsible_region_start('', $collid,
                    $successimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        } else if ($score === 0.0) {
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    $failimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        } else {
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    $partimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        }

        if (isset($ghtest->description) and strlen($ghtest->description) > 0) {
            $result .= html_writer::tag('span', $ghtest->description, array('class' => 'proforma_testlog_description'));
        }

        if ($internalerror) {
            // $csscontent = array('class' => 'proforma_testlog');
            $result .= html_writer::tag('p', '<b>INTERNAL ERROR IN GRADER!!</b>');
        }
    }

    /**
     * renders a test with its score
     *
     * @param $test
     * @param $result
     * @throws moodle_exception
     */
    private function render_proforma_test_with_score($test, &$result) {

        foreach ($test->{'test-result'}->{'feedback-list'}->{'student-feedback'} as $feedback) {
            $result .= $this->print_proforma_single_feedback($feedback);
        }
        if (qtype_proforma\lib\is_teacher()) {
            foreach ($test->{'test-result'}->{'feedback-list'}->{'teacher-feedback'} as $feedback) {
                $result .= $this->print_proforma_single_feedback($feedback, true);
            }
        }
    }

    /**
     * renders a test with its subtests
     *
     * @param $test
     * @param $result
     * @param $qa
     */
    private function render_proforma_test_with_subtests($test, &$result) {

        foreach ($test->{'subtests-response'}->{'subtest-response'} as $response) {
            $result .= $this->print_proforma_subtest_result($response->{'test-result'});
        }
    }

    /**
     * @param question_attempt $qa
     * @param $test
     * @param $gradingtests
     * @param $question
     * @param $totalweight
     * @param $result
     * @return array
     */
    protected function render_test(question_attempt $qa, $test, $gradingtests, $question, $totalweight, &$result): array {
        $containsinternalerror = false;
        $allcorrect = true;
        if (count($test->{'test-result'}) > 0) {
            // handle test with score
            $testresult = $test->{'test-result'}->result;
            $internalerror = ((string) $testresult['is-internal-error'] === 'true');
            if ($internalerror) {
                $containsinternalerror = true;
            }
            $score = floatval((string) $testresult->score);

            $this->render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight,
                    $score, $internalerror, $result, $allcorrect);

            $this->render_proforma_test_with_score($test, $result);
        } else {
            // handle tests with subtest results
            list($score, $internalerror) = qtype_proforma_grader_2::calc_score_for_test($test);
            if ($internalerror) {
                $containsinternalerror = true;
            }
            $this->render_proforma_test_title($test, $gradingtests, $qa, $question, $totalweight,
                    $score, $internalerror, $result, $allcorrect);

            $this->render_proforma_test_with_subtests($test, $result);
        }
        // collapsible region is created inside render_proforma_test_title
        $result .= print_collapsible_region_end(true);
        return array($containsinternalerror, $result, $allcorrect);
    }
}

/**
 * A renderer for questions where the student needs to upload files
 */
class qtype_proforma_format_filepicker_renderer extends plugin_renderer_base {

    /**
     * returns the html fragment for the reponse area in readonly mode
     * @param $name
     * @param $qa
     * @param $step
     * @param $lines
     * @param $context
     * @return string
     */
    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        return '';
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $name
     * @param $qa
     * @param $step
     * @param $lines
     * @param $context
     * @return string
     */
    public function response_area_input($name, $qa, $step, $lines, $context) {
        return '';
    }

    /**
     * returns if the student submission can have attachments (true)
     *
     * @return bool true
     */
    public function can_have_attachments() {
        return true;
    }

    /**
     * returns the class name
     *
     * @return string
     */
    protected function class_name() {
        return 'qtype_proforma_filepicker';
    }

}

/**
 * A renderer for questions where the student enters text into editor
 */
class qtype_proforma_format_editor_renderer extends plugin_renderer_base {

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $name
     * @param $qa
     * @param $step
     * @param $lines
     * @param $context
     * @return string
     */
    public function response_area_input($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();
        $mode = $question->programminglanguage;
        $inputname = $qa->get_qt_field_name($name);
        $id = $this->get_textarea_id($qa);

        $input = $this->textarea($step->get_qt_var($name), $lines, array('name' => $inputname,
                        'id' => $id)) .
                html_writer::empty_tag('input', array('type' => 'hidden',
                        'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
        // convert textarea to codemirror editor
        qtype_proforma::as_codemirror($id, $mode, null, false, false);
        return $input;
    }

    /**
     * returns the html identfier for the textarea
     * @param $qa
     * @return string
     */
    protected function get_textarea_id($qa) {
        $responsefieldname = $qa->get_qt_field_name('answer');
        return 'id_' . $responsefieldname;
    }

    /**
     * creates a html fragment for the textarea.
     * @param $response
     * @param $lines
     * @param $attributes
     * @return string string the HTML for the textarea.
     */
    protected function textarea($response, $lines, $attributes) {
        $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;
        return html_writer::tag('textarea', s($response), $attributes);
    }

    /**
     * returns the class name
     *
     * @return string
     */
    protected function class_name() {
        return 'qtype_proforma_editor';
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $name
     * @param $qa
     * @param $step
     * @param $lines
     * @param $context
     * @return string
     */
    public function response_area_read_only($name, $qa, $step, $lines, $context) {
        $question = $qa->get_question();
        $mode = $question->programminglanguage;
        $id = $this->get_textarea_id($qa);
        $input = $this->textarea($step->get_qt_var($name), $lines, array('readonly' => 'readonly',
                        'id' => $id));

        // convert textarea to codemirror editor
        qtype_proforma::as_codemirror($id, $mode, null, true, false);
        return $input;
    }

    /**
     * returns if the student submission can have attachments (false)
     *
     * @return bool false
     */
    public function can_have_attachments() {
        return false;
    }
}




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
require_once($CFG->dirroot . '/question/type/proforma/classes/formatrenderer.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/feedback_renderer.php');



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
     * return protected member page for use in class feedback_renderer
     * @return type
     */
    public function get_page() {
        return $this->page;
    }

    /**
     * make feedback_image public because we have no friend feature in PHP
     */
    public function feedback_image($fraction, $selected = true) {
        return parent::feedback_image($fraction, $selected);
    }

    private function get_last_step_for_vcs($qa) {
        foreach ($qa->get_reverse_step_iterator() as $step) {
            if ($step->has_qt_var(VCSINPUT) or $step->has_qt_var(VCSGROUP) or $step->has_qt_var(VCSUSERNAME)) {
                return $step;
            }
        }
        return new question_attempt_step_read_only();
    }

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
        $answer = '';

        // Get default renderer depending on responsetype.
        $renderer = $question->get_format_renderer($this->page);

        // Get last input according to renderer type.
        if ($question->responseformat == qtype_proforma::RESPONSE_VERSION_CONTROL) {
            $step = $this->get_last_step_for_vcs($qa);
        } else {
            $step = $qa->get_last_step_with_qt_var(ANSWER);
            if (!$step->has_qt_var(ANSWER) && empty($options->readonly)) {
                // Question has never been answered, fill it with response template.
                $step = new question_attempt_step(array(ANSWER => $question->responsetemplate));
            }
        }

        if (empty($options->readonly)) {
            // Student view for input.
            $answer = $renderer->response_area_input($qa, $step, $options);
        } else {
            // Readonly for review
            // => we cannot use default renderer from question settings
            // since the teacher could have changed it in the meantime!!
            // => try and figure out what renderer to use.
            list($step, $renderer) = $this->determine_renderer($qa, $step, $renderer);
            $answer = $renderer->response_area_read_only($qa, $step, $options->context);
        }

        // Show file upload area resp. uploaded files.
        $files = '';
        /*
        if ($renderer->can_have_attachments() && $question->attachments) {
            if (empty($options->readonly)) {
                $files = $this->files_input($qa, $question, $options);
            } else {
                $files = $this->files_read_only($qa, $options);
            }
        }*/
        // debugging('Achtung, class bei files und anwer unterscheiden');

        // Show question test atachments.
        $downloadtext = $this->question_downloads($question);

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa) . $downloadtext,
                array('class' => 'qtext'));

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $answer, array('class' => $renderer->answerfieldname()));
        // $result .= html_writer::tag('div', $files, array('class' => ATTACHMENTS));
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
        $filename = trim($filename);
        $url = moodle_url::make_pluginfile_url($question->contextid, 'qtype_proforma',
                $filearea, $question->id, '/', $filename);
        return '<a href="' . $url->out() . '">' . $filename . '</a> ';
    }

    /**
     * returns the html fragment for download links
     * @param $question
     * @return string
     */
    protected function question_downloads($question) {
        $result = '';

        foreach (qtype_proforma::fileareas_for_studentfiles() as $filearea => $value) { // WITHOUT model solution!!!
            $property = $value['dbcolumn'];
            foreach (explode(',', $question->$property) as $download) {
                $result = $result . $this->get_download_uri($question, $filearea, $download);
            }
        }

        // Prefix.
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
        $output = '';

        // Always show error message (if any) even if no specific feedback shall be reported!
        $error = $qa->get_last_qt_var('_errormsg');
        $format = $qa->get_last_qt_var('_feedbackformat');

        if (!empty($error)) {
            $output .= '';
            switch ($format) {
                case qtype_proforma_grader::FEEDBACK_FORMAT_INVALID:
                case qtype_proforma_grader::FEEDBACK_FORMAT_ERROR:
                case qtype_proforma_grader::FEEDBACK_FORMAT_NONE:
                case qtype_proforma_grader::FEEDBACK_FORMAT_HTTP_ERROR:
                default:
                    $output .= $this->notification('<b>INTERNAL ERROR: </b><p>' . $error . '</p>', 'error');
                    if (qtype_proforma\lib\is_teacher()) {
                        $options->feedback = true;
                    } else {
                        // The student shall not see any error text!
                        $options->feedback = false;
                    }
                    break;
            }
        } else {
            if (!empty($format) && qtype_proforma\lib\is_teacher()) {
                // Force feedback to true for teachers so that they can see the actual feedback
                // (deferred feedback still needs no feedback for students).
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
        $result = '';

        list($feedback, $errormsg, $feedbackformat) = $this->get_feedback_for_last_answer($qa);
        switch ($feedbackformat) {
            case qtype_proforma_grader::FEEDBACK_FORMAT_ERROR: // No feedback.
            case qtype_proforma_grader::FEEDBACK_FORMAT_INVALID: // No feedback.
            case qtype_proforma_grader::FEEDBACK_FORMAT_NONE: // No feedback.
            case qtype_proforma_grader::FEEDBACK_FORMAT_HTTP_ERROR: // No feedback.
                if (!empty($feedback) && qtype_proforma\lib\is_teacher()) {
                    return html_writer::tag('xmp', $feedback, array('class' => 'proforma_testlog'));
                } else {
                    return $result;
                }
            case qtype_proforma_grader::FEEDBACK_FORMAT_PROFORMA2:
                if (empty($feedback)) {
                    return $result . '<no feedback, maybe internal error>';
                }

                return $result . $this->render_proforma2_message($feedback, $errormsg,
                    $qa->get_question());
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

        foreach ($qa->get_reverse_step_iterator() as $step) {
            $answerresponse = $step->get_qt_data();
            if (array_key_exists('answer', $answerresponse)) {
                // Last answer found!
                // check if feedback for last answer is present.
                if (!array_key_exists('_feedback', $answerresponse)) {
                    // No feedback yet.
                    return array("", '', qtype_proforma_grader::FEEDBACK_FORMAT_NONE);
                }
            }
            // Feedback found (answer may be in current or previous steps).
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
     * creates a collapsible region identifier
     *
     * background: often more than one question is displayed per page. In this case
     * the collapsible regions do not work if the identifier is not unique
     *
     * @return string
     */
    public function create_collapsible_region_id() {
        $rand = mt_rand();
        $this->collapseid++;
        return 'm-id-test-proforma-' . $rand . '-' . $this->collapseid;
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

        $qaid = $this->create_collapsible_region_id();

        $output = print_collapsible_region_start('', $qaid,
                get_string('modelsolution', 'qtype_proforma'),
                '', true, true);

        // Read model solution from file(s).
        foreach (explode(',', $question->modelsolfiles) as $ms) {
            // Note! The model solution files are made inline in order to
            // avoid offering a download link for them.
            // Access rules for Downloads must ensure that the student cannot see the
            // model solution before he or she should see it!!! This can be difficult.
            $output .= html_writer::tag('div',
                    get_string('msfilename', 'qtype_proforma') . ': ' .$ms,
                    array('class' => 'proforma_testlog_title'));

            $msarea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_MODELSOL);
            $output .= html_writer::tag('div', '<xmp>' .
                    $msarea->read_file_content($question->contextid, $ms, $question->id) .
                    '</xmp>', array('class' => 'proforma_testlog'));
        }

        $output .= print_collapsible_region_end(true);

        return $output;
    }

    /**
     * @param question_attempt $qa
     * @param $step
     * @return int|string
     */
    protected function determine_renderer(question_attempt $qa, $externalstep, $externalrenderer) {
        foreach ($qa->get_reverse_step_iterator() as $step) {
            if ($step->has_qt_var(VCSINPUT) or $step->has_qt_var(VCSGROUP) or $step->has_qt_var(VCSUSERNAME)) {
                return array($step, $this->page->get_renderer('qtype_proforma', 'format_versioncontrol'));
            }
            if ($step->has_qt_var(ATTACHMENTS)) {
                if ($qa->get_question()->responseformat == qtype_proforma::RESPONSE_EXPLORER) {
                    return array($step, $this->page->get_renderer('qtype_proforma', 'format_explorer'));
                } else {
                    return array($step, $this->page->get_renderer('qtype_proforma', 'format_filepicker'));
                }
            }
            if ($step->has_qt_var(ANSWER)) {
                return array($step, $this->page->get_renderer('qtype_proforma', 'format_editor'));
            }
        }
        return array($externalstep, $externalrenderer);
    }

    /**
     * converts the ProFormA response to html
     *
     * @param $message
     * @param $errormsg
     * @param question_attempt $qa
     * @return string
     */
    public function render_proforma2_message($message, $errormsg, $question) {
        // Delegate.
        $renderer = new feedback_renderer($this, $question);
        return $renderer->render_proforma2_message($message);
    }
}

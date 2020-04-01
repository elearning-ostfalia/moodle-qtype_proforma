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
 * class for creating edit forms for importedd tasks
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');

class proforma_form_creator extends base_form_creator {

    /**
     * proforma_form_creator constructor.
     *
     * @param $form
     */
    public function __construct($form) {
        parent::__construct($form);
    }

    /**
     * validate field values
     *
     * @param $fromform Validation argument
     * @param $files Validation argument
     * @param $errors Array with error messages (so far)
     * @return array with error messages
     */
    public function validation($fromform, $files, $errors) {
        $errors = parent::validation($fromform, $files, $errors);

        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
            if ($repeats > 0 && $sumweight == 0) {
                // error message must be attached to testoptions group
                // otherwise it is not visible
                $errors['testoptions[0]'] = get_string('sumweightzero', 'qtype_proforma');
            }
        }

        return $errors;
    }

    /**
     * Create links for files to be downloaded by student.
     *
     * @param $question
     */
    public function add_questiontext_attachments($question) {
        $mform = $this->form;
        $mform->addElement('hidden', 'taskstorage', qtype_proforma::PERSISTENT_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);
        // Attachments for Question Text (Downloads)
        $this->add_static_text($question, 'downloadlist', get_string('downloads', 'qtype_proforma'),
                'downloads');
        $mform->addHelpButton('downloadlist', 'downloads_hint', 'qtype_proforma');

    }

    /**
     * Create links for model solution files.
     *
     * @param $question
     */
    public function add_modelsolution($question) {
        $mform = $this->form;
        // Model Solution files (instead of modelsollist we show links)
        $mform->addElement('static', 'mslinks', get_string('modelsolfiles', 'qtype_proforma'), '');
        $mform->addHelpButton('mslinks', 'modelsolfiles_hint', 'qtype_proforma');
    }

    /**
     * Add response template editor for main template and links for further templates.
     *
     * @param $question
     */
    protected function add_responsetemplate($question) {
        $mform = $this->form;
        parent::add_responsetemplate($question);
        // Further templates (there should be no other templates)
        $this->add_static_text($question, 'furtherTemplates', get_string('templates', 'qtype_proforma'),
                'templates');
        $mform->addHelpButton('furtherTemplates', 'templates_hint', 'qtype_proforma');

    }

    /**
     * Display response filename.
     *
     * @param $question
     */
    /*
        protected function add_responsefilename($question) {
            $mform = $this->form;

            // since static fields cannot be hidden we create a group
            $group = [];
            $group[] =& $mform->createElement('static',  'dummy' , '', $question->options->responsefilename);
            $mform->addGroup($group, 'responsefilename', get_string('filename', 'qtype_proforma'), ' ', false);
            $mform->addHelpButton('responsefilename', 'filename_hint', 'qtype_proforma');

            // $this->add_static_field($question, 'responsefilename', get_string('filename', 'qtype_proforma'), 50);
        }
    */
    /**
     * Display grader settings.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
        $mform = $this->form;
        parent::add_grader_settings($question);
        // UUID
        $this->add_static_field($question, 'uuid', get_string('uuid', 'qtype_proforma'), 40);
        // $mform->setType('uuid', PARAM_TEXT);
        $mform->addHelpButton('uuid', 'uuid_hint', 'qtype_proforma');

        // Proforma version
        $this->add_static_field($question, 'proformaversion', 'ProFormA Version', 6);
    }

    /**
     * Modify respeatoptions
     *
     * @param $repeatoptions
     */
    protected function modify_repeatoptions(&$repeatoptions) {
        // disable testtype and test identifier for imported tasks
        $repeatoptions['testid']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        $repeatoptions['testtype']['disabledif'] = array('aggregationstrategy', 'neq', 111);
    }

    /**
     * Add test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    public function add_tests($question, $questioneditform) {
        $this->taskhandler = new qtype_proforma_proforma_task();
        $repeats = parent::add_tests($question, $questioneditform);
        // Remove button for adding new test elements.
        $mform = $this->form;
        $mform->removeElement('option_add_fields');
        return $repeats;
    }

    /**
     * Create text for download list
     *
     * @param $qelement
     * @param $oelement
     * @return string
     */
    private function create_downloadlist($qelement, $oelement) {
        $qelement = $oelement;
        if (isset($qelement)) {
            $list = array();
            foreach (explode(',', $qelement) as $download) {
                $list[] = $download;
            }
            $downloadlist = implode(', ', $list);
            return $downloadlist;
        }
        return '';
    }

    /**
     * Prepare question to fit form field names and values.
     *
     * @param $question
     * @param category $cat
     * @param MoodleQuickForm $form
     * @param qtype_proforma_edit_form $editor
     */
    public function data_preprocessing(&$question, $cat, MoodleQuickForm $form, qtype_proforma_edit_form $editor) {
        parent::data_preprocessing($question, $cat, $form, $editor);

        // Create lists for download links in edit form.
        foreach (qtype_proforma::fileareas_with_model_solutions() as $filearea => $value) {
            $property1 = $value['formlist'];
            $property2 = $value['questionlist'];
            $question->$property1 = $this->create_downloadlist($question->$property2,
                    $question->options->$property2);
        }

        // create template list with all template files without the first one
        // which gets its own editor
        // (normally there should be only one template if no filepicker is used)
        $alltemplates = explode(',', $question->templates);
        $question->firstTemplate = array_shift($alltemplates);
        $question->furtherTemplates = implode(',', $alltemplates);

        if (strlen($question->furtherTemplates) == 0) {
            $form->removeElement('furtherTemplates');
        }

        if (!empty($question->modelsolfiles)) {
            $question->mslinks = '';
            foreach (explode(',', $question->modelsolfiles) as $ms) {
                $url = moodle_url::make_pluginfile_url($cat, 'qtype_proforma',
                        qtype_proforma::FILEAREA_MODELSOL, $question->id, '/', $ms);
                $question->mslinks = $question->mslinks . '<a href=' . $url->out() . '>' . $ms . '</a> ';
            }
        }

        $taskfilehandler = new qtype_proforma_proforma_task;
        $taskfilehandler->extract_formdata_from_gradinghints($question, $form);
    }

    /**
     * handle polymorphic behaviour when saving a question
     *
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        parent::save_question_options($options);
        $formdata = $this->form;
        $instance = new qtype_proforma_proforma_task();
        $options->gradinghints = $instance->create_lms_grading_hints($formdata);
    }
}
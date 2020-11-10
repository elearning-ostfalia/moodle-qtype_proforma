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
 * class for creating edit forms for imported ProFormA tasks
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');

/**
 * This class creates the edit form fields for imported ProFormA tasks.
 * It is also the base class for other edit forms.
 */
class proforma_form_creator extends base_form_creator {

    /**
     * proforma_form_creator constructor.
     *
     * @param $form
     */
    public function __construct($form) {
        parent::__construct($form, new qtype_proforma_proforma_task(), qtype_proforma::response_formats());
        echo $this->_taskhandler->create_in_moodle();        
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
     * Add hidden fields for question attributes that are not part of the edit form.
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        parent::add_hidden_fields();

        $mform = $this->_form;
        $mform->addElement('hidden', 'taskstorage', qtype_proforma::PERSISTENT_TASKFILE);
        $mform->setType('taskstorage', PARAM_INT);

        // Attachments for Question Text (Downloads)
        // $mform->addElement('static', 'downloadlinks', get_string('downloads', 'qtype_proforma'), '');
        // $mform->addHelpButton('downloadlinks', 'downloads_hint', 'qtype_proforma');
    }

    /**
     * Create links for model solution files.
     */
    public function add_modelsolution() {
        $mform = $this->_form;
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
        $mform = $this->_form;
        parent::add_responsetemplate($question);
        // Further templates (there should be no other templates)
        $this->add_static_text($question, 'furtherTemplates', get_string('templates', 'qtype_proforma'),
                'templates');
        $mform->addHelpButton('furtherTemplates', 'templates_hint', 'qtype_proforma');

    }

    /**
     * Display grader settings.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
        $mform = $this->_form;
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
    protected function modify_test_repeatoptions(&$repeatoptions) {
        // disable testtype and test identifier for imported tasks
        $repeatoptions['testid']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        $repeatoptions['testtype']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        // Hide weight for case of all-or-nothing.
        $repeatoptions['testweight']['hideif'] = array('aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);       
        
    }

    /**
     * Add test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    public function add_tests($question, $questioneditform) {
        // $this->_taskhandler = new qtype_proforma_proforma_task();
        return $this->add_test_fields($question, $questioneditform, 'unittest');
    }

  
    
    /**
     * Prepare question to fit form field names and values.
     *
     * @param $question
     * @param category $cat
     * @param MoodleQuickForm $form
     * @param qtype_proforma_edit_form $editor
     */
    public function data_preprocessing(&$question, $cat, qtype_proforma_edit_form $editor) {
        parent::data_preprocessing($question, $cat, $editor);
        $form = $editor->get_form();

        if (empty($question->options)) {
            $question->furtherTemplates = '';
            $question->firstTemplate = '';
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

        // Create links for model solution and download files
        $msfilearea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_MODELSOL);
        $question->mslinks = $msfilearea->get_files_as_links($question->contextid,
                $question->id);
        // $downloadfilearea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_DOWNLOAD);
        // $question->downloadlinks = $downloadfilearea->get_files_as_links($question->contextid, $question->id);

        // $taskfilehandler = new qtype_proforma_proforma_task;
        $this->_taskhandler->extract_formdata_from_gradinghints($question, $form);
    }

    /**
     * handle polymorphic behaviour when saving a question
     *
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        $formdata = $this->_form;
        if (isset($formdata->taskfiledraftid)) {
            // special handling for proforma import (interim solution):
            // rename draftid property
            throw new moodle_exception('your qformat_proforma plugin is outdated, please upgrade!');
            $formdata->task = $formdata->taskfiledraftid;
            $formdata->modelsol = $formdata->modelsolid;
            $formdata->download = $formdata->downloadid;
            $formdata->template = $formdata->templateid;
        }
        parent::save_question_options($options);

        // $instance = new qtype_proforma_proforma_task();
        // $options->gradinghints = $this->_taskhandler->create_lms_grading_hints($formdata);
    }
 
}
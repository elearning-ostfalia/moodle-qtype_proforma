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
 * abstract base class for creating polymorph question edit forms
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/classes/filearea.php');
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

/**
 * Bases class for rendering the question editor form for teachers
 */
abstract class base_form_creator {
    /**
     * @var MoodleQuickForm The form object that must be filled with input fields.
     */
    protected $form = null;
    protected $taskhandler = null;
    /** 
     * response options
     */
    protected $responseformats = null;

    // Property name for download manager.
    const DOWNLOADMANAGER = qtype_proforma::FILEAREA_DOWNLOAD;

    /**
     * base_form_creator constructor.
     *
     * @param $form
     */
    protected function __construct($form, $responseformats) {
        $this->form = $form;
        $this->responseformats = $responseformats;
    }

    // override

    /**
     * validate field values
     * @param $fromform Validation argument
     * @param $files Validation argument
     * @param $errors Array with error messages (so far)
     * @return array with error messages
     */
    public function validation($fromform, $files, $errors) {
        return $errors;
    }

    /**
     * Add something to select the programming language.
     *
     * @param $question
     */
    public function add_proglang_selection($question) {
    }

    /**
     * Add grader options/information.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
        // ProFormA fields
        $mform = $this->form;
        $mform->addElement('header', 'graderoptions_header', get_string('graderoptions_header', 'qtype_proforma'));

        // Task Filename
        $this->add_static_text($question, 'link', 'taskfilename', 'qtype_proforma');

        // $mform->addElement('static', 'link', get_string('taskfilename', 'qtype_proforma'), '');
        // $mform->setType('link', PARAM_TEXT);
        $mform->addHelpButton('link', 'taskfilename_hint', 'qtype_proforma');
    }

    /**
     *  Add hidden fields for question attributes that are not part of the edit form.
     * (Static elements are not sent as input data when submit is pressed,
     * needed for duplicating a question)
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        $mform = $this->form;

        $hiddenfields = array('taskfilename', 'taskpath', // 'templates', 'modelsolfiles', 'downloads'
        // , 'gradinghints' // values for grading hints are redundant
        );

        // add hidden fields for filearea draft ids (if any)
        foreach (qtype_proforma::proforma_fileareas() as $filearea => $value) {
            $hiddenfields[] = $filearea;
        }

        foreach ($hiddenfields as $field) {
            $mform->addElement('hidden', $field, null, array('size' => '30'));
            // $mform->addElement('text', $field, ' should be hidden ' . $field, array('size' => '30'));
            $mform->setType($field, PARAM_RAW);
        }
    }

    /**
     * Add downloads for question text.
     *
     * @param $question
     */
    public function add_questiontext_attachments($question) {
        $mform = $this->form;

        // Add Filemanager for download links associated with question text.
        // Remove hidden element in base class.
        $mform->removeElement(self::DOWNLOADMANAGER);
        $mform->addElement('filemanager', self::DOWNLOADMANAGER, get_string('downloads', 'qtype_proforma'), null,
                array('subdirs' => true));
        $mform->addHelpButton(self::DOWNLOADMANAGER, 'downloads_hint', 'qtype_proforma');
    }

    /**
     * Add response template.
     *
     * @param $question
     */
    protected function add_responsetemplate($question) {
        $mform = $this->form;
        $mform->addElement('textarea', 'responsetemplate', get_string('responsetemplate', 'qtype_proforma'), 'rows="20" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma\lib\as_codemirror('id_responsetemplate', 'java', 'id_responsetemplateheader');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                    array('id_programminglanguage', 'id_responsetemplate'));
        }
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_proforma');
        // Show only if response format is editor
        $mform->hideIf('responsetemplate', 'responseformat', 'neq', 'editor');
    }

    /**
     * Add response filename (edit field)
     *
     * @param $question
     */
    protected function add_responsefilename() {
        $mform = $this->form;
        $mform->addElement('text', 'responsefilename', get_string('filename', 'qtype_proforma'), array('size' => '60'));
        $mform->setType('responsefilename', PARAM_TEXT);
        $mform->addHelpButton('responsefilename', 'filename_hint', 'qtype_proforma');
        // do not set required since the filed can be hidden
        // $mform->addRule('responsefilename', null, 'required', '', 'client', false, false);

        // note: hidding responsefilename does not work with static text
        $mform->hideIf('responsefilename', 'responseformat', 'neq', 'editor');

        // maybe in the future...
        // $mform->addElement('button', 'generatefilename', get_string('generatefilename', 'qtype_proforma'));
    }

    /**
     * Add model solution.
     *
     * @param $question
     */
    public function add_modelsolution($question) {
    }

    // override

    /**
     * Get test label for add_tests.
     *
     * @return string label of (unit) tests
     */
    protected function get_test_label() {
        return get_string('testlabel', 'qtype_proforma');
    }

    /**
     * Modify repeatarray in add_tests.
     *
     * @param $repeatarray
     */
    protected function modify_test_repeatarray(&$repeatarray) {
    }

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function modify_test_repeatoptions(&$repeatoptions) {
    }

    /**
     * Modify testoptions in add_tests
     *
     * @param $testoptions
     */
    protected function modify_test_testoptions(&$testoptions) {
    }

    /**
     * Add tests as repeat group
     * @param $question
     * @param $questioneditform
     * @return int
     */
    public function add_tests($question, $questioneditform) {
        $mform = $this->form;
        // Retrieve number of tests (resp. unit tests).
        $repeats = $this->get_count_tests($question);
        if ($repeats == 0) {
            // No tests available => finished.
            $mform->addElement('static', 'no_tests', get_string('notests', 'qtype_proforma'), '');
            return $repeats;
        }

        // Unit tests resp. tests from imported task
        // Create test group:
        $testoptions = array();
        $this->add_test_weight_option($testoptions, 'test', '1', true);
        $testoptions[] = $mform->createElement('text', 'testid', 'Id', array('size' => 3));
        $testoptions[] = $mform->createElement('text', 'testtype',
                get_string('testtype', 'qtype_proforma'), array('size' => 80));
        $testoptions[] = $mform->createElement('text', 'testdescription',
                get_string('testdescription', 'qtype_proforma'), array('size' => 80));
        $this->modify_test_testoptions($testoptions);

        $label = get_string('testlabela', 'qtype_proforma', $this->get_test_label());
        
        $repeatarray = array();
        $repeatarray[] = $mform->createElement('group', 'testoptions', $label, $testoptions, null, false);
        $this->modify_test_repeatarray($repeatarray);
        $repeatoptions = array();
        $repeatoptions['testweight']['default'] = 1;
        // $repeatoptions['testtitle']['default'] = get_string('junittesttitle', 'qtype_proforma');
        $repeatoptions['testdescription']['default'] = '';
        // $repeateloptions['testfilename']['default'] = '';
        $repeatoptions['testtype']['default'] = 'unittest'; // JAVA-JUNIT
        $repeatoptions['testid']['default'] = '{no}'; // JAVA-JUNIT

        // $repeateloptions['testweight']['rule'] = 'numeric';
        $this->modify_test_repeatoptions($repeatoptions);

        // $repeateloptions['testweight']['helpbutton'] = 'Hilfetext';
        $mform->setType('testdescription', PARAM_TEXT);
        $mform->setType('testtitle', PARAM_TEXT);
        $mform->setType('testweight', PARAM_FLOAT);
        $mform->setType('testid', PARAM_RAW);
        $mform->setType('testtype', PARAM_RAW);
        // $mform->setType('testfilename', PARAM_TEXT);

        // $mform->disabledIf('testweight', 'aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);

        $buttonlabel = get_string('addtest', 'qtype_proforma', $this->get_test_label());
        $questioneditform->repeat_elements($repeatarray, $repeats,
                $repeatoptions, 'option_repeats', 'option_add_fields',
                1, $buttonlabel, true);

        // $mform->addGroupRule('testoptions', array('testtitle' => array(null, 'required', null, 'client')));

        return $repeats;
    }

    
    /**
     * get number of tests for repeat group
     * @param $question
     * @return int
     */
    protected function get_count_tests($question) {
        $repeats = 0;
        // Get number of unit tests from (lms) grading hints.
        // In case of an imported task this ist the number of all tests (not just unit tests).
        if (isset($question) && isset($question->options) && isset($question->options->gradinghints)) {
            $repeats = $this->taskhandler->get_count_unit_tests($question->options->gradinghints);
        }

        return $repeats;
    }
    
    protected function add_editor_options($qtype) {
        $mform = $this->form;
        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_proforma'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);
        $mform->hideIf('responsefieldlines', 'responseformat', 'neq', 'editor');    
        
        // Programming Language.
        $mform->addElement('select', 'programminglanguage',
                get_string('highlight', 'qtype_proforma'), $qtype->get_proglang_options());
        $mform->addHelpButton('programminglanguage', 'highlight_hint', 'qtype_proforma');
        $mform->setDefault('programminglanguage', 'java');
        // Show only if response format is editor
        $mform->hideIf('programminglanguage', 'responseformat', 'neq', 'editor');
        // Response filename.
        $this->add_responsefilename();        
    }

    /**
     * Add response options.
     *
     * @param $question
     * @param $qtype
     */
    public function add_response_options($question, $qtype) {
        global $CFG, $COURSE;
        $mform = $this->form;

        // $defaultmaxsubmissionsizebytes = get_config('maxsubmissionsizebytes');
        // $defaultfiletypes = (string)get_config('filetypeslist');
        //
        // Response Options
        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_proforma'));
        $mform->setExpanded('responseoptions');

        switch (count($this->responseformats) >= 1) {
            case 0:
                break;
            case 1:
                $mform->addElement('hidden', 'responseformat', array_key_first($this->responseformats));
                $mform->setType('responseformat', PARAM_RAW);                
                break;
            default:
                $mform->addElement('select', 'responseformat',
                    get_string('responseformat', 'qtype_proforma'), $this->responseformats);
                break;
        }
        
        // EDITOR OPTIONS
        if (array_key_exists(qtype_proforma::RESPONSE_EDITOR, $this->responseformats)) {
            $mform->setDefault('responseformat', 'editor');
            if ($this->responseformats) {
                $this->add_editor_options($qtype);                
            }            
        }

        // FILEPICKER OPTIONS
        if (array_key_exists(qtype_proforma::RESPONSE_FILEPICKER, $this->responseformats)) {
            $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes,
                    get_config('qtype_proforma', 'maxbytes'));

            $name1 = get_string('maximumsubmissionsize', 'qtype_proforma');
            $name2 = get_string('acceptedfiletypes', 'qtype_proforma');

            $filepickeroptions = array();
            $filepickeroptions[] = $mform->createElement('select', 'attachments',
                    get_string('allowattachments', 'qtype_proforma'), $qtype->attachment_options());
            $filepickeroptions[] = $mform->createElement('select', 'maxbytes', $name1, $choices);
            $filepickeroptions[] = $mform->createElement('text', 'filetypes', $name2);
            $mform->addGroup($filepickeroptions, 'filepickergroup',  get_string('filepickeroptions', 'qtype_proforma'), array(' '), false);
            $mform->hideIf('filepickergroup', 'responseformat', 'neq', 'filepicker');
            $mform->addHelpButton('filepickergroup', 'acceptedfiletypes', 'qtype_proforma');            
            $mform->setType('filetypes', PARAM_RAW);
        }

        // VERSION CONTROL OPTIONS
        if (array_key_exists(qtype_proforma::RESPONSE_VERSION_CONTROL, $this->responseformats)) {
            $mform->addElement('text', 'vcsuritemplate', get_string('vcsuritemplate', 'qtype_proforma'), array('size' => '80'));
            $mform->setDefault('vcsuritemplate', get_config('qtype_proforma', 'defaultvcsuri'));
            $mform->setType('vcsuritemplate', PARAM_TEXT);
            $mform->addHelpButton('vcsuritemplate', 'vcsuritemplate', 'qtype_proforma');
            $mform->hideIf('vcsuritemplate', 'responseformat', 'neq', 'versioncontrol');

            $mform->addElement('text', 'vcslabel', get_string('vcslabel', 'qtype_proforma'), array('size' => '20'));
            $mform->setDefault('vcslabel', get_config('qtype_proforma', 'vcslabeldefault'));
            $mform->setType('vcslabel', PARAM_TEXT);
            $mform->addHelpButton('vcslabel', 'vcslabel', 'qtype_proforma');
            $mform->hideIf('vcslabel', 'responseformat', 'neq', 'versioncontrol');
        }

        // Response template.
        $this->add_responsetemplate($question);
        // Model solution.
        $this->add_modelsolution($question);
    }

    /**
     * Add test settings.
     *
     * @param $question
     * @param $questioneditform
     */
    public function add_test_settings($question, $questioneditform) {
        $mform = $this->form;

        // Header.
        $mform->addElement('header', 'test_header', get_string('tests', 'qtype_proforma'));
        $mform->setExpanded('test_header');

        // Aggreagation strategy.
        $aggregationstrategy = array(
                qtype_proforma::ALL_OR_NOTHING => get_string('all_or_nothing', 'qtype_proforma'),
                qtype_proforma::WEIGHTED_SUM  => get_string('weighted_sum', 'qtype_proforma')
        );
        $mform->addElement('select', 'aggregationstrategy',
                get_string('aggregationstrategy', 'qtype_proforma'), $aggregationstrategy);
        $mform->addHelpButton('aggregationstrategy', 'aggregationstrategy', 'qtype_proforma');
        $mform->setDefault('aggregationstrategy', qtype_proforma::WEIGHTED_SUM);

        // Tests
        // - test overview in case of imported task and
        // - test edit fields for tasks created with Moodle
        $this->add_tests($question, $questioneditform);

        // Penalty
        $penalties = array(
                1.0000000,
                0.5000000,
                0.3333333,
                0.2500000,
                0.2000000,
                0.1000000,
                0.0000000
        );
        if (!empty($question->penalty) && !in_array($question->penalty, $penalties)) {
            $penalties[] = $question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
                get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions);
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'question');
        $mform->setDefault('penalty', get_config('qtype_proforma', 'defaultpenalty'));
    }

    /**
     * function for handling data preprocessing depending on question type
     * @param $question
     * @param $cat category
     * @param MoodleQuickForm $form
     * @param qtype_proforma_edit_form $editor
     */
    public function data_preprocessing(&$question, $cat, qtype_proforma_edit_form $editor) {
        if (empty($question->options)) {
            // new question:
            // preset all fields that can be disabled in the form. Otherwise they may be missing
            // somewhere! (resulting in an exception)
            $question->maxbytes = 0;
            $question->attachments = 0;
            $question->filetypes = '';
            $question->taskfilename = '';
            $commenttext = null;
            $commentformat = FORMAT_HTML;

            foreach (qtype_proforma::fileareas_with_model_solutions() as $fileareaname => $value) {
                $property = $value["dbcolumn"];
                $question->$property = '';
            }
        } else {
            $commenttext = $question->options->comment;
            $commentformat = $question->options->commentformat;
        }

        // prepare all fileareas
        foreach (qtype_proforma::proforma_fileareas() as $fileareaname => $value) {
            // create draft area
            $filearea = new qtype_proforma_filearea($fileareaname);
            $filearea->prepare_draft($editor->context->id, $question);
            // create stringlist variable
            if (isset($value['formlist']) && !empty($question->id)) {
                $property1 = $value['formlist'];
                $question->$property1 = $filearea->get_files_as_stringlist($cat, $question->id);
            }
        }

        // Special handling for comment.
        $draftid = file_get_submitted_draft_itemid(qtype_proforma::FILEAREA_COMMENT);
        $question->comment = array();
        $question->comment['text'] = file_prepare_draft_area(
                $draftid,           // Draftid
                $editor->context->id, // context
                'qtype_proforma',      // component
                qtype_proforma::FILEAREA_COMMENT,       // filarea
                !empty($question->id) ? (int) $question->id : null, // itemid
                $editor->fileoptions, // options
                $commenttext
        );
        $question->comment['format'] = $commentformat;
        $question->comment['itemid'] = $draftid;

        // Create task link from actual task.
        if (!empty($question->id)) {
            $task = new qtype_proforma_filearea(qtype_proforma::FILEAREA_TASK);
            $question->link = $task->get_files_as_links($cat, $question->id);
        }
    }

    // helper functions

    /**
     * Add field that shall not be editable.
     *
     * @param $question
     * @param $mform form
     * @param $field fieldname
     * @param $label labeltext
     * @param null $sizefield sizefield
     */
    protected function add_static_field($question, $field, $label, $size) {
        $mform = $this->form;
        if (isset($this->question->options->$field)) {
            $size = $question->options->$field;
        } else if (isset($this->question->$field)) {
            $size = $question->options->$field;
        }
        $mform->addElement('text', $field, $label, array('size' => $size));
        $mform->disabledIf($field, 'responseformat', 'neq', 'alwaysdisabled');
        $mform->setType($field, PARAM_TEXT);

        /* $textelement = $field;
        $mform->addElement('static', $textelement, $label);
        debugging('static text: ' . $field);
        $mform->setType($textelement, PARAM_TEXT);

        $mform->addElement('text', $field, ' should be hidden ' . $field, array('size' => '30'));
        $mform->setType($field, PARAM_TEXT);
        */
    }


    /**
     * Add static text.
     *
     * @param $question
     * @param $mform form
     * @param $field fieldname
     * @param $label labeltext
     * @param null $sizefield sizefield
     */
    protected function add_static_text($question, $field, $label) {
        $mform = $this->form;
        if (isset($sizefield)) {
            if (isset($this->question->options->$sizefield)) {
                $value = $question->options->$sizefield;
                $attributes = array('size' => strlen($question->options->$sizefield));
            } else if (isset($this->question->$sizefield)) {
                $value = $question->$sizefield;
                $attributes = array('size' => strlen($question->$sizefield));
            }
        } // else {
            // $attributes = array('size' => strlen($question->options->$field));
        // }

        if (isset($attributes) && count($attributes) > 0) {
            $mform->addElement('static', $field, $label, $attributes, '');
        } else {
            $mform->addElement('static', $field, $label);
        }

        // debugging('static field: ' . $field);
        $mform->setType($field, PARAM_TEXT);
    }

    /**
     * Helper function to create a weight field.
     *
     * @param $testoptions array to append field(s) to
     * @param $prefix prefix of name
     * @param $defaultweight default value for weight
     * @param bool $withtitle True: also create title field
     */
    protected function add_test_weight_option(&$testoptions, $prefix, $defaultweight, $withtitle = false) {
        $mform = $this->form;
        if ($withtitle) {
            $testoptions[] = $mform->createElement('text', $prefix . 'title',
                    get_string('testtitle', 'qtype_proforma'), array('size' => 60));
            $mform->setType($prefix . 'title', PARAM_TEXT);
        }

        $testoptions[] = $mform->createElement('text', $prefix . 'weight',
                get_string('weight', 'qtype_proforma'), array('size' => 2));
        $mform->setType($prefix . 'weight', PARAM_FLOAT);
        $mform->setDefault($prefix . 'weight', $defaultweight);
    }

    /**
     * handle polymorphic behaviour when saving a question
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        $formdata = $this->form;
        $context = $formdata->context;

        // Save files from draft area into proforma areas (modelsolution, downloads, templates)
        // (needed for import and duplication).
        foreach (qtype_proforma::proforma_fileareas() as $fileareaname => $value) {
            $filearea = new qtype_proforma_filearea($fileareaname);
            $filearea->save_draft($formdata, $options, $value['dbcolumn']);
        }

        // Special handling for responsetemplate:
        // If responseformat is editor than the template is
        // also stored as file for download.
        // Otherwise a previously existing template file is deleted.
        // in order to support file download and editor template in student view.
        // todo: remove redundancy
        $templfilearea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_TEMPLATE);
        if ($formdata->responseformat == qtype_proforma::RESPONSE_EDITOR) { // Editor.
            $options->templates = $formdata->templates = 'template.txt'; /*$formdata->responsefilename*/
            $templfilearea->save_textfile($context->id, $formdata->id, $options->templates,
                    $formdata->responsetemplate);
            if (empty($formdata->responsetemplate)) {
                // Remove templates value.
                $options->templates = $formdata->templates = '';
            }
        } else {
            // Store empty file for filepicker or version control system (= delete file if any)
            $templfilearea->save_textfile($context->id, $formdata->id, 'dummy.txt', '');
            $options->templates = $formdata->templates = '';
        }
    }
}
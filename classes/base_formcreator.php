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
     * Property name for model solution manager.
     * Must be name of associated filearea!!.
     */
    const MODELSOLMANAGER = qtype_proforma::FILEAREA_MODELSOL;

    /** Property name for download manager. */
    const DOWNLOADMANAGER = qtype_proforma::FILEAREA_DOWNLOAD;

    /** Radio button value for editor test code. */
    const TESTCODE_EDITOR = 1;
    /** Radio button value for file uploaded test code. */
    const TESTCODE_FILES = 2;
    /**
     * @var MoodleQuickForm The form object that must be filled with input fields.
     */
    protected $_form = null;

    /**
     * class instance for doing the task related work.
     * @var qtype_proforma_base_task task
     */
    protected $_taskhandler = null;

    /**
     * response options
     */
    protected $_responseformats = null;
    /** input format for (unit) test code */
    protected $_testcode = true;
    protected $_testfiles = true;

    /** Syntax highlighting mode. */
    protected $_syntaxhighlighting = 'java';

    /** Programming language. */
    protected $_proglang = null;

    /** (Unit) test has entrypoint. */
    protected $_entrypoint = false;
    /** Entrypoint label. */
    protected $_entrypointlabel = null;

    /** @var null numberic identifier of task type */
    protected $_tasktype = null;

    /** @var string label for unit test */
    protected $_unittestlabel = null;

    /**
     * constructor
     *
     * @param type $form form instance OR formdata
     * @param type $taskhandler task handler instance
     * @param type $responseformats available response formats
     * @param type $syntaxhighlight syntax highleighting for editor
     * @param type $proglang programming language if any
     */
    protected function __construct($form, $taskhandler) {
        $this->_form = $form;
        $this->_taskhandler = $taskhandler;
        $this->_unittestlabel = get_string('testlabel', 'qtype_proforma');
    }

    // Override.

    /**
     * override if you need to setup the form depending on current
     * values
     */
    public function definition_after_data() {
    }

    /**
     * Add tests as repeat group
     * @param $question
     * @param $questioneditform
     * @return int
     */
    abstract protected function add_tests($question, $questioneditform);

    /**
     * validate field values
     *
     * @param qtype_proforma_edit_form $editor actual editor instance
     * @param $fromform Validation argument
     * @param $files Validation argument
     * @param $errors Array with error messages (so far)
     * @return array with error messages
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validation(qtype_proforma_edit_form &$editor, $fromform, $files, $errors) {
        $title = $fromform["testtitle"][0];
        $titleavailable = strlen(trim($title)) > 0;
        if (!$titleavailable) {
            // At least one test must be defined. This is checked by
            // checking if the first title is set.
            $errors['testtitle[0]'] = get_string('titleempty', 'qtype_proforma');
        }

        // For deleting the last test by leaving all (relevant) fields empty.
        /* $repeats = $this->get_count_tests(null);
        if ($this->is_test_empty($fromform, $repeats - 1)) {
            $errors = $this->prepare_removing_last_test($errors, $repeats - 1);
        }
        */

        if (isset($fromform["responseformat"]) and $fromform["responseformat"] == 'editor') {
            // Missing response filename.
            if (0 == strlen(trim($fromform["responsefilename"]))) {
                $errors['responsefilename'] = get_string('required');
            }
        }

        // Check tests.
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            list($errors, $valid) = $this->validate_unittest($editor, $fromform, $files, $i, $errors);
        }

        // Ensure that sum of weights is > 0.
        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = $this->calc_sumweight($fromform);
            if (/*$repeats > 0 && */$sumweight == 0) {
                // Error message must be attached to testoptions group
                // otherwise it is not visible.
                $errors['testoptions[0]'] = get_string('sumweightzero', 'qtype_proforma');
            }
        }

        return $errors;
    }

    /**
     * calculates the sum of all weights (for validation)
     * @param $fromform
     * @return int|mixed
     */
    protected function calc_sumweight($fromform) {
        $repeats = count($fromform["testweight"]);
        $sumweight = 0;
        for ($i = 0; $i < $repeats; $i++) {
            $sumweight += $fromform["testweight"][$i];
        }
        return $sumweight;
    }

    /**
     * validate Unit test input
     *
     * @param qtype_proforma_edit_form $editor main editor instance
     * @param type $fromform  input data
     * @param type $files file array
     * @param type $i index
     * @param type $errors errors array
     * @return
     * @throws coding_exception
     */
    protected function validate_unittest(qtype_proforma_edit_form $editor, $fromform, $files, $i, $errors) {
        $title = $fromform["testtitle"][$i];
        $format = $fromform["testcodeformat"][$i];
        $testcodefield = "testcode";
        $titleavailable = strlen(trim($title)) > 0;
        switch ($format) {
            case self::TESTCODE_EDITOR: // Editor.
                $code = $fromform["testcode"][$i];
                $codeavailable = (strlen(trim($code)) > 0);
                break;
            case self::TESTCODE_FILES: // Filemanager.
                global $USER;
                $usercontext = context_user::instance($USER->id);
                $testcodefield = "testfiles";
                $draftitemid = $fromform["testfiles"][$i];
                $fs = get_file_storage();
                $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
                $codeavailable = (count($draftfiles) > 1);
                break;
            default:
                throw new coding_exception('unexpected value ' . $format);
        }
        if (!$codeavailable and $titleavailable) {
            // Title is set but code is missing.
            $errors[$testcodefield . '['.$i.']'] = get_string('codeempty', 'qtype_proforma');
        }

        if ($codeavailable and !$titleavailable) {
            // Title is missing:
            // error message must be attached to testoptions group.
            $errors['testtitle['.$i.']'] = get_string('titleempty', 'qtype_proforma');
        }

        return array($errors, $codeavailable and $titleavailable);
    }


    /**
     * Add something to select the programming language.
     */
    public function add_proglang_selection() {
        if (isset($this->_proglang)) {
            $mform = $this->_form;
            $mform->addElement('text', 'proglang',
                    get_string('proglang', 'qtype_proforma'), $this->_proglang);
            $mform->disabledIf('proglang', 'responseformat', 'neq', 'alwaysdisabled');
            $mform->setType('proglang', PARAM_TEXT);
            $mform->setDefault('proglang', $this->_proglang);
        }
    }

     /**
      * Add grader options/information.
      *
      * @param qtype_proforma_question $question question
      * @param type $context
      */
    public function add_grader_settings($question, $context) {
        // ProFormA fields.
        $mform = $this->_form;
        $mform->addElement('header', 'graderoptions_header', get_string('graderoptions_header', 'qtype_proforma'));

        // Task Filename.
        $this->add_static_text($question, 'link', 'taskfilename', 'qtype_proforma');
        $mform->addHelpButton('link', 'createdtask_hint', 'qtype_proforma');
    }

    /**
     *  Add hidden fields for question attributes that are not part of the edit form.
     * (Static elements are not sent as input data when submit is pressed,
     * needed for duplicating a question)
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        $mform = $this->_form;

        // Add hidden fields for filearea draft ids (if any).
        foreach (array_keys(qtype_proforma::proforma_fileareas()) as $filearea) {
            $hiddenfields[] = $filearea;
        }

        foreach ($hiddenfields as $field) {
            $mform->addElement('hidden', $field, null, array('size' => '30'));
            $mform->setType($field, PARAM_RAW);
        }

        $mform->addElement('hidden', 'taskstorage', $this->_tasktype);
        $mform->setType('taskstorage', PARAM_RAW);
    }

    /**
     * Add downloads for question text.
     */
    public function add_questiontext_attachments() {
        $mform = $this->_form;

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
        $mform = $this->_form;
        $mform->addElement('textarea', 'responsetemplate',
            get_string('responsetemplate', 'qtype_proforma'), 'rows="5" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma\lib\as_codemirror('id_responsetemplate',
                $this->_syntaxhighlighting, 'id_responsetemplateheader');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                array('id_programminglanguage', 'id_responsetemplate'));
        }
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_proforma');
        // Show only if response format is editor.
        $mform->hideIf('responsetemplate', 'responseformat', 'neq', 'editor');
    }

    /**
     * Add response filename (edit field)
     *
     * @param $question
     */
    protected function add_responsefilename() {
        $mform = $this->_form;
        $mform->addElement('text', 'responsefilename', get_string('filename', 'qtype_proforma'), array('size' => '60'));
        $mform->setType('responsefilename', PARAM_TEXT);
        $mform->addHelpButton('responsefilename', 'filename_hint', 'qtype_proforma');
        // Do not set required since the filed can be hidden!
        // Note: hidding responsefilename does not work with static text.
        $mform->hideIf('responsefilename', 'responseformat', 'neq', 'editor');
    }

    /**
     * Add model solution as edit field for editor response format or
     * as fielmanager for filepicker response format.
     */
    protected function add_modelsolution() {
        $mform = $this->_form;
        // Model Solution files.
        $mform->addElement('textarea', 'modelsolution', get_string('modelsolution', 'qtype_proforma'), 'rows="10" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma\lib\as_codemirror('id_modelsolution', $this->_syntaxhighlighting);
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
            array('id_programminglanguage', 'id_modelsolution'));
        }
        $mform->addHelpButton('modelsolution', 'modelsolution', 'qtype_proforma');
        $mform->hideIf('modelsolution', 'responseformat', 'neq', 'editor');

        // Add Filemanager for model solution in case of not using the editor.
        // Replace hidden element by actual model solution filemanager.
        $mform->removeElement(self::MODELSOLMANAGER);
        $mform->addElement('filemanager', self::MODELSOLMANAGER, get_string('modelsolfiles', 'qtype_proforma'), null,
        array('subdirs' => true));

        $mform->hideIf(self::MODELSOLMANAGER, 'responseformat', 'eq', 'editor');
    }

    /**
     * Modify repeatarray in add_tests.
     *
     * @param $repeatarray
     */
    protected function adjust_test_repeatarray(&$repeatarray) {
        $mform = $this->_form;

        if ($this->_testcode and $this->_testfiles) {
            // Add choice for test code input: editor or filemanager.
            $radioarray = array();
            $radioarray[] = $mform->createElement('radio', 'testcodeformat', '',
                    get_string('editorinput', 'qtype_proforma'), self::TESTCODE_EDITOR);
            $radioarray[] = $mform->createElement('radio', 'testcodeformat', '',
                    get_string('fileinput', 'qtype_proforma'), self::TESTCODE_FILES);
            $repeatarray[] = $mform->createElement('group', 'testcodearray',
                    get_string('testcode', 'qtype_proforma'),
                    $radioarray, null, false);
        }
        if ($this->_testcode) {
            // Add textarea for unit test code.
            $repeatarray[] = $mform->createElement('textarea', 'testcode', '',
                    array('rows' => 20, 'cols' => 80));
        } else {
            $repeatarray[] = $mform->createElement("hidden", "testcodeformat", self::TESTCODE_FILES);
            $this->_form->setType('testcodeformat', PARAM_INT);
            // $repeatoptions['testfiles']['rule'] = 'required';
        }
        if ($this->_testfiles) {
            // Add filemanager.
            $repeatarray[] = $mform->createElement('filemanager', 'testfiles', '', null,
                    array('subdirs' => 0, 'areamaxbytes' => 10485760, 'maxfiles' => 15));
        } else {
            $repeatarray[] = $mform->createElement("hidden", "testcodeformat", self::TESTCODE_EDITOR);
            $this->_form->setType('testcodeformat', PARAM_INT);
            // $repeatoptions['testcode']['rule'] = 'required';
        }

        if ($this->_entrypoint) {
            // Add Unit test entry point.
            $repeatarray[] = $mform->createElement('text', 'testentrypoint',
                    $this->_entrypointlabel, array('size' => 80));
            $this->_form->setType('testentrypoint', PARAM_TEXT);
        }
    }

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function adjust_test_repeatoptions(&$repeatoptions) {
        // Always HIDE testtype and test identifier.
        $repeatoptions['testid']['hideif'] = array('aggregationstrategy', 'neq', 111);
        $repeatoptions['testtype']['hideif'] = array('aggregationstrategy', 'neq', 111);
        // Hide weight for case of all-or-nothing.
        $repeatoptions['testweight']['hideif'] = array('aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);

        // Show testcode editor/filemanager depending on radio button.
        $repeatoptions['testcode']['hideif'] = array('testcodeformat', 'eq', self::TESTCODE_FILES);
        $repeatoptions['testfiles']['hideif'] = array('testcodeformat', 'eq', self::TESTCODE_EDITOR);

        // Note that setting the testcodeformat default ($repeatoptions['testcodeformat']['default'])
        // also applies to already existing questions. So this is not set here.
        if ($this->_testcode and $this->_testfiles) {
            $repeatoptions['testcodeformat']['default'] = self::TESTCODE_EDITOR;
        }

    }
    /**
     * Modify testoptions in add_tests
     *
     * @param $testoptions
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function adjust_test_testoptions(&$testoptions) {
    }

    /**
     * Add tests as repeat group
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_test_fields($question, $questioneditform, $testtype) {

        $mform = $this->_form;
        // Retrieve number of tests (resp. unit tests).
        $repeats = $this->get_count_tests($question);
        if ($repeats == 0) {
            // No tests available => finished.
            $mform->addElement('static', 'no_tests', get_string('notests', 'qtype_proforma'), '');
            return $repeats;
        }

        // Create test group with 'small' elements.
        $testoptions = array();
        // Test id.
        $testoptions[] = $mform->createElement('text', 'testid', '');
        // Test type.
        $testoptions[] = $mform->createElement('text', 'testtype',
            get_string('testtype', 'qtype_proforma'), array('size' => 80));
        // Derived class could modify test options.
        $this->adjust_test_testoptions($testoptions);
        // Weight.
        $this->add_test_weight_option($testoptions, 'test', '1', false);

        $repeatarray = array();
        $label = get_string('testlabela', 'qtype_proforma', $this->_unittestlabel);
        $repeatarray[] = $mform->createElement('group', 'testoptions', $label, $testoptions, null, false);
        // Title.
        $repeatarray[] = $mform->createElement('text', 'testtitle',
            get_string('testtitle', 'qtype_proforma'), array('size' => 60));
        $mform->setType('testtitle', PARAM_TEXT);
        // Description.
        $repeatarray[] = $mform->createElement('text', 'testdescription',
            get_string('testdescription', 'qtype_proforma'), array('size' => 120));

        // Derived class could modify array.
        $this->adjust_test_repeatarray($repeatarray);

        $repeatoptions = array();
        $repeatoptions['testweight']['default'] = 1;
        $repeatoptions['testdescription']['default'] = '';
        $repeatoptions['testtype']['default'] = $testtype;
        // Autoincrement test identifier.
        $repeatoptions['testid']['default'] = '{no}';
        // Title is required.
        // $repeatoptions['testtitle']['rule'] = 'required';

        // Derived class could modify array options.
        $this->adjust_test_repeatoptions($repeatoptions);

        $mform->setType('testdescription', PARAM_TEXT);
        $mform->setType('testtitle', PARAM_TEXT);
        $mform->setType('testweight', PARAM_FLOAT);
        $mform->setType('testid', PARAM_RAW);
        $mform->setType('testtype', PARAM_RAW);
        // Add tests with button for adding tests.
        $buttonlabel = get_string('addtest', 'qtype_proforma', $this->_unittestlabel);
        $questioneditform->repeat_elements($repeatarray, $repeats,
            $repeatoptions, 'option_repeats', 'option_add_fields',
            1, $buttonlabel, true);

        if ($this->_taskhandler->can_be_edited()) {
            // Set CodeMirror for unit test code.
            for ($i = 0; $i < $repeats; $i++) {
                qtype_proforma\lib\as_codemirror('id_testcode_' . $i);
                /* Here you can add further element handling that cannot be done in
                 * adjust_test_repeatoptions which is the preferred solution.
                 * This can be done as e.g.
                 * $mform->hideif('testtype[' . $i . ']', 'aggregationstrategy', 'neq', 111);
                 */
            }
        } else {
            // There is no option not to create the button for
            // adding new tests. Therefore the button must be removed.
            $mform->removeElement('option_add_fields');
        }

        return $repeats;
    }

    /**
     * Add compilation options.
     */
    protected function add_compilation($label) {
        $mform = $this->_form;
        $compilegroup = array();
        $compilegroup[] = & $mform->createElement('advcheckbox', 'compile', '', '');
        $this->add_test_weight_option($compilegroup, 'compile', '0');
        $mform->addGroup($compilegroup, 'compilegroup', $label, ' ', false);
        $mform->addGroupRule('compilegroup', array(
            'compileweight' => array(array(get_string('err_numeric', 'form'), 'numeric', '', 'client'))));
        $mform->hideIf('compileweight', 'compile');
        // Compilation is done in all tests. So we do not need to again
        $mform->setDefault('compile', 0);
    }

    /**
     * get number of unit tests for repeat group
     * @param $question
     * @return int
     */
    protected function get_count_tests($question) {
        $repeats = 0;
        // Get number of unit tests from (lms) grading hints.
        // In case of an imported task this ist the number of all tests (not just unit tests).
        if (isset($question) && isset($question->options) && isset($question->options->gradinghints)) {
            $repeats = $this->_taskhandler->get_count_unit_tests($question->options->gradinghints);
        }

        // In case of manually added unit tests we need to know how many tests are actually present:
        // (unfortunately there is no function to get this from Moodle core).
        $currentrepeats = optional_param('option_repeats', 1, PARAM_INT);
        $addfields = optional_param('option_add_fields', '', PARAM_TEXT);
        if (!empty($addfields)) {
            $currentrepeats += 1;
        }
        if ($currentrepeats > $repeats) {
            $repeats = $currentrepeats;
        }

        return $repeats;
    }

    /**
     * Add response options for editor
     *
     * @param type $qtype
     */
    protected function add_editor_options($qtype) {
        $mform = $this->_form;
        $mform->addElement('select', 'responsefieldlines',
        get_string('responsefieldlines', 'qtype_proforma'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);
        $mform->hideIf('responsefieldlines', 'responseformat', 'neq', 'editor');

        // Programming Language.
        $mform->addElement('select', 'programminglanguage',
        get_string('highlight', 'qtype_proforma'), $qtype->get_proglang_options());
        $mform->addHelpButton('programminglanguage', 'highlight_hint', 'qtype_proforma');
        $mform->setDefault('programminglanguage', $this->_syntaxhighlighting);
        // Show only if response format is editor.
        $mform->hideIf('programminglanguage', 'responseformat', 'neq', 'editor');
        // Response filename.
        $this->add_responsefilename();
    }

    /**
     * Add response options.
     *
     * @param $question
     * @param $qtype
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function add_response_options($question, $qtype) {
        global $CFG, $COURSE;
        $mform = $this->_form;

        // $defaultmaxsubmissionsizebytes = get_config('maxsubmissionsizebytes');
        // $defaultfiletypes = (string)get_config('filetypeslist');
        //
        // Header.
        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_proforma'));
        $mform->setExpanded('responseoptions');

        // Create select if there is more than one format available.
        switch (count($this->_responseformats)) {
            case 0:
                break;
            case 1:
                $firstelement = reset($this->_responseformats);
                $mform->addElement('hidden', 'responseformat', $firstelement);
                $mform->setType('responseformat', PARAM_RAW);
                break;
            default:
                $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_proforma'), $this->_responseformats);
                break;
        }

        // Editor options.
        if (array_key_exists(qtype_proforma::RESPONSE_EDITOR, $this->_responseformats)) {
            $mform->setDefault('responseformat', 'editor');
            if ($this->_responseformats) {
                $this->add_editor_options($qtype);
            }
        }

        // Filepicker options.
        if (array_key_exists(qtype_proforma::RESPONSE_FILEPICKER, $this->_responseformats) or
            array_key_exists(qtype_proforma::RESPONSE_EXPLORER, $this->_responseformats)) {
            // Also for Explorer/IDE.

            $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes,
            get_config('qtype_proforma', 'maxbytes'));

            $name1 = get_string('maximumsubmissionsize', 'qtype_proforma');
            $name2 = get_string('acceptedfiletypes', 'qtype_proforma');

            $filepickeroptions = array();
            $filepickeroptions[] = $mform->createElement('select', 'attachments',
            get_string('allowattachments', 'qtype_proforma'), $qtype->attachment_options());
            $filepickeroptions[] = $mform->createElement('select', 'maxbytes', $name1, $choices);
            $filepickeroptions[] = $mform->createElement('text', 'filetypes', $name2);
            $mform->addGroup($filepickeroptions, 'filepickergroup',
                get_string('filepickeroptions', 'qtype_proforma'), array(' '), false);
            $mform->hideIf('filepickergroup', 'responseformat', 'eq', 'editor');
            $mform->hideIf('filepickergroup', 'responseformat', 'eq', 'versioncontrol');
            $mform->hideIf('attachments', 'responseformat', 'eq', 'explorer');
            $mform->hideIf('filetypes', 'responseformat', 'eq', 'explorer');
            $mform->addHelpButton('filepickergroup', 'acceptedfiletypes', 'qtype_proforma');
            $mform->setType('filetypes', PARAM_RAW);
        }

        // Version control options.
        if (array_key_exists(qtype_proforma::RESPONSE_VERSION_CONTROL, $this->_responseformats)) {
            $mform->addElement('text', 'vcsuritemplate',
                get_string('vcsuritemplate', 'qtype_proforma'), array('size' => '80'));
            $mform->setDefault('vcsuritemplate', get_config('qtype_proforma', 'defaultvcsuri'));
            $mform->setType('vcsuritemplate', PARAM_TEXT);
            $mform->addHelpButton('vcsuritemplate', 'vcsuritemplate', 'qtype_proforma');
            $mform->hideIf('vcsuritemplate', 'responseformat', 'neq', 'versioncontrol');

            $mform->addElement('text', 'vcslabel', get_string('vcslabel', 'qtype_proforma'), array('size' => '20'));
            $mform->setDefault('vcslabel', get_config('qtype_proforma', 'vcslabeldefault'));
            $mform->setType('vcslabel', PARAM_TEXT);
            $mform->addHelpButton('vcslabel', 'vcslabel', 'qtype_proforma');
            // Hide label if not used. Done with JavaScript.
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/formhelper', 'showif', array('id_vcslabel',
                'id_vcsuritemplate', '{input}', 'id_responseformat', 'versioncontrol'));
        }

        // Response template.
        $this->add_responsetemplate($question);
        // Model solution.
        $this->add_modelsolution();
    }

    /**
     * Add test settings.
     *
     * @param $question
     * @param $questioneditform
     */
    public function add_test_settings($question, $questioneditform) {
        $mform = $this->_form;

        // Header.
        $mform->addElement('header', 'test_header', get_string('tests', 'qtype_proforma'));
        $mform->setExpanded('test_header');

        // Aggreagation strategy.
        $aggregationstrategy = array(
            qtype_proforma::ALL_OR_NOTHING => get_string('all_or_nothing', 'qtype_proforma'),
            qtype_proforma::WEIGHTED_SUM => get_string('weighted_sum', 'qtype_proforma')
        );
        $mform->addElement('select', 'aggregationstrategy',
            get_string('aggregationstrategy', 'qtype_proforma'), $aggregationstrategy);
        $mform->addHelpButton('aggregationstrategy', 'aggregationstrategy', 'qtype_proforma');
        $mform->setDefault('aggregationstrategy', qtype_proforma::WEIGHTED_SUM);

        // Tests.
        // - test overview in case of imported task and
        // - test edit fields for tasks created with Moodle.
        $this->add_tests($question, $questioneditform);

        // Penalty.
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

    public function add_feedback_options($question, $questioneditform) {
        $mform = $this->_form;

        // Header.
        $mform->addElement('header', 'feedbackoptions', get_string('feedbackoptions_heading', 'qtype_proforma'));

        // Collapse/Expand.
        $collapse = array(
            qtype_proforma::ALWAYS_COLLPASE => get_string('always_collapse', 'qtype_proforma'),
            qtype_proforma::ALWAYS_EXPAND => get_string('always_expand', 'qtype_proforma'),
            /* Maybe for future use:
            qtype_proforma::EXPAND_STUDENT => get_string('expand_student', 'qtype_proforma'),
            qtype_proforma::EXPAND_TEACHER => get_string('expand_teacher', 'qtype_proforma'),
            qtype_proforma::EXPAND_SMALL => get_string('expand_small', 'qtype_proforma'),
             */
        );
        $mform->addElement('select', 'expandcollapse',
            get_string('collapse', 'qtype_proforma'), $collapse);
        $mform->addHelpButton('expandcollapse', 'collapse', 'qtype_proforma');
        $mform->setDefault('expandcollapse', get_config('qtype_proforma', 'expandcollapse'));

        $embedmessageoptions = array();
        // Switch on/off.
        $embedmessageoptions[] = $mform->createElement('advcheckbox', 'inlinemessages',
            '', null, array(0, 1));
        // Initial state.
        // $embedmessageoptions[] = $mform->createElement('advcheckbox', 'initiallyembedded',
        // get_string('initiallyembedded', 'qtype_proforma'), null, array(0, 1));
        // Disable initial state if feature is not used.
        // $mform->disabledIf('initiallyembedded', 'inlinemessages', 'neq', '1');
        // $mform->setDefault('initiallyembedded', get_config('qtype_proforma', 'initiallyembedded'));

        $mform->addElement('group', 'embed', get_string('inlinemessages', 'qtype_proforma'), $embedmessageoptions, null, false);
        $mform->addHelpButton('embed', 'inlinemessages', 'qtype_proforma');
        // Editor embedded messages (group).
        if (!get_config('qtype_proforma', 'usecodemirror')) {
            $mform->addElement('static', 'nocodemirror', '', get_string('nocodemirror', 'qtype_proforma'));
        }

        // Disable group if editor is not selected.
        $mform->disabledIf('embed', 'responseformat', 'neq', 'editor');
        $mform->setDefault('inlinemessages', get_config('qtype_proforma', 'inlinemessages'));
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
            // New question!
            // Preset all fields that can be disabled in the form. Otherwise they may be missing
            // somewhere! (resulting in an exception).
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

        // Prepare all fileareas.
        foreach (qtype_proforma::proforma_fileareas() as $fileareaname => $value) {
            // Create draft area.
            $filearea = new qtype_proforma_filearea($fileareaname);
            $filearea->prepare_draft($editor->context->id, $question);
            // Create stringlist variable.
            if (isset($value['formlist']) && !empty($question->id)) {
                $property1 = $value['formlist'];
                $question->$property1 = $filearea->get_files_as_stringlist($cat, $question->id);
            }
        }

        // Special handling for comment.
        $draftid = file_get_submitted_draft_itemid(qtype_proforma::FILEAREA_COMMENT);
        $question->comment = array();
        $question->comment['text'] = file_prepare_draft_area(
            $draftid, // Draftid.
            $editor->context->id, // Context id.
            'qtype_proforma', // Component.
            qtype_proforma::FILEAREA_COMMENT, // Filarea.
            !empty($question->id) ? (int) $question->id : null, // Item id.
            $editor->fileoptions, // Options.
            $commenttext
        );
        $question->comment['format'] = $commentformat;
        $question->comment['itemid'] = $draftid;

        // Create task link from actual task.
        if (!empty($question->id)) {
            $task = new qtype_proforma_filearea(qtype_proforma::FILEAREA_TASK);
            $question->link = $task->get_files_as_links($cat, $question->id);
        }
        $this->preset_formdata($question, $cat, $editor);
    }

    /**
     * Preset form data for normal questions (not imported)
     * @param $question
     * @param $cat
     * @param qtype_proforma_edit_form $editor
     * @return void
     * @throws coding_exception
     */
    protected function preset_formdata(&$question, $cat, qtype_proforma_edit_form $editor) {
        if (isset($question->id)) {
            // Preset data if question already exists.
            $form = $editor->get_form();

            if ($question->taskstorage != $this->_tasktype) {
                throw new coding_exception('invalid taskstorage value ' . $question->taskstorage);
            }
            $this->_taskhandler->extract_formdata_from_taskfile($cat, $question);
            $this->_taskhandler->extract_formdata_from_gradinghints($question, $form);

            // testcode format is set from default for existing questions
            $count = count($question->testid);
            for ($key = 0; $key < $count; $key++) {
                // We need to delete the default values for the testcodeformat
                // for all existing tests in order to prevent Moodle
                // from using the default value instead of the value read from task file.
                unset($form->_defaultValues["testcodeformat[{$key}]"]);
            }

            // Model solution files can be uploaded with a file manager
            // or entered as text in editor.
            $msfilearea = new qtype_proforma_filearea(self::MODELSOLMANAGER);
            $files = $msfilearea->get_files($editor->context->id, $question->id);
            if (count($files) === 1) {
                $question->modelsolution = $files[0]->get_content();
            }
        }
    }

    // Helper functions.

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
        $mform = $this->_form;
        if (isset($this->question->options->$field)) {
            $size = $question->options->$field;
        } else if (isset($this->question->$field)) {
            $size = $question->options->$field;
        }
        $mform->addElement('text', $field, $label, array('size' => $size));
        $mform->disabledIf($field, 'responseformat', 'neq', 'alwaysdisabled');
        $mform->setType($field, PARAM_TEXT);
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
        $mform = $this->_form;
        if (isset($sizefield)) {
            if (isset($this->question->options->$sizefield)) {
                $attributes = array('size' => strlen($question->options->$sizefield));
            } else if (isset($this->question->$sizefield)) {
                $attributes = array('size' => strlen($question->$sizefield));
            }
        }

        if (isset($attributes) && count($attributes) > 0) {
            $mform->addElement('static', $field, $label, $attributes, '');
        } else {
            $mform->addElement('static', $field, $label);
        }

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
        $mform = $this->_form;
        if ($withtitle) {
            // Title.
            $testoptions[] = $mform->createElement('text', $prefix . 'title',
                get_string('testtitle', 'qtype_proforma'), array('size' => 60));
            $mform->setType($prefix . 'title', PARAM_TEXT);
        }

        // Weight.
        $testoptions[] = $mform->createElement('text', $prefix . 'weight',
        get_string('weight', 'qtype_proforma'), array('size' => 2));
        $mform->setType($prefix . 'weight', PARAM_FLOAT);
        $mform->setDefault($prefix . 'weight', $defaultweight);
        $mform->hideIf($prefix . 'weight', 'aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);
    }

    /**
     * handle polymorphic behaviour when saving a question
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        $formdata = $this->_form;
        $context = $formdata->context;

        // Remove beginning and trailing spaces from response filename.
        if (isset($formdata->responsefilename)) {
            $formdata->responsefilename = trim($formdata->responsefilename);
            $options->responsefilename = $formdata->responsefilename;
        }

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
        // TODO: remove redundancy.
        $templfilearea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_TEMPLATE);
        if ($formdata->responseformat == qtype_proforma::RESPONSE_EDITOR) { // Editor.
            $options->templates = $formdata->templates = 'template.txt';
            $templfilearea->save_textfile($context->id, $formdata->id, $options->templates,
            $formdata->responsetemplate);
            if (empty($formdata->responsetemplate)) {
                // Remove templates value.
                $options->templates = $formdata->templates = '';
            }
        } else {
            // Store empty file for filepicker or version control system (= delete file if any).
            $templfilearea->save_textfile($context->id, $formdata->id, 'dummy.txt', '');
            $options->templates = $formdata->templates = '';
        }

        if (!isset($this->_taskhandler)) {
            throw new coding_exception('where is the taskhandler??');
        }
        if (isset($this->_taskhandler)) {
            // Extract grading hints.
            $options->gradinghints = $this->_taskhandler->create_lms_grading_hints($formdata);
            if ($this->_taskhandler->can_be_edited()) {
                if (!isset($formdata->import_process) or !$formdata->import_process) {
                    // When importing a moodle xml question the preprocessing step is missing and
                    // we have no actual form data.
                    // So we must skip creating task because the task.xml already exists
                    // and some data needed to create task.xml does not.
                    // Otherwise we create the task.xml from the input data.
                    $taskfile = $this->_taskhandler->create_task_file($formdata);
                    $options->taskfilename = 'task.xml';
                    qtype_proforma_base_task::store_task_file($taskfile, $options->taskfilename,
                    $formdata->context->id, $formdata->id);
                    if ($formdata->responseformat == qtype_proforma::RESPONSE_EDITOR) { // Editor.
                        // Store model solution text as file.
                        // Property 'modelsolution' exists only if the form editor was used.
                        // So if we come from import we cannot evalute 'modelsolution'.
                        // Filearea object for handling model solution files.
                        $msfilearea = new qtype_proforma_filearea(self::MODELSOLMANAGER);
                        $msfilearea->save_textfile($formdata->context->id, $formdata->id,
                        $formdata->responsefilename, isset($formdata->modelsolution) ? $formdata->modelsolution : '');
                    }
                }
            }
        }
    }
}

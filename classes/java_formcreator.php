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
 * class for creating java question edit forms
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/javatask.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class java_form_creator extends base_form_creator {

    // Property name for model solution manager.
    // Must be name of associated filearea!!.
    const MODELSOLMANAGER = qtype_proforma::FILEAREA_MODELSOL;

    protected $_newquestion = false;

    /**
     * java_form_creator constructor.
     *
     * @param $form
     * @param null $newquestion new question indicator
     */
    public function __construct($form, $newquestion = null) {
        parent::__construct($form);
        if (isset($newquestion) && $newquestion) {
            $this->_newquestion = $newquestion;
        }
    }

    // override

    /**
     * Add hidden fields for question attributes that are not part of the edit form.
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        parent::add_hidden_fields();
        $mform = $this->form;

        $mform->addElement('hidden', 'taskstorage', qtype_proforma::VOLATILE_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);
    }

    /**
     * Add something to select the programming language.
     *
     * @param $question
     */
    public function add_proglang_selection($question) {
        $mform = $this->form;

        $mform->addElement('text', 'proglang',
                get_string('proglang', 'qtype_proforma'), 'Java');
        $mform->disabledIf('proglang', 'responseformat', 'neq', 'alwaysdisabled');
        $mform->setType('proglang', PARAM_TEXT);
        $mform->setDefault('proglang', 'Java');

        /*
        $proglangooptions = array('Java'); // , get_string('other', 'qtype_proforma'));
        $mform->addElement('select', 'proglang',
                get_string('proglang', 'qtype_proforma'), $proglangooptions);
        $mform->addHelpButton('proglang', 'proglang_hint', 'qtype_proforma');
        $mform->setDefault('proglang', 'Java');
        */

        $javaversion = get_config('qtype_proforma', 'javaversion');
        $proglangversions = array();
        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            $proglangversions[0] = get_string('choose');
        }
        foreach (explode(',', $javaversion) as $version) {
            $proglangversions[trim($version)] = trim($version);
        }
        $mform->addElement('select', 'proglangversion',
                get_string('proglangversion', 'qtype_proforma'), $proglangversions);
        $mform->addHelpButton('proglangversion', 'proglangversion_hint', 'qtype_proforma');

        $mform->addRule('proglangversion', get_string('error'), 'nonzero', null, 'client', false, false);
    }

    /**
     * Add model solution as edit field for editor response format or
     * as fielmanager for filepicker response format.
     *
     * @param $question
     */
    public function add_modelsolution($question) {
        $mform = $this->form;
        // Model Solution files
        $mform->addElement('textarea', 'modelsolution', get_string('modelsolution', 'qtype_proforma'), 'rows="20" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma\lib\as_codemirror('id_modelsolution', 'java');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                    array('id_programminglanguage', 'id_modelsolution'));
        }
        $mform->addHelpButton('modelsolution', 'modelsolution', 'qtype_proforma');
        $mform->hideIf('modelsolution', 'responseformat', 'neq', 'editor');

        // Add Filemanager for model solution in case of using the filepicker.
        // Remove hidden element in base class.
        $mform->removeElement(self::MODELSOLMANAGER);
        $mform->addElement('filemanager', self::MODELSOLMANAGER, get_string('modelsolfiles', 'qtype_proforma'), null,
                array('subdirs' => 0));

        $mform->hideIf(self::MODELSOLMANAGER, 'responseformat', 'neq', 'filepicker');
    }


    /**
     * Add grader options/information.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
        if (qtype_proforma\lib\is_admin()) {
            // allow admin to see the created task.xml (for debugging purposes)
            parent::add_grader_settings($question);
            // ProFormA fields
            $mform = $this->form;
            $mform->addHelpButton('link', 'createdtask_hint', 'qtype_proforma');
        }
    }

    /**
     * Get test label for add_tests.
     *
     * @return string label of JUnit tests
     */
    protected function get_test_label() {
        return get_string('junittestlabel', 'qtype_proforma'); // use different label
    }

    /**
     * Modify repeatarray in add_tests: add editor for testcode
     *
     * @param $repeatarray
     */
    protected function modify_test_repeatarray(&$repeatarray) {
        $mform = $this->form;
        // Add textarea for unit test code.
        $repeatarray[] = $mform->createElement('textarea', 'testcode', '' , 'rows="20" cols="80"');
    }

    /**
     * Modify testoptions in add_tests: add Junit version
     *
     * @param $testoptions
     */
    protected function modify_test_testoptions(&$testoptions) {
        $mform = $this->form;
        $csversion = get_config('qtype_proforma', 'junitversion');
        $versions = array();
        // force PHP to use strings as key even if the first key is an integer
        $obj = new stdClass;

        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            //$versions[] = get_string('choose');
            $obj->{'0'} = get_string('choose');
        }
        foreach (explode(',', $csversion) as $version) {
            $strversion = trim($version);
            //$versions[$strversion] = $strversion;
            $obj->{$strversion} = $strversion;
        }
        $versions = (array) $obj;

        //debugging('Testversionen: ' . count($versions) . ' ' . $versions[0] . ' ' . var_dump($versions));

        $testoptions[] = $mform->createElement('select', 'testversion',
                get_string('version', 'qtype_proforma'), $versions);

    }

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function modify_test_repeatoptions(&$repeatoptions) {
    }

    /**
     * Add compilation options.
     */
    private function add_compilation() {
        $mform = $this->form;
        $compilegroup = array();
        $compilegroup[] =& $mform->createElement('advcheckbox', 'compile', '', '');
        $this->add_test_weight_option($compilegroup, 'compile', '0');
        $mform->addGroup($compilegroup, 'compilegroup', get_string('compile', 'qtype_proforma'), ' ', false);
        $mform->addGroupRule('compilegroup', array(
                'compileweight' => array(array(get_string('err_numeric', 'form'), 'numeric', '', 'client'))));
        $mform->hideIf('compileweight', 'compile');
        $mform->setDefault('compile', 1);
    }

    /**
     * Add Checkstyle options.
     */
    private function add_checkstyle() {
        $mform = $this->form;
        // Create a Checkstyle test (not part of the repeat group).
        $testoptions = array();
        // Add checkbox.
        $testoptions[] =& $mform->createElement('advcheckbox', 'checkstyle', '', '');
        // Add Checkstyle version.
        $csversion = get_config('qtype_proforma', 'checkstyleversion');
        $versions = array();
        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            $versions[0] = get_string('choose');
        }
        foreach (explode(',', $csversion) as $version) {
            $versions[trim($version)] = trim($version);
        }
        $testoptions[] =& $mform->createElement('select', 'checkstyleversion',
                get_string('version', 'qtype_proforma'), $versions);
        // Add weight.
        $this->add_test_weight_option($testoptions, 'checkstyle', '0.2');
        $mform->addGroup($testoptions, 'checkstyleoptions', 'Checkstyle',
                array(' '), false);
        $mform->addGroupRule('checkstyleoptions', array(
                'checkstyleweight' => array(array(get_string('err_numeric', 'form'), 'numeric', '', 'client'))));
        // is checked even if checkstyle is not visible!
        // $mform->addGroupRule('checkstyleoptions', array(
        //        'checkstyleversion' => array(array(get_string('error'), 'nonzero', '', 'client'))));
        // Add textarea.
        $mform->addElement('textarea', 'checkstylecode', '', 'rows="20" cols="80"');
        qtype_proforma\lib\as_codemirror('id_checkstylecode', 'xml');
        $mform->hideIf('checkstyleversion', 'checkstyle');
        $mform->hideIf('checkstyleweight', 'checkstyle');
        $mform->hideIf('checkstylecode', 'checkstyle');
        // cannot use required rule because rule is checked even if control is hidden :-(
        // $mform->addRule('checkstylecode', null, 'required', '', 'client', false, false);
    }

    /**
     * returns the number of tests. Since the user can add tests the hidden
     * count field in the html output is also considered.
     *
     * @param $question
     * @return int|mixed
     */
    protected function get_count_tests($question) {
        $repeats = parent::get_count_tests($question);

        // In case of manually added unit tests we need to know how many tests are actually present:
        // (unfortunately there is no function to get this from Moodle core)
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
     * add Java specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    public function add_tests($question, $questioneditform) {
        $mform = $this->form;
        $this->taskhandler = new qtype_proforma_java_task();
        // add compilation
        $this->add_compilation();
        // add JUnit
        $repeats = parent::add_tests($question, $questioneditform);
        // Set CodeMirror for unit test code.
        for ($i = 0; $i < $repeats; $i++) {
            qtype_proforma\lib\as_codemirror('id_testcode_' . $i);
            // Hide testtype and test identifier for unit tests.
            // So far (Moodle 3.6) hideif is not implemented for groups => quickhack.
            // (needed from creating grading hints)
            $mform->hideif('testtype[' . $i . ']', 'aggregationstrategy', 'neq', 111);
            $mform->hideif('testid[' . $i . ']', 'aggregationstrategy', 'neq', 111);
            // does not work
            // $repeatoptions['testtitle']['rule'] = 'required'; // array(null, 'required', null, 'client');
            // $repeatoptions['testweight']['rule'] = 'required'; // array(get_string('err_numeric', 'form'), 'numeric', '', 'client');
        }

        // add checkstyle
        $this->add_checkstyle();
        // $this->form->addGroupRule('testoptions', array(
        // 'testversion' => array(array(get_string('error'), 'nonzero', '', 'client'))));
        return $repeats;
    }

    /**
     * Validate form fields.
     *
     * @param Validation $fromform
     * @param Validation $files
     * @param array $errors
     * @return array
     */
    public function validation($fromform, $files, $errors) {
        $errors = parent::validation($fromform, $files, $errors);
        if ($fromform["checkstyle"]) {
            // Check Checkstyle values:
            if (0 == strlen(trim($fromform["checkstylecode"]))) {
                // Checkstyle code muse not be empty.
                // $errors['checkstylecode'] = get_string('required');
                $errors['checkstylecode'] = get_string('codeempty', 'qtype_proforma');
            }
            if (0 == $fromform["checkstyleversion"]) {
                // Unsupported version and no new choice.
                $errors['checkstyleoptions'] = get_string('versionrequired', 'qtype_proforma');
            }
        }

        // Check Junit tests:
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            $title = $fromform["testtitle"][$i];
            $code = $fromform["testcode"][$i];
            $lencode = strlen(trim($code));
            $lentitle = strlen(trim($title));
            if (0 < $lentitle and 0 == $lencode) {
                // Title is set but code is missing.
                $errors['testcode['.$i.']'] = get_string('codeempty', 'qtype_proforma');
            } else if (0 == $lentitle and 0 < $lencode) {
                // Title is missing
                // error message must be attached to testoptions group
                // $errors['testweight['.$i.']'] = get_string('titleempty', 'qtype_proforma');
                $errors['testoptions['.$i.']'] = get_string('titleempty', 'qtype_proforma');
            } else if ($lencode > 0 and $lentitle > 0) {
                // check classname
                if (!qtype_proforma_java_task::get_java_file($code)) {
                    $errors['testcode['.$i.']'] = get_string('filenameerror', 'qtype_proforma');
                } else if (!qtype_proforma_java_task::get_java_entrypoint($code)) {
                    $errors['testcode['.$i.']'] = get_string('entrypointerror', 'qtype_proforma');
                }
            }
            if (0 == $fromform["testversion"][$i]) {
                // Unsupported version and no new choice.
                $errors['testoptions['.$i.']'] = get_string('versionrequired', 'qtype_proforma');
            }
        }

        if ($fromform["responseformat"] == 'editor') {
            if (0 == strlen(trim($fromform["responsefilename"]))) {
                $errors['responsefilename'] = get_string('required');
            }
            if (0 < strlen(trim($fromform["modelsolution"]))) {
                $filename = qtype_proforma_java_task::get_java_file($fromform["modelsolution"]);
                if ($filename != null and trim($filename) != trim($fromform["responsefilename"])) {
                    $errors['responsefilename'] = $filename . ' expected';
                }
            }
        }

        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
            if ($fromform["checkstyle"]) {
                $sumweight += $fromform["checkstyleweight"];
            }
            if ($fromform["compile"]) {
                $sumweight += $fromform["compileweight"];
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
     * Prepare question to fit form field names and values.
     *
     * @param $question
     * @param category $cat
     * @param MoodleQuickForm $form
     * @param qtype_proforma_edit_form $editor
     */
    public function data_preprocessing(&$question, $cat, qtype_proforma_edit_form $editor) {
        parent::data_preprocessing($question, $cat, $editor);

        if (isset($question->id)) {
            // preset data if question already exists
            $form = $editor->get_form();
            $taskfilehandler = new qtype_proforma_java_task();
            $taskfilehandler->extract_formdata_from_taskfile($cat, $question);
            $taskfilehandler->extract_formdata_from_gradinghints($question, $form);

            // Model solution files can be uploaded with a file manager
            // or entered as text in editor.
            $msfilearea = new qtype_proforma_filearea(self::MODELSOLMANAGER);
            $files = $msfilearea->get_files($editor->context->id, $question->id);
            if (count($files) === 1) {
                $question->modelsolution = $files[0]->get_content();
            }
        }
    }

    /**
     * handle polymorphic behaviour when saving a question
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        parent::save_question_options($options);

        $formdata = $this->form;
        $instance = new qtype_proforma_java_task;
        $options->gradinghints = $instance->create_lms_grading_hints($formdata);

        if (!isset($formdata->import_process) or !$formdata->import_process) {
            // When importing a moodle xml question the preprocessing step is missing and
            // we have no actual form data.
            // So we must skip creating task because the task.xml already exists
            // and some data needed to create task.xml does not.

            // Otherwise we create the task.xml from the input data
            $taskfile = $instance->create_task_file($formdata);
            $options->taskfilename = 'task.xml';
            qtype_proforma_proforma_task::store_task_file($taskfile, $options->taskfilename,
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
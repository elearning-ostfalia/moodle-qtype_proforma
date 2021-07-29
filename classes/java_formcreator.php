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
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/java_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

/**
 * Edit form for creating Java questions.
 */
class java_form_creator extends base_form_creator {

    /** Key for Choose option in version selection. */
    const CHOOSE_OPTION = '0';

    /**
     * flag indicates if a new question is to be created. For new questions
     * an invalid cohooser option is preselected.
     * For existing questions the old value is preselected.
     * @var type
     */
    protected $_newquestion = false;


    /**
     * java_form_creator constructor.
     * @param type $form form instance OR formdata
     * @param bool $newquestion new question indicator
     */
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, new qtype_proforma_java_task());
        // Set parent options.
        $this->_syntaxhighlighting = 'java';
        $this->_proglang = 'Java';
        $this->_responseformats = qtype_proforma::response_formats();
        $this->_entrypointlabel = get_string('entrypoint', 'qtype_proforma');
        $this->_entrypoint = true;
        // $this->_evalentrypoint = true;
        $this->_taskType = qtype_proforma::JAVA_TASKFILE;
        $this->_unittestlabel = get_string('junit', 'qtype_proforma');

        $this->_newquestion = $newquestion;
    }

    // Override.

    /**
     * Add something to select the programming language.
     */
    public function add_proglang_selection() {
        parent::add_proglang_selection();

        $mform = $this->_form;
        $javaversions = get_config('qtype_proforma', 'javaversion');
        $proglangversions = array();
        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            $proglangversions[self::CHOOSE_OPTION] = get_string('choose');
        }
        foreach (explode(',', $javaversions) as $version) {
            $proglangversions[trim($version)] = trim($version);
        }
        $mform->addElement('select', 'proglangversion',
                get_string('proglangversion', 'qtype_proforma'), $proglangversions);
        $mform->addHelpButton('proglangversion', 'proglangversion_hint', 'qtype_proforma');

        $mform->addRule('proglangversion', get_string('error'), 'nonzero', null, 'client', false, false);
    }

    /**
     * Modify repeatarray in add_tests: add editor for testcode
     *
     * @param $repeatarray
     */
    /*
    protected function adjust_test_repeatarray(&$repeatarray) {
        $mform = $this->_form;

        parent::adjust_test_repeatarray($repeatarray);
        // Add Unit test entry point.
        $repeatarray[] = $mform->createElement('text', 'testentrypoint',
            get_string('entrypoint', 'qtype_proforma'), array('size' => 80));
    }
    */

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function adjust_test_repeatoptions(&$repeatoptions) {
        parent::adjust_test_repeatoptions($repeatoptions);

        $repeatoptions['testentrypoint']['hideif'] = array('testcodeformat', 'eq', self::TESTCODE_EDITOR);
        /*if ($this->_newquestion) {
            $repeatoptions['testcodeformat']['default'] = self::TESTCODE_EDITOR;
        }*/
    }

    /**
     * Modify testoptions in add_tests: add Junit version
     *
     * @param $testoptions
     */
    protected function adjust_test_testoptions(&$testoptions) {
        $mform = $this->_form;
        $csversion = get_config('qtype_proforma', 'junitversion');
        $versions = array();
        // Force PHP to use strings as key even if the first key is an integer.
        $obj = new stdClass;

        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            $obj->{self::CHOOSE_OPTION} = get_string('choose');
        }
        foreach (explode(',', $csversion) as $version) {
            $strversion = trim($version);
            $obj->{$strversion} = $strversion;
        }
        $versions = (array) $obj;

        $testoptions[] = $mform->createElement('select', 'testversion',
                get_string('version', 'qtype_proforma'), $versions);
    }


    /**
     * Add Checkstyle options.
     */
    private function add_checkstyle() {
        $mform = $this->_form;
        // Create a Checkstyle test (not part of the repeat group).
        $testoptions = array();
        // Add checkbox.
        $testoptions[] =& $mform->createElement('advcheckbox', 'checkstyle', '', '');
        // Add Checkstyle versions.
        $csversion = get_config('qtype_proforma', 'checkstyleversion');
        $versions = array();
        if (!$this->_newquestion) {
            // In order to handle invalid values we add a new option with value 0 (= invalid) as the first one.
            // In case no other value can be selected this is chosen by default.
            $versions[self::CHOOSE_OPTION] = get_string('choose');
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
        // Add textarea.
        $mform->addElement('textarea', 'checkstylecode', '', 'rows="20" cols="80"');
        qtype_proforma\lib\as_codemirror('id_checkstylecode', 'xml');
        $mform->hideIf('checkstyleversion', 'checkstyle');
        $mform->hideIf('checkstyleweight', 'checkstyle');
        $mform->hideIf('checkstylecode', 'checkstyle');
        // Cannot use required rule because rule is checked even if control is hidden.
    }

    /**
     * add Java specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        // Add compilation.
        $this->add_compilation(get_string('compile', 'qtype_proforma'));
        // Add JUnit.
        $repeats = $this->add_test_fields($question, $questioneditform, 'unittest');
        // Add checkstyle.
        $this->add_checkstyle();
        return $repeats;
    }

    /**
     * validate JUnit test input
     *
     * @param qtype_proforma_edit_form $editor main editor instance
     * @param type $fromform  input data
     * @param type $files file array
     * @param type $i index
     * @param type $errors errors array
     * @return type
     * @throws coding_exception
     */
    private function _validate_junit(qtype_proforma_edit_form $editor, $fromform, $files, $i, $errors) {
        list($errors, $valid) = $this->validate_unittest($editor, $fromform, $files, $i, $errors);
        if ($valid) {
            if (0 == $fromform["testversion"][$i]) {
                // Unsupported version and no new choice.
                $errors['testoptions['.$i.']'] = get_string('versionrequired', 'qtype_proforma');
            }
            $format = $fromform["testcodeformat"][$i];
            switch ($format) {
                case self::TESTCODE_EDITOR: // Editor.
                    $code = $fromform["testcode"][$i];
                    if (!qtype_proforma_java_task::get_java_file($code)) {
                        // Cannot determine filename from test code.
                        $errors['testcode['.$i.']'] = get_string('filenameerror', 'qtype_proforma');
                    } else if (!qtype_proforma_java_task::get_java_entrypoint($code)) {
                        // Cannot determine entrypoint from test code.
                        $errors['testcode['.$i.']'] = get_string('entrypointerror', 'qtype_proforma');
                    }
                    break;
                case self::TESTCODE_FILES: // Filemanager.
                    $entrypoint = $fromform["testentrypoint"][$i];
                    if (0 == strlen(trim($entrypoint))) {
                        // Entrypoint missing.
                        $errors['testentrypoint['.$i.']'] = get_string('entrypointrequired', 'qtype_proforma');
                    }
                    break;
                default:
                    throw new coding_exception('unexpected value ' . $format);
            }
        }

        return $errors;
    }


    /**
     * Validate form fields.
     *
     * @param qtype_proforma_edit_form $editor actual editor instance
     * @param Validation $fromform
     * @param Validation $files
     * @param array $errors
     * @return array
     */
    public function validation(qtype_proforma_edit_form &$editor, $fromform, $files, $errors) {
        $errors = parent::validation($editor, $fromform, $files, $errors);
        if ($fromform["checkstyle"]) {
            // Check Checkstyle values.
            if (0 == strlen(trim($fromform["checkstylecode"]))) {
                // Checkstyle code muse not be empty.
                $errors['checkstylecode'] = get_string('codeempty', 'qtype_proforma');
            }
            if (0 == $fromform["checkstyleversion"]) {
                // Unsupported version and no new choice.
                $errors['checkstyleoptions'] = get_string('versionrequired', 'qtype_proforma');
            }
        }

        // Check Junit tests.
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            $errors = $this->_validate_junit($editor, $fromform, $files, $i, $errors);
        }
        if (isset($fromform["responseformat"]) and $fromform["responseformat"] == 'editor') {
            // Check response filename.
            if (0 < strlen(trim($fromform["modelsolution"]))) {
                $filename = qtype_proforma_java_task::get_java_file($fromform["modelsolution"]);
                if ($filename != null and trim($filename) != trim($fromform["responsefilename"])) {
                    $errors['responsefilename'] = $filename . ' expected';
                }
            }
        }

        // Sum of weights must be > 0.
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
                // Error message must be attached to testoptions group
                // otherwise it is not visible.
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
            // Preset data if question already exists.
            $form = $editor->get_form();

            if ($question->taskstorage != qtype_proforma::JAVA_TASKFILE) {
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

    /**
     * checks if the first option can be removed in unit test version
     *
     * @param type $versionelement
     * @return boolean
     */
    protected function remove_choose_option($versionelement) {
        if (!isset($versionelement)) {
            debugging('version element not found');
            return false;
        }
        // Get options.
        $options = $versionelement->_options;
        if (!isset($options) or !is_array($options) or count($options) <= 1) {
            debugging('no options found');
            return false;
        }

        $firstoption = array_shift($options);
        if ($firstoption['text'] != get_string('choose')) {
            // Choose option is already removed => nothing to be done.
            return false;
        }
        // Get current selection.
        $value = $versionelement->getValue();
        if (!isset($value)) {
            // No selection => first option can be removed.
            return true;
        }

        if (is_array($value) and count($value) == 1) {
            $selection = $value[0];
            if ($selection != get_string('choose')) {
                // If 'choose' option is NOT selected then first option can be removed.
                return true;
            }
            // If 'choose' option is selected then do NOT remove first option.
        }

        // Default.
        return false;
    }

    /**
     * Do form definitions things that need to be done when data is set
     */
    public function definition_after_data() {
        // debugging('definition_after_data');
        $i = 0;
        while ($this->_form->elementExists('testcodearray[' . $i . ']')) {
            $group = &$this->_form->getElement('testcodearray[' . $i . ']');
            $elements = &$group->getElements();
            // debugging('testcodearray');
            // var_dump($elements);
            // $testcodeformat = &$this->_form->getElement('testcodeformat[' . $i . ']');
            $i ++;
        }
         // Try and remove 'Choose' option from JUnit version field.
        $i = 0;
        while ($this->_form->elementExists('testoptions[' . $i . ']')) {
            $group = &$this->_form->getElement('testoptions[' . $i . ']');
            $elements = &$group->getElements();
            // Find element with version.
            // There seems to be no simple solution for finding a field.
            foreach ($elements as $element) {
                // Find version element.
                if ($element->getName() == 'testversion[' . $i . ']') {
                    $versionelement = $element;
                    break;
                }
            }

            if ($this->remove_choose_option($versionelement)) {
                $options = $versionelement->_options;
                $firstoption = array_shift($options);
                $versionelement->_options = $options;
            }
            $i ++;
        }
    }
}
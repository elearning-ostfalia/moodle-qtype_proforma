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
require_once($CFG->dirroot . '/question/type/proforma/classes/java_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class java_form_creator extends base_form_creator {

    protected $_newquestion = false;

    /**
     * java_form_creator constructor.
     *
     * @param $form
     * @param bool $newquestion new question indicator
     */
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, new qtype_proforma_java_task(),
            qtype_proforma::response_formats(), 'java', 'Java');
        $this->_newquestion = $newquestion;
    }

    // Override.

    /**
     * the numeric type of task
     */
    public function get_task_storage() {
        return qtype_proforma::JAVA_TASKFILE;
    }

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
            $proglangversions[0] = get_string('choose');
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
     * Add grader options/information.
     *
     * @param $question
     */
    public function add_grader_settings($question, $context) {
        if (qtype_proforma\lib\can_view_systeminfo($context->id)) {
            // Allow admin to see the created task.xml (for debugging purposes).
            parent::add_grader_settings($question, $context);
            // ProFormA fields.
            $mform = $this->_form;
            $mform->addHelpButton('link', 'createdtask_hint', 'qtype_proforma');
        }
    }

    /**
     * Get test label for add_tests.
     *
     * @return string label of JUnit tests
     */
    protected function get_test_label() {
        return get_string('junit', 'qtype_proforma');
    }

    /**
     * Modify repeatarray in add_tests: add editor for testcode
     *
     * @param $repeatarray
     */
    protected function adjust_test_repeatarray(&$repeatarray) {
        $mform = $this->_form;

        parent::adjust_test_repeatarray($repeatarray);
        // Add JUnit entry point.
        $testfileoptions = array();
        $testfileoptions[] = $mform->createElement('text', 'testentrypoint',
            get_string('entrypoint', 'qtype_proforma'), array('size' => 50));
        $repeatarray[] = $mform->createElement('group', 'testfileoptions', '', $testfileoptions, null, false);
    }

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function adjust_test_repeatoptions(&$repeatoptions) {
        parent::adjust_test_repeatoptions($repeatoptions);

        $repeatoptions['testentrypoint']['hideif'] = array('testcodeformat', 'eq', self::EDITORTESTINPUT);
        $this->_form->setType('testentrypoint', PARAM_TEXT);
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
            $obj->{'0'} = get_string('choose');
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

    private function _validate_junit(qtype_proforma_edit_form $editor, $fromform, $files, $i, $errors) {
        $title = $fromform["testtitle"][$i];
        $format = $fromform["testcodeformat"][$i];
        $codeavailable = false;
        $titleavailable = strlen(trim($title)) > 0;
        switch ($format) {
            case self::EDITORTESTINPUT: // Editor.
                $code = $fromform["testcode"][$i];
                $codeavailable = (strlen(trim($code)) > 0);
                if ($codeavailable) {
                    if (!qtype_proforma_java_task::get_java_file($code)) {
                        // Cannot determine filename from test code.
                        $errors['testcode['.$i.']'] = get_string('filenameerror', 'qtype_proforma');
                    } else if (!qtype_proforma_java_task::get_java_entrypoint($code)) {
                        // Cannot determine entrypoint from test code.
                        $errors['testcode['.$i.']'] = get_string('entrypointerror', 'qtype_proforma');
                    }
                } else {
                    if ($titleavailable) {
                        // Title is set but code is missing.
                        $errors['testcode['.$i.']'] = get_string('codeempty', 'qtype_proforma');
                    }
                }
                break;
            case self::FILETESTINPUT: // Filemanager.
                global $USER;
                $usercontext = context_user::instance($USER->id);
                $draftitemid = $fromform["testfiles"][$i];
                $fs = get_file_storage();
                $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
                $codeavailable = (count($draftfiles) > 1);
                if ($codeavailable) {
                    $entrypoint = $fromform["testentrypoint"][$i];
                    if (0 == strlen(trim($entrypoint))) {
                        // Entrypoint missing.
                        $errors['testfileoptions['.$i.']'] = get_string('entrypointrequired', 'qtype_proforma');
                    }
                } else {
                    // No test files.
                    if ($titleavailable) {
                        // Title is set but code is missing.
                        $errors['testfiles['.$i.']'] = get_string('codeempty', 'qtype_proforma');
                    }
                }
                break;
            default:
                throw new coding_exception('unexpected value ' . $format);
        }

        if ($codeavailable) {
            if ($titleavailable) {
                // Code and title are set.
                if (0 == $fromform["testversion"][$i]) {
                    // Unsupported version and no new choice.
                    $errors['testoptions['.$i.']'] = get_string('versionrequired', 'qtype_proforma');
                }
            } else {
                // Title is missing:
                // error message must be attached to testoptions group.
                $errors['testoptions['.$i.']'] = get_string('titleempty', 'qtype_proforma');
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
    public function validation(qtype_proforma_edit_form $editor, $fromform, $files, $errors) {
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

        if ($fromform["responseformat"] == 'editor') {
            // Missing response filename.
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

            switch ($question->taskstorage) {
                case qtype_proforma::JAVA_TASKFILE:
                    $this->_taskhandler->extract_formdata_from_taskfile($cat, $question);
                    $this->_taskhandler->extract_formdata_from_gradinghints($question, $form);

                    // Model solution files can be uploaded with a file manager
                    // or entered as text in editor.
                    $msfilearea = new qtype_proforma_filearea(self::MODELSOLMANAGER);
                    $files = $msfilearea->get_files($editor->context->id, $question->id);
                    if (count($files) === 1) {
                        $question->modelsolution = $files[0]->get_content();
                    }
                    break;
                default:
                    throw new coding_exception('invalid taskstorage value ' . $question->taskstorage);
            }
        }
    }
}
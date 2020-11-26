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
     * @param null $newquestion new question indicator
     */
    public function __construct($form, $newquestion = null) {
        parent::__construct($form, new qtype_proforma_java_task(),
            qtype_proforma::response_formats(), 'java', 'Java');
        if (isset($newquestion) && $newquestion) {
            $this->_newquestion = $newquestion;
        }
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
     *
     * @param $question
     */
    public function add_proglang_selection($question) {
        parent::add_proglang_selection($question);

        $mform = $this->_form;
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
     * Add grader options/information.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
        if (qtype_proforma\lib\is_admin()) {
            // Allow admin to see the created task.xml (for debugging purposes).
            parent::add_grader_settings($question);
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
        return get_string('junit', 'qtype_proforma'); // use different label
    }

    /**
     * Modify repeatarray in add_tests: add editor for testcode
     *
     * @param $repeatarray
     */
    protected function modify_test_repeatarray(&$repeatarray) {
        $mform = $this->_form;
        // Add textarea for unit test code.
        $repeatarray[] = $mform->createElement('textarea', 'testcode', '' , 'rows="20" cols="80"');
    }

    /**
     * Modify testoptions in add_tests: add Junit version
     *
     * @param $testoptions
     */
    protected function modify_test_testoptions(&$testoptions) {
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
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    protected function modify_test_repeatoptions(&$repeatoptions) {
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
        // 'checkstyleversion' => array(array(get_string('error'), 'nonzero', '', 'client'))));
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
    public function add_tests($question, $questioneditform) {
        // Add compilation.
        $this->add_compilation(get_string('compile', 'qtype_proforma'));
        // Add JUnit.
        $repeats = $this->add_test_fields($question, $questioneditform, 'unittest');

        // Add checkstyle.
        $this->add_checkstyle();
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
                // error message must be attached to testoptions group.
                $errors['testoptions['.$i.']'] = get_string('titleempty', 'qtype_proforma');
            } else if ($lencode > 0 and $lentitle > 0) {
                // Check classname.
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
/*                case qtype_proforma::SELECT_TASKFILE:
                    // State transition from SELECT to JAVA.
                    $question->taskstorage = qtype_proforma::JAVA_TASKFILE;
                    break;*/
                default:
                    throw new coding_exception('invalid taskstorage value ' . $question->taskstorage);
            }
        }
    }
}
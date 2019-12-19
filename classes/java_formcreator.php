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

class java_form_creator extends base_form_creator {

    /**
     * java_form_creator constructor.
     *
     * @param $form
     */
    public function __construct($form) {
        parent::__construct($form);
    }

    // override
    /**
     * Add something to select the programming language.
     *
     * @param $question
     */
    public function add_proglang_selection($question) {
        $mform = $this->form;
        // create new question
        $mform->addElement('hidden', 'taskstorage', qtype_proforma::VOLATILE_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);

        $proglangooptions = array('Java'); // , get_string('other', 'qtype_proforma'));
        $mform->addElement('select', 'proglang',
                get_string('proglang', 'qtype_proforma'), $proglangooptions);
        $mform->addHelpButton('proglang', 'proglang_hint', 'qtype_proforma');

        $mform->setDefault('proglang', 'Java');
    }

    /**
     * Add response filename (edit field)
     *
     * @param $question
     */
    public function add_responsefilename($question) {
        $mform = $this->form;
        $mform->addElement('text', 'responsefilename', get_string('filename', 'qtype_proforma'), array('size' => '60'));
        // $mform->addRule('responsefilename', null, 'required', null, 'client');
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
            qtype_proforma::as_codemirror('id_modelsolution', 'java');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                    array('id_programminglanguage', 'id_modelsolution'));
        }
        $mform->addHelpButton('modelsolution', 'modelsolution', 'qtype_proforma');
        $mform->hideIf('modelsolution', 'responseformat', 'neq', 'editor');

        $mform->addElement('filemanager', 'modelsolfilemanager', get_string('modelsolfiles', 'qtype_proforma'), null,
                array('subdirs' => 0));

        $mform->hideIf('modelsolfilemanager', 'responseformat', 'neq', 'filepicker');
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
    protected function modify_repeatarray(&$repeatarray) {
        $mform = $this->form;
        // Add textarea for unit test code.
        $repeatarray[] = $mform->createElement('textarea', 'testcode', '' , 'rows="20" cols="80"');
    }

    /**
     * Add compilation options.
     *
     * @param $question
     */
    private function add_compilation($question) {
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
     *
     * @param $question
     */
    private function add_checkstyle($question) {
        $mform = $this->form;
        // Create a Checkstyle test (not part of the repeat group).
        $testoptions = array();
        $testoptions[] =& $mform->createElement('advcheckbox', 'checkstyle', '', '');
        $this->add_test_weight_option($testoptions, 'checkstyle', '0.2');
        $mform->addGroup($testoptions, 'checkstyleoptions', 'Checkstyle',
                array(' '), false);
        $mform->addGroupRule('checkstyleoptions', array(
                'checkstyleweight' => array(array(get_string('err_numeric', 'form'), 'numeric', '', 'client'))));

        $mform->addElement('textarea', 'checkstylecode', '', 'rows="20" cols="80"');
        qtype_proforma::as_codemirror('id_checkstylecode', 'xml');
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
        $this->add_compilation($question);
        // add JUnit
        $repeats = parent::add_tests($question, $questioneditform);
        // Set CodeMirror for unit test code.
        for ($i = 0; $i < $repeats; $i++) {
            qtype_proforma::as_codemirror('id_testcode_' . $i);
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
        $this->add_checkstyle($question);
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
            if (0 == strlen(trim($fromform["checkstylecode"]))) {
                // checkstyle code muse be set
                // $errors['checkstylecode'] = get_string('required');
                $errors['checkstylecode'] = get_string('codeempty', 'qtype_proforma');
            }
        }

        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            $title = $fromform["testtitle"][$i];
            $code = $fromform["testcode"][$i];
            $lencode = strlen(trim($code));
            $lentitle = strlen(trim($title));
            if (0 < $lentitle and 0 == $lencode) {
                // Code is missing.
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
    public function data_preprocessing(&$question, $cat, MoodleQuickForm $form, qtype_proforma_edit_form $editor) {
        parent::data_preprocessing($question, $cat, $form, $editor);

        $taskfilehandler = new qtype_proforma_java_task();
        $taskfilehandler->extract_formdata_from_taskfile($cat, $question);
        $taskfilehandler->extract_formdata_from_gradinghints($question, $form);

        $draftitemid = file_get_submitted_draft_itemid('modelsolfilemanager');
        file_prepare_draft_area($draftitemid, $editor->context->id, 'qtype_proforma', qtype_proforma::FILEAREA_MODELSOL,
                $question->id, array('subdirs' => 0));
        $question->modelsolfilemanager = $draftitemid;
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($editor->context->id, 'qtype_proforma', qtype_proforma::FILEAREA_MODELSOL, $question->id);
        $files = array();
        foreach ($draftfiles as $file) {
            if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                $files[] = $file;
            }
        }
        if (count($files) === 1) {
            $question->modelsolution = $files[0]->get_content();
        }
    }
}
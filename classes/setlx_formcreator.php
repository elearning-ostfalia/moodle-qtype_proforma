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
 * class for creating setlx questions edit forms
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/setlx_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class setlx_form_creator extends base_form_creator {

    /**
     * setlx_form_creator constructor.
     *
     * @param $form
     * @param null $newquestion new question indicator
     */
    public function __construct($form, $newquestion = null) {
        // Only allow editor as reponse format.
        $ro = qtype_proforma::response_formats();        
        $responseoptions = [qtype_proforma::RESPONSE_EDITOR => $ro[qtype_proforma::RESPONSE_EDITOR]];
        
        parent::__construct($form, new qtype_proforma_setlx_task(), $responseoptions, 'setlx');
        echo $this->_taskhandler->create_in_moodle();
    }

    // override

    /**
     * create task class instance belonging to form creator
     */
    protected function create_task_instance() {
        return new qtype_proforma_proforma_task();
    }
    
    
    /**
     * Add hidden fields for question attributes that are not part of the edit form.
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        parent::add_hidden_fields();
        // Store setlx.
        $mform = $this->_form;
        $mform->addElement('hidden', 'taskstorage', qtype_proforma::SETLX_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);
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
        return get_string('setlx', 'qtype_proforma'); // use different label
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
     *  Response filename is fixed to submission.stlx
     * (does not depend on test or submission code)
     */
    protected function add_responsefilename() {
        $mform = $this->_form;
        $mform->addElement('hidden', 'responsefilename', 'submission.stlx');
        $mform->setType('responsefilename', PARAM_RAW);
    }    
 
    /**
     * add SetlX specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    public function add_tests($question, $questioneditform) {
        $mform = $this->_form;
        // $this->_taskhandler = new qtype_proforma_setlx_task();
        // add compilation
        $this->add_compilation(get_string('syntaxcheck', 'qtype_proforma'));
        // add SetlX tests
        return $this->add_test_fields($question, $questioneditform, TRUE, 'setlx');
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
/*        if ($fromform["checkstyle"]) {
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
        }*/

        // Check SetlX tests:
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
/*                // check classname
                if (!qtype_proforma_java_task::get_java_file($code)) {
                    $errors['testcode['.$i.']'] = get_string('filenameerror', 'qtype_proforma');
                } else if (!qtype_proforma_java_task::get_java_entrypoint($code)) {
                    $errors['testcode['.$i.']'] = get_string('entrypointerror', 'qtype_proforma');
                }*/
            }
/*            if (0 == $fromform["testversion"][$i]) {
                // Unsupported version and no new choice.
                $errors['testoptions['.$i.']'] = get_string('versionrequired', 'qtype_proforma');
            }*/
        }

/*        if ($fromform["responseformat"] == 'editor') {
            if (0 == strlen(trim($fromform["responsefilename"]))) {
                $errors['responsefilename'] = get_string('required');
            }
            if (0 < strlen(trim($fromform["modelsolution"]))) {
                $filename = qtype_proforma_java_task::get_java_file($fromform["modelsolution"]);
                if ($filename != null and trim($filename) != trim($fromform["responsefilename"])) {
                    $errors['responsefilename'] = $filename . ' expected';
                }
            }
        }*/

        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
/*            if ($fromform["checkstyle"]) {
                $sumweight += $fromform["checkstyleweight"];
            }*/
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
            
            switch ($question->taskstorage) {
                case qtype_proforma::SETLX_TASKFILE:
                    // $taskfilehandler = new qtype_proforma_setlx_task();
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
                case qtype_proforma::SELECT_TASKFILE:
                    // State transition from SELECT to SETLX.
                    $question->taskstorage = qtype_proforma::SETLX_TASKFILE;
                    break;
                default:
                    throw new coding_exception('invalid taskstorage value ' . $question->taskstorage);                
            }
        }
    }

    /**
     * handle polymorphic behaviour when saving a question
     * @param $formdata
     * @param $options
     */
    /*
    public function save_question_options(&$options) {
        parent::save_question_options($options);

        $formdata = $this->_form;
        // $this->_taskhandler = new qtype_proforma_setlx_task;
        // $options->gradinghints = $this->_taskhandler->create_lms_grading_hints($formdata);

        if (!isset($formdata->import_process) or !$formdata->import_process) {
            // When importing a moodle xml question the preprocessing step is missing and
            // we have no actual form data.
            // So we must skip creating task because the task.xml already exists
            // and some data needed to create task.xml does not.

            // Otherwise we create the task.xml from the input data
            $taskfile = $this->_taskhandler->create_task_file($formdata);
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
    }*/
}
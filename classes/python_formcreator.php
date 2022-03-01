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
 * class for creating Python question edit forms
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/python_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

/**
 * Edit form for creating Python questions.
 */
class python_form_creator extends base_form_creator {

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
     * python_form_creator constructor.
     * @param type $form form instance OR formdata
     * @param bool $newquestion new question indicator
     */
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, new qtype_proforma_python_task());
        // Set parent options.
        $this->_syntaxhighlighting = 'python';
        $this->_proglang = 'python';
        $this->_responseformats = qtype_proforma::response_formats();
        $this->_entrypoint = false;
        $this->_tasktype = qtype_proforma::PYTHON_TASKFILE;
        $this->_unittestlabel = get_string('pythonunit', 'qtype_proforma');

        $this->_newquestion = $newquestion;
    }

    // Override.


    /**
     * add Java specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        // Add Python Unittest.
        return $this->add_test_fields($question, $questioneditform, 'python');
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

        // Check tests.
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            list($errors, $valid) = $this->validate_unittest($editor, $fromform, $files, $i, $errors);
        }
        // Sum of weights must be > 0.
        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
            if ($repeats > 0 && $sumweight == 0) {
                // Error message must be attached to testoptions group
                // otherwise it is not visible.
                $errors['testoptions[0]'] = get_string('sumweightzero', 'qtype_proforma');
            }
        }

        debugging($errors);
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

}
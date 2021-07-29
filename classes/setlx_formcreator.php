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
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, new qtype_proforma_setlx_task());
        // Set parent options.
        $this->_syntaxhighlighting = 'setlx';
        $this->_proglang = 'SetlX';
        // Only allow editor as reponse format.
        $ro = qtype_proforma::response_formats();
        $responseoptions = [qtype_proforma::RESPONSE_EDITOR => $ro[qtype_proforma::RESPONSE_EDITOR]];
        $this->_responseformats = $responseoptions;
        $this->_testfiles = false;
        $this->_entrypoint = false;
        $this->_taskType = qtype_proforma::SETLX_TASKFILE;
        $this->_unittestlabel = get_string('setlx', 'qtype_proforma');
    }

    // Override.

    /**
     * Modify repeatarray in add_tests: add editor for testcode
     *
     * @param $repeatarray
     */
    /*
    protected function adjust_test_repeatarray(&$repeatarray) {
        $mform = $this->_form;
        // Simply use textarea for unit test code.
        $repeatarray[] = $mform->createElement('textarea', 'testcode', '', 'rows="20" cols="80"');
    }
    */

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
    protected function add_tests($question, $questioneditform) {
        // Add compilation = Setlx Syntax check.
        $this->add_compilation(get_string('syntaxcheck', 'qtype_proforma'));
        // Add SetlX tests.
        return $this->add_test_fields($question, $questioneditform, 'setlx');
    }


    /**
     * Add test settings.
     *
     * @param $question
     * @param $questioneditform
     */
    public function add_test_settings($question, $questioneditform) {
        parent::add_test_settings($question, $questioneditform);

        // Set aggregation strategy to 'all-or-nothing'.
        $this->_form->setDefault('aggregationstrategy', qtype_proforma::ALL_OR_NOTHING);
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

        // Check SetlX tests.
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            list($errors, $valid) = $this->validate_unittest($editor, $fromform, $files, $i, $errors);
        }

        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
            if ($fromform["compile"]) {
                $sumweight += $fromform["compileweight"];
            }
            if ($repeats > 0 && $sumweight == 0) {
                // Error message must be attached to testoptions group.
                // Otherwise it is not visible.
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
                case qtype_proforma::SETLX_TASKFILE:
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

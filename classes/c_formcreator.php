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
 * class for creating c questions edit forms
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/c_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class c_form_creator extends base_form_creator {


    /**
     * c_form_creator constructor.
     *
     * @param $form
     * @param null $newquestion new question indicator
     */
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, new qtype_proforma_c_task());
        // Set parent options.
        $this->_syntaxhighlighting = 'c';
        $this->_proglang = 'c';
        // Only allow editor and filepicker as reponse format.
        $ro = qtype_proforma::response_formats();
        $responseoptions = [
                qtype_proforma::RESPONSE_EDITOR => $ro[qtype_proforma::RESPONSE_EDITOR],
                qtype_proforma::RESPONSE_FILEPICKER => $ro[qtype_proforma::RESPONSE_FILEPICKER]
        ];

        $this->_responseformats = $responseoptions;
        $this->_entrypointlabel = get_string('executable', 'qtype_proforma');
        $this->_entrypoint = true;
        $this->_taskType = qtype_proforma::C_TASKFILE;
        $this->_unittestlabel = get_string('clang', 'qtype_proforma');
        $this->_testcode = false;
    }

    // Override.

    /**
     * Modify repeatarray in add_tests: add filepicker for testcode
     *
     * @param $repeatarray
     */
    /*
    protected function adjust_test_repeatarray(&$repeatarray) {
        parent::adjust_test_repeatarray($repeatarray);
        $mform = $this->_form;
        // Add entry point field.
        $repeatarray[] = $mform->createElement('text', 'testexecutable',
                get_string('executable', 'qtype_proforma'), array('size' => 80));
        // $repeatarray[] = $mform->createElement('helpbutton', 'testexecutable_help', get_string('testexecutable_help', 'qtype_proforma'));
        // $repeatarray[] = $mform->createElement('helpbutton', 'testexecutable', 'testexecutable', 'qtype_proforma');
    }
    */

    /**
     * Modify repeatoptions in add_tests
     *
     * @param $repeatoptions
     */
    /*
    protected function adjust_test_repeatoptions(&$repeatoptions) {
        parent::adjust_test_repeatoptions($repeatoptions);
        $repeatoptions['testentrypoint']['rule'] = 'required';
        // $repeatoptions['testfiles']['rule'] = 'required';

        // $this->_form->setType('testexecutable', PARAM_TEXT);
        // $this->_form->setType('testcodeformat', PARAM_INT);
    }
    */

    /**
     * add c specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        $this->_form->addElement('html', get_string('clangfiles_help', 'qtype_proforma'));

        // Add c tests.
        return $this->add_test_fields($question, $questioneditform, 'clang');
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

        // Check C tests.
        $repeats = $this->get_count_tests(null);
        for ($i = 0; $i < $repeats; $i++) {
            list($errors, $valid) = $this->validate_unittest($editor, $fromform, $files, $i, $errors);
            if ($valid) {
                $entrypoint = $fromform["testentrypoint"][$i];
                if (0 == strlen(trim($entrypoint))) {
                    // Entrypoint missing.
                    $errors['testentrypoint['.$i.']'] = get_string('executablerequired', 'qtype_proforma');
                }
            }
        }

        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
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

            if ($question->taskstorage != qtype_proforma::C_TASKFILE) {
                throw new coding_exception('invalid taskstorage value ' . $question->taskstorage);
            }
            $this->_taskhandler->extract_formdata_from_taskfile($cat, $question);
            $this->_taskhandler->extract_formdata_from_gradinghints($question, $form);

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

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
 * editiing form for ProFormA question
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/proforma_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/java_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/setlx_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/select_formcreator.php');

/**
 * ProFormA question type editing form.
 */
class qtype_proforma_edit_form extends question_edit_form {

    /**
     * @var base_form_creator The class that creates the form elements
     */
    protected $formcreator = null;

    /**
     * overloaded definition detects that a new question will be created.
     */
    protected function definition() {
        $removesubmit = false;
        if (empty($this->question->options)) {
            // New question => create select form creator for selecting
            // a programming language.
            $this->formcreator = new select_form_creator($this->_form, true);
            $removesubmit = true;
        }
        parent::definition();
        if ($removesubmit) {
            // Do not show submit button.
            $this->_form->removeElement('buttonar');
            // Re-add cancel button.
            $this->_form->addElement('cancel');
        }
    }

    public function get_form() {
        return $this->_form;
    }

    /**
     * This function checks if the user input is valid.
     *
     * @param array $fromform
     * @param array $files
     * @return mixed
     */
    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);
        /*if (!isset($this->formcreator)) {
            return $errors;
        } else { */
            return $this->formcreator->validation($fromform, $files, $errors);
        // }
    }

    /**
     * comment from abstract super method: Override this in the subclass to question type name.
     * @return the question type name, should be the same as the name() method
     *      in the question type class.
     */
    public function qtype() {
        return 'proforma';
    }

    /**
     * Get the type sets passed.
     *
     * @param string $types The space , ; separated list of types
     * @return array('groupname', 'mime/type', ...)
     */
    /*
     private function get_typesets($types) {
        $sets = array();
        if (!empty($types)) {
            $sets = preg_split('/[\s,;:"\']+/', $types, null, PREG_SPLIT_NO_EMPTY);
        }
        return $sets;
    }
    */

    protected function create_form_creator_in_definition($mform) {
        if (isset($this->question->options->taskstorage)) {
            switch ($this->question->options->taskstorage) {
                case qtype_proforma::PERSISTENT_TASKFILE:
                    $this->formcreator = new proforma_form_creator($this->_form);
                    break;
                case qtype_proforma::VOLATILE_TASKFILE:
                case qtype_proforma::JAVA_TASKFILE:
                    // Question was created by form editor.
                    $this->formcreator = new java_form_creator($this->_form);
                    break;
                case qtype_proforma::SETLX_TASKFILE:
                    // Question was created by form editor.
                    $this->formcreator = new setlx_form_creator($this->_form);
                    break;
                case qtype_proforma::SELECT_TASKFILE:
                    // Question was created by form editor but not yet finished.
                    $classname = $this->question->options->programminglanguage . '_form_creator';
                    $this->formcreator = new $classname($this->_form);
                    break;
                default:
                    throw new coding_exception('invalid taskstorage value ' . $this->question->options->taskstorage);
            }
        }
        if ($this->formcreator == null) {
            // Question was imported.
            $this->formcreator = new proforma_form_creator($this->_form);
        }
    }

    /**
     * Add any question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('proforma');

        if ($this->formcreator == null) {
            // Use case: edit existing question:
            $this->create_form_creator_in_definition($mform);
        }

        $this->formcreator->add_hidden_fields();
        $this->formcreator->add_questiontext_attachments();
        $this->formcreator->add_proglang_selection($this->question);

        $this->formcreator->add_response_options($this->question, $qtype);

        $this->formcreator->add_test_settings($this->question, $this);

        $this->formcreator->add_grader_settings($this->question);

        // Internal description (Comment)
        $mform->addElement('header', 'commentheader', get_string('commentheader', 'qtype_proforma'));
        // $mform->setExpanded('commentheader');
        $mform->addElement('editor', 'comment', get_string('comment', 'qtype_proforma'),
                array('rows' => 10), $this->editoroptions);

        // Attention! the following assignment is put at the very end of the function
        // in order to avoid problems with a call to repeat_elements which
        // crashes in case of previous closures.
        $mform->addFormRule(function ($values, $files) {
            if (empty($values['filetypes'])) {
                return true;
            }
            // .py is not recognised => do not check extensions!
            // TODO: check valid format: ; separated + . with extension
            return true;
        });
    }

    /**
     * Perform any preprocessing needed on the data passed to {@link set_data()}
     * before it is used to initialise the form.
     *
     * different scenarios:
     * 1. edit:
     * - $question->options->X are original values read from database
     *  (if record is already stored in database)
     * - $question-X copy from $question->options->X (???)
     *
     * 2. submit:
     * - $question->options->X are original values read from database
     *  (if record is already stored in database)
     * - $question-X value from input (???)
     *
     * 3. duplicate:
     * - $question->options->X are original values read from database
     *  (no record created, draft filearea must be created)
     * - $question-X copy from $question->options->X (???)
     *
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question); // TODO das muss ohne gehen

        // Remember that data comes from user input.
        $question->edit_form = true;
        /*
        $cat = $question->category;
        $found = false;
        foreach (explode(',', $question->category) as $category) {
            $cat = $category;
            if ($question->contextid == $category) {
                $found = true;
            }
        }
        // Can we use $question->contextid instead of $question->category?
        // Check if the debugging message is visible...
        if (!$found) {
            debugging('$question->contextid not found in $question->category');
        } else {
            $cat = $question->contextid;
        }
        */
        // $cat = $question->contextid;
        $cat = $this->context->id;
        if ($this->formcreator == null) {
            throw new coding_exception('formcreator does not exist in data_preprocessing');
        }
        $this->formcreator->data_preprocessing($question, $cat, $this);

        return $question;
    }
}

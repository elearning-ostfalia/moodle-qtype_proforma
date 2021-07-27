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
require_once($CFG->dirroot . '/question/type/proforma/classes/c_formcreator.php');
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
     * creates a formcreator for generating a new question.
     * return true: if programming language will be selected.
     * Otherwise false.
     *
     * @global type $CFG
     * @global type $PAGE
     */
    private function create_creator_for_new_question() {
        // Check how many programming languages are available at this site.
        $proglangs = [
            [qtype_proforma::JAVA_TASKFILE, 'Java (JUnit)'],
        ];
        if (get_config('qtype_proforma', 'setlx')) {
            array_push($proglangs, [qtype_proforma::SETLX_TASKFILE, 'SetlX']);
        }
        if (get_config('qtype_proforma', 'clang')) {
            array_push($proglangs, [qtype_proforma::C_TASKFILE, 'c (CUnit, Make)']);
        }
        if (count($proglangs) > 1) {
            // More than one programming language:
            // user has to choose.
            global $CFG;
            global $PAGE;
            // Pass returnurl for cancel action.
            $originalreturnurl = $CFG->wwwroot . optional_param('returnurl', 0, PARAM_LOCALURL);
            // Call Javascript function for selection.
            // (Create 2-dimensional array with available programming languages
            // because it is easier to handle in Javascript).
            $title = get_string('selectlangtitle', 'qtype_proforma');
            $PAGE->requires->js_call_amd('qtype_proforma/selectlang', 'select_lang',
                array($title, $proglangs, $originalreturnurl));

            // Create a dummy form selector.
            $this->formcreator = new select_form_creator($this->_form, true);
            return true;
        } else {
            // Only Java is available:
            // Create Java edit form.
            $this->formcreator = new java_form_creator($this->_form, true);
            return false;
        }
    }

    /**
     * overloaded definition detects that a new question will be created.
     */
    protected function definition() {
        if (!empty($this->question->options)) {
            // Edit existing question.
            parent::definition();
            return;
        }

        // New question!
        // Because the form fields depend on the programming language
        // we must know what programming language is required.
        // For choosing the programming language a simple Javascript
        // popup window is used. The selected value is appended as proglang
        // to the URI and a redirection is triggered (all in Javascript).
        // Therefore we need to check for the existance of the 'proglang' value.
        $proglang = optional_param('proglang', 0, PARAM_INTEGER);
        if (empty($proglang)) {
            // Hack: We need to know what taskstorage is submitted right now!
            // Otherwise we cannot create the appropriate instance
            // and all submitted data belonging to the right subclass
            // is not evaluated.
            if (isset($_POST['taskstorage'])) {
                $proglang = $_POST['taskstorage'];
            }
        }

        if (empty($proglang)) {
            // Value 'proglang' does not exist => evaluate creator.
            $select = $this->create_creator_for_new_question();
            parent::definition();
            if ($select) {
                // Do not show submit button.
                $this->_form->removeElement('buttonar');
                // Re-add cancel button.
                $this->_form->addElement('cancel');
            }
        } else {
            // Value 'proglang' exists (user has choosen a programming language)
            // => create form.
            // New question!
            $this->create_form_creator($proglang, true);
            parent::definition();
        }
    }

    /**
     * enable access to form variable for formcreator
     * @return type
     */
    public function &get_form() {
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
        return $this->formcreator->validation($this, $fromform, $files, $errors);
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
     * returns the protected member variable question
     * @return type protected question
     */
    public function get_question() {
        return $this->question;
    }

    protected function create_form_creator($taskstorage, bool $newquestion) {
        if (!isset($taskstorage)) {
            throw new coding_exception('do not know what edit form to create');
        }

        switch ($taskstorage) {
            case qtype_proforma::PERSISTENT_TASKFILE:
                $this->formcreator = new proforma_form_creator($this->_form);
                break;
            case qtype_proforma::JAVA_TASKFILE:
                // Question was created by form editor.
                $this->formcreator = new java_form_creator($this->_form, $newquestion);
                break;
            case qtype_proforma::C_TASKFILE:
                // Question was created by form editor.
                $this->formcreator = new c_form_creator($this->_form, $newquestion);
                break;
            case qtype_proforma::SETLX_TASKFILE:
                // Question was created by form editor.
                $this->formcreator = new setlx_form_creator($this->_form, $newquestion);
                break;
            case qtype_proforma::SELECT_TASKFILE:
                // Question was created by form editor but not yet finished.
                $classname = $this->question->options->programminglanguage . '_form_creator';
                $this->formcreator = new $classname($this->_form);
                break;
            default:
                throw new coding_exception('invalid taskstorage value ' . $taskstorage);
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
            // Use case: edit existing question.
            if (isset($this->question->options->taskstorage)) {
                $this->create_form_creator($this->question->options->taskstorage, false);
            }
            if ($this->formcreator == null) {
                // Question was imported.
                $this->formcreator = new proforma_form_creator($this->_form);
            }
        }

        $this->formcreator->add_hidden_fields();
        $this->formcreator->add_questiontext_attachments();
        $this->formcreator->add_proglang_selection();

        $this->formcreator->add_response_options($this->question, $qtype);

        $this->formcreator->add_test_settings($this->question, $this);

        $this->formcreator->add_feedback_options($this->question, $this);

        $this->formcreator->add_grader_settings($this->question, $this->context);

        // Internal description (Comment).
        $mform->addElement('header', 'commentheader', get_string('commentheader', 'qtype_proforma'));
        $mform->addElement('editor', 'comment', get_string('comment', 'qtype_proforma'),
                array('rows' => 10), $this->editoroptions);

        // Attention! the following assignment is put at the very end of the function
        // in order to avoid problems with a call to repeat_elements which
        // crashes in case of previous closures.
        // @SuppressWarnings(PHPMD.UnusedFormalParameter).
        $mform->addFormRule(function ($values, $files) {
            if (empty($values['filetypes'])) {
                return true;
            }
            // Extension .py is not recognised => do not check extensions!
            // TODO: check valid format: ; separated + . with extension.
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
        $question = $this->data_preprocessing_hints($question);

        // Remember that data comes from user input.
        $question->edit_form = true;
        $cat = $this->context->id;
        if ($this->formcreator == null) {
            throw new coding_exception('formcreator does not exist in data_preprocessing');
        }
        $this->formcreator->data_preprocessing($question, $cat, $this);

        return $question;
    }

    /**
     * Do form definitions things that need to be done when data is set
     */
    public function definition_after_data() {
        parent::definition_after_data();
        $this->formcreator->definition_after_data();
    }
}

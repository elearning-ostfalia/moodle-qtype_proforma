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
 * class for selecting programming language in new question
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class select_form_creator extends base_form_creator {


    /**
     * java_form_creator constructor.
     *
     * @param $form
     * @param null $newquestion new question indicator
     */
    public function __construct($form, $newquestion = null) {
        parent::__construct($form, null, null);
    }

    // override

    /**
     * Add hidden fields for question attributes that are not part of the edit form.
     * @throws coding_exception
     */
    public function add_hidden_fields() {
        parent::add_hidden_fields();
        $mform = $this->_form;

        $mform->addElement('hidden', 'taskstorage', qtype_proforma::SELECT_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);
        
        // Add hidden default values for missing fields.
        $mform->addElement('hidden', 'responseformat', qtype_proforma::RESPONSE_EDITOR);
        $mform->setType('responseformat', PARAM_RAW);

        $mform->addElement('hidden', 'responsetemplate', '');
        $mform->setType('responsetemplate', PARAM_TEXT);
        
        
    }

    /**
     * Add something to select the programming language.
     *
     * @param $question
     */
    public function add_proglang_selection($question) {
        $mform = $this->_form;
      
        $programminglangs = array('java' => 'Java', 'setlx' => 'SetlX');
        $mform->addElement('select', 'programminglanguage',
                get_string('proglang', 'qtype_proforma'), $programminglangs);
        $mform->setType('programminglanguage', PARAM_TEXT);
        $mform->setDefault('programminglanguage', 'Java');
    }

    /**
     * Remove attachments for question text.
     *
     * @param $question
     */
    public function add_questiontext_attachments($question) {
    }    
    
    /**
     * Remove grader options/information.
     *
     * @param $question
     */
    public function add_grader_settings($question) {
    }    

    /**
     * Remove response options.
     *
     * @param $question
     * @param $qtype
     */   
    public function add_response_options($question, $qtype) {
    }    

    /**
     * Remove test settings.
     *
     * @param $question
     * @param $questioneditform
     */
    public function add_test_settings($question, $questioneditform) {        
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
        }
    }

    /**
     * handle polymorphic behaviour when saving a question
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        parent::save_question_options($options);
    }
    
  
}
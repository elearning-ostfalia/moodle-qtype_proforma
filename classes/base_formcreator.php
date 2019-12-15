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
 * base class for creating polymorph question edit forms
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
defined('MOODLE_INTERNAL') || die();

class base_form_creator {
    protected $form = null;
    protected $taskhandler = null;

    function __construct($form) {
        $this->form = $form;
    }

    // override
    public function validation($fromform, $files, $errors) {
        return $errors;
    }
    public function add_proglang_selection($question) {
    }
    public function add_grader_settings($question) {
    }
    public function add_questiontext_attachments($question) {
    }
    public function add_responsetemplate($question) {
        $mform = $this->form;
        $mform->addElement('textarea', 'responsetemplate', get_string('responsetemplate', 'qtype_proforma'), 'rows="20" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma::as_codemirror('id_responsetemplate', 'java', 'id_responsetemplateheader');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                    array('id_programminglanguage', 'id_responsetemplate'));
        }
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_proforma');

    }
    public function add_responsefilename($question) {
    }
    public function add_modelsolution($question) {
    }



    protected function add_test_weight_option(&$testoptions, $prefix, $defaultweight, $withtitle = false) {
        $mform = $this->form;
        if ($withtitle) {
            $testoptions[] = $mform->createElement('text', $prefix . 'title',
                    get_string('testtitle', 'qtype_proforma'), array('size' => 60));
            $mform->setType($prefix . 'title', PARAM_TEXT);
        }

        $testoptions[] = $mform->createElement('text', $prefix . 'weight',
                get_string('weight', 'qtype_proforma'), array('size' => 2));
        $mform->setType($prefix . 'weight', PARAM_FLOAT);
        $mform->setDefault($prefix . 'weight', $defaultweight);
    }

    // override
    protected function get_test_label() {
        return get_string('testlabel', 'qtype_proforma');
    }
    protected function modify_repeatarray(&$repeatarray) {
    }
    protected function modify_repeatoptions(&$repeatoptions) {
    }

    public function add_tests($question, $question_edit_form) {
        $mform = $this->form;
        // retrieve number of tests (resp. unit tests)
        $repeats = $this->get_count_tests($question);
        if ($repeats == 0) {
            $mform->addElement('static', 'no_tests', get_string('notests', 'qtype_proforma'), '');
            return $repeats;
        }

        // Unit tests resp. tests from imported task
        // Create row:
        $testoptions = array();
        $this->add_test_weight_option($testoptions, 'test', '1', true);
        $testoptions[] = $mform->createElement('text', 'testid', 'Id', array('size' => 3));
        $testoptions[] = $mform->createElement('text', 'testtype',
                get_string('testtype', 'qtype_proforma'), array('size' => 80));
        $testoptions[] = $mform->createElement('text', 'testdescription',
                get_string('testdescription', 'qtype_proforma'), array('size' => 80));

        $label = $this->get_test_label();

        $repeatarray = array();
        $repeatarray[] = $mform->createElement('group', 'testoptions', $label, $testoptions, null, false);
        $this->modify_repeatarray($repeatarray);
        $repeatoptions = array();
        $repeatoptions['testweight']['default'] = 1;
        // $repeatoptions['testtitle']['default'] = get_string('junittesttitle', 'qtype_proforma');
        $repeatoptions['testdescription']['default'] = '';
        // $repeateloptions['testfilename']['default'] = '';
        $repeatoptions['testtype']['default'] = 'unittest'; // JAVA-JUNIT
        $repeatoptions['testid']['default'] = '{no}'; // JAVA-JUNIT

        // $repeateloptions['testweight']['rule'] = 'numeric';
        $this->modify_repeatoptions($repeatoptions);

        // $repeateloptions['testweight']['helpbutton'] = 'Hilfetext';
        $mform->setType('testdescription', PARAM_TEXT);
        $mform->setType('testtitle', PARAM_TEXT);
        $mform->setType('testweight', PARAM_FLOAT);
        $mform->setType('testid', PARAM_RAW);
        $mform->setType('testtype', PARAM_RAW);
        // $mform->addRule('testtitle', null, 'required', null, 'client');
        // $mform->setType('testfilename', PARAM_TEXT);

        // $mform->disabledIf('testweight', 'aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);

        $question_edit_form->repeat_elements($repeatarray, $repeats,
                $repeatoptions, 'option_repeats', 'option_add_fields',
                1, get_string('addjunit', 'qtype_proforma'), true);

        return $repeats;
    }

    protected function get_count_tests($question) {
        $repeats = 0;
        // Get number of unit tests from (lms) grading hints.
        // In case of an imported task this ist the number of all tests (not just unit tests).
        if (isset($question) && isset($question->options) && isset($question->options->gradinghints)) {
            $repeats = $this->taskhandler->get_count_unit_tests($question->options->gradinghints);
        }

        return $repeats;
    }

    public function add_response_options($question, $qtype) {
        global $CFG, $COURSE;
        $mform = $this->form;

        $defaultmaxsubmissionsizebytes = get_config('maxsubmissionsizebytes');
        // $defaultfiletypes = (string)get_config('filetypeslist');
        //
        // Response Options
        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_proforma'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_proforma'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');
        // disable only if responseformat is filepicker!!
        // $mform->disabledIf('responseformat', 'attachments', 'neq', '1');

        // EDITOR OPTIONS
        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_proforma'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);
        $mform->hideIf('responsefieldlines', 'responseformat', 'eq', 'filepicker');

        // FILEPICKER OPTIONS
        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes,
                get_config('qtype_proforma', 'maxbytes'));

        $name1 = get_string('maximumsubmissionsize', 'qtype_proforma');
        $name2 = get_string('acceptedfiletypes', 'qtype_proforma');

        $filepickeroptions = array();
        $filepickeroptions[] = $mform->createElement('select', 'attachments',
                get_string('allowattachments', 'qtype_proforma'), $qtype->attachment_options());
        $filepickeroptions[] = $mform->createElement('select', 'maxbytes', $name1, $choices);
        $filepickeroptions[] = $mform->createElement('text', 'filetypes', $name2);
        $mform->addGroup($filepickeroptions, 'filepickergroup',  get_string('filepickeroptions', 'qtype_proforma'), array(' '), false);
        $mform->hideIf('filepickergroup', 'responseformat', 'eq', 'editor');
        $mform->addHelpButton('filepickergroup', 'acceptedfiletypes', 'qtype_proforma');

        $mform->setType('filetypes', PARAM_RAW);

        // Programming Language
        $mform->addElement('select', 'programminglanguage',
                get_string('highlight', 'qtype_proforma'), $qtype->get_proglang_options());
        $mform->addHelpButton('programminglanguage', 'highlight_hint', 'qtype_proforma');
        $mform->setDefault('programminglanguage', 'java');

        // response template
        $this->add_responsetemplate($question);

        // Response Filename
        $this->add_responsefilename($question);
        $mform->hideIf('responsefilename', 'responseformat', 'neq', 'editor');
        $mform->setType('responsefilename', PARAM_TEXT);
        $mform->addHelpButton('responsefilename', 'filename_hint', 'qtype_proforma');

        $this->add_modelsolution($question);
    }

    public function add_test_settings($question, $question_edit_form) {
        $mform = $this->form;

        // Header.
        $mform->addElement('header', 'test_header', get_string('tests', 'qtype_proforma'));
        $mform->setExpanded('test_header');

        // Aggreagation strategy.
        $aggregationstrategy = array(
                qtype_proforma::ALL_OR_NOTHING => get_string('all_or_nothing', 'qtype_proforma'),
                qtype_proforma::WEIGHTED_SUM  => get_string('weighted_sum', 'qtype_proforma')
        );
        $mform->addElement('select', 'aggregationstrategy',
                get_string('aggregationstrategy', 'qtype_proforma'), $aggregationstrategy);
        $mform->addHelpButton('aggregationstrategy', 'aggregationstrategy', 'qtype_proforma');
        $mform->setDefault('aggregationstrategy', qtype_proforma::WEIGHTED_SUM);

        // Tests
        // - test overview in case of imported task and
        // - test edit fields for tasks created with Moodle
        $this->add_tests($question, $question_edit_form);

        // Penalty
        $penalties = array(
                1.0000000,
                0.5000000,
                0.3333333,
                0.2500000,
                0.2000000,
                0.1000000,
                0.0000000
        );
        if (!empty($question->penalty) && !in_array($question->penalty, $penalties)) {
            $penalties[] = $question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
                get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions);
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'question');
        $mform->setDefault('penalty', get_config('qtype_proforma', 'defaultpenalty'));
    }

    // helper functions
    protected function add_static_field($question, $mform, $field, $label, $sizefield = null) {
        // $mform->addElement('static', $field, $label);
        if (isset($sizefield)) {
            if (isset($this->question->options->$sizefield)) {
                $value = $question->options->$sizefield;
                $attributes = array('size' => strlen($question->options->$sizefield));
            } else if (isset($this->question->$sizefield)) {
                $value = $question->$sizefield;
                $attributes = array('size' => strlen($question->$sizefield));
            }
        } else {
            $attributes = array('size' => strlen($question->options->$field));
        }

        if (isset($attributes) && count($attributes) > 0) {
            $mform->addElement('static', $field, $label, $attributes, '');
        } else {
            $mform->addElement('static', $field, $label);
        }

        $mform->setType($field, PARAM_TEXT);
    }
}
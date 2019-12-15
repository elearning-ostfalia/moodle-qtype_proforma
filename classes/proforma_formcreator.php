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
 * class for creating edit forms for importedd tasks
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');

class proforma_form_creator extends base_form_creator {
    function __construct($form) {
        parent::__construct($form);
    }

    public function add_questiontext_attachments($question) {
        $mform = $this->form;
        $mform->addElement('hidden', 'taskstorage', qtype_proforma::PERSISTENT_TASKFILE);
        $mform->setType('taskstorage', PARAM_RAW);
        // Attachments for Question Text (Downloads)
        $this->add_static_field($question, $mform, 'downloadlist', get_string('downloads', 'qtype_proforma'),
                'downloads');
        $mform->addHelpButton('downloadlist', 'downloads_hint', 'qtype_proforma');

    }
    public function add_modelsolution($question) {
        $mform = $this->form;
        // Model Solution files (instead of modelsollist we show links)
        $mform->addElement('static', 'mslinks', get_string('modelsolfiles', 'qtype_proforma'), '');
        $mform->addHelpButton('mslinks', 'modelsolfiles_hint', 'qtype_proforma');
    }
    public function add_responsetemplate($question) {
        $mform = $this->form;
        parent::add_responsetemplate($question);
        // Further templates (there should be no other templates)
        $this->add_static_field($question, $mform, 'furtherTemplates', get_string('templates', 'qtype_proforma'),
                'templates');
        $mform->addHelpButton('furtherTemplates', 'templates_hint', 'qtype_proforma');

    }

    public function add_responsefilename($question) {
        $mform = $this->form;
        $this->add_static_field($question, $mform, 'responsefilename', get_string('filename', 'qtype_proforma'));
    }

    public function add_grader_settings($question) {
        // ProFormA fields
        $mform = $this->form;
        $mform->addElement('header', 'graderoptions_header', get_string('graderoptions_header', 'qtype_proforma'));

        // Task Filename
        $mform->addElement('static', 'link', get_string('taskfilename', 'qtype_proforma'), '');
        $mform->setType('link', PARAM_TEXT);
        $mform->addHelpButton('link', 'taskfilename_hint', 'qtype_proforma');

        // UUID
        $this->add_static_field($question, $mform, 'uuid', get_string('uuid', 'qtype_proforma'));
        $mform->setType('uuid', PARAM_TEXT);
        $mform->addHelpButton('uuid', 'uuid_hint', 'qtype_proforma');

        // Proforma version
        $mform->addElement('static', 'proformaversion', 'ProFormA Version');
    }

    protected function modify_repeatoptions(&$repeatoptions) {
        // disable testtype and test identifier for imported tasks
        $repeatoptions['testid']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        $repeatoptions['testtype']['disabledif'] = array('aggregationstrategy', 'neq', 111);
    }

    public function add_tests($question, $question_edit_form) {
        $this->taskhandler = new qtype_proforma_proforma_task();
        $repeats = parent::add_tests($question, $question_edit_form);
        // Remove button for adding new test elements.
        $mform = $this->form;
        $mform->removeElement('option_add_fields');

    }
}
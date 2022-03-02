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
        $this->_tasktype = qtype_proforma::C_TASKFILE;
        $this->_unittestlabel = get_string('clang', 'qtype_proforma');
        $this->_testcode = false;
    }

    // Override.

    /**
     * add c specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        $this->_form->addElement('html', get_string('cunit_help', 'qtype_proforma'));

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
     * Check for run command (in addition to base function)
     * @param qtype_proforma_edit_form $editor
     * @param $fromform
     * @param $files
     * @param $i
     * @param $errors
     * @return array
     * @throws coding_exception
     */
    protected function validate_unittest(qtype_proforma_edit_form $editor, $fromform, $files, $i, $errors) {
        list($errors, $valid) = parent::validate_unittest($editor, $fromform, $files, $i, $errors);
        if ($valid) {
            $entrypoint = $fromform["testentrypoint"][$i];
            if (0 == strlen(trim($entrypoint))) {
                // Entrypoint missing.
                $errors['testentrypoint['.$i.']'] = get_string('executablerequired', 'qtype_proforma');
                $valid = false;
            }
        }
        return array($errors, $valid);
    }

}

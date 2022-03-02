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
        $this->_tasktype = qtype_proforma::SETLX_TASKFILE;
        $this->_unittestlabel = get_string('setlx', 'qtype_proforma');
    }

    // Override.

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
     * calculates the sum of all weights (for validation)
     * @param $fromform
     * @return int|mixed
     */
    protected function calc_sumweight($fromform) {
        $sumweight = parent::calc_sumweight($fromform);
        if ($fromform["compile"]) {
            $sumweight += $fromform["compileweight"];
        }

        return $sumweight;
    }
}

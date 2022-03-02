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
require_once($CFG->dirroot . '/question/type/proforma/classes/c_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/cpp_task.php');
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

class cpp_form_creator extends c_form_creator {

    /**
     * cpp_form_creator constructor.
     *
     * @param $form
     * @param null $newquestion new question indicator
     */
    public function __construct($form, bool $newquestion = false) {
        parent::__construct($form, $newquestion);
        $this->_taskhandler = new qtype_proforma_cpp_task();
        $this->_syntaxhighlighting = 'cpp';
        $this->_proglang = 'cpp';

        $this->_tasktype = qtype_proforma::CPP_TASKFILE;
        $this->_unittestlabel = get_string('cppunittest', 'qtype_proforma');
    }

    // Override.

    /**
     * add C++ specific test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        $this->_form->addElement('html', get_string('gtest_help', 'qtype_proforma'));

        // Add cpp tests.
        return $this->add_test_fields($question, $questioneditform, 'cpp');
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
        $this->_form->setDefault('aggregationstrategy', qtype_proforma::WEIGHTED_SUM);
    }
}

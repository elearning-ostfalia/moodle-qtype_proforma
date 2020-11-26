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
 * ProFormA task file
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/simplexmlwriter.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/base_task.php');

/*
 * (class for handling ProFormA tasks for different programming languages
 * (i.e. create task and extract data for editor)
 * Note that this class is stateless i.e. has no member variables.
 */
class qtype_proforma_proforma_task extends qtype_proforma_base_task {

    /**
     * returns false if the task is imported and cannot be modified,
     * returns true if the task is created and can be modified inside Moodle.
     *
     * @return boolean
     */
    public function create_in_moodle() {
        return false;
    }

    /**
     * Set formdata from grading hints
     *
     * @param $question question object
     * @param $ref test ref
     * @param $weight test weight
     * @return bool true if handled otherweise false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)*      *
     */
    protected function set_formdata_from_gradinghints($question, $ref, $weight) {
        return false;
    }

    /**
     * is testcode for given test index set?
     *
     * @param $formdata
     * @param $index
     * @return bool
     */
    protected function is_test_set($formdata, $index) {
        // return isset($formdata->testcode[$index]) && strlen(trim($formdata->testcode[$index]));
        return true;
    }

    /**
     * Get number of tests from grading hints.
     *
     * @param $gradinghints
     * @return int
     */
    public function get_count_tests($gradinghints) {
        if (empty($gradinghints)) {
            return 0;
        }
        $xmldoc = new DOMDocument;

        if (!$xmldoc->loadXML($gradinghints )) {
            debugging('gradinghints is not valid XML');
            return 0; // 'INTERNAL ERROR: $taskresult is not XML';
        }

        $xpath = new DOMXPath($xmldoc);
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');
        $xpathresult = $xpath->query('//grading-hints/root/test-ref');
        return $xpathresult->length;
    }

    // Override.

    /**
     * get number of unit tests (if any), to be overriden
     *
     * @param $gradinghints
     * @return int
     */
    public function get_count_unit_tests($gradinghints) {
        return $this->get_count_tests($gradinghints);
    }

    /**
     * override: task file must not be created.
     *
     * @param type $formdata
     * @throws coding_exception
     */
    public function create_task_file($formdata) {
        throw new coding_exception("create_task_file must not be called");
    }
}



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
 * create ProFormA python task file resp. extract data from such a file
 *
 * @package    qtype_proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/classes/base_task.php');

/**
 * Class for handling ProFormA tasks with Python unit tests.
 *
 * @package    qtype_proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_proforma_python_task extends qtype_proforma_base_task {
    /**
     * add extra namespaces to XML
     *
     * @param SimpleXmlWriter $xw
     */
    protected function add_namespace_to_xml(SimpleXmlWriter $xw) {
        $xw->create_attribute('xmlns:unit', 'urn:proforma:tests:unittest:v1.1');
    }

    /**
     * add programming language to XML
     * @param SimpleXmlWriter $xw
     * @param stdClass $formdata
     */
    protected function add_programming_language_to_xml(SimpleXmlWriter $xw, $formdata) {
        $xw->create_attribute('version', '');
        $xw->text($formdata->programminglanguage);
    }

    /**
     * Returns the filename of the test file in the task.
     *
     * @param int $index test index
     * @param string $id test id
     * @param string $code test code
     * @return string filename
     */
    protected function get_testfilename($index, $id, $code) {
        return 'test_pythontest' . $id . '.py';
    }

    /**
     * add unittest element to test in XML
     * @param SimpleXmlWriter $xw
     * @param int $index
     * @param stdClass $formdata
     * @return void
     */
    protected function add_unittest_to_xml(SimpleXmlWriter $xw, $index, $formdata) {
        $xw->startElement('unit:unittest');
        $xw->endElement(); // End tag unit:unittest.
    }
}

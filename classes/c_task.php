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
 * create ProFormA c task file resp. extract data from such a file
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_task.php');

class qtype_proforma_c_task extends qtype_proforma_base_task {

    /**
     * add extra namespaces to XML
     *
     * @param $xw
     */
    protected function add_namespace_to_xml(SimpleXmlWriter $xw) {
        $xw->create_attribute('xmlns:unit', 'urn:proforma:tests:unittest:v1.1');
    }

    /**
     * add programming language to XML
     * @param $xw
     * @param $formdata
     */
    protected function add_programming_language_to_xml(SimpleXmlWriter $xw, $formdata) {
        $xw->create_attribute('version', '');
        $xw->text($formdata->programminglanguage);
    }

    /**
     * add unittest element to test in XML
     * @param SimpleXmlWriter $xw
     * @param $index
     * @param $formdata
     * @return void
     */
    protected function add_unittest_to_xml(SimpleXmlWriter $xw, $index, $formdata) {
        $xw->startElement('unit:unittest');
        // $xw->create_attribute('framework', 'JUnit');
        $entrypoint = $formdata->testentrypoint[$index];
        $xw->create_childelement_with_text('unit:entry-point', trim($entrypoint));
        $xw->endElement(); // End tag unit:unittest.
    }

    /**
     * called by extract_formdata_from_taskfile in order to
     * extract form data from task test.
     *
     * @param type $question: return instance
     * @param type $test: test entity from task
     * @param type $files: files array
     * @param type $index: index of next unit test (in/out)
     */
    protected function extract_formdata_from_taskfile_test($question, $test, $files, &$index) {
        $config = $test->{'test-configuration'};
        // Switch to namespace 'unit'.
        $unittest = $config->children('unit', true)->{'unittest'};
        // $question->testversion[$index] = (string)$unittest->attributes()->version;
        // Call parent function for setting testcode attribute.
        // Note that index will be increemented there, too.
        $originalindex = $index;
        parent::extract_formdata_from_taskfile_test($question, $test, $files, $index);
        if (!isset($question->testcode[$originalindex])) {
            // Only set entrypoint if code for editor is set.
            $question->testentrypoint[$originalindex] = $unittest->{'entry-point'};
        }
    }
}

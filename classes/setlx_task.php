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
 * create ProFormA SetlX task file resp. extract data from such a file
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_task.php');

class qtype_proforma_setlx_task extends qtype_proforma_base_task {

    /**
     * is compiler option enabled?
     *
     * @param $formdata
     * @return bool
     */
    private static function has_compiler($formdata) {
        return isset($formdata->compile) && $formdata->compile;
    }

    /**
     * add programming language to XML
     * @param $xw
     * @param $formdata
     */
    protected function add_programming_language_to_xml(SimpleXmlWriter $xw, $formdata) {
        // Set fix programming language (might be replaced by $formdata->proglangversion).
        $xw->create_attribute('version', '2.7');
        $xw->text($formdata->programminglanguage);
    }

    /**
     * add test files to XML.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_testfiles_to_xml(SimpleXmlWriter $xw, $formdata) {
        if (self::has_compiler($formdata)) {
            $xw->startElement('file');
            $xw->create_attribute('id', 'compiler');
            $xw->create_attribute('used-by-grader', 'true');
            $xw->create_attribute('visible', 'no');
            $xw->startElement('embedded-txt-file');
            $filename = 'syntaxcheck.stlx';
            $xw->create_attribute('filename', $filename);
            $xw->text('print("");');
            $xw->endElement(); // End tag embedded-txt-file.
            $xw->endElement(); // End tag file.
        }

        // Setlx files.
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                $xw->startElement('file');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_attribute('used-by-grader', 'true');
                $xw->create_attribute('visible', 'no');
                $xw->startElement('embedded-txt-file');
                $code = $formdata->testcode[$index];
                $filename = 'test' . $id . '.stlx';
                $xw->create_attribute('filename', $filename);
                $xw->text($formdata->testcode[$index]);
                $xw->endElement(); // End tag embedded-txt-file.
                $xw->endElement(); // End tag file.
            }
        }
    }

    /**
     * Add tests to xml.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_xml(SimpleXmlWriter $xw, $formdata) {
        // Create Setlx Syntax check.
        if (self::has_compiler($formdata)) {
            $xw->startElement('test');
            $xw->create_attribute('id', 'compiler');
            $xw->create_childelement_with_text('title', 'Syntax Check');
            $xw->create_childelement_with_text('test-type', 'setlx');
            $xw->startElement('test-configuration');
            $xw->startElement('filerefs');
            $xw->startElement('fileref');
            $xw->create_attribute('refid', 'compiler');
            $xw->endElement(); // End tag fileref.
            $xw->endElement(); // End tag filerefs.
            $xw->endElement(); // End tag test-configuration.
            $xw->endElement(); // End tag test.
        }

        // SetlX tests.
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                $xw->startElement('test');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('test-type', 'setlx');

                $xw->startElement('test-configuration');
                $xw->startElement('filerefs');
                $xw->startElement('fileref');
                $xw->create_attribute('refid', $formdata->testid[$index]);
                $xw->endElement(); // End tag fileref.
                $xw->endElement(); // End tag filerefs.
                $xw->endElement(); // End tag test-configuration.

                $xw->endElement(); // End tag test.
            }
        }
    }

    /**
     * Add tests to LMS internal grading hints.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_lms_grading_hints(SimpleXmlWriter $xw, $formdata) {
        if (self::has_compiler($formdata)) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', 'compiler');
            $xw->create_attribute('weight', $formdata->compileweight);
            $xw->create_childelement_with_text('title', 'Syntax Check');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'setlx');
            $xw->endElement(); // End tag test-ref.
        }

        parent::add_tests_to_lms_grading_hints($xw, $formdata);
    }

    /**
     * set formdata from grading hints
     *
     * @param question $question
     * @param test $ref
     * @param test $weight
     * @return bool
     */
    protected function set_formdata_from_gradinghints($question, $ref, $weight) {
        if ($ref == 'compiler') {
            $question->compileweight = $weight;
            $question->compile = 1;
            return true;
        }
        return false;
    }

    /**
     * get number of SetlX tests.
     *
     * @param $gradinghints
     * @return int
     */
    public function get_count_unit_tests($gradinghints) {
        if (!$gradinghints) {
            return 0;
        }
        $gh = new SimpleXMLElement($gradinghints, LIBXML_PARSEHUGE);
        $count = 0;
        foreach ($gh->root->{'test-ref'} as $test) {
            if ((string)$test['ref'] == 'checkstyle') {
                continue;
            }
            if ((string)$test['ref'] == 'compiler') {
                continue;
            }
            $count++;
        }
        return $count;
    }

    /**
     * called by extract_formdata_from_taskfile in order to
     * extract form data from task test.
     * Override if needed!
     *
     * @param type $question: return instance
     * @param type $test: test entity from task
     * @param type $code: code from referenced file
     */
    protected function extract_formdata_from_test($question, $test, $files, $code, &$index) {
        switch ($test['id']) {
            case 'compiler':
                break;
            default:
                $question->testcode[$index] = $code;
                // Increment index only in case of an actual unit test.
                $index++;
                break;
        }
    }
}
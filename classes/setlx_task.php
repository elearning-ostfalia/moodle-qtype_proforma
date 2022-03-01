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
    const COMPILER   = 'compiler';
    const CHECKSTYLE = 'checkstyle';

    /**
     * constructor
     */
    public function __construct() {
        parent::__construct([self::COMPILER, self::CHECKSTYLE]);
    }

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
    protected function add_files_to_xml(SimpleXmlWriter $xw, $formdata) {
        if (self::has_compiler($formdata)) {
            $xw->startElement('file');
            $xw->create_attribute('id', self::COMPILER);
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
        parent::add_files_to_xml($xw, $formdata);
    }

    protected function get_testfilename($index, $id, $code) {
        return 'test' . $id . '.stlx';
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
            $xw->create_attribute('id', self::COMPILER);
            $xw->create_childelement_with_text('title', 'Syntax Check');
            $xw->create_childelement_with_text('test-type', 'setlx');
            $xw->startElement('test-configuration');
            $xw->startElement('filerefs');
            $xw->startElement('fileref');
            $xw->create_attribute('refid', self::COMPILER);
            $xw->endElement(); // End tag fileref.
            $xw->endElement(); // End tag filerefs.
            $xw->endElement(); // End tag test-configuration.
            $xw->endElement(); // End tag test.
        }

        // SetlX tests.
        parent::add_tests_to_xml($xw, $formdata, 'setlx');
    }

    protected function add_unittest_to_xml(SimpleXmlWriter $xw, $index, $formdata) {
        // Empty
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
            $xw->create_attribute('ref', self::COMPILER);
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
        if ($ref == self::COMPILER) {
            $question->compileweight = $weight;
            $question->compile = 1;
            return true;
        }
        return false;
    }

    /**
     * called by extract_formdata_from_taskfile in order to
     * extract form data from task test.
     * Override if needed!
     *
     * @param type $question: return instance
     * @param type $test: test entity from task
     * @param type $files: files array
     * @param type $index: index of next unit test (in/out)
     */
    protected function extract_formdata_from_taskfile_test($question, $test, $files, &$index) {
        switch ($test['id']) {
            case self::COMPILER: // TODO: Set flag???
                break;
            default:
                parent::extract_formdata_from_taskfile_test($question, $test, $files, $index);
                break;
        }
    }
}
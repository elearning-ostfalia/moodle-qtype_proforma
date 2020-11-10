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
require_once($CFG->dirroot . '/question/type/proforma/classes/proforma_task.php');

class qtype_proforma_setlx_task extends qtype_proforma_proforma_task {

    /** 
     * returns false if the task is imported and cannot be modified,
     * returns true if the task is created and can be modified inside Moodle.
     * 
     * @return boolean
     */
    public function create_in_moodle() {
        return true;
    }    
    
    /**
     * is Checkstyle option enabled?
     *
     * @param $formdata
     * @return bool
     */
    private static function has_checkstyle($formdata) {
        return isset($formdata->checkstyle) && $formdata->checkstyle;
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
     * is testcode for given test index set?
     *
     * @param $formdata
     * @param $index
     * @return bool
     */
    private static function has_test($formdata, $index) {
        return isset($formdata->testcode[$index]) && strlen(trim($formdata->testcode[$index]));
    }

    /**
     * add extra namespaces to XML
     *
     * @param $xw
     */
    protected function add_namespace_to_xml(SimpleXmlWriter $xw) {
        /*$xw->create_attribute('xmlns:unit', 'urn:proforma:tests:unittest:v1.1');
        $xw->create_attribute('xmlns:cs', 'urn:proforma:tests:java-checkstyle:v1.1');*/
    }

    /**
     * add programming language to XML
     * @param $xw
     * @param $formdata
     */
    protected function add_programming_language_to_xml(SimpleXmlWriter $xw, $formdata) {
        $xw->create_attribute('version', '2.7'); // $formdata->proglangversion);
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
            $xw->endElement(); // embedded-txt-file
            $xw->endElement(); // file
        }
        
        
        // Setlx files
        for ($index = 0; $index < count($formdata->testid); $index++) { // $formdata->testid as $id) {
            $id = $formdata->testid[$index];
            if ($id !== '' && self::has_test($formdata, $index)) {
                $xw->startElement('file');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_attribute('used-by-grader', 'true');
                $xw->create_attribute('visible', 'no');
                $xw->startElement('embedded-txt-file');
                $code = $formdata->testcode[$index];
                $filename = 'test' . $id . '.stlx';
                // debugging('FILENAME: ' . $filename);
                $xw->create_attribute('filename', $filename);
                $xw->text($formdata->testcode[$index]);
                $xw->endElement(); // embedded-txt-file
                $xw->endElement(); // file
            }
        }
/*
        // create checkstyle file
        if (self::has_checkstyle($formdata)) {
            $xw->startElement('file');
            $xw->create_attribute('id', 'checkstyle'); // $id);
            $xw->create_attribute('used-by-grader', 'true');
            $xw->create_attribute('visible', 'no');
            $xw->startElement('embedded-txt-file');
            $xw->create_attribute('filename', 'checkstyle.xml');
            $xw->text($formdata->checkstylecode);
            $xw->endElement(); // embedded-txt-file
            $xw->endElement(); // file 

        }*/
    }

    /**
     * Add tests to xml.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_xml(SimpleXmlWriter $xw, $formdata) {
        // create compiler test
        if (self::has_compiler($formdata)) {
            $xw->startElement('test');
            $xw->create_attribute('id', 'compiler');
            $xw->create_childelement_with_text('title', 'Syntax Check');
            $xw->create_childelement_with_text('test-type', 'setlx');
            $xw->startElement('test-configuration');
            $xw->startElement('filerefs');
            $xw->startElement('fileref');
            $xw->create_attribute('refid', 'compiler');
            $xw->endElement(); // fileref
            $xw->endElement(); // filerefs
            $xw->endElement(); // test-configuration            
            $xw->endElement(); // test
        }

        // SetlX tests
        for ($index = 0; $index < count($formdata->testid); $index++) { // $formdata->testid as $id) {
            $id = $formdata->testid[$index];
            if ($id !== '' && self::has_test($formdata, $index)) {
                $xw->startElement('test');
                $xw->create_attribute('id', $formdata->testid[$index]); // $id);
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('test-type', 'setlx');

                $xw->startElement('test-configuration');
                $xw->startElement('filerefs');
                $xw->startElement('fileref');
                $xw->create_attribute('refid', $formdata->testid[$index]);
                $xw->endElement(); // fileref
                $xw->endElement(); // filerefs
                $xw->endElement(); // test-configuration

                $xw->endElement(); // test
            }
        }
/*
        // create checkstyle test
        if (self::has_checkstyle($formdata)) {
            $xw->startElement('test');
            $xw->create_attribute('id', 'checkstyle');
            $xw->create_childelement_with_text('title', 'CheckStyle Test');
            $xw->create_childelement_with_text('test-type', 'java-checkstyle');
            $xw->startElement('test-configuration');

            $xw->startElement('filerefs');
            $xw->startElement('fileref');
            $xw->create_attribute('refid', 'checkstyle');
            $xw->endElement(); // fileref
            $xw->endElement(); // filerefs
            $xw->startElement('cs:java-checkstyle');

            $checkstyleversion = $formdata->checkstyleversion;
            $xw->create_attribute('version', $checkstyleversion);
            $xw->create_childelement_with_text('cs:max-checkstyle-warnings', '4');
            $xw->endElement(); // cs:java-checkstyle
            $xw->endElement(); // test-configuration
            $xw->endElement(); // test
        }*/
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
            $xw->endElement(); // test-ref
        }

        for ($index = 0; $index < count($formdata->testid); $index++) { // $formdata->testid as $id) {
            $id = $formdata->testid[$index];
            if ($id !== '' && self::has_test($formdata, $index)) {
                $xw->startElement('test-ref');
                $xw->create_attribute('ref', $formdata->testid[$index]);
                if (array_key_exists($index, $formdata->testweight)) {
                    $xw->create_attribute('weight', $formdata->testweight[$index]);
                } else {
                    $xw->create_attribute('weight', '-1');
                }
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('description', $formdata->testdescription[$index]);
                $xw->create_childelement_with_text('test-type', $formdata->testtype[$index]);
                $xw->endElement(); // test-ref
            }
        }
/*
        if (self::has_checkstyle($formdata)) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', 'checkstyle');
            $xw->create_attribute('weight', $formdata->checkstyleweight);
            $xw->create_childelement_with_text('title', 'CheckStyle Test');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'java-checkstyle');
            $xw->endElement(); // test-ref
        }*/
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
        switch($ref) {
            case 'compiler':
                $question->compileweight = $weight;
                $question->compile = 1;
                return true;
/*            case 'checkstyle':
                $question->checkstyleweight = $weight;
                $question->checkstyle = 1;
                return true;*/
            default:
                break;
        }
        return false;
    }

    /**
     * extract formdata from taskfile
     *
     * @param $category
     * @param $question
     */
    public function extract_formdata_from_taskfile($category, $question) {
        $content = $this->get_task_xml($category, $question);

        $task = new SimpleXMLElement($content, LIBXML_PARSEHUGE);
        // Read java version.
        $question->proglangversion = (string)$task->proglang['version'];

        // Read files.
        foreach ($task->files->file as $file) {
            $fileobject = array();
            $fileobject['id'] = (string)$file['id'];
            $code = $file->{'embedded-txt-file'}; // //$xpath->query('./dn2:embedded-txt-file', $file);

            $fileobject['filename'] = (string)$code['filename'];
            $fileobject['code'] = (string)$code;
            $files[$fileobject['id']] = $fileobject;
        }
        // Read tests.
        $index = 0;
        foreach ($task->tests->test as $test) {
            $code = null;
            foreach ($test->{'test-configuration'}->filerefs as $filerefs) {
                foreach ($filerefs->fileref as $fileref) {
                    // assume that we have only one file belonging to each test
                    $refid = (string) $fileref['refid'];
                    $fileobject = $files[$refid];
                    $code = (string) $fileobject['code'];
                }
                switch ($test['id']) {
/*                    case 'checkstyle':
                        $question->checkstylecode = $code;
                        $config = $test->{'test-configuration'};
                        // Switch to namespace 'cs'.
                        $cs = $config->children('cs', true);
                        $question->checkstyleversion = (string)$cs->attributes()->version;
                        // debugging('$question->checkstyleversion = ' . $question->checkstyleversion);
                        break;*/
                    case 'compiler': // assert(false);
                        break;
                    default: // SetlX test
                        // $id = (string)$test['id'];
                        $question->testcode[$index] = $code;
                        $config = $test->{'test-configuration'};
/*                        // Switch to namespace 'unit'.
                        $unittest = $config->children('unit', true)->{'unittest'};
                        $question->testversion[$index] = (string)$unittest->attributes()->version;*/
                        // debugging('$question->testversion[$index] = ' . $question->testversion[$index]);
                        $index++;
                        break;
                }
            }
        }
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


}
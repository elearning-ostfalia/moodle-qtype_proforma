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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * create ProFormA java task file resp. extract data from such a file
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/proformatask.php');

class qtype_proforma_java_task extends qtype_proforma_proforma_task {
    public function create_java_task_file($formdata) {
        return $this->create_task_file($formdata);
    }

    protected function add_namespace_to_xml($xw) {
        $xw->create_attribute('xmlns:unit', 'urn:proforma:tests:unittest:v1.1');
        $xw->create_attribute('xmlns:cs', 'urn:proforma:tests:java-checkstyle:v1.1');
    }
    protected function add_programming_language_to_xml($xw, $formdata) {
        $javaversion = get_config('qtype_proforma', 'javaversion');
        $xw->create_attribute('version', $javaversion);
        $xw->text($formdata->programminglanguage);
    }
    protected function add_testfiles_to_xml($xw, $formdata) {
        // Junit files
        $index = 0;
        foreach ($formdata->testid as $id) {
            if ($id !== '') {
                $xw->startElement('file');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_attribute('used-by-grader', 'true');
                $xw->create_attribute('visible', 'no');
                $xw->startElement('embedded-txt-file');
                $code = $formdata->testcode[$index];
                $filename = self::get_java_file($code);
                //debugging('FILENAME: ' . $filename);
                $xw->create_attribute('filename', $filename);
                $xw->text($formdata->testcode[$index]);
                $xw->endElement(); // embedded-txt-file
                $xw->endElement(); // file
                $index++;
            }
        }

        // create checkstyle file
        $xw->startElement('file');
        $xw->create_attribute('id', 'checkstyle'); // $id);
        $xw->create_attribute('used-by-grader', 'true');
        $xw->create_attribute('visible', 'no');
        $xw->startElement('embedded-txt-file');
        $xw->create_attribute('filename', 'checkstyle.xml');
        $xw->text($formdata->checkstylecode);
        $xw->endElement(); // embedded-txt-file
        $xw->endElement(); // file
    }

    protected function add_tests_to_xml($xw, $formdata) {
        // create compiler test
        $xw->startElement('test');
        $xw->create_attribute('id', 'compiler');
        $xw->create_childelement_with_text('title', 'Compiler');
        $xw->create_childelement_with_text('test-type', 'java-compilation');
        $xw->create_childelement_with_text('test-configuration', null);
        $xw->endElement(); // test

        // create checkstyle test
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

        $checkstyleversion = get_config('qtype_proforma', 'checkstyleversion');
        $xw->create_attribute('version', $checkstyleversion);
        $xw->create_childelement_with_text('cs:max-checkstyle-warnings', '4');
        $xw->endElement(); // cs:java-checkstyle
        $xw->endElement(); // test-configuration
        $xw->endElement(); // test

        // Junit tests
        $index = 0;
        foreach ($formdata->testid as $id) {
            if ($id !== '') {
                $xw->startElement('test');
                $xw->create_attribute('id', $formdata->testid[$index]); // $id);
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('test-type', 'unittest');

                $xw->startElement('test-configuration');
                $xw->startElement('filerefs');
                $xw->startElement('fileref');
                $xw->create_attribute('refid', $formdata->testid[$index]);
                $xw->endElement(); // fileref
                $xw->endElement(); // filerefs
                $xw->startElement('unit:unittest');
                $xw->create_attribute('framework', 'JUnit');
                $junitversion = get_config('qtype_proforma', 'junitversion');
                $xw->create_attribute('version', $junitversion);
                $code = $formdata->testcode[$index];
                $entrypoint = self::get_java_entrypoint($code);
                $xw->create_childelement_with_text('unit:entry-point', $entrypoint);
                $xw->endElement(); // unit:unittest
                $xw->endElement(); // test-configuration

                $xw->endElement(); // test
                $index++;
            }
        }
    }

    protected function add_gradinghints_for_tests_to_xml($xw, $formdata) {
        if (isset($formdata->checkstyle)) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', 'compiler');
            $xw->create_attribute('weight', $formdata->compileweight);
            $xw->create_childelement_with_text('title', 'Compiler');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'java-compilation');
            $xw->endElement(); // test-ref
        }

        $index = 0;
        foreach ($formdata->testid as $id) {
            if ($id !== '') {
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
                $index++;
            }
        }

        if (isset($formdata->checkstyle)) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', 'checkstyle');
            $xw->create_attribute('weight', $formdata->checkstyleweight);
            $xw->create_childelement_with_text('title', 'CheckStyle Test');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'java-checkstyle');
            $xw->endElement(); // test-ref
        }
    }

    /*
    // from edit_numerical_form
    // See comment in the parent method about this hack:
    // Evil hack alert. Formslib can store defaults in two ways for
    // repeat elements:
    //   ->_defaultValues['fraction[0]'] and
    //   ->_defaultValues['fraction'][0].
    // The $repeatedoptions['fraction']['default'] = 0 bit above means
    // that ->_defaultValues['fraction[0]'] has already been set, but we
    // are using object notation here, so we will be setting
    // ->_defaultValues['fraction'][0]. That does not work, so we have
    // to unset ->_defaultValues['fraction[0]'].
    unset($this->_form->_defaultValues["testtitle[{$key}]"]);
    */
    public function extract_formdata_from_gradinghints($question, $mform) {
        $question->testtitle = array();
        $question->testdescription = array();
        $question->testtype = array();
        $question->testweight = array();
        $question->testid = array();

        if (!isset($question->gradinghints)) {
            // nothing to be done
            return;
        }

        $xmldoc = new DOMDocument;

        if (!$xmldoc->loadXML($question->gradinghints)) {
            debugging('gradinghints is not valid XML');
            return; // 'INTERNAL ERROR: $taskresult is not XML';
        }

        $xpath = new DOMXPath($xmldoc);
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');
        $xpathresult = $xpath->query('//grading-hints/root/test-ref');
        $key = 0;
        if ($xpathresult->length == 0) {
            debugging('no tests in gradinghints found');
            return;
        }

        foreach ($xpathresult as $testgrading) {
            $ref = $testgrading->getAttribute('ref');
            $weight = $testgrading->getAttribute('weight');
            $titles = $xpath->query('title', $testgrading);
            if ($titles->length > 0) {
                $title = $titles->item(0)->textContent;
            } else {
                $title = 'Title ' . $ref;
            }
            $descriptions = $xpath->query('description', $testgrading);
            if ($descriptions->length > 0) {
                $description = $descriptions->item(0)->textContent;
            } else {
                $description = '';
            }
            $testtypes = $xpath->query('test-type', $testgrading);
            if ($testtypes->length > 0) {
                $testtype = $testtypes->item(0)->textContent;
            } else {
                $testtype = '';
            }

            unset($mform->_defaultValues["testtitle[{$key}]"]);
            unset($mform->_defaultValues["testid[{$key}]"]);
            unset($mform->_defaultValues["testweight[{$key}]"]);
            unset($mform->_defaultValues["testdescription[{$key}]"]);
            unset($mform->_defaultValues["testtype[{$key}]"]);
            if ($question->taskstorage == qtype_proforma::VOLATILE_TASKFILE) {
                // special handling
                switch($ref) {
                    case 'compiler':
                        $question->compileweight = $weight;
                        $question->compile = 1;
                        break;
                    case 'checkstyle':
                        $question->checkstyleweight = $weight;
                        $question->checkstyle = 1;
                        break;
                    default:
                        $question->testid[] = $ref;
                        $question->testtitle[] = $title;
                        $question->testdescription[] = $description;
                        $question->testtype[] = $testtype;
                        $question->testweight[] = $weight;
                        break;
                }
            } else {
                $question->testid[] = $ref;
                $question->testtitle[] = $title;
                $question->testdescription[] = $description;
                $question->testtype[] = $testtype;
                $question->testweight[] = $weight;
            }
            $key++;
        }
    }

    public function extract_formdata_from_taskfile($category, $question) {
        $content = $this->get_task_xml($category, $question);

        $task = new SimpleXMLElement($content, LIBXML_PARSEHUGE);
        // read files
        foreach ($task->files->file as $file) {
            $fileobject = array();
            $fileobject['id'] = (string)$file['id'];
            $code = $file->{'embedded-txt-file'}; // //$xpath->query('./dn2:embedded-txt-file', $file);

            $fileobject['filename'] = (string)$code['filename'];
            $fileobject['code'] = (string)$code;
            $files[$fileobject['id']] = $fileobject;
        }
        // read tests
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
                    case 'checkstyle':
                        $question->checkstylecode = $code;
                        break;
                    case 'compiler': // assert(false);
                        break;
                    default: // JUNIT test
                        $id = (string)$test['id'];
                        $question->testcode[$index] = $code;
                        $index++;
                        break;
                }
            }
        }
    }


    // parse Java code

    /**
     * remove Java comments from code string
     * @param $code
     */
    private static function remove_java_comment(&$code) {
        $newCode = preg_replace('/\/\*[\s\S]*?\*\//m', '', $code); // comment with /* */
        $newCode = preg_replace('/\/\/.*/', '', $newCode); // comment with //
    }

    private static function get_java_classname($code) {
        $matches = array();
        $classname = preg_match('/class\s+([\S]+?)\s*(\{|extends|implements)/', $code, $matches);
        if ($classname === 0)
            return "";
        if ($classname === FALSE) {
            debugging('preg_match failed in get_java_classname');
            return "";
        }

        switch (count($matches)) {
            case 0:  return ""; // no className found???
            case 1:  return $matches[0]; // unclear what it is, deliver everything
            default: return trim($matches[1]); // found, expect className name as 2nd
        }
    }

    private static function get_java_packagename($code) {
        $matches = array();
        $classname = preg_match('/package([\s\S]*?);/', $code, $matches);
        if ($classname === 0)
            return "";
        if ($classname === FALSE) {
            debugging('preg_match failed in get_java_packagename');
            return "";
        }

        switch (count($matches)) {
            case 0:  return ""; // no className found???
            case 1:  return $matches[0]; // unclear what it is, deliver everything
            default: return trim($matches[1]); // found, expect className name as 2nd
        }
    }

    private static function get_java_file($code) {
        self::remove_java_comment($code);
        $classname = self::get_java_classname($code);
        return $classname . ".java";
    }

    private static function get_java_entrypoint($code) {
        self::remove_java_comment($code);
        $package = self::get_java_packagename($code);
        $classname = self::get_java_classname($code);
        if (strlen($package) > 0) {
            return $package . '.' . $classname;
        }
        return $classname;
    }
}
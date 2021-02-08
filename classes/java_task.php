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
 * create ProFormA java task file resp. extract data from such a file
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_task.php');

class qtype_proforma_java_task extends qtype_proforma_base_task {


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
     * add extra namespaces to XML
     *
     * @param $xw
     */
    protected function add_namespace_to_xml(SimpleXmlWriter $xw) {
        $xw->create_attribute('xmlns:unit', 'urn:proforma:tests:unittest:v1.1');
        $xw->create_attribute('xmlns:cs', 'urn:proforma:tests:java-checkstyle:v1.1');
    }

    /**
     * add programming language to XML
     * @param $xw
     * @param $formdata
     */
    protected function add_programming_language_to_xml(SimpleXmlWriter $xw, $formdata) {
        $xw->create_attribute('version', $formdata->proglangversion);
        $xw->text($formdata->programminglanguage);
    }


    /**
     * return the testfile from draft area
     *
     * @global type $USER
     * @param type $formdata
     * @param type $testindex
     * @return type
     */
    private function _get_draft_testfiles($formdata, $testindex) {
        global $USER;
        $usercontext = context_user::instance($USER->id);

        $draftitemid = $formdata->testfiles[$testindex];
        $fs = get_file_storage();
        return $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    }
    /**
     * add test files to XML.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_testfiles_to_xml(SimpleXmlWriter $xw, $formdata) {
        // Create Junit files.
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                if ($formdata->testcodeformat[$index] == base_form_creator::EDITORTESTINPUT) {
                    $xw->startElement('file');
                    $xw->create_attribute('id', $formdata->testid[$index]);
                    $xw->create_attribute('used-by-grader', 'true');
                    $xw->create_attribute('visible', 'no');

                    $xw->startElement('embedded-txt-file');
                    $code = $formdata->testcode[$index];
                    $filename = self::get_java_file($code);
                    $xw->create_attribute('filename', $filename);
                    $xw->text($formdata->testcode[$index]);
                    $xw->endElement(); // End tag embedded-txt-file.
                    $xw->endElement(); // End tag file.
                } else {
                    // Handle uploaded test files.
                    $counter = 1;
                    foreach ($this->_get_draft_testfiles($formdata, $index) as $draftfile) {
                        $xw->startElement('file');
                        $xw->create_attribute('id', $formdata->testid[$index] . '-' . $counter);
                        $xw->create_attribute('used-by-grader', 'true');
                        $xw->create_attribute('visible', 'no');
                        $xw->startElement('embedded-bin-file');
                        $xw->create_attribute('filename', $draftfile->get_filename());
                        $xw->text(base64_encode($draftfile->get_content()));
                        $xw->endElement(); // End tag embedded-bin-file.
                        $xw->endElement(); // End tag file.
                        $counter++;
                    }
                }
            }
        }

        // Create checkstyle file.
        if (self::has_checkstyle($formdata)) {
            $xw->startElement('file');
            $xw->create_attribute('id', 'checkstyle');
            $xw->create_attribute('used-by-grader', 'true');
            $xw->create_attribute('visible', 'no');
            $xw->startElement('embedded-txt-file');
            $xw->create_attribute('filename', 'checkstyle.xml');
            $xw->text($formdata->checkstylecode);
            $xw->endElement(); // End tag embedded-txt-file.
            $xw->endElement(); // End tag file.
        }
    }

    /**
     * Add tests to xml.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_xml(SimpleXmlWriter $xw, $formdata) {
        // Create compiler test.
        if (self::has_compiler($formdata)) {
            $xw->startElement('test');
            $xw->create_attribute('id', 'compiler');
            $xw->create_childelement_with_text('title', 'Compiler');
            $xw->create_childelement_with_text('test-type', 'java-compilation');
            $xw->create_childelement_with_text('test-configuration', null);
            $xw->endElement(); // End tag test.
        }

        // Junit tests.
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                $xw->startElement('test');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('test-type', 'unittest');
                $xw->startElement('test-configuration');
                $xw->startElement('filerefs');
                if ($formdata->testcodeformat[$index] == base_form_creator::EDITORTESTINPUT) {
                    $xw->startElement('fileref');
                    $xw->create_attribute('refid', $formdata->testid[$index]);
                    $xw->endElement(); // End tag fileref.
                } else {
                    $counter = 1;
                    foreach ($this->_get_draft_testfiles($formdata, $index) as $draftfile) {
                        $xw->startElement('fileref');
                        $xw->create_attribute('refid', $formdata->testid[$index] . '-' . $counter);
                        $xw->endElement(); // End tag fileref.
                        $counter++;
                    }
                }

                $xw->endElement(); // End tag filerefs.
                $xw->startElement('unit:unittest');
                $xw->create_attribute('framework', 'JUnit');
                $junitversion = $formdata->testversion[$index];
                $xw->create_attribute('version', $junitversion);
                if ($formdata->testcodeformat[$index] == base_form_creator::EDITORTESTINPUT) {
                    $code = $formdata->testcode[$index];
                    $entrypoint = self::get_java_entrypoint($code);
                } else {
                    $entrypoint = $formdata->testentrypoint[$index];
                }
                $xw->create_childelement_with_text('unit:entry-point', trim($entrypoint));
                $xw->endElement(); // End tag unit:unittest.
                $xw->endElement(); // End tag test-configuration.

                $xw->endElement(); // End tag test.
            }
        }

        // Create checkstyle test.
        if (self::has_checkstyle($formdata)) {
            $xw->startElement('test');
            $xw->create_attribute('id', 'checkstyle');
            $xw->create_childelement_with_text('title', 'CheckStyle Test');
            $xw->create_childelement_with_text('test-type', 'java-checkstyle');
            $xw->startElement('test-configuration');

            $xw->startElement('filerefs');
            $xw->startElement('fileref');
            $xw->create_attribute('refid', 'checkstyle');
            $xw->endElement(); // End tag fileref.
            $xw->endElement(); // End tag filerefs.
            $xw->startElement('cs:java-checkstyle');

            $checkstyleversion = $formdata->checkstyleversion;
            $xw->create_attribute('version', $checkstyleversion);
            $xw->create_childelement_with_text('cs:max-checkstyle-warnings', '4');
            $xw->endElement(); // End tag cs:java-checkstyle.
            $xw->endElement(); // End tag test-configuration.
            $xw->endElement(); // End tag test.
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
            $xw->create_childelement_with_text('title', 'Compiler');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'java-compilation');
            $xw->endElement(); // End tag test-ref.
        }

        parent::add_tests_to_lms_grading_hints($xw, $formdata);

        if (self::has_checkstyle($formdata)) {
            $xw->startElement('test-ref');
            $xw->create_attribute('ref', 'checkstyle');
            $xw->create_attribute('weight', $formdata->checkstyleweight);
            $xw->create_childelement_with_text('title', 'CheckStyle Test');
            $xw->create_childelement_with_text('description', '');
            $xw->create_childelement_with_text('test-type', 'java-checkstyle');
            $xw->endElement(); // End tag test-ref.
        }
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
            case 'checkstyle':
                $question->checkstyleweight = $weight;
                $question->checkstyle = 1;
                return true;
            default:
                break;
        }
        return false;
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
    protected function extract_formdata_from_test($question, $test, $files, &$index) {
        switch ($test['id']) {
            case 'checkstyle':
                $config = $test->{'test-configuration'};
                // Get code (currently exactly one textfile is expected!!).
                foreach ($config->filerefs as $filerefs) {
                    foreach ($filerefs->fileref as $fileref) {
                        $refid = (string) $fileref['refid'];
                        $fileobject = $files[$refid];
                        $question->checkstylecode = (string) $fileobject['code'];
                    }
                }
                // Switch to namespace 'cs'.
                $cs = $config->children('cs', true);
                $question->checkstyleversion = (string)$cs->attributes()->version;
                break;
            case 'compiler':
                // Nothing to be done for compiler.
                break;
            default: // JUNIT test.
                $config = $test->{'test-configuration'};
                // Switch to namespace 'unit'.
                $unittest = $config->children('unit', true)->{'unittest'};
                $question->testversion[$index] = (string)$unittest->attributes()->version;
                // Call parent function for setting testcode attribute.
                // Note that index will be increemented there, too.
                $originalindex = $index;
                parent::extract_formdata_from_test($question, $test, $files, $index);
                if (!isset($question->testcode[$originalindex])) {
                    // Only set entrypoint if code for editor is set.
                    $question->testentrypoint[$originalindex] = $unittest->{'entry-point'};
                }
                break;
        }
    }

    /**
     * get number of JUnit tests.
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


    // Parse Java code.

    /**
     * remove Java comments from code string
     * @param $code
     */
    private static function remove_java_comment(&$code) {
        $code = preg_replace('/\/\*[\s\S]*?\*\//m', '', $code); // Block comment '/* ...*/'.
        $code = preg_replace('/\/\/.*/', '', $code); // Inline comment '//'.
    }

    /**
     * Tries to evaluate the class name in the source code.
     * @param $code
     * @return string The classname evaluated from sourcse code.
     */
    private static function get_java_classname($code) {
        $matches = array();
        $classnamematch = preg_match('/class\s+([\S]+\s*(?:\<(?:.|\R)+\>)?)\s(?:\{|extends|implements)/', $code, $matches);
        if ($classnamematch === 0) {
            return "";
        }
        if ($classnamematch === false) {
            debugging('preg_match failed in get_java_classname');
            return "";
        }

        $classname = "";
        switch (count($matches)) {
            case 0:
                return ""; // No className found!
            case 1:
                $classname = trim($matches[0]); // Unclear what it is, deliver everything.
                break;
            default:
                // There should be only one match.
                $classname = trim($matches[1]); // Found, expect className name as 2nd.
                break;
        }
        // Remove whitespace characters.
        $classname = preg_replace('/\s+/', '', $classname);
        return $classname;
    }

    /**
     * Tries and find a package name in the source code.
     * @param $code
     * @return string
     */
    private static function get_java_packagename($code) {
        $matches = array();
        $package = preg_match('/package([\s\S]*?);/', $code, $matches);
        if ($package === 0) {
            return "";
        }
        if ($package === false) {
            debugging('preg_match failed in get_java_packagename');
            return "";
        }

        switch (count($matches)) {
            case 0:
                return ""; // No package found!
            case 1:
                return $matches[0]; // Unclear what it is, deliver everything.
            default:
                return trim($matches[1]); // Found, expect package name as 2nd.
        }
    }

    /**
     * Tries and detects the classname in the code and returns the associated
     * filename.
     *
     * @param $code
     * @return null|string null if no filename can be evaluated.
     */
    public static function get_java_file($code) {
        self::remove_java_comment($code);
        $package = self::get_java_packagename($code);
        $classname = self::get_java_classname($code);
        // Handle Java Generics.
        $index1 = strpos($classname, '<');
        if ($index1 > 0) {
            $index2 = strpos($classname, '>');
            if ($index2 > 0) {
                $classname = trim(substr($classname, 0, $index1));
            }
        }
        if (strlen($package) > 0) {
            $package = str_replace('.', '/', $package);
            $classname = $package . '/' . $classname;
        }

        if ($classname) {
            return $classname . ".java";
        }
        return null;
    }

    /**
     * Tries and detects the package and classname in the code and returns
     * the 'full' classname.
     *
     * @param $code
     * @return null|string null if no classname can be evaluated otherwise
     * the evaluated full classname.
     */
    public static function get_java_entrypoint($code) {
        self::remove_java_comment($code);
        $package = self::get_java_packagename($code);
        $classname = self::get_java_classname($code);
        if (!$classname) {
            return null;
        }
        if (strlen($package) > 0) {
            return $package . '.' . $classname;
        }
        return $classname;
    }
}
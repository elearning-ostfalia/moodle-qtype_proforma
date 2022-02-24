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
// along with ProFormA Question Type for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains unit tests for handling Setlx task files
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/tests/task_testcase.php');


class qtype_proforma_setlx_task_test extends task_testcase {

    const EXPECTED_BASE = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="2.7">setlx</proglang>
    <submission-restrictions/>
    <files>
        <file id="compiler" used-by-grader="true" visible="no">
            <embedded-txt-file filename="syntaxcheck.stlx">print(&quot;&quot;);</embedded-txt-file>
        </file>
        <file id="1" used-by-grader="true" visible="no">
            <embedded-txt-file filename="test1.stlx">some testcode</embedded-txt-file>
        </file>
        <file id="2" used-by-grader="true" visible="no">
            <embedded-txt-file filename="test2.stlx">some other testcode</embedded-txt-file>
        </file>
        <file id="MS" used-by-grader="false" visible="no">
            <embedded-txt-file filename="modelsolution.java">// no model solution available </embedded-txt-file>
        </file>
    </files>
    <model-solutions>
        <model-solution id="1">
            <filerefs>
                <fileref refid="MS"/>
            </filerefs>
        </model-solution>
    </model-solutions>
    <tests>
        <test id="compiler">
            <title>Syntax Check</title>
            <test-type>setlx</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="compiler"/>
                </filerefs>
            </test-configuration>
        </test>
        <test id="1">
            <title>Setlx Test 1</title>
            <test-type>setlx</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1"/>
                </filerefs>
            </test-configuration>
        </test>
        <test id="2">
            <title>Setlx Test 2</title>
            <test-type>setlx</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="2"/>
                </filerefs>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';    
    

    /* one setlx test with syntax check */
    public function test_create_setlx_file1() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'setlx1');
        $instance = new qtype_proforma_setlx_task;
        $taskfile = $instance->create_task_file($formdata);

        // Remove test id=2 and file id=2
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML(self::EXPECTED_BASE);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[2];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[2];
        $node->parentNode->removeChild($node);
        $expectedxml = $xmldoc->saveXML();      
               
        $this->assert_same_xml($expectedxml, $taskfile);
    }

    /* only syntax check */
    public function test_create_setlx_no_test() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'setlx0');
        $instance = new qtype_proforma_setlx_task;
        $taskfile = $instance->create_task_file($formdata);

        // Remove test id=1,2 and file id=1,2
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML(self::EXPECTED_BASE);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[2];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[2];
        $node->parentNode->removeChild($node);
        
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[1];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[1];
        $node->parentNode->removeChild($node);
        $expectedxml = $xmldoc->saveXML();      
               
        $this->assert_same_xml($expectedxml, $taskfile);
    }


    /* one setlx test without syntax check */
    public function test_create_setlx_without_compilation() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'setlx1a');
        $instance = new qtype_proforma_setlx_task;
        $taskfile = $instance->create_task_file($formdata);

        // Remove test id=2 and file id=2, remove compiler test and file
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML(self::EXPECTED_BASE);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[2];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[2];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[0];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[0];
        $node->parentNode->removeChild($node);
        $expectedxml = $xmldoc->saveXML();      
               
        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_setlx_file_2_tests() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'setlx2');
        $instance = new qtype_proforma_setlx_task;
        $taskfile = $instance->create_task_file($formdata);

        // Do not remove anything
        $expectedxml = self::EXPECTED_BASE;
        /*
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML(self::EXPECTED);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[0];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[0];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('tests')[0]->getElementsByTagName('test')[2];
        $node->parentNode->removeChild($node);
        $node = $xmldoc->getElementsByTagName('files')[0]->getElementsByTagName('file')[2];
        $node->parentNode->removeChild($node);
        $expectedxml = $xmldoc->saveXML();*/
               
        $this->assert_same_xml($expectedxml, $taskfile);
    }
}
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
 * This file contains unit tests for handling ProFormA task files
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


class qtype_proforma_proforma_task_test extends advanced_testcase {

    // TODO: also used in question_test.php!
    public function assert_same_xml($expectedxml, $xml) {
        // remove comments
        $xml = preg_replace('/<!--(.|\s)*?-->/', '', $xml);
        $expectedxml = preg_replace('/<!--(.|\s)*?-->/', '', $expectedxml);
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    public function test_get_question_summary() {
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java1');
        $taskfile = qtype_proforma_proforma_task::create_java_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1" used-by-grader="false" visible="delayed">
            <embedded-txt-file filename="de/ostfalia/gdp/ws19/s4/VerEntschluesselung.java">
                <![CDATA[  ]]>
            </embedded-txt-file>
        </file>
        <file id="2" used-by-grader="true" visible="no">
            <embedded-txt-file filename="de/ostfalia/gdp/ws19/s4/test/VerEntschluesselungTest.java">
                <![CDATA[  ]]>
            </embedded-txt-file>
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
            <title>Compiler Test</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>Junit Test ostfalia/gdp/ws19/s4/test/VerEntschluesselungTest</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="2"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12-gruendel">
                    <unit:entry-point>de.ostfalia.gdp.ws19.s4.test.VerEntschluesselungTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
    </tests>
    <grading-hints/>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }

}
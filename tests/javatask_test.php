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
require_once($CFG->dirroot . '/question/type/proforma/classes/java_task.php');


class qtype_proforma_java_task_test extends advanced_testcase {

    // TODO: also used in question_test.php!
    public function assert_same_xml($expectedxml, $xml) {
        // remove comments
        $xml = preg_replace('/<!--(.|\s)*?-->/', '', $xml);
        $expectedxml = preg_replace('/<!--(.|\s)*?-->/', '', $expectedxml);
        // remove uuid
        $xml = preg_replace('/uuid="(.|\s)*?"/', 'uuid="removed"', $xml);
        $expectedxml = preg_replace('/uuid="(.|\s)*?"/', 'uuid="removed"', $expectedxml);
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    public function test_create_java_file1() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java1unit');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1" used-by-grader="true" visible="no">
            <embedded-txt-file filename="XTest.java">class XTest {}</embedded-txt-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>XTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        // handle non-unix line endings in testsfile
        // $expectedxml = str_replace("\r\n", "\n", $expectedxml);
        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_java_file_no_junit() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java5');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }


    public function test_create_java_file_without_checkstyle() {
        $this->resetAfterTest(true);
        $this->setAdminUser();

        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java2');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1" used-by-grader="true" visible="no">
            <embedded-txt-file filename="XTest.java">class XTest {}</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>XTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_java_without_compilation() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java3');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1" used-by-grader="true" visible="no">
            <embedded-txt-file filename="XTest.java">class XTest {}</embedded-txt-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>XTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_java_file_2_junits() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java_2junit');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1" used-by-grader="true" visible="no">
            <embedded-txt-file filename="XTest.java">class XTest {}</embedded-txt-file>
        </file>
        <file id="2" used-by-grader="true" visible="no">
            <embedded-txt-file filename="YTest.java">class YTest {}</embedded-txt-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>XTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="2">
            <title>JUnit Test 2</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="2"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="5">
                    <unit:entry-point>YTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_java_file_unittestfile_1() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'javafile1');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1-1" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest.java">' . base64_encode('class Junittest {}') . '</embedded-bin-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1-1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>entrypoint</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        // handle non-unix line endings in testsfile
        // $expectedxml = str_replace("\r\n", "\n", $expectedxml);
        $this->assert_same_xml($expectedxml, $taskfile);
    }

    public function test_create_java_file_2_junit_files() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java_2junit_file');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1-1" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest1.java">' . base64_encode('class XTest1 {}') . '</embedded-bin-file>
        </file>
        <file id="1-2" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest2.java">' . base64_encode('class XTest2 {}') . '</embedded-bin-file>
        </file>
        <file id="2-1" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest.java">' . base64_encode('class Junittest {}') . '</embedded-bin-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1-1"/>
                    <fileref refid="1-2"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>entrypoint1</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="2">
            <title>JUnit Test 2</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="2-1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="5">
                    <unit:entry-point>entrypoint2</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }



    public function test_create_java_3junit_file() {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        // Create sample form data
        $formdata = test_question_maker::get_question_form_data('proforma', 'java_3junit_file');
        $instance = new qtype_proforma_java_task;
        $taskfile = $instance->create_task_file($formdata);

        $expectedxml = '<?xml version="1.0" encoding="UTF-8"?>
<task xmlns="urn:proforma:v2.0" lang="de" uuid="bbbf6679-0226-4fb3-8da0-4f370dd027cb" xmlns:unit="urn:proforma:tests:unittest:v1.1" xmlns:cs="urn:proforma:tests:java-checkstyle:v1.1">
    <title>ProFormA question (äöüß)</title>
    <description>Please code the reverse string function not using a library function.(äöüß)</description>
    <proglang version="1.8">java</proglang>
    <submission-restrictions/>
    <files>
        <file id="1-1" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest1.java">' . base64_encode('class XTest1 {}') . '</embedded-bin-file>
        </file>
        <file id="1-2" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest2.java">' . base64_encode('class XTest2 {}') . '</embedded-bin-file>
        </file>
        <file id="2-1" used-by-grader="true" visible="no">
            <embedded-bin-file filename="junittest.java">' . base64_encode('class Junittest {}') . '</embedded-bin-file>
        </file>
        <file id="3" used-by-grader="true" visible="no">
            <embedded-txt-file filename="ZTest.java">class ZTest {}</embedded-txt-file>
        </file>
        <file id="checkstyle" used-by-grader="true" visible="no">
            <embedded-txt-file filename="checkstyle.xml">&lt;?xml version=&quot;1.0&quot; encoding=&quot;UTF-8&quot;?&gt;
&lt;!DOCTYPE module PUBLIC &quot;-//Puppy Crawl//DTD Check Configuration 1.3//EN&quot; &quot;http://www.puppycrawl.com/dtds/configuration_1_3.dtd&quot;&gt;
&lt;module name=&quot;Checker&quot;&gt;
  &lt;property name=&quot;severity&quot; value=&quot;warning&quot;/&gt;
  &lt;module name=&quot;TreeWalker&quot;&gt;
    &lt;module name=&quot;NeedBraces&quot;&gt;
      &lt;property name=&quot;severity&quot; value=&quot;error&quot;/&gt;
    &lt;/module&gt;
  &lt;/module&gt;
&lt;/module&gt;</embedded-txt-file>
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
            <title>Compiler</title>
            <test-type>java-compilation</test-type>
            <test-configuration/>
        </test>
        <test id="1">
            <title>JUnit Test 1</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="1-1"/>
                    <fileref refid="1-2"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="4.12">
                    <unit:entry-point>entrypoint1</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="2">
            <title>JUnit Test 2</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="2-1"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="5">
                    <unit:entry-point>entrypoint2</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="3">
            <title>JUnit Test 3</title>
            <test-type>unittest</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="3"/>
                </filerefs>
                <unit:unittest framework="JUnit" version="5">
                    <unit:entry-point>ZTest</unit:entry-point>
                </unit:unittest>
            </test-configuration>
        </test>
        <test id="checkstyle">
            <title>CheckStyle Test</title>
            <test-type>java-checkstyle</test-type>
            <test-configuration>
                <filerefs>
                    <fileref refid="checkstyle"/>
                </filerefs>
                <cs:java-checkstyle version="8.23">
                    <cs:max-checkstyle-warnings>4</cs:max-checkstyle-warnings>
                </cs:java-checkstyle>
            </test-configuration>
        </test>
    </tests>
    <grading-hints>
        <root/>
    </grading-hints>
    <meta-data/>
</task>
';

        $this->assert_same_xml($expectedxml, $taskfile);
    }



    public function test_class1() {
        $code =
'// class FakeClass (this is not the class)
public class MyClass {
    int x = 11;
}';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('MyClass.java', $instance->get_java_file($code));
        $this->assertEquals('MyClass', $instance->get_java_entrypoint($code));
    }

    public function test_class2() {
        $code =
'/*
    class FakeClass (this is not the class)
*/
public class MyClass {
    int x = 11;
}';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('MyClass.java', $instance->get_java_file($code));
        $this->assertEquals('MyClass', $instance->get_java_entrypoint($code));
    }

    public function test_class3() {
        $code =
'/*
    class FakeClass (this is not the class)
*/
// class FakeClass1 (this is not the class, too)
public class MyClass {
    int x = 11;
}';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('MyClass.java', $instance->get_java_file($code));
        $this->assertEquals('MyClass', $instance->get_java_entrypoint($code));
    }

    public function test_class4() {
        $code = 'class Animal {}
class Pig extends Animal {}
class Dog extends Animal {}
';
        $instance = new qtype_proforma_java_task;
        // ok, but better would be null
        $this->assertEquals('Animal.java', $instance->get_java_file($code));
        $this->assertEquals('Animal', $instance->get_java_entrypoint($code));
    }

    public function test_class5() {
        $code = '// class FakeClass (this is not the class)
    int x = 11;
';
        $instance = new qtype_proforma_java_task;
        $this->assertNull($instance->get_java_file($code));
        $this->assertNull($instance->get_java_entrypoint($code));
    }

    public function test_class6() {
        $code = '/*
    class FakeClass (this is not the class)
*/
    int x = 11;';
        $instance = new qtype_proforma_java_task;
        $this->assertNull($instance->get_java_file($code));
        $this->assertNull($instance->get_java_entrypoint($code));
    }

    public function test_class7() {
        $code = '/*
    class FakeClass (this is not the class)
*/
// class FakeClass1 (this is not the class, too)
    int x = 11;';
        $instance = new qtype_proforma_java_task;
        $this->assertNull($instance->get_java_file($code));
        $this->assertNull($instance->get_java_entrypoint($code));
    }

    public function test_generic1() {
        $code = 'class Animal<T> {}';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('Animal.java', $instance->get_java_file($code));
        $this->assertEquals('Animal<T>', $instance->get_java_entrypoint($code));
    }

    public function test_generic2() {
        $code = 'class Animal < T > {}';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('Animal.java', $instance->get_java_file($code));
        $this->assertEquals('Animal<T>', $instance->get_java_entrypoint($code));
    }

    public function test_generic3() {
        $code = 'class name<T1, T2, T3> { /* ... */ }';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('name.java', $instance->get_java_file($code));
        $this->assertEquals('name<T1,T2,T3>', $instance->get_java_entrypoint($code));
    }

    public function test_generic4() {
        $code = 'class name<T1, T2, T3> { /* .>.. */ }';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('name.java', $instance->get_java_file($code));
        $this->assertEquals('name<T1,T2,T3>', $instance->get_java_entrypoint($code));
    }

    public function test_generic5() {
        $code = 'class name<T1, /* > */ T2, T3> { /* .>.. */ }';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('name.java', $instance->get_java_file($code));
        $this->assertEquals('name<T1,T2,T3>', $instance->get_java_entrypoint($code));
    }

    public function test_generic6() {
        $code = 'class name<T1, // comment1
            T2, // comment2
            T3> { // comment3
                /* .>.. */ }';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('name.java', $instance->get_java_file($code));
        $this->assertEquals('name<T1,T2,T3>', $instance->get_java_entrypoint($code));
    }

    /** TODO handle interfaces  */
    /*
    public function test_generic7() {
        $code = 'public interface IName<T> {  }';
        $instance = new qtype_proforma_java_task;
        $this->assertEquals('IName.java', $instance->get_java_file($code));
        $this->assertEquals('IName<T>', $instance->get_java_entrypoint($code));
    }
*/

}
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
 * This file contains unit tests for class qtype_proforma
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2010 The Open University (for parts from essay question type)
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');
require_once($CFG->dirroot . '/question/type/proforma/tests/walkthrough_test_base.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');


class qtype_proforma_questiontype_test extends qtype_proforma_walkthrough_test_base {
    protected $qtype;

    protected function setUp() {
        $this->qtype = new qtype_proforma();
    }

    protected function tearDown() {
        $this->qtype = null;
    }

    protected function get_test_question_data() {
        $q = new stdClass();
        $q->id = 1;

        return $q;
    }

    public function test_name() {
        $this->assertEquals($this->qtype->name(), 'proforma');
    }


    public function test_can_analyse_responses() {
        $this->assertFalse($this->qtype->can_analyse_responses());
    }

    public function test_get_random_guess_score() {
        $q = $this->get_test_question_data();
        $this->assertEquals(0, $this->qtype->get_random_guess_score($q));
    }

    public function test_get_possible_responses() {
        $q = $this->get_test_question_data();
        $this->assertEquals(array(), $this->qtype->get_possible_responses($q));

    }

    public function assert_same_xml($expectedxml, $xml) {
        // remove comments
        $xml = preg_replace('/<!--(.|\s)*?-->/', '', $xml);
        $expectedxml = preg_replace('/<!--(.|\s)*?-->/', '', $expectedxml);
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }

    public function test_xml_export_and_import() {
        global $CFG, $USER;
        $this->resetAfterTest(true);
        $this->setAdminUser();
        //$usercontextid = context_user::instance($USER->id)->id;

        // Create a proforma question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('proforma', 'editor', array('category' => $cat->id));
        $question->contextid = 1; // must be the same as in questiontype.save_question_options
        // where do we get it? evaluated by debugging :-(
        $question->hidden = null; // dummy

        $questiontype = new qtype_proforma();
        $exporter = new qformat_xml();

        $questiontype->get_question_options($question);

        ////$question = test_question_maker::make_question('proforma', 'editor');
        //$question = test_question_maker::get_question_data('proforma', 'editor');

        //$export = $questiontype->export_to_xml($question, $exporter);
        $export1 = $exporter->writequestion($question);

        $xmldata = xmlize($export1);

        // re-import
        $importer = new qformat_xml();
        $importedq = $importer->try_importing_using_qtypes(
                $xmldata['question'], null, null, 'proforma');

        // problem:
        // - exported question contains values in 'options' member
        // - imported question contains values in 'normal' members
        // => we save the imported question and reload it, afterwards the data
        // are in the 'options' member, too

        $importedq->id = 333; // new
        $importedq->context = context_course::instance(1);
        $questiontype->save_question_options($importedq);
        $questiontype->get_question_options($importedq);
        $importedq->contextid = $importedq->context->id;

        $importedq->hidden = null;
        $export2 = $exporter->writequestion($importedq);

        //$export1->id = $export2->id; // in order to ignore question id
        $this->assert_same_xml($export1, $export2);

    }

    public function test_xml_export_editor() {
        global $CFG, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $usercontextid = context_user::instance($USER->id)->id;

        // Create a proforma question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('proforma', 'editor', array('category' => $cat->id));
        $question->contextid = 1; // must be the same as in questiontype.save_question_options
        // where do we get it? evaluated by debugging :-(
        $question->hidden = null; // dummy

        $questiontype = new qtype_proforma();
        $questiontype->get_question_options($question);
        $exporter = new qformat_xml();
        //$export = $questiontype->export_to_xml($question, $exporter);
        $export = $exporter->writequestion($question);

        $expectedxml = '<!-- question: '. $question->id . '  -->
  <question type="proforma">
    <name>
      <text>'.qtype_proforma_test_helper::QUESTION_NAME.'</text>
    </name>
    <questiontext format="html">
      <text>'.qtype_proforma_test_helper::QUESTION_TEXT.'</text>
    </questiontext>
    <generalfeedback format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_GENERAL_FEEDBACK.']]></text>
    </generalfeedback>
    <defaultgrade>1</defaultgrade>
    <penalty>0.2</penalty>
    <hidden></hidden>
    <idnumber></idnumber>
    <uuid>UUID 1</uuid>
    <proformaversion>2.0</proformaversion>
    <taskrepository>'.qtype_proforma_test_helper::QUESTION_REPOSITORY.'</taskrepository>
    <taskpath>'.qtype_proforma_test_helper::QUESTION_PATH.'</taskpath>
    <taskfilename>'.qtype_proforma_test_helper::QUESTION_TASKFILENAME.'</taskfilename>
    <responsefilename>'.qtype_proforma_test_helper::QUESTION_FILENAME.'</responsefilename>
    <programminglanguage>java</programminglanguage>
    <responsetemplate>'.qtype_proforma_test_helper::QUESTION_TEMPLATE.'</responsetemplate>
    <responseformat>editor</responseformat>
    <responsefieldlines>10</responsefieldlines>
    <attachments>0</attachments>
    <maxbytes>10240</maxbytes>
    <filetypes>.java, .jar</filetypes>
    <taskstorage>'.qtype_proforma_test_helper::QUESTION_TASKSTORAGE.'</taskstorage>
    <aggregationstrategy>1</aggregationstrategy>
    <gradinghints><![CDATA['.qtype_proforma_test_helper::QUESTION_GRADINGHINTS.']]></gradinghints>
    <vcsuritemplate></vcsuritemplate>
    <vcslabel></vcslabel>
    <templates>'.qtype_proforma_test_helper::QUESTION_TEMPLATES.'</templates>
    <downloads>'.qtype_proforma_test_helper::QUESTION_DOWNLOADS.'</downloads>
    <modelsolfiles>'.qtype_proforma_test_helper::QUESTION_MODELSOLS.'</modelsolfiles>
    <comment format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_COMMENT.']]></text>
    </comment>
<templatefiles><file name="'.qtype_proforma_test_helper::QUESTION_TEMPLATES.'" path="/" encoding="base64">Ly90ZXh0IGluIHJlc3BvbnNldGVtcGxhdGU=</file>
</templatefiles>
<downloadfiles><file name="instruction.txt" path="/" encoding="base64">SU5TVFJVQ1RJT04tRHVtbXk=</file>
<file name="lib.txt" path="/" encoding="base64">TElCLUR1bW15</file>
</downloadfiles>
<modelsolutionfiles><file name="ms1.txt" path="/" encoding="base64">TVMxLUR1bW15</file>
<file name="ms2.txt" path="/" encoding="base64">TVMyLUR1bW15</file>
</modelsolutionfiles>
<task><file name="testtask.zip" path="/" encoding="base64">VGFzay5aaXAtRHVtbXk=</file>
</task>
<commentfiles></commentfiles>
    <hint format="html">
      <text><![CDATA[hint 1<br>]]></text>
    </hint>
    <hint format="html">
      <text><![CDATA[hint 2<br>]]></text>
    </hint>
  </question>
';

        $this->assert_same_xml($expectedxml, $export);
    }


    public function test_xml_export_filepicker() {
        global $CFG, $USER;

        $this->resetAfterTest(true);
        $this->setAdminUser();
        $usercontextid = context_user::instance($USER->id)->id;

        // Create a proforma question in the DB.
        $generator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $generator->create_question_category();
        $question = $generator->create_question('proforma', 'filepicker', array('category' => $cat->id));
        $question->contextid = 1; // must be the same as in questiontype.save_question_options
        // where do we get it? evaluated by debugging :-(
        $question->hidden = null; // dummy

        $questiontype = new qtype_proforma();
        $questiontype->get_question_options($question);
        $exporter = new qformat_xml();
        //$export = $questiontype->export_to_xml($question, $exporter);
        $export = $exporter->writequestion($question);

        $expectedxml = '<!-- question: '. $question->id . '  -->
  <question type="proforma">
    <name>
      <text>'.qtype_proforma_test_helper::QUESTION_NAME.'</text>
    </name>
    <questiontext format="html">
      <text>'.qtype_proforma_test_helper::QUESTION_TEXT.'</text>
    </questiontext>
    <generalfeedback format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_GENERAL_FEEDBACK.']]></text>
    </generalfeedback>
    <defaultgrade>1</defaultgrade>
    <penalty>0.2</penalty>
    <hidden></hidden>
    <idnumber></idnumber>
    <uuid>UUID 2</uuid>
    <proformaversion>2.0</proformaversion>
    <taskrepository>'.qtype_proforma_test_helper::QUESTION_REPOSITORY.'</taskrepository>
    <taskpath>'.qtype_proforma_test_helper::QUESTION_PATH.'</taskpath>
    <taskfilename>'.qtype_proforma_test_helper::QUESTION_TASKFILENAME.'</taskfilename>
    <responsefilename>'.qtype_proforma_test_helper::QUESTION_FILENAME.'</responsefilename>
    <programminglanguage>python</programminglanguage>
    <responsetemplate>'.qtype_proforma_test_helper::QUESTION_TEMPLATE.'</responsetemplate>
    <responseformat>filepicker</responseformat>
    <responsefieldlines>10</responsefieldlines>
    <attachments>3</attachments>
    <maxbytes>10240</maxbytes>
    <filetypes>.java, .jar</filetypes>
    <taskstorage>'.qtype_proforma_test_helper::QUESTION_TASKSTORAGE.'</taskstorage>
    <aggregationstrategy>1</aggregationstrategy>
    <gradinghints><![CDATA['.qtype_proforma_test_helper::QUESTION_GRADINGHINTS.']]></gradinghints>
    <vcsuritemplate></vcsuritemplate>
    <vcslabel></vcslabel>
    <templates>'.qtype_proforma_test_helper::QUESTION_TEMPLATES_2.'</templates>
    <downloads>'.qtype_proforma_test_helper::QUESTION_DOWNLOADS.'</downloads>
    <modelsolfiles>'.qtype_proforma_test_helper::QUESTION_MODELSOLS.'</modelsolfiles>
    <comment format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_COMMENT.']]></text>
    </comment>
<templatefiles><file name="'.qtype_proforma_test_helper::QUESTION_TEMPLATES_2.'" path="/" encoding="base64">I2NvZGUgc25pcHBldCBmb3IgcHl0aG9u</file>
</templatefiles>
<downloadfiles><file name="instruction.txt" path="/" encoding="base64">SU5TVFJVQ1RJT04tRHVtbXk=</file>
<file name="lib.txt" path="/" encoding="base64">TElCLUR1bW15</file>
</downloadfiles>
<modelsolutionfiles><file name="ms1.txt" path="/" encoding="base64">TVMxLUR1bW15</file>
<file name="ms2.txt" path="/" encoding="base64">TVMyLUR1bW15</file>
</modelsolutionfiles>
<task><file name="testtask.zip" path="/" encoding="base64">VGFzay5aaXAtRHVtbXk=</file>
</task>
<commentfiles></commentfiles>
    <hint format="html">
      <text><![CDATA[hint 1<br>]]></text>
    </hint>
    <hint format="html">
      <text><![CDATA[hint 2<br>]]></text>
    </hint>
  </question>
';

        $this->assert_same_xml($expectedxml, $export);
    }


    public function test_xml_import() {

        $xml = '<!-- question: 0  -->
  <question type="proforma">
    <name>
      <text>'.qtype_proforma_test_helper::QUESTION_NAME.'</text>
    </name>
    <questiontext format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_TEXT.']]></text>
    </questiontext>
    <generalfeedback format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_GENERAL_FEEDBACK.']]></text>
    </generalfeedback>
    <defaultgrade>1</defaultgrade>
    <penalty>0.2</penalty>
    <hidden></hidden>
    <idnumber></idnumber>    
    <uuid>UUID 1</uuid>
    <taskrepository>'.qtype_proforma_test_helper::QUESTION_REPOSITORY.'</taskrepository>
    <taskpath>'.qtype_proforma_test_helper::QUESTION_PATH.'</taskpath>
    <taskfilename>taskfile.zip</taskfilename>    
    <responsefilename>'.qtype_proforma_test_helper::QUESTION_FILENAME.'</responsefilename>
    <programminglanguage>java</programminglanguage>
    <responsetemplate>'.qtype_proforma_test_helper::QUESTION_TEMPLATE.'</responsetemplate>
    <responseformat>editor</responseformat>
    <responsefieldlines>10</responsefieldlines>
    <attachments>0</attachments>
    <maxbytes>10001</maxbytes>
    <filetypes>.jjj</filetypes>
    <taskstorage>'.qtype_proforma_test_helper::QUESTION_TASKSTORAGE.'</taskstorage>
    <aggregationstrategy>2</aggregationstrategy>
    <gradinghints><![CDATA['.qtype_proforma_test_helper::QUESTION_GRADINGHINTS.']]></gradinghints>
    <proformaversion>2.0</proformaversion>    
    <templates>'.qtype_proforma_test_helper::QUESTION_TEMPLATES.'</templates>
    <downloads>'.qtype_proforma_test_helper::QUESTION_DOWNLOADS.'</downloads>
    <modelsolfiles>'.qtype_proforma_test_helper::QUESTION_MODELSOLS.'</modelsolfiles>
    <comment format="html">
      <text><![CDATA['.qtype_proforma_test_helper::QUESTION_COMMENT.']]></text>
    </comment>
<templatefiles><file name="temp.txt" path="/" encoding="base64">Ly90ZXh0IGluIHJlc3BvbnNldGVtcGxhdGUgKMOkw7bDvMOfKQ==</file>
</templatefiles>
<downloadfiles>
<file name="instruction.txt" path="/" encoding="base64">SU5TVFJVQ1RJT04tRHVtbXk=</file>
<file name="lib.txt" path="/" encoding="base64">TElCLUR1bW15</file>
</downloadfiles>
<modelsolutionfiles><file name="ms1.txt" path="/" encoding="base64">TVMxLUR1bW15</file>
<file name="ms2.txt" path="/" encoding="base64">TVMyLUR1bW15</file>
</modelsolutionfiles>
<task><file name="testtask.zip" path="/" encoding="base64">VGFzay5aaXAtRHVtbXk=</file>
</task> 
<commentfiles></commentfiles>
    <hint format="html">
      <text><![CDATA[hint 1<br>]]></text>
    </hint>
    <hint format="html">
      <text><![CDATA[hint 2<br>]]></text>
    </hint>
  </question>
';


        $this->resetAfterTest(true);
        $this->setAdminUser();

        $xmldata = xmlize($xml);

        $importer = new qformat_xml();
        $importedq = $importer->try_importing_using_qtypes(
                $xmldata['question'], null, null, 'proforma');

        $expectedq = new stdClass();
        $expectedq->qtype                 = 'proforma';
        $expectedq->name                  = qtype_proforma_test_helper::QUESTION_NAME;
        $expectedq->questiontext          = qtype_proforma_test_helper::QUESTION_TEXT;
        $expectedq->questiontextformat    = FORMAT_HTML;
        $expectedq->generalfeedback       = qtype_proforma_test_helper::QUESTION_GENERAL_FEEDBACK;
        $expectedq->generalfeedbackformat = FORMAT_HTML;
        $expectedq->defaultmark           = 1;
        $expectedq->length                = 1;
        $expectedq->penalty               = 0.2;

        $expectedq->taskrepository = qtype_proforma_test_helper::QUESTION_REPOSITORY;
        $expectedq->taskpath = qtype_proforma_test_helper::QUESTION_PATH;
        $expectedq->taskfilename = 'taskfile.zip';
        $expectedq->taskstorage = qtype_proforma_test_helper::QUESTION_TASKSTORAGE;

        $expectedq->responsefilename = qtype_proforma_test_helper::QUESTION_FILENAME;
        $expectedq->programminglanguage = 'java';
        // $expectedq->modelsolution = qtype_proforma_test_helper::QUESTION_MODELSOLUTION;
        $expectedq->responsetemplate = qtype_proforma_test_helper::QUESTION_TEMPLATE;
        $expectedq->uuid = 'UUID 1';

        $expectedq->responseformat = 'editor';
        $expectedq->responsefieldlines = 10;
        $expectedq->attachments = 0;
        $expectedq->maxbytes = 10001;
        $expectedq->filetypes = '.jjj';
        $expectedq->comment = qtype_proforma_test_helper::QUESTION_COMMENT;
        $expectedq->commentformat = FORMAT_HTML;
        $expectedq->penalty = 0.20000;
        $expectedq->proformaversion = '2.0';
        $expectedq->aggregationstrategy = 2;
        $expectedq->gradinghints = qtype_proforma_test_helper::QUESTION_GRADINGHINTS;

        $expectedq->downloads = qtype_proforma_test_helper::QUESTION_DOWNLOADS;
        $expectedq->modelsolfiles = qtype_proforma_test_helper::QUESTION_MODELSOLS;

        $expectedq->hint = array(
                array('text' => 'hint 1<br>', 'format' => FORMAT_HTML),
                array('text' => 'hint 2<br>', 'format' => FORMAT_HTML),
        );

        $this->assertEquals($expectedq->hint, $importedq->hint); // redundant but better feedback on fail
        $this->assert(new question_check_specified_fields_expectation($expectedq), $importedq);

        // check for existing file id
        $this->assertEquals(true, isset($importedq->task));
        $this->assertEquals(true, isset($importedq->template));
        $this->assertEquals(true, isset($importedq->download));
        $this->assertEquals(true, isset($importedq->modelsol));
    }

}

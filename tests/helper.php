<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test helpers for the proforma question type.
 *
 * @package    qtype_proforma
 * @copyright  2013 The Open University (for code from essay question type)
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/*
 * in Moodle 3.6 and later the property 'template' is overridden by the test environment
   which results in failing Behat tests.
   Workaround: we use a copy of the value named 'original_template'.
   Even if no template is used we must set this variable (to ''). Otherwise
   the proforma code thinks that there is a template. :-(
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

/**
 * Test helper class for the proforma question type.
 *
 * @copyright  2013 The Open University
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_proforma_test_helper extends question_test_helper {
    public function get_test_questions() {
        return array(
            // Different response formats.
            'editor',
            'filepicker',
            // Java.
            'java1', // One JUnit test, checkstyle, compilation.
            'java1unit', // Like java, different question text.
            'java2', // Like java1unit, no Checkstyle.
            'java3', // Like java1unit, no compilation.
            'java_2junit', // Like java1unit with 2 JUnit tests, default mark=3.
            'java5', // Like java1unit, no JUnit.
            'javafile1', // Like java1unit, with test code as uploaded file.
            'java_2junit_file', // Like java_2junit, with test code as uploaded file.
            'java_3junit_file', // Like java_2junit_file with 3 tests
            // Setlx.
            'setlx0', 'setlx1', 'setlx1a', 'setlx2',
            // Different grading approaches.
            'weightedsum',
            // Different values set in question.
            'downloads',
            'responsetemplate', 'modelsolution', 'penalty', // Different values.
            'gradecorrect', 'gradeincorrect', 'wronggraderfilename');
    }

    const QUESTION_NAME = 'ProFormA question (äöüß)';

    // This string is also used in behat tests. Behat obviously does not support HTML tags.
    // So they are not used here though supported.
    const QUESTION_TEXT = 'Please code the reverse string function not using a library function.(äöüß)';
    // const QUESTION_TEXT = '<p>Please code the reverse string function <b>not</b> using a library function.(äöüß)</p>';

    const QUESTION_GENERAL_FEEDBACK = '<p>You must not use a library function.</p>';
    const QUESTION_COMMENT = '<p>Check if the code uses a library function.</p>';
    const QUESTION_REPOSITORY = '';
    const QUESTION_PATH = '';
    const QUESTION_FILENAME = 'MyString.java';
    //const QUESTION_MODELSOLUTION = '//text in modelsolution (äöüß)';
    const QUESTION_TEMPLATE = '//text in responsetemplate';

    const QUESTION_INSTRUCTIONS = 'instruction.txt';
    const QUESTION_LIBRARIES = 'lib.txt';
    const QUESTION_DOWNLOADS =  qtype_proforma_test_helper::QUESTION_INSTRUCTIONS .', '.
        qtype_proforma_test_helper::QUESTION_LIBRARIES;

    const QUESTION_TEMPLATES = 'template.txt';
    const QUESTION_TEMPLATES_2 = 'codesnippet.py';
    const QUESTION_MODELSOLS = 'ms1.txt, ms2.txt';
    const QUESTION_TASKFILENAME = 'testtask.zip';
    const QUESTION_TASKFILE = 'das ist das zip-file von testtask.zip';
    const QUESTION_TASKSTORAGE = qtype_proforma::PERSISTENT_TASKFILE;

    const QUESTION_PROFORMAVERSION = '2.0';
    const QUESTION_AGGREGATIONSTRATEGY = qtype_proforma::ALL_OR_NOTHING;
    const QUESTION_GRADINGHINTS = '<grading-hints>'.
  '<root function="sum">'.
   '<test-ref ref="1" weight="2">
        <title>TEST 1</title>
        <test-type>TEST-CONFIG 1</test-type>
        <description>DESCRIPTION 1</description>
    </test-ref>'.
   '<test-ref ref="2" weight="3">
        <title>TEST 2</title>
        <test-type>TEST-CONFIG 2</test-type>
        <description>DESCRIPTION 2</description>
    </test-ref>'.
  '</root>'.
 '</grading-hints>';


    const QUESTION_GRADINGHINTS_JAVA = '<grading-hints>'.
    '<root function="sum">'.
    '<test-ref ref="compiler" weight="2">
        <title>Compiler Test</title>
        <test-type>java-compilation</test-type>
        <description>DESCRIPTION 1</description>
    </test-ref>'.
    '<test-ref ref="1" weight="3">
        <title>Junit Test 1</title>
        <test-type>unittest</test-type>
        <description>DESCRIPTION 2</description>
    </test-ref>'.
    '<test-ref ref="checkstyle" weight="4">
        <title>Checkstyle</title>
        <test-type>java-checkstyle</test-type>
        <description>DESCRIPTION 3</description>
    </test-ref>'.
    '</root>'.
    '</grading-hints>';

    const QUESTION_GRADINGHINTS_JAVA_2JUNIT = '<grading-hints>'.
    '<root function="sum">'.
    '<test-ref ref="compiler" weight="2">
        <title>Compiler Test</title>
        <test-type>java-compilation</test-type>
        <description>DESCRIPTION 1</description>
    </test-ref>'.
    '<test-ref ref="1" weight="3">
        <title>Junit Test 1</title>
        <test-type>unittest</test-type>
        <description>Description Junit 1</description>
    </test-ref>'.
    '<test-ref ref="2" weight="6">
        <title>Junit Test 2</title>
        <test-type>unittest</test-type>
        <description>Description Junit 2</description>
    </test-ref>'.
    '<test-ref ref="checkstyle" weight="4">
        <title>Checkstyle</title>
        <test-type>java-checkstyle</test-type>
        <description>DESCRIPTION 3</description>
    </test-ref>'.
    '</root>'.
    '</grading-hints>';


    const QUESTION_GRADINGHINTS_SETLX = '<grading-hints>'.
    '<root function="sum">'.
    '<test-ref ref="compiler" weight="0">
        <title>Compiler Test</title>
        <test-type>setlx</test-type>
        <description>DESCRIPTION 1</description>
    </test-ref>'.
    '<test-ref ref="1" weight="3">
        <title>Setlx Test 1</title>
        <test-type>setlx</test-type>
        <description>DESCRIPTION 2</description>
    </test-ref>'.
    '</root>'.
    '</grading-hints>';

    const QUESTION_GRADINGHINTS_SETLX2= '<grading-hints>'.
    '<root function="sum">'.
    '<test-ref ref="compiler" weight="2">
        <title>Compiler Test</title>
        <test-type>setlx</test-type>
        <description>DESCRIPTION OF Syntax Check</description>
    </test-ref>'.
    '<test-ref ref="1" weight="3">
        <title>Setlx Test 1</title>
        <test-type>setlx</test-type>
        <description>DESCRIPTION 1</description>
    </test-ref>'.
    '<test-ref ref="2" weight="6">
        <title>Setlx Test 2</title>
        <test-type>setlx</test-type>
        <description>DESCRIPTION 2</description>
    </test-ref>'.
    '</root>'.
    '</grading-hints>';

    private function attach_file_to_question($text, $filename, $filearea, $itemid, $contextid) {
        $fs = get_file_storage();

        $filerecord = new stdClass();
        $filerecord->contextid = $contextid;
        $filerecord->component = 'qtype_proforma';
        $filerecord->filearea = $filearea;
        $filerecord->itemid = $itemid;
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;

        $fs->create_file_from_string($filerecord, $text);
    }

    private function get_proforma_data($container) {
        //$container->qtype = 'proforma';
        $container->uuid = 'UUID 1';

        // valid data for a task file in the repository!
        $container->taskrepository = null; // self::QUESTION_REPOSITORY;
        $container->taskpath = null; // self::QUESTION_PATH;
        $container->taskfilename = self::QUESTION_TASKFILENAME;
        $container->taskstorage = self::QUESTION_TASKSTORAGE;

        $container->responsefilename = ' ' . self::QUESTION_FILENAME . ' ';
        $container->programminglanguage = 'java';

        // $container->modelsolution = self::QUESTION_MODELSOLUTION;
        $container->responsetemplate = self::QUESTION_TEMPLATE;
        $container->templates = self::QUESTION_TEMPLATES;

        $container->downloads = self::QUESTION_DOWNLOADS;
        $container->modelsolfiles = self::QUESTION_MODELSOLS;
        $container->gradinghints = self::QUESTION_GRADINGHINTS;
        $container->aggregationstrategy = self::QUESTION_AGGREGATIONSTRATEGY;
        $container->proformaversion = self::QUESTION_PROFORMAVERSION;

        // in Moodle 3.6 and later the property 'template' is overridden by the test environment
        // which results in failing Behat tests.
        // Workaround: we use a copy of the value named 'original_template'.
        // Even if no template is used we must set this variable. Otherwise
        // the proforma code thinks that there is a template.
        $container->original_template = '';
    }

    private function get_form_data($container) {
        $this->get_proforma_data($container);
        $container->name = self::QUESTION_NAME;
        $container->questiontext = array('text' => self::QUESTION_TEXT, 'format' => FORMAT_HTML);
        $container->defaultmark = 1.0;
        $container->generalfeedback = array('text' => self::QUESTION_GENERAL_FEEDBACK, 'format' => FORMAT_HTML);
        $container->penalty = 0.2;

        $container->responseformat = 'editor';
        $container->responsefieldlines = 10;
        $container->attachments = 15;
        $container->comment = array('text' => self::QUESTION_COMMENT, 'format' => FORMAT_HTML);
        $container->maxbytes = 10240;
        $container->filetypes = '.java, .jar';


        $container->hint = array(
                0 => array(
                        'text' => 'hint 1<br>',
                        'format' => '1',
                        'itemid' => '83894244'),
                1 => array(
                        'text' => 'hint 2<br>',
                        'format' => '1',
                        'itemid' => '34635511'));

        if (isset($container->taskstorage) && $container->taskstorage == qtype_proforma::PERSISTENT_TASKFILE) {

            $container->task =  file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->task, $container->taskfilename,
                    'Task.Zip-Dummy');

            $container->modelsol = file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->modelsol, 'ms1.txt',
                    'MS1-Dummy');
            $this->make_attachment_in_draft_area($container->modelsol, 'ms2.txt',
                    'MS2-Dummy');

            $container->download = file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->download, self::QUESTION_LIBRARIES,
                    'LIB-Dummy');

            $this->make_attachment_in_draft_area($container->download, self::QUESTION_INSTRUCTIONS,
                    'INSTRUCTION-Dummy');

        }
    }


    /**
     * Helper method to reduce duplication.
     * @return qtype_proforma_question
     */
    protected function initialise_proforma_question() {
        question_bank::load_question_definition_classes('proforma');
        $q = new qtype_proforma_question();
        test_question_maker::initialise_a_question($q);
        //$q->contextid = context_system::instance()->id;
        $q->name = self::QUESTION_NAME;
        $q->questiontext = self::QUESTION_TEXT;
        $q->generalfeedback = self::QUESTION_GENERAL_FEEDBACK;
        $q->qtype = question_bank::get_qtype('proforma');

        $q->responseformat = 'editor';
        $q->responsefieldlines = 10;
        $q->attachments = 15;
        $q->comment = self::QUESTION_COMMENT;
        $q->commentformat = FORMAT_HTML;
        $q->penalty = 0.20000;
        $q->maxbytes = 10240;
        $q->filetypes = '.java, .jar';

        // feedback options
        $q->expandcollapse = 1;
        $q->inlinemessages = 1;
        // $q->initiallyinline = 1;


        $q->hints = array(
                new question_hint(1, 'hint 1', FORMAT_HTML),
                new question_hint(2, 'hint 2', FORMAT_HTML),
        );


        $this->get_proforma_data($q);

        return $q;
    }

    /**
     * Makes a proforma question using the editor as input.
     * @return qtype_proforma_question
     */
    public function make_proforma_question_editor() {
        $q = $this->initialise_proforma_question();
        $q->contextid = 12;
        $q->id = 75;
        $q->responseformat = 'editor';
        $q->attachments = 0;

        return $q;
    }

    /**
     * Makes a proforma question using the filepicker.
     * @return qtype_proforma_question
     */
    public function make_proforma_question_filepicker() {
        $q = $this->initialise_proforma_question();
        $q->responseformat = 'filepicker';
        $q->attachments = 3;
        $q->inlinemessages = 0;

        $q->templates = self::QUESTION_TEMPLATES_2;
        $q->programminglanguage = 'python';
        $q->responsetemplate = '#code snippet for python';
        $q->uuid = 'UUID 2';

        return $q;
    }


    public function make_proforma_question_weightedsum() {
        $q = $this->initialise_proforma_question();
        $q->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;
        return $q;
    }

    /**
     * Make the data what would be received from the editing form for a proforma
     * question using the HTML editor allowing embedded files as input, and up
     * to three attachments.
     *
     * @return stdClass the data that would be returned by $form->get_gata();
     */
    public function get_proforma_question_form_data_editor() {
        $fromform = new stdClass();

        $this->get_form_data($fromform);
        $fromform->responseformat = 'editor';
        $fromform->attachments = 0;
        $fromform->testtype[0] = 'java-compilation';
        $fromform->testtype[1] = 'unittest';
        $fromform->testtitle[0] = 'a tile 1';
        $fromform->testtitle[1] = 'a tile 2';

        // feedback options
        $fromform->expandcollapse = 1;
        $fromform->inlinemessages = 1;
        // $fromform->initiallyinline = 1;

        $fromform->template = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($fromform->template, self::QUESTION_TEMPLATES,
                self::QUESTION_TEMPLATE);

        // in Moodle 3.6 the property 'template' is overridden by the test environment
        // which results in failing tests.
        // Workaround: we use a copy of the value named 'original_template'
        $fromform->original_template = $fromform->template;

        return $fromform;
    }


    public function get_proforma_question_form_data_filepicker() {
        $fromform = new stdClass();
        $this->get_form_data($fromform);
        $fromform->responseformat = 'filepicker';
        $fromform->attachments = 3;
        $fromform->inlinemessages = 0;

        $fromform->templates = self::QUESTION_TEMPLATES_2;
        $property = qtype_proforma::FILEAREA_TEMPLATE;
        $fromform->$property = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($fromform->$property, self::QUESTION_TEMPLATES_2,
                '#code snippet for python');

        $fromform->programminglanguage = 'python';
        //$fromform->responsetemplate = '';

        $fromform->uuid = 'UUID 2';

        return $fromform;
    }

    public function get_proforma_question_form_data_weightedsum() {
        $fromform = new stdClass();
        $this->get_form_data($fromform);

        $fromform->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;

        return $fromform;
    }


    // JAVA

    // used for behat tests
    public function get_proforma_question_form_data_java1() {
        $form = new stdClass();

        // in Moodle 3.6 the property 'template' is overridden by the test environment
        // which results in failing tests.
        // Workaround: we use a copy of the value named 'original_template'.
        // Even if no template is used we must set this variable. Otherwise
        // the proforma code thinks that there is a template.
        $form->original_template = '';

        // valid data for a task file in the repository!
        $form->taskstorage = qtype_proforma::JAVA_TASKFILE;

        // Add beginning and trailing space.
        $form->responsefilename = ' ' . self::QUESTION_FILENAME . ' ';
        $form->programminglanguage = 'java';
        $form->proglangversion = '1.8';

        // $container->modelsolution = self::QUESTION_MODELSOLUTION;
        // set redundant :-( template
        $form->responsetemplate = self::QUESTION_TEMPLATE;
        $form->template = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->template, 'template.txt',
                self::QUESTION_TEMPLATE);

        $form->gradinghints = self::QUESTION_GRADINGHINTS_JAVA;
        $form->aggregationstrategy = self::QUESTION_AGGREGATIONSTRATEGY;

        $form->name = self::QUESTION_NAME;
        //$form->questiontext = self::QUESTION_TEXT;
        $form->questiontext = array('text' => self::QUESTION_TEXT, 'format' => FORMAT_HTML);
        $form->defaultmark = 1.0;
        $form->generalfeedback = array('text' => self::QUESTION_GENERAL_FEEDBACK, 'format' => FORMAT_HTML);
        $form->penalty = 0.2;

        $form->responseformat = 'editor';
        $form->modelsolution = '// code for model solution';
        $form->responsefieldlines = 10;
        $form->comment = array('text' => self::QUESTION_COMMENT, 'format' => FORMAT_HTML);
        //$form->maxbytes = 10240;
        //$form->filetypes = '.java';

        $form->testcodeformat[0] = base_form_creator::EDITORTESTINPUT;
        $form->testcode[0] = 'class XTest {}';
        $form->testtitle[0] = 'JUnit Test 1';
        $form->testweight[0] = '3';
        $form->testid[0] = '1';
        $form->testversion[0] = "4.12";

        $form->compile = 1;
        $form->compileweight = 2;

        $form->checkstyle = 1;
        $form->checkstyleweight = 4;
        $form->checkstyleversion = "8.23";
        $form->checkstylecode = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE module PUBLIC "-//Puppy Crawl//DTD Check Configuration 1.3//EN" "http://www.puppycrawl.com/dtds/configuration_1_3.dtd">
<module name="Checker">
  <property name="severity" value="warning"/>
  <module name="TreeWalker">
    <module name="NeedBraces">
      <property name="severity" value="error"/>
    </module>
  </module>
</module>';

        // handle different line ending on different platforms
        $form->checkstylecode = str_replace("\r\n", "\n", $form->checkstylecode);
        $form->hint = array(
                0 => array(
                        'text' => 'hint 1<br>',
                        'format' => '1',
                        'itemid' => '83894244'),
                1 => array(
                        'text' => 'hint 2<br>',
                        'format' => '1',
                        'itemid' => '34635511'));

        return $form;
    }

    /**
     * same as java1 except for PHPUnit tests
     */
    public function get_proforma_question_form_data_java1unit() {
            //$form->questiontext = self::QUESTION_TEXT;
        $form = $this->get_proforma_question_form_data_java1();
        $form->questiontext = self::QUESTION_TEXT;
        return $form;

    }

    /** without checkstyle
     * @return stdClass
     */
    public function get_proforma_question_form_data_java2() {
        $form = $this->get_proforma_question_form_data_java1unit();
        $form->checkstyle = 0;
        return $form;
    }

    /** without compilation
     * @return stdClass
     */
    public function get_proforma_question_form_data_java3() {
        $form = $this->get_proforma_question_form_data_java1unit();
        $form->compile = 0;
        return $form;
    }

    /** with 2 junit test2
     * @return stdClass
     */
    public function get_proforma_question_form_data_java_2junit() {
        $form = $this->get_proforma_question_form_data_java1unit();
        // question format for behat
        $form->questiontext = array('text' => self::QUESTION_TEXT, 'format' => FORMAT_HTML);

        // this is redundant => do we need ist?
        $form->gradinghints = self::QUESTION_GRADINGHINTS_JAVA_2JUNIT;

        // Behat: values are set from grading hints!!
        // Junit #2
        $form->testcodeformat[1] = base_form_creator::EDITORTESTINPUT;
        $form->testcode[1] = 'class YTest {}';
        $form->testtitle[1] = 'JUnit Test 2';
        $form->testweight[1] = '1';
        $form->testid[1] = '2';
        $form->testversion[1] = "5";

        // Junit #3 with empty fields
        $form->testcodeformat[2] = base_form_creator::EDITORTESTINPUT;
        $form->testcode[2] = '';
        $form->testtitle[2] = '';
        $form->testweight[2] = '6';
        $form->testid[2] = '3';

        $form->defaultmark = 3.0;

//       $form->download = file_get_unused_draft_itemid();
//        $this->make_attachment_in_draft_area($form->download, 'a.txt', 'text');
        // self::QUESTION_TEMPLATES, self::QUESTION_TEMPLATE);


        return $form;
    }

    /** with no junit test
     * @return stdClass
     */
    public function get_proforma_question_form_data_java5() {
        $form = $this->get_proforma_question_form_data_java1unit();


        $form->testcode[0] = '';
        $form->testtitle[0] = '';
        $form->testweight[0] = '1';
        $form->testid[0] = '1';

        return $form;
    }

    /** with junit test as uploaded file
     * @return stdClass
     */
    public function get_proforma_question_form_data_javafile1() {
        $form = $this->get_proforma_question_form_data_java1unit();

        $form->testcodeformat[0] = base_form_creator::FILETESTINPUT;
        $form->testentrypoint[0] = 'entrypoint';
        $form->testfiles = array();
        $form->testfiles[0] = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->testfiles[0], 'junittest.java',
                'class Junittest {}');

        return $form;
    }

    /** with 2nd junit test as uploaded file
     * @return stdClass
     */
    public function get_proforma_question_form_data_java_2junit_file() {
        $form = $this->get_proforma_question_form_data_java_2junit();

        $form->testcodeformat[0] = base_form_creator::FILETESTINPUT;
        $form->testentrypoint[0] = 'entrypoint1';
        $form->testfiles = array();
        $form->testfiles[0] = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->testfiles[0], 'junittest1.java',
                'class XTest1 {}');
        $this->make_attachment_in_draft_area($form->testfiles[0], 'junittest2.java',
                'class XTest2 {}');


        $form->testcodeformat[1] = base_form_creator::FILETESTINPUT;
        $form->testentrypoint[1] = 'entrypoint2';
        $form->testfiles[1] = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->testfiles[1], 'junittest.java',
                'class Junittest {}');

        return $form;
    }

    /** with 2nd junit test as uploaded file
     * @return stdClass
     */
    public function get_proforma_question_form_data_java_3junit_file() {
        $form = $this->get_proforma_question_form_data_java_2junit_file();

        $form->testcodeformat[0] = base_form_creator::FILETESTINPUT;
        $form->testentrypoint[0] = 'entrypoint1';
        $form->testfiles = array();
        $form->testfiles[0] = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->testfiles[0], 'junittest1.java',
                'class XTest1 {}');
        $this->make_attachment_in_draft_area($form->testfiles[0], 'junittest2.java',
                'class XTest2 {}');


        $form->testcodeformat[1] = base_form_creator::FILETESTINPUT;
        $form->testentrypoint[1] = 'entrypoint2';
        $form->testfiles[1] = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->testfiles[1], 'junittest.java',
                'class Junittest {}');

        // Junit #3
        $form->testcodeformat[2] = base_form_creator::EDITORTESTINPUT;
        $form->testcode[2] = 'class ZTest {}';
        $form->testtitle[2] = 'JUnit Test 3';
        $form->testweight[2] = '4';
        $form->testid[2] = '3';
        $form->testversion[2] = "5";

        return $form;
    }




    // -------------------
    // SetlX
    // -------------------

    public function get_proforma_question_form_data_setlx_base() {
        $form = new stdClass();

        $form->original_template = '';

        // valid data for a task file in the repository!
        $form->taskstorage = qtype_proforma::SETLX_TASKFILE;

        $form->responsefilename = self::QUESTION_FILENAME;
        $form->programminglanguage = 'setlx';
        $form->proglangversion = '2.7';

        // $container->modelsolution = self::QUESTION_MODELSOLUTION;
        // set redundant :-( template
        $form->responsetemplate = self::QUESTION_TEMPLATE;
        $form->template = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($form->template, 'template.txt',
                self::QUESTION_TEMPLATE);

        $form->gradinghints = self::QUESTION_GRADINGHINTS_SETLX;
        $form->aggregationstrategy = qtype_proforma::ALL_OR_NOTHING;

        $form->name = self::QUESTION_NAME;
        //$form->questiontext = self::QUESTION_TEXT;
        $form->questiontext = array('text' => self::QUESTION_TEXT, 'format' => FORMAT_HTML);
        $form->defaultmark = 3.0;
        $form->generalfeedback = array('text' => self::QUESTION_GENERAL_FEEDBACK, 'format' => FORMAT_HTML);
        $form->penalty = 0.2;

        $form->responseformat = 'editor';
        $form->modelsolution = '// code for model solution';
        $form->responsefieldlines = 10;
        $form->comment = array('text' => self::QUESTION_COMMENT, 'format' => FORMAT_HTML);

        $form->compile = 1;
        $form->compileweight = 2;

        // test weight, title and description are taken from grading hints.
        // They must also exist as test variables.
        $form->testcode[0] = 'some testcode';
        $form->testtitle[0] = 'Setlx Test 1';
        $form->testweight[0] = '3';
        $form->testid[0] = '1';


        // handle different line ending on different platforms
//        $form->checkstylecode = str_replace("\r\n", "\n", $form->checkstylecode);
        $form->hint = array(
                0 => array(
                        'text' => 'hint 1<br>',
                        'format' => '1',
                        'itemid' => '83894244'),
                1 => array(
                        'text' => 'hint 2<br>',
                        'format' => '1',
                        'itemid' => '34635511'));

        return $form;
    }

    /**
     * no test, only syntax check
     */
    public function get_proforma_question_form_data_setlx0() {
        $form = $this->get_proforma_question_form_data_setlx_base();
        // remove test[0]
        unset($form->testcode[0]);
        unset($form->testtitle[0]);
        unset($form->testweight[0]);
        unset($form->testid[0]);
        return $form;
    }

    /** one test and syntax check */
    public function get_proforma_question_form_data_setlx1() {
        $form = $this->get_proforma_question_form_data_setlx_base();
        return $form;
    }

    /** one test and no syntax check */
    public function get_proforma_question_form_data_setlx1a() {
        $form = $this->get_proforma_question_form_data_setlx_base();
        $form->compile = 0;
        return $form;
    }

    /** two tests and syntax check */
    public function get_proforma_question_form_data_setlx2() {
        $form = $this->get_proforma_question_form_data_setlx_base();

        $form->testcode[1] = 'some other testcode';
        // Must be set correctly (=> grading hints) for PhpUnit test!
        $form->testid[1] = '2';
        $form->testtitle[1] = 'Setlx Test 2';
        $form->testweight[1] = '6'; // '-';

        // Behat: Title, weight and description come from grading hints
        $form->gradinghints = self::QUESTION_GRADINGHINTS_SETLX2;
        return $form;
    }


    /**
     * Creates an empty draft area for attachments.
     * @return int The draft area's itemid.
     */
    protected function make_attachment_draft_area() {
        $draftid = 0;
        $contextid = 0;

        $component = 'question';
        $filearea = 'response_attachments';

        // Create an empty file area.
        file_prepare_draft_area($draftid, $contextid, $component, $filearea, null);
        return $draftid;
    }

    /**
     * Creates an attachment in the provided attachment draft area.
     *
     * @param int $draftid The itemid for the draft area in which the file should be created.
     * @param string $filename The filename for the file to be created.
     * @param string $contents The contents of the file to be created.
     */
    protected function make_attachment_in_draft_area($draftid, $filename, $contents) {
        global $USER;

        if (!is_numeric($draftid)) {
            throw new coding_exception('draftid is not numeric!' . $draftid);
        }
        $fs = get_file_storage();
        $usercontext = context_user::instance($USER->id);

        // Create the file in the provided draft area.
        $fileinfo = array(
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $draftid,
            'filepath'  => '/',
            'filename' => $filename,
        );
        $fs->create_file_from_string($fileinfo, $contents);
    }

    /**
     * Generates a draft file area that contains the provided number of attachments. You should ensure
     * that a user is logged in with setUser before you run this function.
     *
     * @param int $attachments The number of attachments to generate.
     * @return int The itemid of the generated draft file area.
     */
    public function make_attachments($attachments) {
        $draftid = $this->make_attachment_draft_area();

        // Create the relevant amount of dummy attachments in the given draft area.
        for ($i = 0; $i < $attachments; ++$i) {
            $this->make_attachment_in_draft_area($draftid, $i, $i);
        }

        return $draftid;
    }
    /**
     * Generates a question_file_saver that contains the provided number of attachments. You should ensure
     * that a user is logged in with setUser before you run this function.
     *
     * @param int $:attachments The number of attachments to generate.
     * @return question_file_saver a question_file_saver that contains the given amount of dummy files, for use in testing.
     */

    public function make_attachments_saver($attachments) {
        return new question_file_saver($this->make_attachments($attachments), 'question', 'response_attachments');
    }

}

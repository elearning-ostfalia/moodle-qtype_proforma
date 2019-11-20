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
            // different response formats
            'editor',
            'filepicker',
            // different settings for java junit tests
            'java1',
            // different grading approaches
            'weightedsum',
            // different values set in question
            'downloads',
            'responsetemplate', 'modelsolution', 'penalty', // different values
            'gradecorrect', 'gradeincorrect', 'wronggraderfilename');
    }

    const QUESTION_NAME = 'ProFormA question (äöüß)';

    // this string is also used in behat tests. Behat obviously does not support HTML tags.
    // So they are not used here though supported
    const QUESTION_TEXT = 'Please code the reverse string function not using a library function.(äöüß)';
//    const QUESTION_TEXT = '<p>Please code the reverse string function <b>not</b> using a library function.(äöüß)</p>';

    const QUESTION_GENERAL_FEEDBACK = '<p>You must not use a library function.</p>';
    const QUESTION_COMMENT = '<p>Check if the code uses a library function.</p>';
    const QUESTION_REPOSITORY = 'https://repository.ostfalia.de';
    const QUESTION_PATH = '/path/to/reversestring.zip';
    const QUESTION_FILENAME = 'MyString.java';
    //const QUESTION_MODELSOLUTION = '//text in modelsolution (äöüß)';
    const QUESTION_TEMPLATE = '//text in responsetemplate';

    const QUESTION_INSTRUCTIONS = 'instruction.txt';
    const QUESTION_LIBRARIES = 'lib.txt';
    const QUESTION_DOWNLOADS = qtype_proforma_test_helper::QUESTION_LIBRARIES .','.
        qtype_proforma_test_helper::QUESTION_INSTRUCTIONS;

    const QUESTION_TEMPLATES = 'temp.txt'; // 'templ1.txt, templ2.txt';
    const QUESTION_TEMPLATES_2 = 'codesnippet.py';
    const QUESTION_MODELSOLS = 'ms1.txt, ms2.txt';
    const QUESTION_TASKFILENAME = 'testtask.zip';
    const QUESTION_TASKFILE = 'das ist das zip-file von testtask.zip';
    const QUESTION_TASKSTORAGE = qtype_proforma::PERSISTENT_TASKFILE;

    const QUESTION_PROFORMAVERSION = '2.0';
    const QUESTION_AGGREGATIONSTRATEGY = qtype_proforma::ALL_OR_NOTHING;
    const QUESTION_GRADINGHINTS = '<grading-hints>'.
  '<root function="sum">'.
   '<test-ref ref="1" weight="2"></test-ref>'.
   '<test-ref ref="2" weight="3"/>'.
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
        $container->taskrepository = self::QUESTION_REPOSITORY;
        $container->taskpath = self::QUESTION_PATH;
        $container->taskfilename = self::QUESTION_TASKFILENAME;
        $container->taskstorage = self::QUESTION_TASKSTORAGE;

        $container->responsefilename = self::QUESTION_FILENAME;
        $container->programminglanguage = 'java';

        // $container->modelsolution = self::QUESTION_MODELSOLUTION;
        $container->responsetemplate = self::QUESTION_TEMPLATE;
        $container->templates = self::QUESTION_TEMPLATES;

        $container->downloads = self::QUESTION_DOWNLOADS;
        $container->modelsolfiles = self::QUESTION_MODELSOLS;
        $container->gradinghints = self::QUESTION_GRADINGHINTS;
        $container->aggregationstrategy = self::QUESTION_AGGREGATIONSTRATEGY;
        $container->proformaversion = self::QUESTION_PROFORMAVERSION;
    }

    private function get_question_form_data($container) {
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
            $container->taskfiledraftid =  file_get_unused_draft_itemid(); // $this->make_attachment_draft_area();
            $this->make_attachment_in_draft_area($container->taskfiledraftid, $container->taskfilename,
                    'Task.Zip-Dummy');

            $container->modelsolid = file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->modelsolid, 'ms1.txt',
                    'MS1-Dummy');
            $this->make_attachment_in_draft_area($container->modelsolid, 'ms2.txt',
                    'MS2-Dummy');

            $container->downloadid = file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->downloadid, 'lib.txt',
                    'LIB-Dummy');

//            $container->instructionid = file_get_unused_draft_itemid();
            $this->make_attachment_in_draft_area($container->downloadid, 'instruction.txt',
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

        $q->templates = self::QUESTION_TEMPLATES_2;
        $q->programminglanguage = 'python';
        $q->responsetemplate = ''; // ????
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

        $this->get_question_form_data($fromform);
        $fromform->responseformat = 'editor';
        $fromform->attachments = 0;

        return $fromform;
    }

    public function get_proforma_question_data_editor() {
        $fromform = new stdClass();

        $this->get_question_data($fromform);
        $fromform->options->responseformat = 'editor';
        $fromform->options->attachments = 0;

        return $fromform;
    }


    public function get_proforma_question_form_data_filepicker() {
        $fromform = new stdClass();
        $this->get_question_form_data($fromform);
        $fromform->responseformat = 'filepicker';
        $fromform->attachments = 3;


        // two templates
        $fromform->templates = self::QUESTION_TEMPLATES_2;
        $fromform->templateid = file_get_unused_draft_itemid();
        $this->make_attachment_in_draft_area($fromform->templateid, self::QUESTION_TEMPLATES_2, //'templ1.txt',
                '#code snippet for python');
        $fromform->programminglanguage = 'python';

        $fromform->uuid = 'UUID 2';

        return $fromform;
    }

    public function get_proforma_question_form_data_weightedsum() {
        $fromform = new stdClass();
        $this->get_question_form_data($fromform);

        $fromform->aggregationstrategy = qtype_proforma::WEIGHTED_SUM;

        return $fromform;
    }


    public function get_proforma_question_form_data_java1() {
        $form = new stdClass();

        // valid data for a task file in the repository!
        $form->taskstorage = qtype_proforma::VOLATILE_TASKFILE;

        $form->responsefilename = self::QUESTION_FILENAME;
        $form->programminglanguage = 'java';

        // $container->modelsolution = self::QUESTION_MODELSOLUTION;
        $form->responsetemplate = self::QUESTION_TEMPLATE;

        $form->gradinghints = self::QUESTION_GRADINGHINTS;
        $form->aggregationstrategy = self::QUESTION_AGGREGATIONSTRATEGY;

        $form->name = self::QUESTION_NAME;
        $form->questiontext = self::QUESTION_TEXT;
        //$form->questiontext = array('text' => self::QUESTION_TEXT, 'format' => FORMAT_HTML);
        $form->defaultmark = 1.0;
        $form->generalfeedback = array('text' => self::QUESTION_GENERAL_FEEDBACK, 'format' => FORMAT_HTML);
        $form->penalty = 0.2;

        $form->responseformat = 'editor';
        $form->responsefieldlines = 10;
        $form->comment = array('text' => self::QUESTION_COMMENT, 'format' => FORMAT_HTML);
        $form->maxbytes = 10240;
        $form->filetypes = '.java';

        $form->code[0] = 'class XTest {}';
        $form->testtitle[0] = 'JUnit Test 1';
        $form->testweight[0] = '1';
        $form->testid[0] = '1';

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

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
 * This file contains unit tests for class qtype_proforma_question
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


class qtype_proforma_question_test extends advanced_testcase {

    public function make_a_proforma_question() {
        $q = test_question_maker::make_question('proforma');
        // $q->contextid = $this->category->contextid;
        return $q;
    }

    public function test_get_question_summary() {
        $proforma = $this->make_a_proforma_question();
        $proforma->questiontext = 'Hello <img src="http://example.com/image.png" alt="no_image" />';
        $this->assertEquals('Hello [no_image]', $proforma->get_question_summary());
    }

    public function test_summarise_response() {
        $longstring = str_repeat('0123456789', 50);
        $proforma = $this->make_a_proforma_question();
        $this->assertEquals($longstring, $proforma->summarise_response(
                array('answer' => $longstring, 'answerformat' => FORMAT_HTML)));
    }

    public function test_is_same_response() {
        $proforma = $this->make_a_proforma_question();

        $proforma->responsetemplate = '';

        $proforma->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($proforma->is_same_response(
                array(),
                array('answer' => '')));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => 'class MyString ')));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => 'class MyString '),
                array('answer' => 'class MyString ')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => 'class MyString '),
                array('answer' => 'class MyString {')));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => ''),
                array()));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => 'class'),
                array()));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => 'class'),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => 0),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => 0)));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => '0'),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => '0')));
    }

    public function test_is_same_response_with_template() {
        $proforma = $this->make_a_proforma_question();

        $template = 'class MyString {}';
        $proforma->responsetemplate = $template;

        $proforma->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($proforma->is_same_response(
                array(),
                array('answer' => $template)));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => $template)));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => $template),
                array('answer' => '')));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => $template),
                array('answer' => $template)));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => $template),
                array('answer' => $template . ' ')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => $template),
                array('answer' => ' ' . $template)));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ' ' . $template),
                array('answer' => $template)));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => $template),
                array('answer' => 'class MyString1 {}')));


        $this->assertTrue($proforma->is_same_response(
                array('answer' => ''),
                array()));

        $this->assertTrue($proforma->is_same_response(
                array('answer' => $template),
                array()));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => 0),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => 0)));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => '0'),
                array('answer' => '')));

        $this->assertFalse($proforma->is_same_response(
                array('answer' => ''),
                array('answer' => '0')));
    }

    public function test_is_same_response_with_filepicker() {

        $proforma = test_question_maker::make_question('proforma', 'filepicker');

        $template = 'class MyString {}';
        $proforma->responsetemplate = $template;

        $proforma->start_attempt(new question_attempt_step(), 1);

/*        $this->assertFalse($proforma->is_same_response(
                array('attachments' => 'file1.c'),
                array('attachments' => 'file2.c')));
*/
        $file1 = $this->upload_file('text1', 'file1.c');
        $file2 = $this->upload_file('text1', 'file2.c');
        $file3 = $this->upload_file('text2', 'file3.c');

        $this->assertTrue($proforma->is_same_response(
                array('attachments' => $file1),
                array('attachments' => $file1)));


        // could also be true but actual (expensive) file compare is necessary
        $this->assertFalse($proforma->is_same_response(
                array('attachments' => $file1),
                array('attachments' => $file2)));

        $this->assertFalse($proforma->is_same_response(
                array('attachments' => $file2),
                array('attachments' => $file3)));


        // reset test state
        self::setUser(0);
        phpunit_util::reset_database();
    }

    protected function upload_file($content, $filename) {
        // user is needed for upload
        $this->setAdminUser();

        global $USER;

        $usercontextid = context_user::instance($USER->id)->id;
        $attachementsdraftid = file_get_unused_draft_itemid();

        // save to draft area
        $fs = get_file_storage();

        $filerecord = new stdClass();
        $filerecord->contextid = $usercontextid;
        $filerecord->component = 'user';
        $filerecord->filearea = 'draft';
        $filerecord->itemid = $attachementsdraftid;
        $filerecord->filepath = '/';
        $filerecord->filename = $filename;
        $fs->create_file_from_string($filerecord, $content);

        // update storage for uploaded files
/*        $this->files[$attachementsdraftid] = array(
                'filename' => $filename,
                'content' => $content);
*/
        return $attachementsdraftid;

    }

    public function test_is_complete_response() {
        $this->resetAfterTest(true);

        // Create a new logged-in user, so we can test responses with attachments.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        // Create sample attachments to use in testing.
        $helper = test_question_maker::get_test_helper('proforma');
        $attachments = array();
        for ($i = 0; $i < 4; ++$i) {
            $attachments[$i] = $helper->make_attachments_saver($i);
        }

        // Create the proforma question under test.
        $proforma = $this->make_a_proforma_question();
        $proforma->start_attempt(new question_attempt_step(), 1);

        // Test the "traditional" case, where we must recieve a response from the user.
        $proforma->responserequired = 1;
        $proforma->attachmentsrequired = 0;
        $proforma->responseformat = 'editor';

        // The empty string should be considered an incomplete response, as should a lack of a response.
        $this->assertFalse($proforma->is_complete_response(array('answer' => '')));
        $this->assertFalse($proforma->is_complete_response(array()));

        // Any nonempty string should be considered a complete response.
        $this->assertTrue($proforma->is_complete_response(array('answer' => 'A student response.')));
        $this->assertTrue($proforma->is_complete_response(array('answer' => '0 times.')));
        $this->assertTrue($proforma->is_complete_response(array('answer' => '0')));

/*
        // Test the case where two files are required.
        //$proforma->attachmentsrequired = 2;
        $proforma->responseformat = 'editorfilepicker';
        $proforma->inputwithfile = 0;

        $this->assertFalse($proforma->is_complete_response(array('answer' => '',
                'attachments' => $attachments[0])));
        $this->assertFalse($proforma->is_complete_response(array('answer' => '')));

        // Attaching less than two files should result in an incomplete response.
        $this->assertTrue($proforma->is_complete_response(array('answer' => 'A')));
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'A', 'attachments' => $attachments[0])));
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'A', 'attachments' => $attachments[1])));

        // Anything without response text should result in an incomplete response.
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => '', 'attachments' => $attachments[2])));

        // Attaching two or more files should result in a complete response.
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'A', 'attachments' => $attachments[2])));
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'A', 'attachments' => $attachments[3])));

        // Test the case in which two files are required, but the inline
        // response is optional.
        //$proforma->responserequired = 0;
        $proforma->inputwithfile = 1;

        $this->assertTrue($proforma->is_complete_response(
                array('answer' => '', 'attachments' => $attachments[1])));

        $this->assertTrue($proforma->is_complete_response(
                array('answer' => '', 'attachments' => $attachments[2])));

        // Test the case in which both the response and online text are optional.
        //$proforma->attachmentsrequired = 0;

        // Providing no answer and no attachment should result in an incomplete
        // response.
        $this->assertFalse($proforma->is_complete_response(
                array('answer' => '')));
        $this->assertFalse($proforma->is_complete_response(
                array('answer' => '', 'attachments' => $attachments[0])));

        // Providing an answer _or_ an attachment should result in a complete
        // response.
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => '', 'attachments' => $attachments[1])));
        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'Answer text.', 'attachments' => $attachments[0])));

        $this->assertTrue($proforma->is_complete_response(
                array('answer' => 'A', 'attachments' => $attachments[2])));
*/

        // Test the case in which we're in "no inline response" mode,
        // in which the response is not required (as it's not provided).
        //$proforma->reponserequired = 0;
        $proforma->responseformat = 'filepicker';
        //$proforma->attachmensrequired = 1;

        $this->assertFalse($proforma->is_complete_response(
                array()));
        $this->assertFalse($proforma->is_complete_response(
                array('attachments' => $attachments[0])));

        // Providing an attachment should result in a complete response.
        $this->assertTrue($proforma->is_complete_response(
                array('attachments' => $attachments[1])));

        // Ensure that responserequired is ignored when we're in inline response mode.
        $proforma->reponserequired = 1;
        $this->assertTrue($proforma->is_complete_response(
                array('attachments' => $attachments[1])));

    }

}

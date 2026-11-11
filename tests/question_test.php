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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file contains unit tests for class qtype_proforma_question
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


class question_test extends advanced_testcase {

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
                ['answer' => $longstring, 'answerformat' => FORMAT_HTML]));
    }

    public function test_is_same_response() {
        $proforma = $this->make_a_proforma_question();

        $proforma->responsetemplate = '';

        $proforma->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($proforma->is_same_response(
                [],
                ['answer' => '']));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => 'class MyString ']));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => 'class MyString '],
                ['answer' => 'class MyString ']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => 'class MyString '],
                ['answer' => 'class MyString {']));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => ''],
                []));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => 'class'],
                []));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => 'class'],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => 0],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => 0]));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => '0'],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => '0']));
    }

    public function test_is_same_response_with_template() {
        $proforma = $this->make_a_proforma_question();

        $template = 'class MyString {}';
        $proforma->responsetemplate = $template;

        $proforma->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($proforma->is_same_response(
                [],
                ['answer' => $template]));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => $template]));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => $template],
                ['answer' => '']));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => $template],
                ['answer' => $template]));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => $template],
                ['answer' => $template . ' ']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => $template],
                ['answer' => ' ' . $template]));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ' ' . $template],
                ['answer' => $template]));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => $template],
                ['answer' => 'class MyString1 {}']));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => ''],
                []));

        $this->assertTrue($proforma->is_same_response(
                ['answer' => $template],
                []));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => 0],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => 0]));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => '0'],
                ['answer' => '']));

        $this->assertFalse($proforma->is_same_response(
                ['answer' => ''],
                ['answer' => '0']));
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
                ['attachments' => $file1],
                ['attachments' => $file1]));

        // could also be true but actual (expensive) file compare is necessary
        $this->assertFalse($proforma->is_same_response(
                ['attachments' => $file1],
                ['attachments' => $file2]));

        $this->assertFalse($proforma->is_same_response(
                ['attachments' => $file2],
                ['attachments' => $file3]));

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
        $attachments = [];
        for ($i = 0; $i < 4; ++$i) {
            $attachments[$i] = $helper->make_attachments_saver($i);
        }

        // Create the proforma question under test.
        $proforma = $this->make_a_proforma_question();
        $proforma->start_attempt(new question_attempt_step(), 1);

        // Test the "traditional" case, where we must receive a response from the user.
        /*        $proforma->responserequired = 1;
        $proforma->attachmentsrequired = 0;
        */
        $proforma->responseformat = 'editor';
        // The empty string should be considered an incomplete response, as should a lack of a response.
        $this->assertFalse($proforma->is_complete_response(['answer' => '']));
        $this->assertFalse($proforma->is_complete_response([]));

        // Any nonempty string should be considered a complete response.
        $this->assertTrue($proforma->is_complete_response(['answer' => 'A student response.']));
        $this->assertTrue($proforma->is_complete_response(['answer' => '0 times.']));
        $this->assertTrue($proforma->is_complete_response(['answer' => '0']));

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
        // $proforma->reponserequired = 0;
        $proforma->responseformat = 'filepicker';
        // $proforma->attachmensrequired = 1;

        $this->assertFalse($proforma->is_complete_response(
                []));
        $this->assertFalse($proforma->is_complete_response(
                ['attachments' => $attachments[0]]));

        // Providing an attachment should result in a complete response.
        $this->assertTrue($proforma->is_complete_response(
                ['attachments' => $attachments[1]]));

        // Ensure that responserequired is ignored when we're in inline response mode.
        // $proforma->reponserequired = 1;
        $this->assertTrue($proforma->is_complete_response(
                ['attachments' => $attachments[1]]));

    }

    /**
     * The group name of a version control response must not be trusted:
     * a student may only use a group he or she is a member of.
     *
     * @covers \qtype_proforma_question::is_complete_response
     */
    public function test_is_complete_response_vcs_group(): void {
        $this->resetAfterTest(true);

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_and_enrol($course, 'student');
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $group1 = $generator->create_group(['courseid' => $course->id, 'name' => 'G1']);
        $generator->create_group(['courseid' => $course->id, 'name' => 'G2']);
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $student->id]);
        $context = context_course::instance($course->id);
        // The student is also member of a group in another course.
        $othercourse = $generator->create_course();
        $generator->enrol_user($student->id, $othercourse->id, 'student');
        $group3 = $generator->create_group(['courseid' => $othercourse->id, 'name' => 'G3']);
        $generator->create_group_member(['groupid' => $group3->id, 'userid' => $student->id]);

        // A second student is member of two groups of the course (not unique).
        $student2 = $generator->create_and_enrol($course, 'student');
        $group4 = $generator->create_group(['courseid' => $course->id, 'name' => 'G4']);
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $student2->id]);
        $generator->create_group_member(['groupid' => $group4->id, 'userid' => $student2->id]);

        // Attempt of the student in a quiz of the first course.
        global $PAGE;
        $quiz = $generator->create_module('quiz', ['course' => $course->id]);
        // Enrolling users has already initialised the theme of the page (welcome message) => use a new page.
        $PAGE = new moodle_page();
        $PAGE->set_cm(get_coursemodule_from_instance('quiz', $quiz->id));
        $this->setUser($student);
        $proforma = test_question_maker::make_question('proforma', 'vcs_git');
        $proforma->vcsuritemplate = 'https://git.uri.org/{group}';
        $proforma->contextid = $context->id;
        $proforma->start_attempt(new question_attempt_step(), 1);

        $this->assertTrue($proforma->is_complete_response(['vcsgroup' => 'G1']));
        $this->assertSame('', $proforma->get_validation_error(['vcsgroup' => 'G1']));
        // Group of other students, no group or no group name.
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'G2']));
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'N/A']));
        // Own group, but in another course.
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'G3']));
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => '']));
        $this->assertSame(
            get_string('invalidgroupname', 'qtype_proforma'),
            $proforma->get_validation_error(['vcsgroup' => 'G2'])
        );

        // The attempt still belongs to the student if a teacher triggers the grading (e.g. regrade).
        $this->setUser($teacher);
        $this->assertTrue($proforma->is_complete_response(['vcsgroup' => 'G1']));
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'G2']));

        // Group is not unique: no group name is accepted.
        $this->setUser($student2);
        $proforma = test_question_maker::make_question('proforma', 'vcs_git');
        $proforma->vcsuritemplate = 'https://git.uri.org/{group}';
        $proforma->contextid = $context->id;
        $proforma->start_attempt(new question_attempt_step(), 1);
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'G1']));
        $this->assertFalse($proforma->is_complete_response(['vcsgroup' => 'G4']));

        // Attempt of the teacher: any group name is accepted.
        $this->setUser($teacher);
        $proforma = test_question_maker::make_question('proforma', 'vcs_git');
        $proforma->vcsuritemplate = 'https://git.uri.org/{group}';
        $proforma->contextid = $context->id;
        $proforma->start_attempt(new question_attempt_step(), 1);
        $this->assertTrue($proforma->is_complete_response(['vcsgroup' => 'G2']));
    }
}

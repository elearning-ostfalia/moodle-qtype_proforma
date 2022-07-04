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
 * This file contains unit tests for locallib file
 *
 * @package    qtype_proforma
 * @copyright  2021 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');


class qtype_proforma_locallib_test extends advanced_testcase {

    private $user1;
    private $user2;
    private $course;
    private $group1;
    private $group2;
    private $group3;
    private $group4;
    private $grouping;
    private $groupingid;

    private function create_group_objects() {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $courseid = $course->id;

        // Make a quiz.
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(['course' => $course->id,
            'grade' => 100.0, 'sumgrades' => 2, 'layout' => '1,0']);

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $generator->enrol_user($user1->id, $courseid);
        $generator->enrol_user($user2->id, $courseid);

        $this->course = $course;
        $this->user1 = $user1;
        $this->user2 = $user2;

        $this->group1 = $generator->create_group(array('courseid' => $courseid, 'name' => 'group1'));
        $this->group2 = $generator->create_group(array('courseid' => $courseid, 'name' => 'group2'));
        $this->group3 = $generator->create_group(array('courseid' => $courseid, 'name' => 'group3'));
        $this->group4 = $generator->create_group(array('courseid' => $courseid, 'name' => 'group4'));

        $this->grouping = $generator->create_grouping(array('courseid' => $courseid, 'name' => 'grouping'));
        $this->groupingid = $this->grouping->id;
        $this->setUser($this->user1);

        return context_module::instance($quiz->cmid);
    }

    // + user without group
    // + user in one group with other user (no groupings)
    // + user in one group alone (no groupings)
    // + user in two groups (no groupings)
    // - user in one group (with groupings)
    // - user in two groups (with groupings)

    public function test_no_group() {
        $this->resetAfterTest();

        $context = $this->create_group_objects();

        // Retrieve groups for user1.
        // User1 belongs to no group

        $groupname = qtype_proforma\lib\get_groupname($context);
        $this->assertEquals('N/A', $groupname, 'no group, no grouping');
    }


    public function test_no_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // Add user1 to group1 and group2.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user1->id));

        // Add user2 to group2 and group3.
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user2->id));
        $generator->create_group_member(array('groupid' => $this->group3->id, 'userid' => $this->user2->id));

        // Retrieve groups for user1.
        // User1 belongs to group1 and group2.

        $groupname = qtype_proforma\lib\get_groupname($context);

        $debugging = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertEquals(1, count($debugging));
        $this->assertStringContainsString('group is not unique', $debugging[0]->message);

        $this->assertEquals('???', $groupname, 'two groups, no grouping');
    }

    public function test_no_grouping_one_group_alone() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // User1 belongs to group1.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }

    public function test_no_grouping_one_group_with_other_user() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // User1 and User2 belong to group1.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user2->id));

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }



    public function test_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(array('groupingid' => $this->groupingid, 'groupid' => $this->group1->id));

        // Add user1 to group1 and group2.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user1->id));

        // Add user2 to group2 and group3.
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user2->id));
        $generator->create_group_member(array('groupid' => $this->group3->id, 'userid' => $this->user2->id));

        // Retrieve groups for user1.
        // User1 belongs to group1 and group2.

        $groupname = qtype_proforma\lib\get_groupname($context);

        $debugging = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertEquals(1, count($debugging));
        $this->assertStringContainsString('group is not unique', $debugging[0]->message);


        $this->assertEquals('???', $groupname, 'two groups, no grouping');
    }

    public function test_grouping_one_group_alone() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(array('groupingid' => $this->groupingid, 'groupid' => $this->group1->id));

        // User1 belongs to group1.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }

    public function test_grouping_one_group_with_other_user() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(array('groupingid' => $this->groupingid, 'groupid' => $this->group1->id));


        // User1 and User2 belong to group1.
        $generator->create_group_member(array('groupid' => $this->group1->id, 'userid' => $this->user1->id));
        $generator->create_group_member(array('groupid' => $this->group2->id, 'userid' => $this->user2->id));

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }

    public function test_todo_availablity_and_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'todo: check quiz availability groups');
    }
}
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
 * This file contains unit tests for locallib file
 *
 * @package    qtype_proforma
 * @copyright  2021 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');


class locallib_test extends advanced_testcase {

    private $user1;
    private $user2;
    private $course;
    private $group1;
    private $group2;
    private $group3;
    private $group4;
    private $grouping;
    private $groupingid;

    /**
     * Creates course, users, groups and a grouping (without quiz).
     */
    private function create_course_and_groups() {
        $generator = $this->getDataGenerator();

        $course = $generator->create_course();
        $courseid = $course->id;

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();
        $generator->enrol_user($user1->id, $courseid);
        $generator->enrol_user($user2->id, $courseid);

        $this->course = $course;
        $this->user1 = $user1;
        $this->user2 = $user2;

        $this->group1 = $generator->create_group(['courseid' => $courseid, 'name' => 'group1']);
        $this->group2 = $generator->create_group(['courseid' => $courseid, 'name' => 'group2']);
        $this->group3 = $generator->create_group(['courseid' => $courseid, 'name' => 'group3']);
        $this->group4 = $generator->create_group(['courseid' => $courseid, 'name' => 'group4']);

        $this->grouping = $generator->create_grouping(['courseid' => $courseid, 'name' => 'grouping']);
        $this->groupingid = $this->grouping->id;
        $this->setUser($this->user1);
    }

    /**
     * Creates a quiz in the course.
     *
     * @param array $options additional quiz options (e.g. groupmode, groupingid, availability)
     * @return context_module quiz context
     */
    private function create_quiz(array $options = []) {
        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $quiz = $quizgenerator->create_instance(array_merge(['course' => $this->course->id,
            'grade' => 100.0, 'sumgrades' => 2, 'layout' => '1,0'], $options));

        return context_module::instance($quiz->cmid);
    }

    /**
     * Creates course, users, groups, grouping and a quiz without restrictions.
     *
     * @return context_module quiz context
     */
    private function create_group_objects() {
        $this->create_course_and_groups();
        return $this->create_quiz();
    }

    /**
     * Adds user1 to group1 and group2 and user2 to group2 and group3.
     */
    private function add_user1_to_two_groups() {
        $generator = $this->getDataGenerator();
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);
        $generator->create_group_member(['groupid' => $this->group3->id, 'userid' => $this->user2->id]);
    }

    /**
     * Asserts that exactly one 'group is not unique' debugging message was emitted.
     */
    private function assert_group_not_unique_debugging() {
        $debugging = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertEquals(1, count($debugging));
        $this->assertStringContainsString('group is not unique', $debugging[0]->message);
    }

    /**
     * Returns availability JSON for the given root children.
     *
     * @param array $children availability conditions
     * @param string $op tree operator
     * @return string JSON
     */
    private function availability(array $children, string $op = '&') {
        return json_encode(\core_availability\tree::get_root_json($children, $op));
    }

    // + user without group
    public function test_no_group() {
        $this->resetAfterTest();

        $context = $this->create_group_objects();

        // Retrieve groups for user1.
        // User1 belongs to no group
        $groupname = qtype_proforma\lib\get_groupname($context);
        $this->assertEquals('N/A', $groupname, 'no group, no grouping');
    }


    // + user in two groups (no groupings)
    // => not unique
    public function test_no_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // Add user1 to group1 and group2.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user1->id]);

        // Add user2 to group2 and group3.
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);
        $generator->create_group_member(['groupid' => $this->group3->id, 'userid' => $this->user2->id]);

        // Retrieve groups for user1.
        // User1 belongs to group1 and group2.

        $groupname = qtype_proforma\lib\get_groupname($context);

        $debugging = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertEquals(1, count($debugging));
        $this->assertStringContainsString('group is not unique', $debugging[0]->message);

        $this->assertEquals('???', $groupname, 'two groups, no grouping');
    }

    // + user in one group (no groupings)
    public function test_no_grouping_one_group() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // User1 belongs to group1.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'one group, no grouping');
    }

    // + user in one group (no groupings)
    public function test_no_grouping_one_group_with_other_user() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();

        // User1 and User2 belong to group1.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }


    // - user in two groups (with groupings)
    // => not unique
    public function test_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group1->id]);

        // Add user1 to group1 and group2.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user1->id]);

        // Add user2 to group2 and group3.
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);
        $generator->create_group_member(['groupid' => $this->group3->id, 'userid' => $this->user2->id]);

        // Retrieve groups for user1.
        // User1 belongs to group1 and group2.

        $groupname = qtype_proforma\lib\get_groupname($context);

        $debugging = $this->getDebuggingMessages();
        $this->resetDebugging();
        $this->assertEquals(1, count($debugging));
        $this->assertStringContainsString('group is not unique', $debugging[0]->message);

        $this->assertEquals('???', $groupname, 'two groups, no grouping');
    }

    // - user in one group (with groupings)
    public function test_grouping_one_group() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group1->id]);

        // User1 belongs to group1.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }

    // + user in one group with other user (no groupings)
    public function test_grouping_one_group_with_other_user() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $context = $this->create_group_objects();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group1->id]);

        // User1 and User2 belong to group1.
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);

        // Retrieve groups for user1.
        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, no grouping');
    }

    // + quiz uses grouping (common module settings), user in one group of grouping and one other group
    // => group in grouping
    public function test_quiz_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->create_course_and_groups();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group1->id]);
        $context = $this->create_quiz(['groupmode' => SEPARATEGROUPS, 'groupingid' => $this->groupingid]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, one in quiz grouping');
    }

    // + quiz uses grouping, user is in no group of the grouping
    public function test_quiz_grouping_no_group_in_grouping() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->create_course_and_groups();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group4->id]);
        $context = $this->create_quiz(['groupmode' => SEPARATEGROUPS, 'groupingid' => $this->groupingid]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('N/A', $groupname, 'no group in quiz grouping');
    }

    // + visible groups mode: only the groups of the user count (not all groups of the course)
    public function test_visible_groups_one_group() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->create_course_and_groups();
        $context = $this->create_quiz(['groupmode' => VISIBLEGROUPS]);
        $generator->create_group_member(['groupid' => $this->group1->id, 'userid' => $this->user1->id]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user2->id]);

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'visible groups, one group');
    }

    // + quiz restricted to group1 (availability), user in group1 and group2
    // => group1
    public function test_availability_group_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_group\condition::get_json($this->group1->id)])]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, quiz restricted to group1');
    }

    // + quiz restricted to grouping (availability), user in group1 (in grouping) and group2
    // => group1
    public function test_availability_grouping_two_groups() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->create_course_and_groups();
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group1->id]);
        $generator->create_grouping_group(['groupingid' => $this->groupingid, 'groupid' => $this->group3->id]);
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_grouping\condition::get_json($this->groupingid)])]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, quiz restricted to grouping');
    }

    // + quiz restricted to group1 OR group3 (availability), user in group1 and group2
    // => group1
    public function test_availability_group_or_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability([
            \availability_group\condition::get_json($this->group1->id),
            \availability_group\condition::get_json($this->group3->id),
        ], '|')]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, quiz restricted to group1 or group3');
    }

    // + quiz restricted to group1 OR group2 (availability), user in group1 and group2
    // => not unique
    public function test_availability_both_groups_allowed() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability([
            \availability_group\condition::get_json($this->group1->id),
            \availability_group\condition::get_json($this->group2->id),
        ], '|')]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assert_group_not_unique_debugging();
        $this->assertEquals('???', $groupname, 'two groups, both allowed by availability');
    }

    // + quiz restricted to 'any group' (availability), user in group1 and group2
    // => not unique
    public function test_availability_any_group_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_group\condition::get_json()])]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assert_group_not_unique_debugging();
        $this->assertEquals('???', $groupname, 'two groups, quiz restricted to any group');
    }

    // + negated restriction (must not be in group2) is ignored, user in group1 and group2
    // => not unique
    public function test_availability_negated_group_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_group\condition::get_json($this->group2->id)], '!&')]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assert_group_not_unique_debugging();
        $this->assertEquals('???', $groupname, 'two groups, negated restriction');
    }

    // + quiz restricted to group3 (availability), user in group1 and group2 (none matches)
    // => not unique
    public function test_availability_other_group_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_group\condition::get_json($this->group3->id)])]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assert_group_not_unique_debugging();
        $this->assertEquals('???', $groupname, 'two groups, quiz restricted to other group');
    }

    // + quiz restricted to group1 (availability), user only in group2
    // => group2 (availability does not hide the user's only group)
    public function test_availability_group_one_other_group() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->create_course_and_groups();
        $context = $this->create_quiz(['availability' => $this->availability(
            [\availability_group\condition::get_json($this->group1->id)])]);
        $generator->create_group_member(['groupid' => $this->group2->id, 'userid' => $this->user1->id]);

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group2', $groupname, 'one group, quiz restricted to other group');
    }

    // + quiz restricted to group1 (availability) nested in sub tree, user in group1 and group2
    // => group1
    public function test_availability_nested_group_two_groups() {
        $this->resetAfterTest();

        $this->create_course_and_groups();
        $subtree = (object)['op' => '&', 'c' => [\availability_group\condition::get_json($this->group1->id)]];
        $context = $this->create_quiz(['availability' => $this->availability([$subtree])]);
        $this->add_user1_to_two_groups();

        $groupname = qtype_proforma\lib\get_groupname($context);

        $this->assertEquals('group1', $groupname, 'two groups, nested restriction to group1');
    }
}

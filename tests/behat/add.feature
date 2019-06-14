@qtype @qtype_proforma
Feature: ADD
  Test creating a ProFormA question (check default values)
  As a teacher
  In order to test my students
  I need to be able to create an ProFormA question

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" node in "Course administration"

  # check info for use of import
  Scenario: Create an ProFormA question, check 'import question'
    When I press "Create a new question"
    And I select "ProFormA Task" radio button
    And I press "Add"
    # When I add a "ProFormA" question filling the form with
    Then I should see "Please use \"import question\" in order to create a new Proforma question"


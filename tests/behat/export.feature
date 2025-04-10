@qtype @qtype_proforma
Feature: EXPORT
  Test exporting ProFormA questions
  As a teacher
  In order to be able to reuse my ProFormA questions
  I need to export them

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name      | template         |
      | Test questions   | proforma | proforma-001 | editor           |
      | Test questions   | proforma | proforma-002 | java1           |
      | Test questions   | proforma | proforma-003 | filepicker            |
#      | Test questions   | proforma | proforma-003 | java_2junit            |
    # And I log in as "teacher1"
    # And I am on "Course 1" course homepage

  Scenario: Export 4 ProFormA questions
    When I am on the "Course 1" "core_question > course question export" page logged in as teacher1
#    When I navigate to "Question bank > Export" in current page administration
    And I set the field "id_format_xml" to "1"
    And I press "Export questions to file"
    Then following "click here" should download file with between "11950" and "12050" bytes


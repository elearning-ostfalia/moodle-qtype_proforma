@qtype @qtype_proforma
Feature: IMPORT
  Test importing ProFormA questions
  As a teacher
  In order to reuse ProFormA questions
  I need to import them

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
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript @_file_upload
  Scenario: import ProFormA question.
    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/testquestion_v2.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 2 questions from file"
    And I should see "1. This is the Description of the task (äöüß)."
    And I should see "2. Please code the reverse string function not using a library function.(äöüß)"
    And I press "Continue"
    And I should see "test question with German Umlauts (äöüß)"
    And I should see "second ProFormA question"
#    And I click on "Edit" "link" in the "second ProFormA question" "table_row"


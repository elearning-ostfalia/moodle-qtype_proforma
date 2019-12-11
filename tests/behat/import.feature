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

    When I click on "Edit" "link" in the "second ProFormA question" "table_row"
    Then the following fields match these values:
      | Question name            | second ProFormA question           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | You must not use a library function.(äöüß)        |
      | Response format          | File picker                         |
      | Max. number of uploaded files          | 3                         |
      | Max. upload size          | 10KB                         |
      | Accepted file types          | .java, .jar                         |
      | Syntax highlighting      | Python                         |
      | Comment                  | Check if the code uses a library function.(äöüß)                 |
      | Aggregation strategy      | All or nothing                 |
      | Penalty for each incorrect try  | 20%                     |
#      | Response template        | multiline              |
    And the field "Weight" number "1" matches value "0"
    And the field "Weight" number "2" matches value "1"
    And the field with xpath "//input[@name='testtitle[0]']" matches value "Compiler Test"
    And the field with xpath "//input[@name='testtitle[1]']" matches value "Junit Test 1"
    And the field with xpath "//input[@name='testdescription[0]']" matches value ""
    And the field with xpath "//input[@name='testdescription[1]']" matches value ""
    And the field with xpath "//input[@name='testtype[0]']" matches value "java-compilation"
    And the field with xpath "//input[@name='testtype[1]']" matches value "unittest"
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
# todo: try and check values of static fields
    And I should see "UUID 1"
    And I should see "testtask.zip"
    And I should see "2.0"

    And I press "Cancel"

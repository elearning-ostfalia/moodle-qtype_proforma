@qtype @qtype_proforma
Feature: VALIDATION
  Test validating a ProFormA question
  As a teacher
  In order to test my students
  I need to be able to create a consistent and complete question

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
    And the following config values are set as admin:
      | clang | 0  | qtype_proforma |
      | cpp | 0  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript @_file_upload
  Scenario: Create Java question with missing or incorrect values
##########################################################################
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
    # Step is automatically finished with: I press "id_submitbutton"

    # Response filename required:
    Then I should see "Required"
    When I set the field "Response filename" to "XClass.java"
    And I press "id_submitbutton"
    Then I should see "Title required"

    # Then I should see "java-question"
    # When I choose "Edit question" action for "java-question" in the question bank

    # Testcode required
    And I set the field "testtitle[0]" to "JUnit test 1"
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    And I set the codemirror "testcode_0" to "class XClass1 {}"
    And I press "id_submitbutton"
    Then I should see "java-question"
    When I choose "Edit question" action for "java-question" in the question bank

    # Testtitle required
    And I set the field "testtitle[0]" to ""
    And I press "id_submitbutton"
    Then I should see "Title required"
    And I set the field "testtitle[0]" to "JUnit test 1"
    And I press "id_submitbutton"
    Then I should see "java-question"
    When I choose "Edit question" action for "java-question" in the question bank

    # Testcode (files) required
    And I set the field "id_testcodeformat_0_2" to "1"
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit2.java" to "testfiles[0]" filemanager by name
    And I press "id_submitbutton"
    Then I should see "JUnit entrypoint required"
    And I set the field "testentrypoint[0]" to "XClass"
    And I press "id_submitbutton"
    Then I should see "java-question"

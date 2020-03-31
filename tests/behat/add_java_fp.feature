@qtype @qtype_proforma
Feature: ADD JAVA FILEPICKER QUESTION
  Test creating a ProFormA java question
  As a teacher
  In order to test my students
  I need to be able to create a Java questions that support file upload (filepicker)

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
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript @_file_upload
  Scenario: Create ProFormA java filepicker question with compilation, one Junit test (default values)
##########################################################################
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | filepicker                         |
      | Title                    | JUnit test title               |
    # check that Response filename is no input field
    And I should not see "Response filename"
    And I set the codemirror "testcode_0" to "class TestClass {}"
    # updload model solution file
    And I upload "question/type/proforma/tests/fixtures/MyString.java" file to "Model solution files" filemanager
    And I press "id_submitbutton"
    Then I should see "java-question"

    When I choose "Edit question" action for "java-question" in the question bank
    Then the following fields match these values:
      | Question name            |     java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | filepicker                         |
      | Input box size           | 15 lines                       |
      | Response template        |                                |
      | Comment                  |                                |
      | Title                    | JUnit test title               |
      | Description              |                                |
      | Penalty for each incorrect try  | 10%                     |
    # compile
      | compileweight              |      0                       |
    #And the field "compileweight" matches value "0"
    And I should not see "Response filename"
    # And I pause
    And I should see "1" elements in "Model solution files" filemanager
    And the "compile" checkbox is "checked"
    # JUnit
    And the field "testweight[0]" matches value "1"
    And the codemirror "testcode_0" matches value "class TestClass {}"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "Cancel"

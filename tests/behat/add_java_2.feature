@qtype @qtype_proforma
Feature: ADD JAVA QUESTION
  Test creating a ProFormA java question
  As a teacher
  In order to test my students
  I need to be able to create a simple Java question

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
  Scenario: Create, save and open a Java question with uploaded Junit test
##########################################################################
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
    # title for JUnit #1
      | Title                    | Junit #1               |
    And I set the field "testweight[0]" to "10"
    And I set the field "testversion[0]" to "4.12"
    And I set the field "testdescription[0]" to "first JUnit test"
    # change JUnit code format to 'files'
    And I select "Files" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit.java" to "testfiles[0]" filemanager by name

    # add second Junit test
    And I press "id_option_add_fields"

    And I set the field "testtitle[1]" to "Junit #2"
    And I set the field "testdescription[1]" to "second JUnit test"
    And I set the field "testversion[1]" to "5"
    And I set the codemirror "testcode_1" to "class XClass {}"
    And I set the field "testweight[1]" to "20"

    # compilation
    And I set the field "compileweight" to "5"

    And I press "id_submitbutton"
    Then I should see "JUnit entrypoint required"
    And I set the field "testentrypoint[0]" to "XClass"

    And I press "id_submitbutton"
    Then I should see "java-question"

    When I choose "Edit question" action for "java-question" in the question bank
    Then the following fields match these values:
      | Question name            | java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        |                                |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Programming language version  | 1.8                       |

    # compilation
    And the field "compileweight" matches value "5"
    And the "compile" checkbox is "checked"

    # JUnit #1
    And the field "testweight[0]" matches value "10"
    And the field "testversion[0]" matches value "4.12"
    And the field "testdescription[0]" matches value "first JUnit test"
    # check test file(s)
    And "reverseJUnit.java" "link" should exist
    # there seems to be no step out of the box for downloading a file from filemanager :-(
    And I click on "reverseJUnit.java" "link"
    And I should see "2KB" in the "Edit reverseJUnit.java" "dialogue"
    # And I pause
    And I click on "Cancel" "button" in the "Edit reverseJUnit.java" "dialogue"

    # JUnit #2
    And the field "testtitle[1]" matches value "Junit #2"
    And the field "testdescription[1]" matches value "second JUnit test"
    And the field "testversion[1]" matches value "5"
    And the field "testweight[1]" matches value "20"
    And the field "testcode[1]" matches value "class XClass {}"

    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "Cancel"


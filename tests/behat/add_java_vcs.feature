@qtype @qtype_proforma
Feature: ADD JAVA QUESTION WITH VERSION CONTROL SYSTEM USE
  Test creating a ProFormA java question
  As a teacher
  In order to test my students using a version control system
  I need to be able to create a Java questions that support version control system

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
      | cpp| 0  | qtype_proforma |
      | python| 0  | qtype_proforma |
      | setlx| 0  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript @_file_upload
  Scenario: Create ProFormA java question with compilation, no Junit test, no checkstyle
##########################################################################
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | Version control system         |
      | URI of repository        | https://code.ostfalia.de/svn/{group}/task1/  |
    Then I should see "Title required"
    When I set the field "testtitle[0]" to "JUnit test title"
    And I set the codemirror "testcode_0" to "class XClass {}"
    # check that Response filename is not visible
    Then I should not see "Response filename"
    # upload question attachment file (i.e. download link in preview)
    And I upload "question/type/proforma/tests/fixtures/questiondownload.txt" file to "Downloadable files" filemanager
    # It is not possible to upload files in two filemanagers (bug in test environment?)
    # Therefore we close the editor and reopen it.
    And I press "id_submitbutton"
    # And I pause
    When I choose "Edit question" action for "java-question" in the question bank
    # upload model solution file
    And I upload "question/type/proforma/tests/fixtures/MyString.java" file to "Model solution files" filemanager

    And I press "id_submitbutton"
    Then I should see "java-question"

    When I choose "Edit question" action for "java-question" in the question bank
    Then the following fields match these values:
      | Question name            | java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | Version control system         |
      | URI of repository        | https://code.ostfalia.de/svn/{group}/task1/  |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |
#      | compileweight              |      0                       |
      | Programming language version |    17                    |

    And I should not see "Response filename"
    And I should see "1" elements in "Model solution files" filemanager
    And I should see "1" elements in "Downloadable files" filemanager
    And the "compile" checkbox is "not checked"
    # JUnit
    # is not available
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "id_submitbutton"
    Then I should see "java-question"

    # check for download link
    When I open preview for "java-question" in the question bank
    #And I expand all fieldsets
    Then I should see "questiondownload.txt"
    And following "questiondownload.txt" should download file with between "65" and "67" bytes

    # And I switch to the main window

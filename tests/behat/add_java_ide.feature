@qtype @qtype_proforma
Feature: ADD JAVA EXPLORER/IDE QUESTION
  Test creating a ProFormA java question
  As a teacher
  In order to test my students
  I need to be able to create a Java questions that support file upload with many files (explorer)

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher@moodle.org |
      | student1 | Student | 1 | student@moodle.org |

    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Quiz 1    | C1     | quiz1    |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
#    And the following config values are set as admin:
#      | clang | 0  | qtype_proforma |
#      | cpp | 0  | qtype_proforma |


##########################################################################
  @javascript @_file_upload
  Scenario: Create a Proforma question in quiz subcategory
##########################################################################
    Given I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I navigate to "Question bank" in current page administration
    And I set the field "Select a category" to "Default for Quiz 1"
    And I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_java" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    And I should see "Adding a ProFormA question"

    And  I set the following fields to these values:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Title                    | JUnit test title               |
    And I set the codemirror "testcode_0" to "class TestClass {}"
    And I upload "question/type/proforma/tests/fixtures/questiondownload.txt" file to "Downloadable files" filemanager
    And I press "id_submitbutton"
    Then I should see "java-question" in the "categoryquestions" "table"

    When I open preview for "java-question" in the question bank
    Then I should see "questiondownload.txt"
    And following "questiondownload.txt" should download file with between "65" and "67" bytes

##########################################################################
  @javascript @_file_upload
  Scenario: Create ProFormA java explorer question with compilation, one Junit test (default values)
##########################################################################
    # And I log in as "teacher1"
    # And I am on "Course 1" course homepage
    # And I navigate to "Question bank" in current page administration
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher1

    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_java" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | explorer                     |
      | Title                    | JUnit test title               |
      | Max. response upload size         | 2097152                            |
    # check that Response filename is not visible
    And I should not see "Response filename"
    And I set the codemirror "testcode_0" to "class TestClass {}"
    #And I pause
    # updload question attachment file (i.e. download link in preview)
    And I upload "question/type/proforma/tests/fixtures/questiondownload.txt" file to "Downloadable files" filemanager
    # It is not possible to upload files twice in the editor (bug in test environment?)
    # Therefor ewe close the editor and reopen it.
    And I press "id_submitbutton"
    When I choose "Edit question" action for "java-question" in the question bank
    # updload model solution file
    And I upload "question/type/proforma/tests/fixtures/MyString.java" file to "Model solution files" filemanager

    And I press "id_submitbutton"
    Then I should see "java-question"

    When I choose "Edit question" action for "java-question" in the question bank
    Then the following fields match these values:
      | Question name            | java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | explorer                         |
      | Response template        |                                |
      | Comment                  |                                |
      | Title                    | JUnit test title               |
      | Description              |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Max. response upload size         | 2097152                            |
#      | compileweight              |      0                       |
      | testweight[0]              |      1                       |
    And I should not see "Response filename"
    # And I pause
    And I should see "1" elements in "Model solution files" filemanager
    And I should see "1" elements in "Downloadable files" filemanager
    And the "compile" checkbox is "not checked"
    # JUnit
    #And the field "testweight[0]" matches value "1"
    And the codemirror "testcode_0" matches value "class TestClass {}"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "id_submitbutton"
    Then I should see "java-question"

    # check for download link
    #When I am on the "java-question" "core_question > preview" page
    When I open preview for "java-question" in the question bank
    # When I choose "Preview" action for "java-question" in the question bank
    # And I switch to "questionpreview" window
    Then I should see "questiondownload.txt"
    And following "questiondownload.txt" should download file with between "65" and "67" bytes

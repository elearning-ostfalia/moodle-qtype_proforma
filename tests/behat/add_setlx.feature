@qtype @qtype_proforma
Feature: ADD SETLX QUESTION
  Test creating a ProFormA setlx question
  As a teacher
  In order to test my students
  I need to be able to create a simple Setlx question

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
      | setlx | 1  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript
  Scenario: Create, save and open a ProFormA setlx question without compilation, two SetlX tests
##########################################################################
    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_setlx" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | setlx-question with 2 tests    |
      | Question text            | write a setlx program that..... |
      | Aggregation strategy     | Weighted sum                |
      | Comment                  | a comment                   |
    # The default functions do not work for CodeMirror with Javascript.
    # So we must use other functions.
    And I set the field "testtitle[0]" to "Setlx #1"
    And I set the codemirror "responsetemplate" to "// type your code here"
    And I set the codemirror "modelsolution" to "// code for model solution"

    # Then I should see "Code required"
    # add new Setlx Test
    When I press "id_option_add_fields"
    And I set the codemirror "testcode_0" to "some test code"
    And I set the codemirror "testcode_1" to "some other test code"

    And I set the field "testdescription[1]" to "this is the second Setlx test"

    And I press "id_submitbutton"
    Then I should see "Title required"

    When I set the field "testtitle[1]" to "Setlx #2"
    And I press "id_submitbutton"
    Then I should see "setlx-question with 2 tests"

    # CHECK VALUES
    When I choose "Edit question" action for "setlx-question with 2 tests" in the question bank
    Then the following fields match these values:
      | Question name            | setlx-question with 2 tests     |
      | Question text            | write a setlx program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Input box size           | 15 lines                       |
      | Response template        | // type your code here         |
      | Model solution           | // code for model solution     |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy     | Weighted sum                   |
      | Comment                  | a comment                      |

    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "0"
    # Setlx #1
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "some test code"
    And the field "testdescription[0]" matches value ""
    # Setlx #2
    And the field "testweight[1]" matches value "1"
    And the field "testcode[1]" matches value "some other test code"
    And the field "testdescription[1]" matches value "this is the second Setlx test"

    And I press "Cancel"


##########################################################################
  @javascript
  Scenario: Create, save and open a ProFormA setlx question with compilation, one SetlX test
  # Check default values
##########################################################################
    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_setlx" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | setlx-question                  |
      | Question text            | write a setlx program that..... |
      | Title                    | Setlx test  |
    And I press "id_submitbutton"
    Then I should see "Code required"
    # SetlX test
    When I set the codemirror "testcode_0" to "some test code"
    And I press "id_submitbutton"
    Then I should see "setlx-question"

    # Check default values:
    When I choose "Edit question" action for "setlx-question" in the question bank
    Then the following fields match these values:
      | Question name            | setlx-question             |
      | Question text            | write a setlx program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Input box size           | 15 lines                       |
      | Response template        |                                |
      | Comment                  |                                |
      | Title                    | Setlx test               |
      | Description              |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy      | All or nothing                |

    # compile
      | compileweight              |      0                       |
    #And the field "compileweight" matches value "0"
    And the "compile" checkbox is "checked"
    # SetlX test
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "some test code"
    # Cancel (form is not modified)
    And I press "Cancel"


@qtype @qtype_proforma
Feature: ADD JAVA QUESTION
  Test creating a ProFormA java question (check default values)
  As a teacher
  In order to test my students
  I need to be able to create a ProFormA question

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

  # check info for use of import
  #Scenario: Create a Java ProFormA question, check 'add'
  #  When I press "Create a new question"
  #  And I select "ProFormA Task" radio button
  #  And I press "Add"
  #  # When I add a "ProFormA" question filling the form with
  #  Then I should see "Please use \"import question\" in order to create a new Proforma question"

  Scenario: Create a ProFormA java question with simply compilation
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Default mark             | 2                              |
      | General feedback         | This is general feedback       |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
      | Comment                  | this is a new question         |
      | Weight                   | 17                             |
#      | Title                    | JUnit test                     |
#      | Description              | JUnit description              |
#    And I set the field with xpath "//input[@name='testweight[0]']" to "25"
# Compilation
# JUnit test
# TODO: code
# TODO: weight
# Checkstyle
# TODO: enable
# TODO: code
# TODO: weight
      | Penalty for each incorrect try  | 20%     |
    Then I should see "java-question"
    When I click on "Edit" "link" in the "java-question" "table_row"
    And I set the following fields to these values:
      | Question name | |
    And I press "id_submitbutton"
    Then I should see "You must supply a value here."

    When I set the following fields to these values:
      | Question name   | edited java-question |
    #  | Response format | Only filepicker        |
    # And I press "id_submitbutton"
    # Then I should see "When \"Only filepicker\" is selected, or responses are optional, you must allow at least one attachment."
    # When I set the following fields to these values:
    #  | Response format | Editor |
    And I press "id_submitbutton"
    Then I should see "edited java-question"

    When I click on "Edit" "link" in the "edited java-question" "table_row"
    # in the "Question name" "table_row"
    # textarea (CodeMirror fields)
    Then the field "Question name" matches value "edited java-question"
    And the field "Question text" matches value "write a java program that....."
    And the field "Default mark" matches value "2"
    And the field "General feedback" matches value "This is general feedback"
    And the field "Response format" matches value "editor"
    And the field "Response filename" matches value "MyClass.java"
    And the field "Response template" matches value "// type your code here"
    And the field "Comment" matches value "this is a new question"
    # compilation
    And the field "Weight" matches value "17"
    # JUnit test
#    And the field "Title" matches value "JUnit test"
#    And the field "Description" matches value "JUnit description"
    # Checkstyle
    And the field "Penalty for each incorrect try" matches value "20%"

#    Then I should see "write a java program that....." in the "#id_questiontext" "css_element"
#    And I should see "This is general feedback" in the "#id_generalfeedback" "css_element"
#    And I should see "// type your code here" in the "#id_responsetemplate" "css_element"
#    # input type=text
#    And "#id_responsefilename[value='MyClass.java']" "css_element" should exist
#    And "#id_name[value='edited java-question']" "css_element" should exist


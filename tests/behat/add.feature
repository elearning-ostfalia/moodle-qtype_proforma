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
      | General feedback         | This is general feedback       |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
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
    Then I should see "write a java program that....." in the "#id_questiontext" "css_element"
    And I should see "This is general feedback" in the "#id_generalfeedback" "css_element"
    And I should see "// type your code here" in the "#id_responsetemplate" "css_element"
    # input type=text
    And "#id_responsefilename[value='MyClass.java']" "css_element" should exist
    And "#id_name[value='edited java-question']" "css_element" should exist


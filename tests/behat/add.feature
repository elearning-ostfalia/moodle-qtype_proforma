@qtype @qtype_proforma
Feature: ADD JAVA QUESTION WITH COMPILATION, JUNIT AND CHECKSTYLE
  Test creating a ProFormA java question
  As a teacher
  In order to test my students
  I need to be able to create a Java question

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

  Scenario: Create, save and open a ProFormA java question with compilation, one Junit test and checkstyle
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Default mark             | 2                              |
      | General feedback         | This is general feedback       |
      | Response format          | editor                         |
      | Input box size           | 20 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
      | Comment                  | this is a new question         |
      | Title                    | JUnit test title               |
      | Description              | JUnit description              |
      | Penalty for each incorrect try  | 20%     |
    Then I should see "Code is missing"

    When I set the following fields to these values:
      | Question name   | new java-question |
    # Compilation
    And I set the field "Weight" number "1" to "10"
    # JUnit
    And I set the field "Weight" number "2" to "20"
    And I set the field with xpath "//textarea[@name='testcode[0]']" to "// class XClass {}"
    # Checkstyle
    And I set the field with xpath "//input[@name='checkstyle']" to "1"
    And I set the field "Weight" number "3" to "30"
    And I set the field with xpath "//textarea[@name='checkstylecode']" to "<!-- checkstyle code-->"
    And I press "id_submitbutton"
    Then I should see "Cannot determine classname (filename)"

    When I set the field with xpath "//textarea[@name='testcode[0]']" to "class XClass {}"
    And I press "id_submitbutton"
    Then I should see "new java-question"

    When I click on "Edit" "link" in the "new java-question" "table_row"
    Then the following fields match these values:
      | Question name            | new java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 2                              |
      | General feedback         | This is general feedback       |
      | Response format          | editor                         |
      | Input box size           | 20 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
      | Comment                  | this is a new question         |
      | Title                    | JUnit test title               |
      | Description              | JUnit description              |
      | Penalty for each incorrect try  | 20%                     |

    And the field "Weight" number "1" matches value "10"
    # JUnit
    And the field "Weight" number "2" matches value "20"
    And the field with xpath "//textarea[@name='testcode[0]']" matches value "class XClass {}"
    # Checkstyle
    And the field with xpath "//input[@name='checkstyle']" matches value "1"
    And the field "Weight" number "3" matches value "30"
    And the field with xpath "//textarea[@name='checkstylecode']" matches value "<!-- checkstyle code-->"

    And I press "Cancel"

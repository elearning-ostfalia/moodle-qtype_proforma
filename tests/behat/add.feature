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

  Scenario: Create, save and open a ProFormA java question with compilation, one Junit test (default values)
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Title                    | JUnit test title               |
    Then I should see "Code is missing"

    # JUnit
    When I set the field with xpath "//textarea[@name='testcode[0]']" to "class XClass {}"
    And I press "id_submitbutton"
    Then I should see "java-question"

    When I click on "Edit" "link" in the "java-question" "table_row"
    Then the following fields match these values:
      | Question name            |     java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        |                                |
      | Comment                  |                                |
      | Title                    | JUnit test title               |
      | Description              |                                |
      | Penalty for each incorrect try  | 10%                     |
    # compile
      | compileweight              |      0                       |
    #And the field "compileweight" matches value "0"
    And the "compile" checkbox is "checked"
    # JUnit
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "class XClass {}"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"

    And I press "Cancel"


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
      | Model solution           | // code for model solution     |
      | Comment                  | this is a new question         |
      | Title                    | JUnit test title               |
      | Description              | JUnit description              |
      | Penalty for each incorrect try  | 20%     |
    Then I should see "Code is missing"

    When I set the following fields to these values:
      | Question name   | new java-question |
      | compileweight   | 10                |

    # Compilation
    #And I set the field "compileweight" to "10"
    # JUnit
    And I set the field "testweight[0]" to "20"
    And I set the field "testcode[0]" to "// class XClass {}"
    # Checkstyle
    And I set the field "checkstyle" to "1"
    And I set the field "checkstyleweight" to "30"
    And I set the field "checkstylecode" to "<!-- checkstyle code-->"
    And I press "id_submitbutton"
    Then I should see "Cannot determine classname (filename)"

    When I set the field "testcode[0]" to "class XClass {}"
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
      | Model solution           | // code for model solution     |
      | Comment                  | this is a new question         |
      | Title                    | JUnit test title               |
      | Description              | JUnit description              |
      | Penalty for each incorrect try  | 20%                     |

    # Compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "10"
    # JUnit
    And the field "testweight[0]" matches value "20"
    And the field "testcode[0]" matches value "class XClass {}"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "30"
    And the field "checkstylecode" matches value "<!-- checkstyle code-->"

    And I press "Cancel"

  Scenario: Create, save and open a ProFormA java question with compilation and two Junit tests
    When I add a "ProFormA" question filling the form with:
      | Question name            | java-question with 2 tests     |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
      | Model solution           | // code for model solution     |
      | Title                    | JUnit #1                       |
    Then I should see "Code is missing"
    When I press "id_option_add_fields"
    And I set the field "testcode[0]" to "class XClass {}"
    And I set the field "testcode[1]" to "class YClass {}"
    And I set the field "testdescription[1]" to "this is the second JUnit test"
    And I press "id_submitbutton"
    Then I should see "Title is missing"

    When I set the field "testtitle[1]" to "Junit #2"
    And I press "id_submitbutton"
    Then I should see "java-question with 2 tests"

    When I click on "Edit" "link" in the "java-question with 2 tests" "table_row"
    Then the following fields match these values:
      | Question name            | java-question with 2 tests     |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        | // type your code here         |
      | Model solution           | // code for model solution     |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |

    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "0"
    # JUnit #1
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "class XClass {}"
    And the field "testdescription[0]" matches value ""
    # JUnit #2
    And the field "testweight[1]" matches value "1"
    And the field "testcode[1]" matches value "class YClass {}"
    And the field "testdescription[1]" matches value "this is the second JUnit test"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"

    And I press "Cancel"
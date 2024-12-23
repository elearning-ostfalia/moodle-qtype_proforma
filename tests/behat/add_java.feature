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
    And the following config values are set as admin:
      | clang | 0  | qtype_proforma |
      | cpp| 0  | qtype_proforma |
      | python| 0  | qtype_proforma |
      | setlx| 0  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript
  Scenario: Create, save and open a ProFormA java question with compilation, one Junit test (default values)
##########################################################################
    When I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Title                    | JUnit test title               |
    # Step is automatically finished with: I press "id_submitbutton"
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    
    # feedback options
    And I expand all fieldsets
    And the field "Initially collapse/expand" matches value "collapse"
    # 'Show messages in editor'
    And the "inlinemessages" checkbox is "1"    
    
    And I should not see "Model solution files "
    # JUnit
    When I set the codemirror "testcode_0" to "class XClass {}"
    And I press "id_submitbutton"
    Then I should see "java-question"

    When I choose "Edit question" action for "java-question" in the question bank
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
      | Programming language version  | 21                       |
      | Model solution           |    |
    # compile
      | compileweight              |      0                       |
    #And the field "compileweight" matches value "0"
    And the "compile" checkbox is "not checked"
    # JUnit
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "class XClass {}"
    And the field "testversion[0]" matches value "4.12"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # No model solution files
    And I should not see "Model solution files "
    # Finish
    And I press "Cancel"

##########################################################################
  @javascript
  Scenario: Create, save and open a ProFormA java question with compilation, one Junit test and checkstyle
##########################################################################
    When I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Default mark             | 2                              |
      | General feedback         | This is general feedback       |
      | Response format          | editor                         |
      | Input box size           | 20 lines                       |
      | Response filename        | MyClass.java                   |
#      | Response template        | // type your code here         |
#      | Model solution           | // code for model solution     |
      | Comment                  | this is a new question         |
      | Title                    | JUnit test title               |
      | Description              | JUnit description              |
      | Penalty for each incorrect try  | 20%     |
      | Programming language version  | 1.8     |
    And I set the codemirror "responsetemplate" to "// type your code here"
    And I set the codemirror "modelsolution" to "// code for model solution"
    And I press "id_submitbutton"
    Then I should see "Testcode required"

    When I set the following fields to these values:
      | Question name   | new java-question |
#      | compileweight   | 10                |

    # Compilation
    And I check the "compile" checkbox
    And I set the field "compileweight" to "11"
    # JUnit
    And I set the field "testweight[0]" to "20"
    # And I set the field "testcode[0]" to "// class XClass {}"
    And I set the codemirror "testcode_0" to "// class XClass {}"
    And I set the field "testversion[0]" to "4.12"
    # Checkstyle
    And I set the field "checkstyle" to "1"
    And I set the field "checkstyleweight" to "30"
    # And I set the field "checkstylecode" to "<!-- checkstyle code-->"
    And I set the codemirror "checkstylecode" to "<!-- checkstyle code-->"
    And I set the field "checkstyleversion" to "8.23"
    And I press "id_submitbutton"
    Then I should see "Cannot determine classname (filename)"

    # When I set the field "testcode[0]" to "class XClass {}"
    When I set the codemirror "testcode_0" to "class XClass {}"
    And I press "id_submitbutton"
    Then I should see "new java-question"

    # Leave out preview because of problems with test environment (Moodle 3 => 4)
    # TODO: check for download link
    # When I open preview for "new java-question" in the question bank
    # Then I should see "template.txt"
    # Then I should see "// type your code here"
    # And following "template.txt" should download file with between "22" and "24" bytes
    # And I close preview

    When I am on the "new java-question" "core_question > edit" page logged in as teacher1
    # When I choose "Edit question" action for "new java-question" in the question bank
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
    And the field "Programming language version" matches value "1.8"

    # Compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "11"
    # JUnit
    And the field "testweight[0]" matches value "20"
    And the field "testcode[0]" matches value "class XClass {}"
    And the field "testversion[0]" matches value "4.12"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "30"
    And the field "checkstylecode" matches value "<!-- checkstyle code-->"
    And the field "checkstyleversion" matches value "8.23"

    And I set the codemirror "responsetemplate" to "new code snippet that can be used as a starting point for the student"
    And I press "id_submitbutton"
    Then I should see "new java-question"

    # check for download link
    # The following line does not work as there are multiple records found :-(
    # When I am on the "new java-question" "core_question > preview" page
    When I open preview for "new java-question" in the question bank
    #Then I should see "questiondownload.txt"
    #Then I should see "lib.txt"
    #Then I should see "instruction.txt"
    # new code template in editor and for download
    Then I should see "template.txt"
    Then I should see "new code snippet that can be used as a starting point for the student"
    #And following "questiondownload.txt" should download file with between "65" and "67" bytes
    #And following "instruction.txt" should download file with between "17" and "20" bytes
    #And following "lib.txt" should download file with between "9" and "12" bytes
    And following "template.txt" should download file with between "69" and "73" bytes
    # And I switch to the main window
    # And I close preview

@javascript
##########################################################################
  Scenario: Create, save and open a ProFormA java question with compilation and two Junit tests
##########################################################################
    When I create a new "java" question
    And I set the following fields to these values:
        | Question name            | java-question with 2 tests     |
        | Question text            | write a java program that..... |
        | Response format          | editor                         |
        | Response filename        | MyClass.java                   |
        | Title                    | JUnit #1                       |
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    And I set the codemirror "responsetemplate" to "// type your code here"
    And I set the codemirror "modelsolution" to "// code for model solution"

    # add second Junit
    When I press "id_option_add_fields"
    And I set the codemirror "testcode_0" to "class XClass {}"
    And I set the field "testversion[0]" to "5"

    And I set the codemirror "testcode_1" to "class YClass {}"
    And I set the field "testdescription[1]" to "this is the second JUnit test"
    And I set the field "testversion[1]" to "4.12"

    And I press "id_submitbutton"
    # Title #2 missing:
    Then I should see "Title required"
    When I set the field "testtitle[1]" to "Junit #2"
    And I press "id_submitbutton"
    Then I should see "java-question with 2 tests"

    When I choose "Edit question" action for "java-question with 2 tests" in the question bank
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

    And the "compile" checkbox is "not checked"
    # And the field "compileweight" matches value "0"
    # JUnit #1
    And the field "testweight[0]" matches value "1"
    And the field "testcode[0]" matches value "class XClass {}"
    And the field "testdescription[0]" matches value ""
    And the field "testversion[0]" matches value "5"
    # JUnit #2
    And the field "testweight[1]" matches value "1"
    And the field "testcode[1]" matches value "class YClass {}"
    And the field "testdescription[1]" matches value "this is the second JUnit test"
    And the field "testversion[1]" matches value "4.12"
    # Checkstyle
    And the "checkstyle" checkbox is "not checked"

    And I press "Cancel"


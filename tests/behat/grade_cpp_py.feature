@qtype @qtype_proforma @javascript @grade_proforma
Feature: GRADE C/C++/Python
  Grade c question with actual grader
  As a teacher
  In order to check my c questions will work for students
  I need to preview and grade them

  # Requires valid Praktomat connection on http://praktomat:8010
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following config values are set as admin:
      | graderuri_host | http://praktomat:8010  | qtype_proforma |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
#    And the following "question categories" exist:
#      | contextlevel | reference | name           |
#      | Course       | C1        | Test questions |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript @_switch_window @_file_upload
  Scenario: Create a C++ question, preview and submit a response.
    When I navigate to "Question bank" in current page administration

    And I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_c++/c" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | C++ question                  |
      | Question text            | question text  |
      | Response format          | editor                         |
      | Response filename        | squareroot.cpp                   |
      | Penalty for each incorrect try  | 20%     |
    # GoogleTest 1
    And I set the field "testtitle[0]" to "GoogleTest 1"
    And I set the field "testweight[0]" to "10"
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/CMakeLists.txt" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/tests.cpp" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/squareroot.h" to "testfiles[0]" filemanager by name
    And I set the field "testentrypoint[0]" to "./demo"

    # GoogleTest 2
    # add another test
    And I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "GoogleTest 2"
    And I set the field "testweight[1]" to "10"
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/CMakeLists.txt" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/tests2.cpp" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/squareroot.h" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "./demo"

    And I press "id_submitbutton"
    Then I should see "C++ question"
    When I choose "Preview" action for "C++ question" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
    """
    #include <math.h>

    double squareroot(const double a) {
        return sqrt(a);
    }
    """

    And I press "Check"
    Then I should see "GoogleTest 1 (33/50 %)"
    And I should see "GoogleTest 2 (50/50 %)"
    And I should see "Log"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 0.83/1.00."

  @javascript @_file_upload
  Scenario: Import a ProFormA question, preview and submit a response.

    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_proforma" to "1"
    And I upload "question/type/proforma/tests/fixtures/behat/Palindrom.zip" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. Implementieren Sie"
    And I press "Continue"
    And I should see "Palindrom mit Checkstyle Vorne V2"
    When I choose "Preview" action for "Palindrom mit Checkstyle Vorne V2" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
    """
    public class MyString {
      static public Boolean isPalindrom(String s) {
        String r = new StringBuilder(s).reverse().toString();
        return (s.equalsIgnoreCase(r));
      }
    }
    """

    And I press "Check"
    Then I should see "CheckStyle Test (0/17 %)"
    And I should see "Java Compiler Test"
    And I should see " Junit Test PalindromTest (83/83 %)"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 0.83/1.00."
    # And I switch to the main window


  @javascript @_switch_window @_file_upload
  Scenario: Create a Setlx question, preview and submit a response.
    When the following config values are set as admin:
      | setlx | 1  | qtype_proforma |
    And I navigate to "Question bank" in current page administration
    And I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_setlx" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | setlx question    |
      | Question text            | write a setlx program that..... |
    # The default functions do not work for CodeMirror with Javascript.
    # So we must use other functions.
    And I set the field "testtitle[0]" to "Setlx #1"
    And I set the codemirror "testcode_0" to multiline:
"""
  testfunction := procedure(set, operation){
    return (forall(a in set, b in set| operation(a,b) in set));
  };

  print("Test1:$#set1>=2$");
  print("Test1:$#set2>=2$");
  print("Test2:$testfunction(set1,operation)$");
  print("Test3:$!testfunction(set2,operation)$");
"""
    And I press "id_submitbutton"
    Then I should see "setlx question"
    When I choose "Preview" action for "setlx question" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
    """
operation := procedure(a,b){
    return a*b;
};
set1 := {0,1};
set2 := {0,1,2};
    """

    And I press "Check"
    Then I should see "Setlx #1"
    And I should see "Correct"
    And I should see "Marks for this submission: 1.00/1.00."

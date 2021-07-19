@qtype @qtype_proforma @javascript @grade_proforma
Feature: GRADE
  Grade ProFormA questions with actual grader
  As a teacher
  In order to check my ProFormA questions will work for students
  I need to preview them

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





  @javascript @_switch_window @_file_upload
  Scenario: Create a Java question, preview and submit a response.
    When I navigate to "Question bank" in current page administration
    And I add a "ProFormA" question filling the form with:
      | Question name            | Java question                  |
      | Question text            | Write a class MyString that checks if a given string is a palindrome.  |
      | Response format          | editor                         |
      | Response filename        | MyString.java                   |
      | Penalty for each incorrect try  | 20%     |
      | Programming language version  | 11     |
    # Compilation
    And I check the "compile" checkbox
    And I set the field "compileweight" to "1"

    # JUnit 1
    And I set the field "testtitle[0]" to "Junit 1"
    And I set the field "testweight[0]" to "10"
    And I set the field "testversion[0]" to "4.12"
    And I set the codemirror "testcode_0" to multiline:
    """
import static org.junit.Assert.*;
import org.junit.Test;

public class PalindromTest {
	@Test
	public void testLagertonnennotregal() {
		assertTrue( MyString.isPalindrom("Lagertonnennotregal"));
	}

	@Test
	public void testEmpty() {
		assertTrue( MyString.isPalindrom(""));
	}


	@Test
	public void testFalse1() {
		assertFalse( MyString.isPalindrom("abc123321cbc"));
	}
}
"""

    # JUnit 2
    # add another Junit test
    And I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "Junit 2"
    And I set the field "testweight[1]" to "10"
    And I set the field "testversion[1]" to "4.12"
    And I select "id_testcodeformat_1_1" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/behat/Palindrom2Test.java" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "Palindrom2Test"

    # JUnit 3
    # add another Junit test
    And I press "id_option_add_fields"
    And I set the field "testtitle[2]" to "Junit 3"
    And I set the field "testweight[2]" to "10"
    And I set the field "testversion[2]" to "4.12"
    And I select "id_testcodeformat_2_1" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/behat/JunitPalindromTest.jar" to "testfiles[2]" filemanager by name
    And I set the field "testentrypoint[2]" to "PalindromTest"

    # Checkstyle
    And I set the field "checkstyle" to "1"
    And I set the field "checkstyleweight" to "5"
    And I set the field "checkstyleversion" to "8.29"
    ## And I set the field "checkstylecode" to "<!-- checkstyle code-->"
    And I set the codemirror "checkstylecode" to multiline:
"""
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE module PUBLIC "-//Checkstyle//DTD Check Configuration 1.3//EN" "https://checkstyle.org/dtds/configuration_1_3.dtd">
<module name="Checker">
  <property name="severity" value="warning"/>
  <module name="TreeWalker">
    <property name="tabWidth" value="4"/>
    <module name="LocalFinalVariableName"/>
    <module name="LocalVariableName"/>
    <module name="MemberName"/>
    <module name="MethodName"/>
    <module name="PackageName">
      <property name="severity" value="warning"/>
    </module>
    <module name="TypeName">
      <property name="severity" value="error"/>
    </module>
    <module name="ParameterNumber">
      <property name="severity" value="warning"/>
    </module>
    <module name="EmptyBlock">
      <property name="severity" value="warning"/>
    </module>
    <module name="LeftCurly">
      <property name="severity" value="info"/>
    </module>
    <module name="NeedBraces">
      <property name="severity" value="error"/>
    </module>
    <module name="EmptyStatement">
      <property name="severity" value="warning"/>
    </module>
    <module name="RightCurly">
      <property name="severity" value="info"/>
    </module>
  </module>
</module>
"""

    And I press "id_submitbutton"
    Then I should see "Java question"
    When I choose "Preview" action for "Java question" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
    """
    public class MyString {
        static public Boolean isPalindrom(String aString) {
            String reverse = new StringBuilder(aString).reverse().toString();
            return (aString.equalsIgnoreCase(reverse));
        }
    }
    """

    And I press "Check"
    Then I should see "Compiler (3/3 %)"
    Then I should see "Junit 1 (28/28 %)"
    And I should see "Junit 2 (14/28 %)"
    And I should see "Junit 3 (28/28 %)"
    And I should see "CheckStyle Test (14/14 %)"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 0.86/1.00."

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

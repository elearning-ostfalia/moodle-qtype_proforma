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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype        | name         | template         |
      | Test questions   | proforma     | proforma-001 | explorer           |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber | preferredbehaviour | canredoquestions |
      | quiz     | Quiz 1 | Quiz 1 description | C1     | quiz1    | immediatefeedback  | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Explorer/Student Submit file, finish and return to attempt
##########################################################################
    Given quiz "Quiz 1" contains the following questions:
      | proforma-001 | 1 |

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"
    And I should see "Solution"
    # Create new file MyString.java
    And I click on "New file..." in "Solution" contextmenu
    And I should see "New file"
    And I should see "Filename"
    And I set the field with xpath "//input[@name='promptname']" to "MyString.java"
    And I press "Ok"
    And I should see "MyString.java"
    And I should not see "New file"
    And I should not see "Filename"
    # Editor with new file is opened by default => enter text
    # Enter text in MyString.java
    # And I doubleclick on "//*[text() = 'MyString.java']" "xpath_element"
    And I set the explorer editor text to "hallo MyString"
    # wait until autosave passes
    # And I wait "30" seconds
    # THIS MUST WORK WITHOUT WAITING!!!
    # Submit
    And I press "Check"
    # Response does not matter (grade ris not configured)
    # we just want to check if the file is saved in Moodle

    And I press "Finish attempt"
    And I press "Return to attempt"
    And I should see "hallo MyString"

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Explorer/Student Finish and return to attempt with two files
##########################################################################
    Given quiz "Quiz 1" contains the following questions:
      | proforma-001 | 1 |

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"
    And I should see "Solution"
    # Create new file MyString.java
    And I click on "New file..." in "Solution" contextmenu
    And I should see "New file"
    And I should see "Filename"
    And I set the field with xpath "//input[@name='promptname']" to "MyString.java"
    And I press "Ok"
    And I should see "MyString.java"
    And I should not see "New file"
    And I should not see "Filename"
    # There should not a text inside the editor
    # (if there is text then it is the whole side as error)    
    And I should not see "Error | Acceptance test site"

    # Create new file Dummy.java with text 'hallo Dummy'
    And I click on "New file..." in "Solution" contextmenu
    And I should see "New file"
    And I should see "Filename"
    And I set the field with xpath "//input[@name='promptname']" to "Dummy.java"
    And I press "Ok"
    And I should see "Dummy.java"
    And I should not see "New file"
    And I should not see "Filename"
    And I set the explorer editor text to "hallo Dummy"
    # Enter text in MyString.java
    And I doubleclick on "//*[text() = 'MyString.java']" "xpath_element"
    And I wait "1" seconds
    And I set the explorer editor text to "hallo MyString"
    And I wait "1" seconds
    # And I pause
    # Finish attempt (without grading)
    And I press "Finish attempt"

    And I press "Return to attempt"
    And I doubleclick on "//*[text() = 'Dummy.java']" "xpath_element"
    And I should see "hallo Dummy"
    And I doubleclick on "//*[text() = 'MyString.java']" "xpath_element"
    # The entered text is not visible because it is not stored on
    # finishing the attempt (onbeforeunload is not called)
    And I should see "hallo MyString"

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Run explorer test as a student
##########################################################################
    # teacher creates question in quiz
    # todo: put question into xml file and import

    Given I am on the "Quiz 1" "mod_quiz > Edit" page logged in as teacher1
    And the following config values are set as admin:
      | graderuri_host | http://praktomat:8010  | qtype_proforma |

    And I open the "last" add to quiz menu
    And I follow "a new question"

    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_java" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
    # And I add a "ProFormA" question to the "Quiz 1" quiz with:
      | Question name            | Java question                  |
      | Question text            | Write a class MyString that checks if a given string is a palindrome.  |
      | Response format          | explorer                         |
      | Penalty for each incorrect try  | 20%     |
      | Programming language version  | 17     |
    And I upload "question/type/proforma/tests/fixtures/questiondownload.txt" file to "Downloadable files" filemanager

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
    And I press "id_submitbutton"
#    And I log out

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    # And I press "Attempt quiz now"
    And I press "Attempt quiz"
    And I should see "Solution"

    And I click on "New file..." in "Solution" contextmenu
    And I set the field with xpath "//input[@name='promptname']" to "MyString.java"
    And I press "Ok"
    And I set the explorer editor text to multiline:
        """
    public class MyString {
        static public Boolean isPalindrom(String aString) {
            String reverse = new StringBuilder(aString).reverse().toString();
            return (aString.equalsIgnoreCase(reverse));
        }
    }
    """

    # check solution
    When I press "Check"
    And I wait "2" seconds
    Then I should see "Junit 1 (100/100"
    And I should see "Correct"
    And I should see "Marks for this submission: 1.00/1.00."

    # finish attempt
    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"

    # check that code is visible
    And I should see "MyString.java"
    And I should see "isPalindrom(String aString)"


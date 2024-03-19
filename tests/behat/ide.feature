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
    And quiz "Quiz 1" contains the following questions:
      | proforma-001 | 1 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Explorer/Student Submit file, finish and return to attempt
##########################################################################
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
  Scenario: Run test as a student TODO
##########################################################################
    # teacher creates question in quiz
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher1
    # And I log in as "teacher1"
    # And I am on "Course 1" course homepage with editing mode on
    When I press "Create a new question ..."
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
    And I am on the "Quiz" "mod_quiz > edit" page
    # Add Question Essay 01 from question bank.
    And I open the "Page 1" add to quiz menu
    And I pause
    And I follow "from question bank"
    And I click on "Add to quiz" "link" in the "Java question" "table_row"
    And I should see "Java question" on quiz page "1"
    And I pause
    And I log out

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I pause
    # And I press "Attempt quiz now"
    And I press "Attempt quiz"
    And I should see "Solution"
#    And I pause
    # The following code does not work!!!
    And I click on "New empty file..." in "Solution" contextmenu
    And I should see "New filename:"
#    And "New filename" "dialogue" should be visible
    # And I click on "//*[text() = 'Solution']" "xpath_element"
#    And I click on "//*[normalize-space() = 'Solution']" "xpath_element"
#    And I click on "//span[contains(@class, 'name')]" "xpath_element"
#    And I click on ".name" "css_element"

#    And I pause

    And I press "Finish attempt"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I should see "5.00 out of 10.00"
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I delete "Quiz 1" activity
    And I run all adhoc tasks
    And I navigate to "Recycle bin" in current page administration
    And I should see "Quiz 1"
    And I click on "Restore" "link" in the "region-main" "region"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I navigate to "User report" in the course gradebook
    Then "Quiz 1" row "Grade" column of "user-grade" table should contain "5"
    And "Quiz 1" row "Percentage" column of "user-grade" table should contain "50"


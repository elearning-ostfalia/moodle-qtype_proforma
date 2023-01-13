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
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Quiz 1    | C1     | quiz1    |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | clang | 0  | qtype_proforma |
      | cpp| 0  | qtype_proforma |
      | python| 0  | qtype_proforma |
      | setlx| 0  | qtype_proforma |

#    And the following config values are set as admin:
#      | clang | 0  | qtype_proforma |
#      | cpp | 0  | qtype_proforma |


##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Run test as a student
##########################################################################
    # teacher creates question in quiz
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "ProFormA" question to the "Quiz 1" quiz with:
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
    And I log out

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    # And I press "Attempt quiz now"
    And I press "Attempt quiz"
    And I should see "Solution"
    And I click on "New empty file..." in "Solution" contextmenu
    And I pause
    # two clicks because the localized strings must be retrieved from server
    # And I rightclick on "//*[text() = 'Solution']" "xpath_element"
    # And I rightclick on "//*[text() = 'Solution']" "xpath_element"
    # And I pause
    And I should see "New filename:"
    And I click on "//*[text() = 'New empty file...']" "xpath_element"
    # And I click on "Upload H5P content types" "button" in the "#fitem_id_uploadlibraries" "css_element"
    And I should see "New filename"
#    And "New filename" "dialogue" should be visible
    And I pause
    # And I click on "//*[text() = 'Solution']" "xpath_element"
#    And I click on "//*[normalize-space() = 'Solution']" "xpath_element"
#    And I click on "//span[contains(@class, 'name')]" "xpath_element"
#    And I click on ".name" "css_element"

    And I pause

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




##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Run old test as a student
##########################################################################
    # neuer Versuch
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I am on the "Quiz 1" "quiz activity editing" page
    And I pause
    And I open the action menu in ".slots" "css_element"
    And I choose "Add question" in the open action menu
    And I pause

    And I add a "ProFormA" question to the "Quiz 1" quiz with:
      | Question name            | Java question                  |
      | Question text            | Write a class MyString that checks if a given string is a palindrome.  |
      | Response format          | explorer                         |
      | Penalty for each incorrect try  | 20%     |
      | Programming language version  | 17     |
    And I upload "question/type/proforma/tests/fixtures/questiondownload.txt" file to "Downloadable files" filemanager

    And I add a "True/False" question to the "Quiz 1" quiz with:
      | Question name                      | First question                          |
      | Question text                      | Answer the first question               |
      | General feedback                   | Thank you, this is the general feedback |
      | Correct answer                     | False                                   |
      | Feedback for the response 'True'.  | So you think it is true                 |
      | Feedback for the response 'False'. | So you think it is false                |
    And I log out

    And I am on the "Test quiz name" "quiz activity" page logged in as student1
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I should see "Answer the first question"





    # alter Versuch
    Given I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I pause
    When I navigate to "Edit quiz" in current page administration
    And I open the action menu in ".page-add-actions" "css_element"
    And I follow "a new question"
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_java" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    And I should see "Adding a ProFormA question"

    And  I set the following fields to these values:
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
    And I log out
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz now"
    And I should see "Solution"
    And I pause
    And I click on "New empty file..." in "Solution" contextmenu
    And I pause
    # two clicks because the localized strings must be retrieved from server
    # And I rightclick on "//*[text() = 'Solution']" "xpath_element"
    # And I rightclick on "//*[text() = 'Solution']" "xpath_element"
    # And I pause
    And I should see "New filename:"
    And I click on "//*[text() = 'New empty file...']" "xpath_element"
    # And I click on "Upload H5P content types" "button" in the "#fitem_id_uploadlibraries" "css_element"
    And I should see "New filename"
#    And "New filename" "dialogue" should be visible
    And I pause
    # And I click on "//*[text() = 'Solution']" "xpath_element"
#    And I click on "//*[normalize-space() = 'Solution']" "xpath_element"
#    And I click on "//span[contains(@class, 'name')]" "xpath_element"
#    And I click on ".name" "css_element"

    And I pause

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

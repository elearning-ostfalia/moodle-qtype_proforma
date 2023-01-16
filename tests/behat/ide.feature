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
  Scenario: Run test as a student TODO
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
    And I pause
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


@qtype @qtype_proforma
Feature: WORK IN EXPLORER
  Test basic functions in explorer

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

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"
    And I should see "Solution"
    # Create new file MyString.java
    And I click on "New file..." in "Solution" contextmenu
    And I set the field with xpath "//input[@name='promptname']" to "MyString.java"
    And I press "Ok"
    And I should see "MyString.java"
    And I should not see "New file"
    And I should not see "Filename"
    And I set the explorer editor text to "hallo MyString"
    And I should see "hallo MyString"
    # Create new file Dummy.java with text 'hallo Dummy'
    And I click on "New file..." in "Solution" contextmenu
    And I set the field with xpath "//input[@name='promptname']" to "Dummy.java"
    And I press "Ok"
    And I should see "Dummy.java"
    And I should not see "New file"
    And I should not see "Filename"
    And I set the explorer editor text to "hallo Dummy"
    And I should see "hallo Dummy"


##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Explorer/Student rename file
# Create 2 files and rename one of them.
# Change content of that file.
##########################################################################
    # Rename MyString.java => YourString.java
    And I click on "Rename..." in "MyString.java" contextmenu
    And I set the field with xpath "//input[@name='promptname']" to "YourString.java"
    And I press "Ok"
    # Change input
    And I doubleclick on "//*[text() = 'YourString.java']" "xpath_element"
    And I set the explorer editor text to "hallo YourString"
    And I press "Check"
    And I press "Finish attempt"
    And I press "Return to attempt"
    And I should see "YourString.java"
    And I should see "Dummy.java"
    And I doubleclick on "//*[text() = 'YourString.java']" "xpath_element"
    And I should see "hallo YourString"
    And I doubleclick on "//*[text() = 'Dummy.java']" "xpath_element"
    And I should see "hallo Dummy"

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Explorer/Student delete file
##########################################################################
    # Delete MyString.java
    And I should see "MyString.java"
    When I click on "Delete..." in "MyString.java" contextmenu
    And I should see "Are you sure that you want to delete file /MyString.java?"
    And I press "Ok"
    And I should not see "MyString.java"
    And I press "Check"
    And I press "Finish attempt"
    And I press "Return to attempt"
    And I should not see "YourString.java"
    And I should see "Dummy.java"
    And I doubleclick on "//*[text() = 'Dummy.java']" "xpath_element"
    And I should see "hallo Dummy"
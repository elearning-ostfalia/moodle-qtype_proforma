@qtype @qtype_proforma
Feature: PREVIEW
  Preview ProFormA questions
  As a teacher
  In order to check my ProFormA questions will work for students
  I need to preview them

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name      | template         |
      | Test questions   | proforma | proforma-001 | editor           |
      | Test questions   | proforma | proforma-003 | filepicker            |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  #@javascript @_switch_window
  @_switch_window
  Scenario: Preview a ProFormA question and submit a partially correct response.
    When I click on "Preview" "link" in the "proforma-001" "table_row"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I should see "Please code the reverse string function not using a library function.(äöüß)"
    # how do we test downloads and template??
    # And I should see "Downloads: temp.txt instruction.txt lib.txt"
    And I switch to the main window

  #@javascript @_switch_window
  @_switch_window
  Scenario: Preview a ProFormA question and submit a partially correct response.
    When I click on "Preview" "link" in the "proforma-003" "table_row"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"

    And I should see "Please code the reverse string function not using a library function.(äöüß)"
    And I switch to the main window

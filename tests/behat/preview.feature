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

  @javascript @_switch_window
  Scenario: Preview a ProFormA question and submit a partially correct response.
    When I choose "Preview" action for "proforma-001" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I should see "Please code the reverse string function not using a library function.(äöüß)"
    # text in response template
    And I should see "//text in responsetemplate"
    # check download links
    And I should not see "codesnippet.py"
    And I should see "template.txt"
    And I should see "instruction.txt"
    And I should see "lib.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    And following "template.txt" should download file with between "26" and "29" bytes
    # And I pause
    And following "lib.txt" should download file with between "9" and "12" bytes

    And I switch to the main window

  @javascript @_switch_window
  Scenario: Preview a ProFormA question and submit a partially correct response.
    When I choose "Preview" action for "proforma-003" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"

    And I should see "Please code the reverse string function not using a library function.(äöüß)"
    # text in response template
    # And I pause
    And I should not see "#code snippet for python"
    # check download links
    And I should not see "codesnippet.py"
    And I should see "instruction.txt"
    And I should see "lib.txt"
    And I should not see "template.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    # And following "template.txt" should download file with between "24" and "27" bytes
    And following "lib.txt" should download file with between "9" and "12" bytes

    And I switch to the main window

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
#    And the following "questions" exist:
#      | questioncategory | qtype | name      | template         |
#      | Test questions   | proforma | proforma-001 | editor           |
#      | Test questions   | proforma | proforma-003 | filepicker            |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
#    And I navigate to "Question bank" in current page administration
    


  @javascript @_switch_window @_file_upload
  Scenario: Import and preview a ProFormA question and submit a partially correct response.

    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_proforma" to "1"
    And I upload "question/type/proforma/tests/fixtures/behatPalindrom.zip" file to "Import" filemanager
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
    And I set the preview answer to "public class MyString {static public Boolean isPalindrom(String s){String r = new StringBuilder(s).reverse().toString();return (s.equalsIgnoreCase(r));}}"
    And I press "Check"
    Then I should see "CheckStyle Test (0/17 %)"
    And I should see "Java Compiler Test"
    And I should see " Junit Test PalindromTest (83/83 %)"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 0.83/1.00."
    # And I switch to the main window

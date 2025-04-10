@qtype @qtype_proforma
Feature: BACKUP AND RESTORE
  Test duplicating a quiz containing a ProFormA question
  As a teacher
  In order re-use my courses containing ProFormA questions
  I need to be able to backup and restore them

  Background:

    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And the following config values are set as admin:
      | enableasyncbackup | 0 |
    And the following config values are set as admin:
      | taskmaxbytes        | 10485760          | qtype_proforma |

  @javascript
  Scenario: Duplicate a proforma quiz with Java question
        # create Java question
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java-question                  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
      | Title                    | JUnit test title               |
    # feedback options
    And I expand all fieldsets
    And the field "Initially collapse/expand" matches value "collapse"
    # 'Show messages in editor'
    And the "inlinemessages" checkbox is "1"
    # JUnit
    When I set the codemirror "testcode_0" to "class XClass {}"
    And I press "id_submitbutton"
    Then I should see "java-question"
    And quiz "Test quiz" contains the following questions:
      | java-question | 1 |
    # duplicate
    When I am on "Course 1" course homepage with editing mode on
    And I duplicate "Test quiz" activity editing the new copy with:
      | Name | Quiz 2 |
    And I am on the "Quiz 2" "mod_quiz > Edit" page
    Then I should see "java"
    And I am on the "Test quiz" "mod_quiz > Edit" page
    Then I should see "java"
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I should see "(1)"
    And I should not see "(2)"

  @javascript @_file_upload
  Scenario: Duplicate a proforma quiz with imported Java question
        # create Java question
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    # When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/javaquestion.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. write a function that checks if a given string is a palindrom"
    And I press "Continue"
    And I should see "palindrom"
    And quiz "Test quiz" contains the following questions:
      | palindrom | 1 |

    # duplicate
    When I am on "Course 1" course homepage with editing mode on
    And I duplicate "Test quiz" activity editing the new copy with:
      | Name | Quiz 2 |
    And I am on the "Quiz 2" "mod_quiz > Edit" page
    Then I should see "palindrom"
    And I am on the "Test quiz" "mod_quiz > Edit" page
    Then I should see "palindrom"
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I should see "(1)"
    And I should not see "(2)"

  @javascript @_file_upload
  Scenario: Duplicate a proforma quiz with imported Proforma task file question
    # import
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_proforma" to "1"
    And I upload "question/type/proforma/tests/fixtures/isPalindrom.zip" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
#    And I should see "1. write a function that checks if a given string is a palindrom"
    And I press "Continue"
    And I should see "isPalindrom"
    And quiz "Test quiz" contains the following questions:
      | isPalindrom | 1 |

    # duplicate
    When I am on "Course 1" course homepage with editing mode on
    And I duplicate "Test quiz" activity editing the new copy with:
      | Name | Quiz 2 |
    And I am on the "Quiz 2" "mod_quiz > Edit" page
    Then I should see "isPalindrom"
    And I am on the "Test quiz" "mod_quiz > Edit" page
    Then I should see "isPalindrom"
    When I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I should see "(1)"
    And I should not see "(2)"


@qtype_proforma @qtype @qformat_proforma @javascript @_file_upload
Feature: REPLACE TASK
  Replace task file after import
  As a teacher
  In order to update imported ProFormA questions
  I need to replace the task

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | T1        | Teacher1 | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

    # And I log in as "teacher1"
    # And I am on "Course 1" course homepage
    # And I navigate to "Question bank > Import" in current page administration
    And I am on the "Course 1" "core_question > course question import" page logged in as teacher1

    And I set the field "id_format_proforma" to "1"
    And I upload "question/type/proforma/tests/fixtures/isPalindrom.zip" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. Palindrom"
    And I press "Continue"
    And I should see "isPalindrom"
    And I choose "Edit question" action for "isPalindrom" in the question bank
    And I expand all fieldsets

  @_file_upload
  Scenario: Check default values.
    Then the following fields match these values:
      | Question name            | isPalindrom              |
      | Question text            | Palindrom |
      | Default mark             | 1                              |
      | General feedback         |        |
      | Response format          | Editor                         |
      | Response filename          | de/ostfalia/zell/isPalindromTask/MyString.java |
      | Input box size           | 15 lines                       |
      | Comment                  |        |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy     | Weighted sum  |
      | UUID                     | ebbfec1c-81f0-4446-9031-d4b92ec33333     |
      | ProFormA Version         | 2.0                        |

    And I should see "1" elements in "Downloadable files" filemanager
    # filename of download file with complex path is not completely visible
    #And I should see "de/ostfalia/zell/isPalindromTask/MyStringTemplate.java"

    And the field "testtitle[0]" matches value "Compiler Test"
    And the field "testweight[0]" matches value "0"
    And the field "testid[0]" matches value "1"
    And the field "testtype[0]" matches value "java-compilation"
    And the field "testdescription[0]" matches value ""

    And the field "testtitle[1]" matches value "Junit Test ostfalia/zell/isPalindromTask/PalindromTest"
    And the field "testweight[1]" matches value "1"
    And the field "testid[1]" matches value "2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testdescription[1]" matches value ""

    And following "de/ostfalia/zell/isPalindromTask/MyString.java" should download file with between "290" and "300" bytes
    # grader settings
    And I should see "isPalindrom.zip"
    # multiline fields
    And the field "Response template" starts with "// skeleton code"

### Replace task file

  @_file_upload
  Scenario: replace task with TASK WITH MIXED TESTS => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromTestsuiteInverted.zip" file to "ProFormA task file" filemanager
    And I press "id_submitbutton"
    Then I should see "isPalindrom"
    When I choose "Edit question" action for "isPalindrom" in the question bank
    And I expand all fieldsets
    # UUID should have been changed
    And the field "UUID" matches value "27a11e4d-f538-4673-8b0b-24d710b0cad5"
    And "isPalindromTestsuiteInverted.zip" "link" should exist
    And I press "Cancel"

### Error messages

  @_file_upload
  Scenario: replace task with OTHER PROGRAMMING LANGUAGE => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromErrPython.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "Programming language in new task is not 'java'."
    And I should see "Please check task or use ProFormA import."
    And the field "UUID" matches value "ebbfec1c-81f0-4446-9031-d4b92ec33333"
    # undo changes
    And I press "Cancel"
    When I choose "Edit question" action for "isPalindrom" in the question bank
    And I expand all fieldsets
    # UUID should been old values
    And the field "UUID" matches value "ebbfec1c-81f0-4446-9031-d4b92ec33333"
    And "isPalindrom.zip" "link" should exist
    And "isPalindromErrPython.zip" "link" should not exist
    And I press "Cancel"

  @_file_upload
  Scenario: replace task with CHANGED TEST ID => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromIdChanged.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "Test types or order do not match."
    And I should see "Please check task or use ProFormA import."
    And the field "UUID" matches value "ebbfec1c-81f0-4446-9031-d4b92ec33333"
    # undo changes
    And I press "Cancel"

  @_file_upload
  Scenario: replace task with DIFFERENT TEST TYPES => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromCheckstyleInsteadOfCompilertest.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "Test types or order do not match."
    And I should see "Please check task or use ProFormA import."
    And the field "UUID" matches value "ebbfec1c-81f0-4446-9031-d4b92ec33333"
    # undo changes
    And I press "Cancel"

  @_file_upload
  Scenario: replace task with MORE TESTS => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromWithCheckstyle.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "Number of tests has been changed: 3."
    And I should see "Please check task or use ProFormA import."
    And the field "UUID" matches value "ebbfec1c-81f0-4446-9031-d4b92ec33333"
    # undo changes
    And I press "Cancel"

  @_file_upload
  Scenario: replace task with MISSING TASK.XML => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromErrTaskMissing.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "ProFormA task file is missing."
    # undo changes
    And I press "Cancel"

  @_file_upload
  Scenario: replace task with INVALID TASK.XML => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I upload "question/type/proforma/tests/fixtures/isPalindromErrTaskInvalid.zip" file to "ProFormA task file" filemanager
    And I press "Save changes"
    Then I should see "Task.xml within ProFormA file is invalid."
    # undo changes
    And I press "Cancel"



### Delete task

  @_file_upload
  Scenario: delete task => error message.
    When I delete "isPalindrom.zip" from "ProFormA task file" filemanager
    And I press "id_submitbutton"
    Then I should see "Required"
    And I upload "question/type/proforma/tests/fixtures/isPalindrom.zip" file to "ProFormA task file" filemanager
    And I press "id_submitbutton"
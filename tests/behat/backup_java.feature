@qtype @qtype_proforma
Feature: BACKUP AND RESTORE JAVA
  Test duplicating a quiz containing a ProFormA Java question
  As a teacher
  In order re-use my courses containing ProFormA questions
  I need to be able to backup and restore them

  Background:
    Given the following config values are set as admin:
      | clang | 0  | qtype_proforma |
      | cpp | 0  | qtype_proforma |
    And the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
#    And the following "course enrolments" exist:
#      | user     | course | role           |
#      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name   | intro                           | course | idnumber |
      | quiz       | Quiz 1 | Quiz 1 for testing the Add menu | C1     | quiz1    |

    And I am on the "Quiz 1" "mod_quiz > Edit" page logged in as "admin"
    And I should see "Editing quiz: Quiz 1"

##########################################################################
  @javascript @_file_upload
  Scenario: Add some new question to the quiz using '+ a new question' options of the 'Add' menu.
##########################################################################
    When I open the "last" add to quiz menu
    And I follow "a new question"
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_java" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"
    When I set the following fields to these values:
      | Question name            | java-question  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
    # title for JUnit #1
      | Title                    | Junit #1               |

    # compilation
    And I check the "compile" checkbox
    And I set the field "compileweight" to "5"

    # Junit #1: editor
    And I set the field "testtitle[0]" to "Junit #1"
    And I set the field "testdescription[0]" to "first JUnit test"
    And I set the field "testversion[0]" to "5"
    And I set the codemirror "testcode_0" to "class XClass {}"
    And I set the field "testweight[0]" to "10"

    # add another Junit test
    And I press "id_option_add_fields"
    # Junit #2: two files
    And I set the field "testtitle[1]" to "Junit #2"
    And I set the field "testweight[1]" to "20"
    And I set the field "testdescription[1]" to "second JUnit test"
    And I set the field "testversion[1]" to "4.12"
    # change JUnit code format to 'files'
    And I set the field "id_testcodeformat_1_2" to "1"
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit2.java" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit1.java" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "XClass"

    # There seems to be a bug in the test environment uploading files 
    # into a second filemenager. So we close and reopen.
    And I press "id_submitbutton"
    And I click on "Edit question java-question" "link"

    # Junit #3: one file
    And I press "id_option_add_fields"
    And I set the field "testtitle[2]" to "Junit #3"
    And I set the field "testweight[2]" to "30"
    And I set the field "testversion[2]" to "5"
    And I set the field "testdescription[2]" to "third JUnit test"
    # change JUnit code format to 'files'
    And I set the field "id_testcodeformat_2_2" to "1"
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit.java" to "testfiles[2]" filemanager by name
    And I set the field "testentrypoint[2]" to "YClass"

    # confirm
    And I press "id_submitbutton"
    Then I should see "java-question"

    # start backup
    And I am on "Course 1" course homepage
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
  
    When I choose "Edit question" action for "java-question" in the question bank
    Then the following fields match these values:
      | Question name            | java-question              |
      | Question text            | write a java program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyClass.java                   |
      | Response template        |                                |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Programming language version  | 17                       |

    # compilation
    And the field "compileweight" matches value "5"
    And the "compile" checkbox is "checked"

    # Junit #1: editor
    And the field "testtitle[0]" matches value "Junit #1"
    And the field "testdescription[0]" matches value "first JUnit test"
    And the field "testversion[0]" matches value "5"
    And the field "testweight[0]" matches value "10"
    And the field "testcode[0]" matches value "class XClass {}"

    # Junit #2: two files
    And the field "testtitle[1]" matches value "Junit #2"
    And the field "testweight[1]" matches value "20"
    And the field "testdescription[1]" matches value "second JUnit test"
    And the field "testversion[1]" matches value "4.12"
    # check test file(s)
    And "reverseJUnit2.java" "link" should exist
    And "reverseJUnit1.java" "link" should exist
    # there seems to be no step out of the box for downloading a file from filemanager :-(
    And the size of file "reverseJUnit2.java" is between "680" and "690" bytes
    And the size of file "reverseJUnit1.java" is between "1700" and "1800" bytes

    And the field "testentrypoint[1]" matches value "XClass"

    # Junit #3: one file
    And the field "testtitle[2]" matches value "Junit #3"
    And the field "testweight[2]" matches value "30"
    And the field "testdescription[2]" matches value "third JUnit test"
    And the field "testversion[2]" matches value "5"
    # check test file(s)
    And "reverseJUnit.java" "link" should exist
    And the size of file "reverseJUnit.java" is between "1900" and "2000" bytes
    And the field "testentrypoint[2]" matches value "YClass"

    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "Cancel"


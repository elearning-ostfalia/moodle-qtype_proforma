@qtype @qtype_proforma
Feature: ADD JAVA JUNIT TESTCODE UPLOAD QUESTION
  Test creating a ProFormA java question
  As a teacher
  In order to test my students
  I need to be able to create a simple Java question

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
    And the following config values are set as admin:
      | clang | 0  | qtype_proforma |
      | cpp | 0  | qtype_proforma |
      | python | 0  | qtype_proforma |
      | setlx | 0  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript @_file_upload
  Scenario: more complex Java question
##########################################################################
    When I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java-question  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
    # title for JUnit #1
      | Title                    | Junit #1               |

    # add another Junit test
    And I press "id_option_add_fields"

    # Junit #1: editor
    And I set the field "testtitle[0]" to "Junit #1"
    And I set the field "testdescription[0]" to "first JUnit test"
    And I set the field "testversion[0]" to "5"
    And I set the codemirror "testcode_0" to "class XClass {}"
    And I set the field "testweight[0]" to "10"

    # Junit #2: two files
    And I set the field "id_testcodeformat_1_2" to "1"
    And I set the field "testtitle[1]" to "Junit #2"
    And I set the field "testweight[1]" to "20"
    And I set the field "testdescription[1]" to "second JUnit test"
    And I set the field "testversion[1]" to "4.12"
    # change JUnit code format to 'files'
    # select "Files" radio button
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit2.java" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit1.java" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "XClass"

    # There seems to be a bug in the test environment uploading files
    # into a second filemenager. So we close and reopen.
    And I press "id_submitbutton"
    And I choose "Edit question" action for "java-question" in the question bank

    # add Junit #3: one file
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

    # compilation
    And I check the "compile" checkbox
    And I set the field "compileweight" to "5"

    # save question and check values.
    And I press "id_submitbutton"
    Then I should see "java-question"

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
      | Programming language version  | 21                       |

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

##########################################################################
  @javascript @_file_upload
  Scenario: Create, save and open a Java question with uploaded Junit test
##########################################################################
    When I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java-question  |
      | Question text            | write a java program that..... |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |
    # title for JUnit #1
      | Title                    | Junit #1               |
    And I set the field "testweight[0]" to "10"
    And I set the field "testversion[0]" to "4.12"
    And I set the field "testdescription[0]" to "first JUnit test"
    # change JUnit code format to 'files'
    And I select "Files" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/reverseJUnit.java" to "testfiles[0]" filemanager by name

    # add second Junit test
    And I press "id_option_add_fields"

    And I set the field "testtitle[1]" to "Junit #2"
    And I set the field "testdescription[1]" to "second JUnit test"
    And I set the field "testversion[1]" to "5"
    And I set the codemirror "testcode_1" to "class XClass {}"
    And I set the field "testweight[1]" to "20"

    # compilation
    And I check the "compile" checkbox
    And I set the field "compileweight" to "5"

    And I press "id_submitbutton"
    Then I should see "JUnit entrypoint required"
    And I set the field "testentrypoint[0]" to "XClass"

    And I press "id_submitbutton"
    Then I should see "java-question"

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
      | Programming language version  | 21                       |

    # compilation
    And the field "compileweight" matches value "5"
    And the "compile" checkbox is "checked"

    # JUnit #1
    And the field "testweight[0]" matches value "10"
    And the field "testversion[0]" matches value "4.12"
    And the field "testdescription[0]" matches value "first JUnit test"
    And the field "testentrypoint[0]" matches value "XClass"
    # check test file(s)
    And "reverseJUnit.java" "link" should exist
    # there seems to be no step out of the box for downloading a file from filemanager :-(
    And the size of file "reverseJUnit.java" is between "1900" and "2000" bytes

    # JUnit #2
    And the field "testtitle[1]" matches value "Junit #2"
    And the field "testdescription[1]" matches value "second JUnit test"
    And the field "testversion[1]" matches value "5"
    And the field "testweight[1]" matches value "20"
    And the field "testcode[1]" matches value "class XClass {}"

    # Checkstyle
    And the "checkstyle" checkbox is "not checked"
    # Finish
    And I press "Cancel"


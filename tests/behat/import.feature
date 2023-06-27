@qtype @qtype_proforma
Feature: IMPORT (Moodle-XML format)
  Test importing ProFormA questions
  As a teacher
  In order to reuse ProFormA questions
  I need to import them

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

  @javascript @_file_upload
  Scenario: import Java question.
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

    When I choose "Edit question" action for "palindrom" in the question bank
    Then the following fields match these values:
      | Question name            | palindrom              |
      | Question text            | write a function that checks if a given string is a palindrom |
      | Default mark             | 1                              |
      | General feedback         | <p>general feedback<br></p>       |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyString.java                   |
      | Comment                  | <p>a comment</p>         |
      | Penalty for each incorrect try  | 10%                     |

    # multiline fields
    And the field "Response template" matches multiline
    """
    public class MyString {
        // todo
    }
    """
    And the field "Model solution" starts with "public class MyString {"
    #And the field "testcode[0]" matches multiline
    # Compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "0"
    # JUnit
    And the field "testweight[0]" matches value "1.4"
    And the field "testtitle[0]" matches value "JUnit-Test1"
    And the field "testdescription[0]" matches value ""
    And the field "testweight[1]" matches value "1"
    And the field "testtitle[1]" matches value "JUnit 2"
    And the field "testdescription[1]" matches value "Test 2"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "0.2"
    And the field with xpath "//textarea[@name='checkstylecode']" matches value "<!-- empty file -->"
    # Finish
    And I press "Cancel"

  @javascript @_file_upload
  Scenario: import ProFormA question.
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/testquestion_v2.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 3 questions from file"
    And I should see "1. This is the Description of the task (äöüß)."
    And I should see "2. Please code the reverse string function not using a library function.(äöüß)"
    And I should see "3. Write a short program that..."
    And I press "Continue"
    And I should see "test question with German Umlauts (äöüß)"
    And I should see "second ProFormA question"
    And I should see "java question"

    When I choose "Edit question" action for "java question" in the question bank
    Then the following fields match these values:
      | Question name            | java question              |
      | Question text            | Write a short program that... |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>       |
      | Response format          | editor                         |
      | Input box size           | 10 lines                       |
      | Response filename        | MyString.java                   |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution     |
      | Comment                  | <p>Check if the code uses a library function.</p>         |
      | Penalty for each incorrect try  | 20%                     |

    And the field "compileweight" matches value "2"
    # JUnit
    And the field "testweight[0]" matches value "3"
    And the field "testtitle[0]" matches value "Junit Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 2"
    And the field "testcode[0]" matches value "class XTest {}"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "4"
    And the field "checkstylecode" matches multiline
    """
    <?xml version="1.0" encoding="UTF-8"?>
    <!DOCTYPE module PUBLIC "-//Puppy Crawl//DTD Check Configuration 1.3//EN" "http://www.puppycrawl.com/dtds/configuration_1_3.dtd">
    <module name="Checker">
      <property name="severity" value="warning"/>
      <module name="TreeWalker">
        <module name="NeedBraces">
          <property name="severity" value="error"/>
        </module>
      </module>
    </module>
    """
    And I press "Cancel"

    When I choose "Edit question" action for "second ProFormA question" in the question bank
    # And I pause
    Then the following fields match these values:
      | Question name            | second ProFormA question           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | File picker                         |
      | Max. number of uploaded files          | 3                         |
      | Accepted file types          | .java, .jar                         |
      | Syntax highlighting      | Python                         |
      | Comment                  | <p>Check if the code uses a library function.(äöüß)</p>                 |
      | Aggregation strategy      | All or nothing                 |
      | Penalty for each incorrect try  | 20%                     |
      | UUID                     | UUID 1                     |
      | ProFormA Version         | 2.0                        |
#      | Response template        | multiline              |
    And the field "Max. response upload size" matches value "10240"
    And the field with name "testweight[0]" matches value "0"
    And the field with name "testweight[1]" matches value "1"
    And the field with name "testtitle[0]" matches value "Compiler Test"
    And the field with name "testtitle[1]" matches value "Junit Test 1"
    And the field with name "testdescription[0]" matches value ""
    And the field with name "testdescription[1]" matches value ""
    And the field with name "testtype[0]" matches value "java-compilation"
    And the field with name "testtype[1]" matches value "unittest"
    And the field with name "testid[0]" matches value "1"
    And the field with name "testid[1]" matches value "2"
    # static fields
    # download links
    And I should see "2" elements in "Downloadable files" filemanager
    # And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt"
    And I should see "ms2.txt"
    # templates are not supported for filepicker
    And I should not see "templ2.txt"
    # grader settings
#    And I should see "UUID 1"
    And I should see "testtask.zip"
#    And I should see "2.0"

    And I press "Cancel"

    # check for download link in "proforma-003"
    When I am on the "second ProFormA question" "core_question > preview" page logged in as teacher1
    # When I choose "Preview" action for "second ProFormA question" in the question bank
    # And I switch to "questionpreview" window
    Then I should see "lib.txt"
    Then I should see "instruction.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    And following "lib.txt" should download file with between "9" and "12" bytes
    And I switch to the main window

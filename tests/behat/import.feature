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
    And I log in as "teacher1"
    And I am on "Course 1" course homepage

  @javascript @_file_upload
  Scenario: import Java question.
    When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/javaquestion.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. write a function that checks if a given string is a palindrom"
    And I press "Continue"
    And I should see "palindrom"

    When I click on "Edit" "link" in the "palindrom" "table_row"
    Then the following fields match these values:
      | Question name            | palindrom              |
      | Question text            | write a function that checks if a given string is a palindrom |
      | Default mark             | 1                              |
      | General feedback         | <p>general feedback<br></p>       |
      | Response format          | editor                         |
      | Input box size           | 15 lines                       |
      | Response filename        | MyString.java                   |
      # multiline cannot be tested that what
#      | Response template        | public class MyString {         |
#      | Model solution           | public class MyString {         |
      | Comment                  | <p>a comment</p>         |
      | Penalty for each incorrect try  | 10%                     |

    And the field "Weight" number "1" matches value "0"
    # JUnit
    And the field "Weight" number "2" matches value "1.4"
    When I set the field with xpath "//input[@name='testtitle[0]']" to "JUnit-Test1"
    And I set the field with xpath "//input[@name='testdescription[0]']" to ""

    #And the field with xpath "//textarea[@name='testcode[0]']" matches value "class XClass {}"
    And the field "Weight" number "3" matches value "1"
    When I set the field with xpath "//input[@name='testtitle[1]']" to "JUnit 2"
    And I set the field with xpath "//input[@name='testdescription[1]']" to "Test 2"
    # Checkstyle
    And the checkstyle checkbox is checked
    And the field "Weight" number "4" matches value "0.2"
    And the field with xpath "//textarea[@name='checkstylecode']" matches value "<!-- empty file -->"

    And I press "Cancel"



  @javascript @_file_upload
  Scenario: import ProFormA question.
    When I navigate to "Question bank > Import" in current page administration
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

    When I click on "Edit" "link" in the "java question" "table_row"
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

    And the field "Weight" number "1" matches value "2"
    # JUnit
    And the field "Weight" number "2" matches value "3"
    When I set the field with xpath "//input[@name='testtitle[0]']" to "Junit Test 1"
    #And the field with xpath "//textarea[@name='testcode[0]']" matches value "class XClass {}"
    # Checkstyle
    And the checkstyle checkbox is checked
    And the field "Weight" number "3" matches value "4"
    #And the field with xpath "//textarea[@name='checkstylecode']" matches value "<!-- checkstyle code-->"

    And I press "Cancel"


    When I click on "Edit" "link" in the "second ProFormA question" "table_row"
    Then the following fields match these values:
      | Question name            | second ProFormA question           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | File picker                         |
      | Max. number of uploaded files          | 3                         |
      | Max. upload size          | 10KB                         |
      | Accepted file types          | .java, .jar                         |
      | Syntax highlighting      | Python                         |
      | Comment                  | <p>Check if the code uses a library function.(äöüß)</p>                 |
      | Aggregation strategy      | All or nothing                 |
      | Penalty for each incorrect try  | 20%                     |
#      | Response template        | multiline              |
    And the field "Weight" number "1" matches value "0"
    And the field "Weight" number "2" matches value "1"
    And the field with xpath "//input[@name='testtitle[0]']" matches value "Compiler Test"
    And the field with xpath "//input[@name='testtitle[1]']" matches value "Junit Test 1"
    And the field with xpath "//input[@name='testdescription[0]']" matches value ""
    And the field with xpath "//input[@name='testdescription[1]']" matches value ""
    And the field with xpath "//input[@name='testtype[0]']" matches value "java-compilation"
    And the field with xpath "//input[@name='testtype[1]']" matches value "unittest"
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
# todo: try and check values of static fields
    # download links
    And I should see "lib.txt, instruction.txt"
    And I should see "ms1.txt"
    And I should see "ms2.txt"
    And I should see "templ2.txt"
    And I should see "MyString.java"
    # grader settings
    And I should see "UUID 1"
    And I should see "testtask.zip"
    And I should see "2.0"

    And I press "Cancel"



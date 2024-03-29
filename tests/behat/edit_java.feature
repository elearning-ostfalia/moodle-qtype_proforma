@qtype @qtype_proforma
Feature: EDIT JAVA
  Test editing a Java question
  As a teacher
  In order to be able to update my Java question
  I need to edit them

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name      | template         |
      | Test questions   | proforma | proforma-java | java_2junit           |

##########################################################################
  @javascript
  Scenario: Check precondition for all successive scenarios
##########################################################################
    When I am on the "proforma-java" "core_question > edit" page logged in as teacher1
    # assert(expected old values)
    Then the following fields match these values:
      | Question name            | proforma-java           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution                 |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
    And I expand all fieldsets
    # feedback options
    And the field "Initially collapse/expand" matches value "collapse"
    # 'Show messages in editor'
    And the "inlinemessages" checkbox is "1"

    # compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2"
    # JUnit 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Junit Test 1"
    And the field "testdescription[0]" matches value "Description Junit 1"
    And the field "testtype[0]" matches value "unittest"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "class XTest {}"
    And the field "testversion[0]" matches value "4.12"
    # JUnit 2
    And the field "testtitle[1]" matches value "Junit Test 2"
    And the field "testdescription[1]" matches value "Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testversion[1]" matches value "5"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "4"
    And the field "checkstyleversion" matches value "8.23"
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

    # Finish
    And I press "Cancel"
    Then I should see "proforma-java"

    # check for download link
    When I open preview for "proforma-java" in the question bank
    #Then I should see "lib.txt"
    #Then I should see "instruction.txt"
    Then I should see "template.txt"
    Then I should see "//text in responsetemplate"
    #And following "instruction.txt" should download file with between "17" and "20" bytes
    #And following "lib.txt" should download file with between "9" and "12" bytes
    And following "template.txt" should download file with between "26" and "28" bytes
    # And I switch to the main window

##########################################################################
  @javascript
  Scenario: Edit a Java question (uncheck/check checkstyle and compile)
##########################################################################
    When I am on the "proforma-java" "core_question > edit" page logged in as teacher1
    # uncheck compile and checkstyle
    And I uncheck the "compile" checkbox
    And I uncheck the "checkstyle" checkbox
    And the "compile" checkbox is "unchecked"
    And the "checkstyle" checkbox is "unchecked"

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank
    # check for unchecked checkboxes
    And the "compile" checkbox is "unchecked"
    And the "checkstyle" checkbox is "unchecked"
    # recheck
    And I check the "compile" checkbox
    And I check the "checkstyle" checkbox
    And the "compile" checkbox is "checked"
    And the "checkstyle" checkbox is "checked"
    And the field "compileweight" matches value "0"
    And the field "checkstyleweight" matches value "0.2"
    And the field "checkstylecode" matches value ""
    # checkstyle code must be set because old value is lost.
    # since this is a Javascript testcase we need to use javascript function
    # in order to set value in codemirror
#    And I set the field "checkstylecode" to "<!-- empty-->"
    And I set the codemirror "checkstylecode" to "<!-- empty-->"
    # Check for Checkstyle version:
    # Standard value is prefreed, but 'Choose' is also ok.
    And the field "checkstyleversion" matches value "Choose"
    And I press "id_submitbutton"
    And I should see "Version required."
    And I set the field "checkstyleversion" to "8.29"

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank
    # check for checked checkboxes
    And the "compile" checkbox is "checked"
    # And the field "compileweight" should not be visible
    And the "checkstyle" checkbox is "checked"
    # And the field "checkstyleweight" should not be visible
#    And the field "checkstyleweight" matches value "0.2"
    And the field "checkstyleversion" matches value "8.29"
    # since this is a Javascript testcase we need to use javascript function
    # in order to check value in codemirror
    And the codemirror "checkstylecode" matches value "<!-- empty-->"

    And I press "Cancel"

##########################################################################
  @javascript @_file_upload
  Scenario: Edit a Java question (simply edit all values)
##########################################################################
    When I am on the "proforma-java" "core_question > edit" page logged in as teacher1
    # change all values that can be changed (keep editor set)
    And  I set the following fields to these values:
      | Question name            | updated proforma-java|
      | Question text            | new question text           |
      | Default mark             | 4                              |
      | General feedback         | do not use a library functions|
      | Response format          | Editor                         |
      | Syntax highlighting      | Python                           |
      | Input box size           | 20 lines                       |
#      | Response template        | new code snippet that can be used as a starting point for the student     |
#      | Model solution           | // new code for model solution                 |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |
      | Response filename        | newMyString.java                     |

    And I set the codemirror "responsetemplate" to "new code snippet that can be used as a starting point for the student"
    And I set the codemirror "modelsolution" to "// new code for model solution"

    # feedback options
    And I set the field "Initially collapse/expand" to "expand"
    # 'Show messages in editor'
    And I uncheck the "inlinemessages" checkbox

    # compile
    #And I set the field "compile" to "0"
    And I set the field "compileweight" to "2.5"
    # JUnit 1
    And I set the field "testtitle[0]" to "new Junit Test 1"
    And I set the field "testdescription[0]" to "new Description Junit 1"
    And I set the field "testweight[0]" to "3.5"
#    And I set the field "testcode[0]" to "class NewXTest {}"
    And I set the codemirror "testcode_0" to "class NewXTest {}"

    And I set the field "testversion[0]" to "5"
    # JUnit 2
    And I set the field "testtitle[1]" to "new Junit Test 2"
    And I set the field "testdescription[1]" to "new Description Junit 2"
    And I set the field "testweight[1]" to "6.5"
#    And I set the field "testcode[1]" to "class NewYTest {}"
    And I set the codemirror "testcode_1" to "class NewYTest {}"
    And I set the field "testversion[1]" to "5"
    # Checkstyle
    #And I set the field "checkstyle" to "0"
    And I set the field "checkstyleweight" to "4.5"
    # And I set the field "checkstylecode" to "<!-- empty-->"
    And I set the codemirror "checkstylecode" to "<!-- empty-->"
    And I set the field "checkstyleversion" to "8.29"

    And I press "id_submitbutton"
    Then I should see "updated proforma-java"

    When I choose "Edit question" action for "updated proforma-java" in the question bank
    Then the following fields match these values:
      | Question name            | updated proforma-java|
      | Question text            | new question text           |
      | Default mark             | 4                              |
      | General feedback         | do not use a library functions|
      | Response format          | Editor                         |
      | Syntax highlighting      | Python                           |
      | Input box size           | 20 lines                       |
      | Response template        | new code snippet that can be used as a starting point for the student     |
      | Model solution           | // new code for model solution                 |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |
      | Response filename        | newMyString.java                     |
    # feedback options
    And the field "Initially collapse/expand" matches value "expand"
    # 'Show messages in editor'
    And the "inlinemessages" checkbox is "0"
  # compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2.5"
    # JUnit 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "new Junit Test 1"
    And the field "testdescription[0]" matches value "new Description Junit 1"
    And the field "testtype[0]" matches value "unittest"
    And the field "testweight[0]" matches value "3.5"
    And the field "testcode[0]" matches value "class NewXTest {}"
    And the field "testversion[0]" matches value "5"
    # JUnit 2
    And the field "testtitle[1]" matches value "new Junit Test 2"
    And the field "testdescription[1]" matches value "new Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testweight[1]" matches value "6.5"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class NewYTest {}"
    And the field "testversion[1]" matches value "5"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "4.5"
    And the field "checkstylecode" matches value "<!-- empty-->"
    And the field "checkstyleversion" matches value "8.29"

    # Finish
    And I press "id_submitbutton"
    Then I should see "updated proforma-java"


    # check for download link
    When I open preview for "updated proforma-java" in the question bank
    #Then I should see "questiondownload.txt"
    #Then I should see "lib.txt"
    #Then I should see "instruction.txt"
    # new code template in editor and for download
    Then I should see "template.txt"
    Then I should see "new code snippet that can be used as a starting point for the student"
    #And following "questiondownload.txt" should download file with between "65" and "67" bytes
    #And following "instruction.txt" should download file with between "17" and "20" bytes
    #And following "lib.txt" should download file with between "9" and "12" bytes
    And following "template.txt" should download file with between "69" and "73" bytes
    # And I switch to the main window

@javascript
##########################################################################
  Scenario: Edit a Java question (remove and add Junit)
##########################################################################
    When I am on the "proforma-java" "core_question > edit" page logged in as teacher1
    # When I choose "Edit question" action for "proforma-java" in the question bank

    # remove JUnit 2 data by deleting content
    And I set the field "testtitle[1]" to ""
    And I set the field "testdescription[1]" to ""
    And I set the codemirror "testcode_1" to ""
    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank

    # JUnit 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Junit Test 1"
    And the field "testdescription[0]" matches value "Description Junit 1"
    And the field "testtype[0]" matches value "unittest"
    # weight is not visible (All or nothing)
    #And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "class XTest {}"
    And the field "testversion[0]" matches value "4.12"
    # JUnit 2 is not visible
    And I should not see "JUnit test 2"

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank
    # add Junit 2
    And I press "Add JUnit test"
    Then I should not see "JUnit test 3"
    And I should see "JUnit test 2"
    # add JUnit 2
    And I set the field "testtitle[1]" to "new Junit Test 2"
    And I set the field "testdescription[1]" to "new Description Junit 2"
    # weight is not visible (All or nothing)
    # And I set the field "testweight[1]" to "6.5"
    And I set the codemirror "testcode_1" to "class NewYTest {}"
    And I set the field "testversion[1]" to "5"

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank
    # check all values
    Then the following fields match these values:
      | Question name            | proforma-java|
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution                 |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2"
    # JUnit 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Junit Test 1"
    And the field "testdescription[0]" matches value "Description Junit 1"
    And the field "testtype[0]" matches value "unittest"
    # And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "class XTest {}"
    And the field "testversion[0]" matches value "4.12"
    # JUnit 2
    And the field "testtitle[1]" matches value "new Junit Test 2"
    And the field "testdescription[1]" matches value "new Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    # And the field "testweight[1]" matches value "6.5"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class NewYTest {}"
    And the field "testversion[1]" matches value "5"
    # Checkstyle
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "4"
    And the field "checkstyleversion" matches value "8.23"
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

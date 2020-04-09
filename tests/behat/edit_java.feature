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
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration



##########################################################################
  @javascript
  Scenario: Edit a ProFormA question (uncheck/check checkstyle and compile)
##########################################################################
    When I choose "Edit question" action for "proforma-java" in the question bank
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
    And the field "compileweight" matches value "0"
    And the "checkstyle" checkbox is "checked"
    And the field "checkstyleweight" matches value "0.2"
    And the field "checkstyleversion" matches value "8.29"
    # since this is a Javascript testcase we need to use javascript function
    # in order to check value in codemirror
    And the codemirror "checkstylecode" matches value "<!-- empty-->"

    And I press "Cancel"


##########################################################################
  Scenario: Check precondition for all successive scenarios
##########################################################################
    When I choose "Edit question" action for "proforma-java" in the question bank
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
    And the field "testversion[1]" matches value "4.12"
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

##########################################################################
  Scenario: Edit a ProFormA question (simply edit all values)
##########################################################################
    When I choose "Edit question" action for "proforma-java" in the question bank
    # change all values that can be changed (keep editor set)
    And  I set the following fields to these values:
      | Question name            | updated proforma-java|
      | Question text            | new question text           |
      | Default mark             | 4                              |
      | General feedback         | do not use a library functions|
      | Response format          | Editor                         |
      | Syntax highlighting      | Python                           |
      | Input box size           | 20 lines                       |
      | Response template        | //new text in responsetemplate     |
      | Model solution           | // new code for model solution                 |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |
      | Response filename        | newMyString.java                     |

    # compile
    #And I set the field "compile" to "0"
    And I set the field "compileweight" to "2.5"
    # JUnit 1
    And I set the field "testtitle[0]" to "new Junit Test 1"
    And I set the field "testdescription[0]" to "new Description Junit 1"
    And I set the field "testweight[0]" to "3.5"
    And I set the field "testcode[0]" to "class NewXTest {}"
    And I set the field "testversion[0]" to "5"
    # JUnit 2
    And I set the field "testtitle[1]" to "new Junit Test 2"
    And I set the field "testdescription[1]" to "new Description Junit 2"
    And I set the field "testweight[1]" to "6.5"
    And I set the field "testcode[1]" to "class NewYTest {}"
    And I set the field "testversion[1]" to "5"
    # Checkstyle
    #And I set the field "checkstyle" to "0"
    And I set the field "checkstyleweight" to "4.5"
    And I set the field "checkstylecode" to "<!-- empty-->"
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
      | Response template        | //new text in responsetemplate     |
      | Model solution           | // new code for model solution                 |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |
      | Response filename        | newMyString.java                     |
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

    And I press "Cancel"

##########################################################################
  Scenario: Edit a ProFormA question (remove and add Junit)
##########################################################################
    When I choose "Edit question" action for "proforma-java" in the question bank

    # remove JUnit 2 data by deleting content
    And I set the field "testtitle[1]" to ""
    And I set the field "testdescription[1]" to ""
    And I set the field "testcode[1]" to ""
    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank

    # JUnit 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Junit Test 1"
    And the field "testdescription[0]" matches value "Description Junit 1"
    And the field "testtype[0]" matches value "unittest"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "class XTest {}"
    And the field "testversion[0]" matches value "4.12"
    # JUnit 2 is not visible
    And I should not see "2. JUnit Test"

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Edit question" action for "proforma-java" in the question bank
    # add Junit 2
    And I press "Add JUnit test"
    Then I should not see "3. JUnit Test"
    And I should see "2. JUnit Test"
    # add JUnit 2
    And I set the field "testtitle[1]" to "new Junit Test 2"
    And I set the field "testdescription[1]" to "new Description Junit 2"
    And I set the field "testweight[1]" to "6.5"
    And I set the field "testcode[1]" to "class NewYTest {}"
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
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "class XTest {}"
    And the field "testversion[0]" matches value "4.12"
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



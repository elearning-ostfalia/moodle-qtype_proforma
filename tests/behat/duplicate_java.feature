@qtype @qtype_proforma
Feature: DUPLICATE JAVA
  Test copying a ProFormA question
  As a teacher
  In order to use an exsiting ProFormA question with some changes
  I need to copy them

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


##########################################################################
  @javascript @_file_upload
  Scenario: Duplicate a Java question with use of filepicker
##########################################################################
    # at first we need to create a filepicker question
    When I am on the "proforma-java" "core_question > edit" page logged in as teacher1
    And I set the field "Response format" to "filepicker"
    # model solution converted to file
    And I should see "1" elements in "Model solution files" filemanager
    # updload model solution file
    # (the file aready exists because the old model solution from editor is
    # already stored in draft area)
    And I upload and overwrite "question/type/proforma/tests/fixtures/MyString.java" file to "Model solution files" filemanager
    And  I set the following fields to these values:
      | Accepted file types      | .java                          |
      | Max. number of uploaded files | 2                         |
      | Max. response upload size         | 2097152                            |

    And I press "id_submitbutton"
    Then I should see "proforma-java"

    When I choose "Duplicate" action for "proforma-java" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-java (copy)            |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | filepicker                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Accepted file types      | .java                          |
      | Max. number of uploaded files | 2                         |
      | Max. response upload size         | 2097152                            |
    And I should see "1" elements in "Model solution files" filemanager
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
    # JUnit 2
    And the field "testtitle[1]" matches value "Junit Test 2"
    And the field "testdescription[1]" matches value "Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class YTest {}"
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

    # save without changing any values
    And I press "id_submitbutton"
    Then I should see "proforma-java"
    And I should see "proforma-java (copy)"

    # open copied question and check values
    When I choose "Edit question" action for "proforma-java (copy)" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-java (copy)            |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | filepicker                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Accepted file types      | .java                          |
      | Max. number of uploaded files | 2                         |
    And the field "Max. response upload size" matches value "2097152"
    And I should see "1" elements in "Model solution files" filemanager

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
# todo: try and check values of static fields
    # download links
    #And I should see "instruction.txt, lib.txt"
    #And I should see "ms1.txt"
    #And I should see "ms2.txt"
#    And I should see "MyString.java"
    # grader settings
    # And I should see "task.xml"

# editing a duplicated question needs not to be tested here

##########################################################################
  Scenario: Duplicate a Java question without editing
##########################################################################
    When I am on the "Course 1" "core_question > course question bank" page
    And I choose "Duplicate" action for "proforma-java" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-java (copy)            |
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
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
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
    # JUnit 2
    And the field "testtitle[1]" matches value "Junit Test 2"
    And the field "testdescription[1]" matches value "Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class YTest {}"
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
# todo: try and check values of static fields
    # download links
    #And I should see "instruction.txt, lib.txt"
    #And I should see "ms1.txt"
    #And I should see "ms2.txt"
#    And I should see "MyString.java"
    # grader settings
    # And I should see "task.xml"

    # save without changing any values
    And I press "id_submitbutton"
    Then I should see "proforma-java"
    And I should see "proforma-java (copy)"

    # open copied question and check values
#    When I am on the "proforma-java" "core_question > edit" page
    When I choose "Edit question" action for "proforma-java (copy)" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-java (copy)            |
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
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
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
    # JUnit 2
    And the field "testtitle[1]" matches value "Junit Test 2"
    And the field "testdescription[1]" matches value "Description Junit 2"
    And the field "testtype[1]" matches value "unittest"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class YTest {}"
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
# todo: try and check values of static fields
    # download links
    #And I should see "instruction.txt, lib.txt"
    #And I should see "ms1.txt"
    #And I should see "ms2.txt"
#    And I should see "MyString.java"
    # grader settings
    # And I should see "task.xml"

# editing a duplicated question needs not to be tested here

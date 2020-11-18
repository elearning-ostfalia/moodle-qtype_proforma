@qtype @qtype_proforma
Feature: DUPLICATE SetlX
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
      | Test questions   | proforma | proforma-setlx | setlx2           |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  Scenario: Duplicate a Setlx question without editing
##########################################################################
    When I choose "Duplicate" action for "proforma-setlx" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-setlx (copy)            |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Syntax highlighting      | SetlX                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution                 |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
    # compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2"
    # Setlx 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Setlx Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 1"
    And the field "testtype[0]" matches value "setlx"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "some testcode"
    # Setlx 2
    And the field "testtitle[1]" matches value "Setlx Test 2"
    And the field "testdescription[1]" matches value "DESCRIPTION 2"
    And the field "testtype[1]" matches value "setlx"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "some other testcode"
# todo: try and check values of static fields
    # download links
    #And I should see "instruction.txt, lib.txt"
    #And I should see "ms1.txt"
    #And I should see "ms2.txt"
    # grader settings
    # And I should see "task.xml"

    # save without changing any values
    And I press "id_submitbutton"
    Then I should see "proforma-setlx"
    And I should see "proforma-setlx (copy)"

    # open copied question and check values
    When I choose "Edit question" action for "proforma-setlx (copy)" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-setlx (copy)            |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Syntax highlighting      | SetlX                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution                 |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
    # compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2"
    # Setlx 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Setlx Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 1"
    And the field "testtype[0]" matches value "setlx"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "some testcode"
    # Setlx 2
    And the field "testtitle[1]" matches value "Setlx Test 2"
    And the field "testdescription[1]" matches value "DESCRIPTION 2"
    And the field "testtype[1]" matches value "setlx"
    And the field "testweight[1]" matches value "6"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "some other testcode"
# todo: try and check values of static fields
    # download links
    #And I should see "instruction.txt, lib.txt"
    #And I should see "ms1.txt"
    #And I should see "ms2.txt"
#    And I should see "MyString.java"
    # grader settings
    # And I should see "task.xml"

# editing a duplicated question needs not to be tested here

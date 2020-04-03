@qtype @qtype_proforma
Feature: EDIT PROFORMA
  Test editing an ProFormA question
  As a teacher
  In order to be able to update my ProFormA question
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
      | Test questions   | proforma | proforma-001 | editor           |
      | Test questions   | proforma | proforma-003 | filepicker            |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: Edit a ProFormA question
    When I choose "Edit question" action for "proforma-001" in the question bank
    # assert(expected old values)
    Then the following fields match these values:
      | Question name            | proforma-001                  |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy     | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
      | UUID                     | UUID 1                     |
      | ProFormA Version         | 2.0                        |
    And the field "testweight[0]" matches value "2"
    And the field "testweight[1]" matches value "3"
    And the field with xpath "//input[@name='testtitle[0]']" matches value "TEST 1"
    And the field with xpath "//input[@name='testtitle[1]']" matches value "TEST 2"
    And the field with xpath "//input[@name='testdescription[0]']" matches value "DESCRIPTION 1"
    And the field with xpath "//input[@name='testdescription[1]']" matches value "DESCRIPTION 2"
    # check readony-fields
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
    And the field with xpath "//input[@name='testtype[0]']" matches value "TEST-CONFIG 1"
    And the field with xpath "//input[@name='testtype[1]']" matches value "TEST-CONFIG 2"
    # download links
    And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt"
    And I should see "ms2.txt"
    # And I should see "MyString.java"
    # grader settings
    #And I should see "UUID 1"
    And I should see "testtask.zip"
    #And I should see "2.0"

    # change all values that can be changed (keep editor set)
    When I set the following fields to these values:
      | Question name            | edited question name           |
      | Question text            | edited question text           |
      | Default mark             | 2                              |
      | General feedback         | edited general feedback        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Python                         |
      | Input box size           | 25 lines                       |
      | Response template        | edited start code              |
      | Comment                  | edited comment                 |
      | Aggregation strategy      | Weighted sum                 |
      | Penalty for each incorrect try  | 50%                     |
      | Response filename        | MyOtherString.java                     |
#      | UUID                     | UUID 2                     |
#      | ProFormA Version         | 3.0                        |
    And I set the field "testweight[0]" to "11"
    And I set the field "testweight[1]" to "22"
    And I set the field with xpath "//input[@name='testtitle[0]']" to "edited title #1"
    And I set the field with xpath "//input[@name='testtitle[1]']" to "edited title #2"
    And I set the field with xpath "//input[@name='testdescription[0]']" to "edited testdescription #1"
    And I set the field with xpath "//input[@name='testdescription[1]']" to "edited testdescription #2"
    And I press "id_submitbutton"
    Then I should see "edited question name"

    When I choose "Edit question" action for "edited question name" in the question bank
    Then the following fields match these values:
      | Question name            | edited question name           |
      | Question text            | edited question text           |
      | Default mark             | 2                              |
      | General feedback         | edited general feedback        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Python                         |
      | Input box size           | 25 lines                       |
      | Response template        | edited start code              |
      | Comment                  | edited comment                 |
      | Aggregation strategy      | Weighted sum                 |
      | Penalty for each incorrect try  | 50%                     |
      | Response filename        | MyOtherString.java                     |
      | UUID                     | UUID 1                     |
      | ProFormA Version         | 2.0                        |
    And the field "testweight[0]" matches value "11"
    And the field "testweight[1]" matches value "22"
    And the field with xpath "//input[@name='testtitle[0]']" matches value "edited title #1"
    And the field with xpath "//input[@name='testtitle[1]']" matches value "edited title #2"
    And the field with xpath "//input[@name='testdescription[0]']" matches value "edited testdescription #1"
    And the field with xpath "//input[@name='testdescription[1]']" matches value "edited testdescription #2"

    # check readony-fields
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
    And the field with xpath "//input[@name='testtype[0]']" matches value "TEST-CONFIG 1"
    And the field with xpath "//input[@name='testtype[1]']" matches value "TEST-CONFIG 2"
    # download links
    And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt"
    And I should see "ms2.txt"
    # And I should see "MyString.java"
    # grader settings
    #And I should see "UUID 1"
    And I should see "testtask.zip"
    #And I should see "2.0"

    And I press "Cancel"
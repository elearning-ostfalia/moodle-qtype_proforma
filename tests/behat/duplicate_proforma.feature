@qtype @qtype_proforma
Feature: DUPLICATE PROFORMA
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
      | Test questions   | proforma | proforma-001 | editor           |
      | Test questions   | proforma | proforma-003 | filepicker            |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: Edit a ProFormA question
    When I choose "Duplicate" action for "proforma-001" in the question bank
    #When I click on "Edit" "link" in the "proforma-001" "table_row"
    Then the following fields match these values:
      | Question name            | proforma-001 (copy)            |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
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
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
    And the field with xpath "//input[@name='testtype[0]']" matches value "TEST-CONFIG 1"
    And the field with xpath "//input[@name='testtype[1]']" matches value "TEST-CONFIG 2"
# todo: try and check values of static fields
    # download links
    And I should see "2" elements in "Downloadable files" filemanager
    # And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt, ms2.txt"
#    And I should see "MyString.java"
    # grader settings
#    And I should see "UUID 1"
    And I should see "testtask.zip"
#    And I should see "2.0"

    # save without changing any values
    And I press "id_submitbutton"
    Then I should see "proforma-001"
    And I should see "proforma-001 (copy)"

    # open copied question and check values
    When I choose "Edit question" action for "proforma-001 (copy)" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-001 (copy)            |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
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
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
    And the field with xpath "//input[@name='testtype[0]']" matches value "TEST-CONFIG 1"
    And the field with xpath "//input[@name='testtype[1]']" matches value "TEST-CONFIG 2"
# todo: try and check values of static fields
    # download links
    And I should see "2" elements in "Downloadable files" filemanager
    # And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt, ms2.txt"
#    And I should see "MyString.java"
    # grader settings
#    And I should see "UUID 1"
    And I should see "testtask.zip"
#    And I should see "2.0"

    And I set the following fields to these values:
      | Question name | Duplicated question name                |
      | Question text | Write a lot about duplicating questions |

    And I press "id_submitbutton"
    Then I should see "Duplicated question name"

    When I choose "Edit question" action for "Duplicated question name" in the question bank
    Then the following fields match these values:
      | Question name            | Duplicated question name                  |
      | Question text            | Write a lot about duplicating questions           |
    And the field "testweight[0]" matches value "2"
    And the field "testweight[1]" matches value "3"
    And the field with xpath "//input[@name='testtitle[0]']" matches value "TEST 1"
    And the field with xpath "//input[@name='testtitle[1]']" matches value "TEST 2"
    And the field with xpath "//input[@name='testdescription[0]']" matches value "DESCRIPTION 1"
    And the field with xpath "//input[@name='testdescription[1]']" matches value "DESCRIPTION 2"
    And the field with xpath "//input[@name='testid[0]']" matches value "1"
    And the field with xpath "//input[@name='testid[1]']" matches value "2"
    And the field with xpath "//input[@name='testtype[0]']" matches value "TEST-CONFIG 1"
    And the field with xpath "//input[@name='testtype[1]']" matches value "TEST-CONFIG 2"
# todo: try and check values of static fields
    # download links
    And I should see "2" elements in "Downloadable files" filemanager
    # And I should see "instruction.txt, lib.txt"
    And I should see "ms1.txt, ms2.txt"
#    And I should see "MyString.java"
    # grader settings
#    And I should see "UUID 1"
    And I should see "testtask.zip"
#    And I should see "2.0"

    And I press "Cancel"

    # check for download link in "proforma-003"
    When I choose "Preview" action for "Duplicated question name" in the question bank
    And I switch to "questionpreview" window
    Then I should see "lib.txt"
    Then I should see "instruction.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    And following "lib.txt" should download file with between "9" and "12" bytes
    And I switch to the main window
@qtype @qtype_proforma
Feature: BACKUP AND RESTORE
  Test duplicating a quiz containing a ProFormA question
  As a teacher
  In order re-use my courses containing ProFormA questions
  I need to be able to backup and restore them

  Background:
    # the correct values of all templates should be tested in other testcases
    # such as edit_stlx.feature

    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype        | name         | template         |
      | Test questions   | proforma     | proforma-001 | editor           |
      | Test questions   | proforma     | proforma-002 | java1            |
      | Test questions   | proforma     | proforma-003 | filepicker       |
      | Test questions   | proforma     | proforma-setlx | setlx2       |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | proforma-001 | 1 |
      | proforma-002 | 1 |
      | proforma-003 | 1 |
      | proforma-setlx | 1 |
    And I log in as "admin"
    And I am on "Course 1" course homepage

  @javascript
  Scenario: Backup and restore a course containing 2 ProFormA questions
    When I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name | Course 2 |
    And I navigate to "Question bank" in current page administration
    And I should see "proforma-001"
    And I should see "proforma-002"
    And I should see "proforma-003"
    And I should see "proforma-setlx"

    # Check Setlx
    When I choose "Edit question" action for "proforma-setlx" in the question bank
    # assert(expected old values)
    Then the following fields match these values:
      | Question name            | proforma-setlx           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 3                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Syntax highlighting      | SetlX                           |
      | Input box size           | 10 lines                       |
      | Response template        | //text in responsetemplate     |
      | Model solution           | // code for model solution                 |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
    # Syntax Check
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2"
    # Setlx Test 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Setlx Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 1"
    And the field "testtype[0]" matches value "setlx"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "some testcode"
    # Setlx Test 2
    And the field "testid[1]" matches value "2"
    And the field "testtitle[1]" matches value "Setlx Test 2"
    And the field "testdescription[1]" matches value "DESCRIPTION 2"
    And the field "testtype[1]" matches value "setlx"
    And the field "testweight[1]" matches value "6"
    And the field "testcode[1]" matches value "some other testcode"

    # Finish
    And I press "Cancel"

    When I choose "Edit question" action for "proforma-002" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-002           |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | Editor                         |
      | Syntax highlighting      | Java                           |
      | Input box size           | 10 lines                       |

      | Syntax highlighting      | Java                         |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                 |
      | Penalty for each incorrect try  | 20%                     |
      | Model solution           | // code for model solution     |
      | Response filename        | MyString.java                  |
#      | Response template        | multiline              |
    And the field with name "testtitle[0]" matches value "Junit Test 1"
    And the field with name "testdescription[0]" matches value "DESCRIPTION 2"
    And the field with name "testtype[0]" matches value "unittest"
    And the field with name "testid[0]" matches value "1"
  # cannot be tested that way
#    And I set the field with xpath "//textarea[@name='testcode[0]']" to "class XClass {}"
    And the field with name "compileweight" matches value "2"
    And the field with name "testweight[0]" matches value "3"
#    And the field with name "testweight[1]" matches value "4"

    And I press "Cancel"

    When I choose "Edit question" action for "proforma-001" in the question bank
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
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
      | Response filename        | MyString.java                     |
      | UUID                     | UUID 1                     |
      | ProFormA Version         | 2.0                        |
    And the field with name "testweight[0]" matches value "2"
    And the field with name "testweight[1]" matches value "3"
    And the field with name "testtitle[0]" matches value "TEST 1"
    And the field with name "testtitle[1]" matches value "TEST 2"
    And the field with name "testdescription[0]" matches value "DESCRIPTION 1"
    And the field with name "testdescription[1]" matches value "DESCRIPTION 2"
    And the field with name "testid[0]" matches value "1"
    And the field with name "testid[1]" matches value "2"
    And the field with name "testtype[0]" matches value "TEST-CONFIG 1"
    And the field with name "testtype[1]" matches value "TEST-CONFIG 2"
    # download links
    # And I should see "instruction.txt, lib.txt"
    And I should see "2" elements in "Downloadable files" filemanager
    And I should see "ms1.txt"
    And I should see "ms2.txt"
#    And I should see "MyString.java"
    # grader settings
#    And I should see "UUID 1"
    And I should see "testtask.zip"
#    And I should see "2.0"

    And I press "Cancel"

    When I choose "Edit question" action for "proforma-003" in the question bank
    Then the following fields match these values:
      | Question name            | proforma-003          |
      | Question text            | Please code the reverse string function not using a library function.(äöüß)           |
      | Default mark             | 1                              |
      | General feedback         | <p>You must not use a library function.</p>        |
      | Response format          | File picker                         |
      | Max. number of uploaded files          | 3                         |
      | Accepted file types          | .java, .jar                         |
      | Syntax highlighting      | Python                         |

      | Response template        | //text in responsetemplate     |
      | Comment                  | <p>Check if the code uses a library function.</p>                 |
      | Aggregation strategy      | All or nothing                |
      | Penalty for each incorrect try  | 20%                     |
#      | Response template        | multiline              |
      | UUID                     | UUID 2                     |
      | ProFormA Version         | 2.0                        |
    And the field "Max. upload size" matches value "10240"
    And the field "testweight[0]" matches value "2"
    And the field "testweight[1]" matches value "3"
    And the field with name "testtitle[0]" matches value "TEST 1"
    And the field with name "testtitle[1]" matches value "TEST 2"
    And the field with name "testdescription[0]" matches value "DESCRIPTION 1"
    And the field with name "testdescription[1]" matches value "DESCRIPTION 2"
    And the field with name "testid[0]" matches value "1"
    And the field with name "testid[1]" matches value "2"
    And the field with name "testtype[0]" matches value "TEST-CONFIG 1"
    And the field with name "testtype[1]" matches value "TEST-CONFIG 2"
# todo: try and check values of static fields
    # download links
    # And I should see "instruction.txt, lib.txt"
    And I should see "2" elements in "Downloadable files" filemanager
    And I should see "ms1.txt"
    And I should see "ms2.txt"
    # grader settings
#    And I should see "UUID 2"
    And I should see "testtask.zip"
#    And I should see "2.0"
    And I press "Cancel"

    # check for download link in "proforma-003"
    When I am on the "proforma-001" "core_question > preview" page
    # When I choose "Preview" action for "proforma-001" in the question bank
    # And I switch to "questionpreview" window
    Then I should see "lib.txt"
    Then I should see "instruction.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    And following "lib.txt" should download file with between "9" and "12" bytes
    And I switch to the main window

    # check for download link in "proforma-003"
    When I am on the "proforma-003" "core_question > preview" page
#    When I choose "Preview" action for "proforma-003" in the question bank
#    And I switch to "questionpreview" window
    Then I should see "lib.txt"
    Then I should see "instruction.txt"
    And following "instruction.txt" should download file with between "17" and "20" bytes
    And following "lib.txt" should download file with between "9" and "12" bytes
    And I switch to the main window

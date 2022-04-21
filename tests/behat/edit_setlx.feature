@qtype @qtype_proforma
Feature: EDIT SETLX
  Test editing a SetlX question
  As a teacher
  In order to be able to update my SetlX question
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
      | Test questions   | proforma | proforma-setlx | setlx2           |
    #And I log in as "teacher1"
    #And I am on "Course 1" course homepage
    #And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript
  Scenario: Check precondition for all successive scenarios
##########################################################################
    # When I choose "Edit question" action for "proforma-setlx" in the question bank
    When I am on the "proforma-setlx" "core_question > edit" page logged in as teacher1
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
    Then I should see "proforma-setlx"

    # check for download link
    When I am on the "proforma-setlx" "core_question > preview" page
    # When I choose "Preview" action for "proforma-setlx" in the question bank
    # And I switch to "questionpreview" window
    #Then I should see "lib.txt"
    #Then I should see "instruction.txt"
    Then I should see "template.txt"
    Then I should see "//text in responsetemplate"
    #And following "instruction.txt" should download file with between "17" and "20" bytes
    #And following "lib.txt" should download file with between "9" and "12" bytes
    And following "template.txt" should download file with between "26" and "28" bytes
    And I switch to the main window

##########################################################################
  @javascript
  Scenario: Edit a ProFormA question (uncheck/check Syntax Check)
##########################################################################
    When I am on the "proforma-setlx" "core_question > edit" page logged in as teacher1
    # When I choose "Edit question" action for "proforma-setlx" in the question bank
    # uncheck compile
    And I uncheck the "compile" checkbox
    And the "compile" checkbox is "unchecked"

    And I press "id_submitbutton"
    Then I should see "proforma-setlx"

    When I choose "Edit question" action for "proforma-setlx" in the question bank
    # check for unchecked checkboxes
    And the "compile" checkbox is "unchecked"
    # recheck
    And I check the "compile" checkbox
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "0"
    And I press "id_submitbutton"
    Then I should see "proforma-setlx"

    When I choose "Edit question" action for "proforma-setlx" in the question bank
    # check for checked checkboxes
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "0"

    And I press "Cancel"

##########################################################################
  @javascript @_file_upload
  Scenario: Edit a ProFormA question (simply edit all values)
##########################################################################
    When I am on the "proforma-setlx" "core_question > edit" page logged in as teacher1
    # When I choose "Edit question" action for "proforma-setlx" in the question bank
    # change all values that can be changed (keep editor set)
    And  I set the following fields to these values:
      | Question name            | updated proforma-setlx|
      | Question text            | new question text           |
      | Default mark             | 4                              |
      | General feedback         | do not use a library functions|
      | Syntax highlighting      | Python                           |
      | Input box size           | 20 lines                       |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |

    And I set the codemirror "responsetemplate" to "new code snippet that can be used as a starting point for the student"
    And I set the codemirror "modelsolution" to "// new code for model solution"

    # compile
    #And I set the field "compile" to "0"
    And I set the field "compileweight" to "2.5"
    # SetlX 1
    And I set the field "testtitle[0]" to "new Setlx Test 1"
    And I set the field "testdescription[0]" to "new Description Setlx 1"
    And I set the field "testweight[0]" to "3.5"
#    And I set the field "testcode[0]" to "class NewXTest {}"
    And I set the codemirror "testcode_0" to "class NewXTest {}"

    # SetlX 2
    And I set the field "testtitle[1]" to "new Setlx Test 2"
    And I set the field "testdescription[1]" to "new Description Setlx 2"
    And I set the field "testweight[1]" to "6.5"
#    And I set the field "testcode[1]" to "class NewYTest {}"
    And I set the codemirror "testcode_1" to "class NewYTest {}"

    And I press "id_submitbutton"
    Then I should see "updated proforma-setlx"

    When I choose "Edit question" action for "updated proforma-setlx" in the question bank
    Then the following fields match these values:
      | Question name            | updated proforma-setlx|
      | Question text            | new question text           |
      | Default mark             | 4                              |
      | General feedback         | do not use a library functions|
      | Syntax highlighting      | Python                           |
      | Input box size           | 20 lines                       |
      | Response template        | new code snippet that can be used as a starting point for the student     |
      | Model solution           | // new code for model solution                 |
      | Comment                  | new comment                  |
      | Aggregation strategy      | Weighted sum                |
      | Penalty for each incorrect try  | 10%                     |
  # compile
    And the "compile" checkbox is "checked"
    And the field "compileweight" matches value "2.5"
    # SetlX 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "new Setlx Test 1"
    And the field "testdescription[0]" matches value "new Description Setlx 1"
    And the field "testtype[0]" matches value "setlx"
    And the field "testweight[0]" matches value "3.5"
    And the field "testcode[0]" matches value "class NewXTest {}"
    # SetlX 2
    And the field "testtitle[1]" matches value "new Setlx Test 2"
    And the field "testdescription[1]" matches value "new Description Setlx 2"
    And the field "testtype[1]" matches value "setlx"
    And the field "testweight[1]" matches value "6.5"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class NewYTest {}"

    # Finish
    And I press "id_submitbutton"
    Then I should see "updated proforma-setlx"

    # check for download link
    When I am on the "updated proforma-setlx" "core_question > preview" page
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
    And I switch to the main window

  @javascript
    ##########################################################################
  Scenario: Edit a ProFormA question (remove and add Setlx)
##########################################################################
    When I am on the "proforma-setlx" "core_question > edit" page logged in as teacher1
    # When I choose "Edit question" action for "proforma-setlx" in the question bank

    # remove SetlX 2 data by deleting content
    And I set the field "testtitle[1]" to ""
    And I set the field "testdescription[1]" to ""
    And I set the codemirror "testcode_1" to ""
    And I press "id_submitbutton"
    Then I should see "proforma-setlx"

    When I choose "Edit question" action for "proforma-setlx" in the question bank

    # SetlX 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Setlx Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 1"
    And the field "testtype[0]" matches value "setlx"
    And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "some testcode"
    # SetlX 2 is not visible
    And I should not see "SetlX test 2"

    And I press "id_submitbutton"
    Then I should see "proforma-setlx"

    When I choose "Edit question" action for "proforma-setlx" in the question bank
    # add Setlx 2
    And I press "Add SetlX test"
    Then I should not see "SetlX test 3"
    And I should see "SetlX test 2"
    # add SetlX 2
    And I set the field "testtitle[1]" to "new Setlx Test 2"
    And I set the field "testdescription[1]" to "new Description Setlx 2"
    # And I set the field "testweight[1]" to "6.5"
    And I set the codemirror "testcode_1" to "class NewYTest {}"

    And I press "id_submitbutton"
    Then I should see "proforma-setlx"

    When I choose "Edit question" action for "proforma-setlx" in the question bank
    # check all values
    Then the following fields match these values:
      | Question name            | proforma-setlx|
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
    And the "compile" checkbox is "checked"
    # And the field "compileweight" matches value "2"
    # SetlX 1
    And the field "testid[0]" matches value "1"
    And the field "testtitle[0]" matches value "Setlx Test 1"
    And the field "testdescription[0]" matches value "DESCRIPTION 1"
    And the field "testtype[0]" matches value "setlx"
    # And the field "testweight[0]" matches value "3"
    And the field "testcode[0]" matches value "some testcode"
    # SetlX 2
    And the field "testtitle[1]" matches value "new Setlx Test 2"
    And the field "testdescription[1]" matches value "new Description Setlx 2"
    And the field "testtype[1]" matches value "setlx"
    # And the field "testweight[1]" matches value "6.5"
    And the field "testid[1]" matches value "2"
    And the field "testcode[1]" matches value "class NewYTest {}"

    And I press "Cancel"

@qtype @qtype_proforma
Feature: ADD C++ QUESTION
  Test creating a ProFormA C++ question
  As a teacher
  In order to test my students
  I need to be able to create a simple C++ question

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | clang | 1  | qtype_proforma |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript  @_file_upload
  Scenario: Create, save and open a ProFormA C++ question with two C++ tests
##########################################################################
    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_c++/c" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | C++ Question with 2 tests    |
      | Question text            | write a C++ program that..... |
      | Aggregation strategy     | Weighted sum                |
      | Comment                  | a comment                   |
      | Response format          | filepicker                     |
      | Accepted file types      | .cpp                          |
      | Max. number of uploaded files | 1                         |
      | Command for executing test | ./test1                      |

    # The default functions do not work for CodeMirror with Javascript.
    # So we must use other functions.
    # Set upload size to 2MB
    And I set the field "Max. response upload size" to "2097152"
    And I set the field "testtitle[0]" to "C++ #1"
    And I set the codemirror "responsetemplate" to "// type your code here"
    # And I set the codemirror "modelsolution" to "// code for model solution"
    # And I set the codemirror "responsetemplate" to "// type your code here"

    # Then I should see "Testcode required"
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    And I upload "question/type/proforma/tests/fixtures/behat/c/main.c" to "testfiles[0]" filemanager by name
    # close and reopen in order to upload second file (seems to be a bug in test environment)
    And I press "id_submitbutton"
    When I choose "Edit question" action for "C++ Question with 2 tests" in the question bank

    # add new c Test
    When I press "id_option_add_fields"
    And I upload "question/type/proforma/tests/fixtures/behat/c/CMakeLists.txt" to "testfiles[1]" filemanager by name
    And I set the field "testdescription[1]" to "this is the second C++ test"

    And I press "id_submitbutton"
    Then I should see "Title required"

    When I set the field "testtitle[1]" to "C #2"
    And I set the field "testentrypoint[1]" to "./test1"
    And I set the field "testweight[1]" to "3"
    And I press "id_submitbutton"
    Then I should see "C++ Question with 2 tests"

    # CHECK VALUES
    When I choose "Edit question" action for "C++ Question with 2 tests" in the question bank
    Then the following fields match these values:
      | Question name            | C++ Question with 2 tests     |
      | Question text            | write a C++ program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Input box size           | 15 lines                       |
#      | Response template        | // type your code here         |
#      | Model solution           | // code for model solution     |
      | Comment                  |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy     | Weighted sum                   |
      | Comment                  | a comment                      |
      | Accepted file types      | .cpp                           |
      | Max. number of uploaded files | 1                         |
      | Response format          | filepicker                         |

    # Filpicker option => no response template, model solution is empry (file)
    # And the codemirror "responsetemplate" matches value "// type your code here"
    # And the codemirror "modelsolution" matches value "// code for model solution"
    # C++ #1
    # Check for 2MB (until 3.11 and 2&nbsp;MB starting from 4.0)
    # And the field "Max. upload size" matches regexp "/2(\&nbsp\;)?MB/i"
    And the field "Max. response upload size" matches value "2097152"
    And the field "testweight[0]" matches value "1"
    And the field "testdescription[0]" matches value ""
    And "main.c" "link" should exist
    # size is given in 1.1kB
    And the size of file "main.c" is between "1000" and "1200" bytes

    # C++ #2
    And the field "testweight[1]" matches value "3"
    And the field "testdescription[1]" matches value "this is the second C++ test"
    And "CMakeLists.txt" "link" should exist
    And the size of file "CMakeLists.txt" is between "150" and "160" bytes

    And I press "Cancel"

##########################################################################
  @javascript @_file_upload
  Scenario: Create, save and open a ProFormA C++ question with one C++ test
  # Check default values
##########################################################################
    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_c++/c" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | cpp-question                  |
      | Question text            | write a C++ program that..... |
      | Title                    | C++ test  |
    And I press "id_submitbutton"
    Then I should see "Testcode required"
    # C++ test
    # we use a c file as it does not matter
    And I upload "question/type/proforma/tests/fixtures/behat/c/main.c" to "testfiles[0]" filemanager by name
    And I press "id_submitbutton"
    Then I should see "Run command required"
    And I set the field "Command for executing test" to "./test"

    And I press "id_submitbutton"
    # Response filename missing
    Then I should see "Required"
    And I set the field "Response filename" to "response.cpp"
    And I press "id_submitbutton"
    Then I should see "cpp-question"


    # Check default values:
    When I choose "Edit question" action for "cpp-question" in the question bank
    Then the following fields match these values:
      | Question name            | cpp-question             |
      | Question text            | write a C++ program that..... |
      | Default mark             | 1                              |
      | General feedback         |                                |
      | Input box size           | 15 lines                       |
      | Response template        |                                |
      | Response filename        | response.cpp                   |
      | Comment                  |                                |
      | Title                    | C++ test               |
      | Description              |                                |
      | Penalty for each incorrect try  | 10%                     |
      | Aggregation strategy      | Weighted sum                |
      | Command for executing test | ./test                |

    # compile
    # c test
    And the field "testweight[0]" matches value "1"
    And "main.c" "link" should exist
    # size is given in 1.1kB
    And the size of file "main.c" is between "1000" and "1200" bytes
    # Cancel (form is not modified)
    And I press "Cancel"

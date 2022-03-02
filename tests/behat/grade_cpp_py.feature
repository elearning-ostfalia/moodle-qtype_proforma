@qtype @qtype_proforma @javascript @grade_proforma
Feature: GRADE C/C++/Python
  Grade question with actual grader
  As a teacher
  In order to check if my questions will work for students
  I need to preview and grade them

  # Requires valid Praktomat connection on http://praktomat:8010
  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following config values are set as admin:
      | graderuri_host | http://praktomat:8010  | qtype_proforma |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |

    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
#    And the following "question categories" exist:
#      | contextlevel | reference | name           |
#      | Course       | C1        | Test questions |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

##########################################################################
  @javascript @_switch_window @_file_upload
  Scenario: Python question grading
##########################################################################

    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_python" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | Python Question with 2 tests    |
      | Question text            | write a Python program that..... |
      | Aggregation strategy     | Weighted sum                |
      | Comment                  | a comment                   |
      | Response format          | editor                     |
      | Response filename        | palindrome.py                   |

    And I set the field "testtitle[0]" to "Python #1"
    And I set the field "id_testcodeformat_0_2" to "1"
    And I upload "question/type/proforma/tests/fixtures/behat/python/test.py" to "testfiles[0]" filemanager by name

    # add new Test
    When I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "Python #2"
    And I set the field "testdescription[1]" to "this is the second Python test"
    And I set the field "testweight[1]" to "3"
    And I set the codemirror "testcode_1" to multiline:
"""
# coding=utf-8

import unittest
from palindrome import is_palindrome

class PalindromeTest(unittest.TestCase):

    def test_long(self):
        self.assertEqual(True, is_palindrome('Roma tibi subito motibus ibit amor'), 'Roma tibi subito motibus ibit amor')

    def test_short(self):
        self.assertEqual(True, is_palindrome('otto'), 'otto')
        self.assertEqual(True, is_palindrome('rentner'), 'rentner')
        self.assertEqual(True, is_palindrome('a'), 'a')

    def test_empty(self):
        self.assertEqual(True, is_palindrome(''), '<empty>')
"""

    And I press "id_submitbutton"
    Then I should see "Python Question with 2 tests"
    When I choose "Preview" action for "Python Question with 2 tests" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
"""
# coding=utf-8

import sys

def is_palindrome(text):
    print('is_palindrome ' + text)
    sys.stderr.write('to stderr')
    text = text.lower()
    text = text.replace(' ', '')
    reversestring = text[::-1]
    return reversestring == text
"""

    And I press "Check"
    Then I should see "Python #1 (25/25 %)"
    And I should see "Python #2 (75/75 %)"
    And I should see "Log"
    And I should see "Correct"
    And I should see "Marks for this submission: 1.00/1.00."

##########################################################################
  @javascript @_switch_window @_file_upload
  Scenario: C++ question grading
##########################################################################
    When I press "Create a new question ..."
    And I set the field "item_qtype_proforma" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I set the field "item_c++/c" to "1"
    And I click on "Ok" "button" in the "Select programming language" "dialogue"
    Then I should see "Adding a ProFormA question"

    When I set the following fields to these values:
      | Question name            | C++ question                  |
      | Question text            | question text  |
      | Response format          | editor                         |
      | Response filename        | squareroot.cpp                   |
      | Penalty for each incorrect try  | 20%     |
    # GoogleTest 1
    And I set the field "testtitle[0]" to "GoogleTest 1"
    And I set the field "testweight[0]" to "10"
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/CMakeLists.txt" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/tests.cpp" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/squareroot.h" to "testfiles[0]" filemanager by name
    And I set the field "testentrypoint[0]" to "./demo"

    # GoogleTest 2
    # add another test
    And I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "GoogleTest 2"
    And I set the field "testweight[1]" to "10"
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/CMakeLists.txt" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/tests2.cpp" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/squareroot.h" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "./demo"

    And I press "id_submitbutton"
    Then I should see "C++ question"
    When I choose "Preview" action for "C++ question" in the question bank
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Adaptive mode (no penalties)"
    And I press "Start again with these options"
    And I set the response to
    """
    #include <math.h>

    double squareroot(const double a) {
        return sqrt(a);
    }
    """

    And I press "Check"
    Then I should see "GoogleTest 1 (33/50 %)"
    And I should see "GoogleTest 2 (50/50 %)"
    And I should see "Log"
    And I should see "Partially correct"
    And I should see "Marks for this submission: 0.83/1.00."



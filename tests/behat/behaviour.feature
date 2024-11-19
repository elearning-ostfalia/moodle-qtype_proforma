@qtype @qtype_proforma
Feature: PROFORMA BEHAVIOUR
  Test creating a ProFormA question
  As a teacher
  In order to test my students
  I need to be able to use the ProformA question in deferred behaviour

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher@moodle.org |
      | student1 | Student | 1 | student@moodle.org |

    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
      And the following "questions" exist:
      | questioncategory | qtype        | name         | template         |
      | Test questions   | proforma     | proforma-001 | editor           |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber | preferredbehaviour | canredoquestions |
      | quiz     | Quiz 1 | Quiz 1 description | C1     | quiz1    | deferredfeedback   | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | graderuri_host | http://praktomat:8010  | qtype_proforma |

    And the following "groups" exist:
      | name     | course | idnumber | participation |
      | Group001 | C1     | G1       | 1             |
    And the following "group members" exist:
      | user       | group |
      | teacher1   | G1    |
      | student1   | G1    |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma behaviour states with VCS (fake URL and with actual grading)
##########################################################################
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/palindrome_in_python.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Write a python function named"
    And I press "Continue"
    And I should see "Palindrome in Python"

    Given quiz "Quiz 1" contains the following questions:
      | question             | page |
      | Palindrome in Python | 1    |

    When I choose "Edit question" action for "Palindrome in Python" in the question bank
    And I set the following fields to these values:
      | Response format          | Version control system         |
      | URI of repository        | https://svn.test.org/svn/{group}/task1/  |
    And I press "Save changes"

    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"


    # 1. Start test => not yet answered / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

    When I press "Finish attempt"
    And I should see "<answersaved>" in the "1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "In progress" in the "Student 1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Continue your attempt"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"

    And I should see "Not yet graded" in the "Finished" "table_row"

    When I follow "Review"
    And I should see "Not yet graded"
    And I should see "INTERNAL ERROR"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Not yet graded" in the "Student 1" "table_row"
    Then I should see "Requires grading" in the "Student 1" "table_row"
    And I follow "Review attempt"
    And I should see "Not yet graded"
    And I should see "INTERNAL ERROR"

    Examples:
      | behaviour         | noanswer         | answersaved  |
      | immediatefeedback | Not complete     | Not complete |
      | deferredfeedback  | Not yet answered | Answer saved |
#      | deferredcbm       | Not yet answered | Answer saved |
#      | immediatecbm      | Not complete     | Not complete |
      | interactive       | Not complete     | Not complete |
      | adaptive          | Not complete     | Not complete |
      | adaptivenopenalty | Not complete     | Not complete |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma behaviour states with no input (with actual grading)
##########################################################################
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/palindrome_in_python.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Write a python function named"
    And I press "Continue"
    And I should see "Palindrome in Python"

    Given quiz "Quiz 1" contains the following questions:
      | question             | page |
      | Palindrome in Python | 1    |

    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"

    # 1. Start test => not yet answered / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

    When I press "Finish attempt"
    # There is a template given for the question so that the template text is stored as a result
    And I should see "<answersaved>" in the "1" "table_row"
    #And I should see "Not yet answered" in the "1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "In progress" in the "Student 1" "table_row"


    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Continue your attempt"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"

    And I should see "0.00" in the "Finished" "table_row"

    When I follow "Review"
    And I should see "Incorrect"
    And I should see "0/100 %"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Finished" in the "Student 1" "table_row"
    # The input is handled as if there is no input (because there is only the template).
    # Therefore there is no grading result.
    Then I should not see "Not yet graded" in the "Student 1" "table_row"
    Then I should not see "Requires grading" in the "Student 1" "table_row"
    And I should see "0.00" in the "Student 1" "table_row"
    And I follow "Review attempt"
    And I should see "Incorrect"
    And I should see "0/100 %"

    Examples:
      | behaviour         | noanswer         | answersaved  |
      | deferredfeedback  | Not yet answered | Answer saved |
#      | deferredcbm       | Not yet answered | Answer saved |
      | immediatefeedback | Not complete     | Not complete |
#      | immediatecbm      | Not complete     | Not complete |
      | interactive       | Not complete     | Not complete |
      | adaptive          | Not complete     | Not complete |
      | adaptivenopenalty | Not complete     | Not complete |


##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma behaviour states with correct input (no grader)
##########################################################################
    And the following config values are set as admin:
      | graderuri_host |   | qtype_proforma |

    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/palindrome_in_python.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Write a python function named"
    And I press "Continue"
    And I should see "Palindrome in Python"

    Given quiz "Quiz 1" contains the following questions:
      | question             | page |
      | Palindrome in Python | 1    |

    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"

    # 1. Start test => not yet answered / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

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

    When I press "Finish attempt"
    # There is a template given for the question so that the template text is stored as a result
    And I should see "<answersaved>" in the "1" "table_row"
    #And I should see "Not yet answered" in the "1" "table_row"

    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"

    Then I should see "Not yet graded" in the "Finished" "table_row"

    When I follow "Review"
    And I should see "Not yet graded"
    And I should see "Model solution"
    And I should see "INTERNAL ERROR"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Finished" in the "Student 1" "table_row"
    # The input is handled as if there is no input (because there is only the template).
    # Therefore there is no grading result.
    Then I should see "Not yet graded" in the "Student 1" "table_row"
    Then I should see "Requires grading" in the "Student 1" "table_row"

    And I follow "Review attempt"
    And I should see "Not yet graded"
    And I should see "Model solution"
    And I should see "INTERNAL ERROR"

    Examples:
      | behaviour         | noanswer         | answersaved  |
      | deferredfeedback  | Not yet answered | Answer saved |
#      | deferredcbm       | Not yet answered | Answer saved |
      | immediatefeedback | Not complete     | Not complete |
#      | immediatecbm      | Not complete     | Not complete |
      | interactive       | Not complete     | Not complete |
      | adaptive          | Not complete     | Not complete |
      | adaptivenopenalty | Not complete     | Not complete |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma behaviour states with correct input (with actual grading)
##########################################################################
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/palindrome_in_python.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Write a python function named"
    And I press "Continue"
    And I should see "Palindrome in Python"

    Given quiz "Quiz 1" contains the following questions:
      | question             | page |
      | Palindrome in Python | 1    |

    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"

    # Start test and enter correct answer => Answer saved / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

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

    When I press "Finish attempt"
    And I should see "<answersaved>" in the "1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "In progress" in the "Student 1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Continue your attempt"
    And I press "Finish attempt"
    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"
    And I should see "1.00" in the "Finished" "table_row"
    And I should see "100.00" in the "Finished" "table_row"
    When I follow "Review"
    And I should see "Correct"
    And I should see "100/100 %"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Finished" in the "Student 1" "table_row"
    And I should not see "Not yet graded" in the "Student 1" "table_row"
    And I should not see "Requires grading" in the "Student 1" "table_row"
    And I should see "100.00" in the "Student 1" "table_row"
    And I follow "Review attempt"
    And I should see "Correct"
    And I should see "100/100 %"

    Examples:
      | behaviour         | answersaved  |
      | deferredfeedback  | Answer saved |
#      | deferredcbm       | Answer saved |
      | immediatefeedback | Not complete |
#      | immediatecbm      | Not complete |
      | interactive       | Not complete |
      | adaptive          | Not complete |
      | adaptivenopenalty | Not complete |

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma behaviour states with incorrect input (with actual grading)
##########################################################################
    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
    And I set the field "id_format_xml" to "1"
    And I upload "question/type/proforma/tests/fixtures/palindrome_in_python.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "Write a python function named"
    And I press "Continue"
    And I should see "Palindrome in Python"

    Given quiz "Quiz 1" contains the following questions:
      | question             | page |
      | Palindrome in Python | 1    |

    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"

    # Start test and enter correct answer => Answer saved / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

    And I set the response to
"""
# coding=utf-8

import sys

def is_palindrome(text):
    return 0
"""

    When I press "Finish attempt"
    And I should see "<answersaved>" in the "1" "table_row"

    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"
    And I should see "0.25" in the "Finished" "table_row"
    And I should see "25.00" in the "Finished" "table_row"

    When I follow "Review"
    And I should see "Partially correct"
    And I should see "25/100 %"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Finished" in the "Student 1" "table_row"
    And I should not see "Not yet graded" in the "Student 1" "table_row"
    And I should not see "Requires grading" in the "Student 1" "table_row"
    And I should see "25.00" in the "Student 1" "table_row"
    And I follow "Review attempt"
    And I should see "Partially correct"
    And I should see "25/100 %"

    Examples:
      | behaviour          | answersaved  |
      | deferredfeedback   | Answer saved |
#      | deferredcbm        | Answer saved |
      | immediatefeedback  | Not complete |
#      | immediatecbm       | Not complete |
      | interactive        | Not complete |
      | adaptive           | Not complete |
      | adaptivenopenalty  | Not complete |


##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario Outline: Proforma and numerical question types without grader
##########################################################################

    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher1
    And I maximize window
    And I add a "Numerical" question filling the form with:
      | Question name                      | Numerical-002                               |
      | Question text                      | How many meter is 1m + 20cm + 50mm?         |
      | Default mark                       | 1                                           |
      | General feedback                   | The correct answer is 1.25m                 |
      | id_answer_0                        | 1.25                                        |
      | id_tolerance_0                     | 0                                           |
      | id_fraction_0                      | 100%                                        |
      | id_answer_1                        | 125                                         |
      | id_tolerance_1                     | 0                                           |
      | id_fraction_1                      | 0%                                          |
      | id_answer_2                        | 1250                                        |
      | id_tolerance_2                     | 0                                           |
      | id_fraction_2                      | 0%                                          |
      | id_unitrole                        | The unit must be given, and will be graded. |
      | id_unitpenalty                     | 0.15                                        |
      | id_unitgradingtypes                | as a fraction (0-1) of the question grade   |
      | id_unitsleft                       | on the right, for example 1.00cm or 1.00km  |
      | id_multichoicedisplay              | a drop-down menu                            |
      | id_unit_0                          | m                                           |
    Then I should see "Numerical-002"

    Given quiz "Quiz 1" contains the following questions:
      | question        | page |
      | proforma-001    | 1    |
      | Numerical-002   | 1    |
#      | palindrom | 1    |
    Given I am on the "Quiz 1" "quiz activity editing" page logged in as teacher1
    And I set the following fields to these values:
      | preferredbehaviour | <behaviour> |
    And I press "Save and display"

    # 1. Start test => not yet answered / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

    When I press "Finish attempt"
    And I should see "<noanswer2>" in the "2" "table_row"
    And I should see "<noanswer1>" in the "1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "In progress" in the "Student 1" "table_row"

    # 2. Enter response => Answer saved / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
     And I press "Continue your attempt"

    And I set the field with xpath "//input[@type='text']" to "11"
    And I set the field with xpath "//select" to "m"
    And I set the response to
    """
    // This is some test input
    """

    And I press "Finish attempt"

    And I should see "<answersaved2>" in the "2" "table_row"
    And I should see "<answersaved1>" in the "1" "table_row"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "In progress" in the "Student 1" "table_row"

    # 3. Submit response => Answer saved / In progress
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Continue your attempt"
    And I should see "This is some test input"
    And the field with xpath "//input[@type='text']" matches value "11"
    And the field with xpath "//select" matches value "m"

    And I press "Finish attempt"
    And I press "Submit all and finish"
    # confirm dialog
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    And I follow "Finish review"

    When I am on the "Quiz 1" "quiz activity" page logged in as teacher1
    And I follow "Attempts: 1"
    Then I should see "Finished" in the "Student 1" "table_row"
    Then I should see "Not yet graded" in the "Student 1" "table_row"
    Then I should see "Requires grading" in the "Student 1" "table_row"

    Examples:
    # 1: proforma
    # 2: numerical question
      | behaviour          | noanswer1        | noanswer2          | answersaved1 | answersaved2      |
      | deferredfeedback   | Not yet answered | Not yet answered   | Answer saved | Answer saved      |
#      | deferredcbm        | Not yet answered | Not yet answered   | Answer saved | Incomplete answer |
      | immediatefeedback  | Not complete     | Not complete       | Not complete | Not complete      |
#      | immediatecbm       | Not complete     | Not complete       | Not complete | Not complete      |
      | interactive        | Not complete     | Tries remaining: 1 | Not complete | Tries remaining: 1 |
      | adaptive           | Not complete     | Not complete       | Not complete | Not complete       |
      | adaptivenopenalty  | Not complete     | Not complete       | Not complete | Not complete       |


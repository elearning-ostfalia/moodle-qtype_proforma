@qtype @qtype_proforma
Feature: DEFERRED
  Test creating a ProFormA java question
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
    And I maximize window

##########################################################################
  @javascript @_file_upload @_switch_window
  Scenario: Deferred Proforma
##########################################################################
#    When I am on the "Course 1" "core_question > course question import" page logged in as teacher1
#    And I set the field "id_format_xml" to "1"
#    And I upload "question/type/proforma/tests/fixtures/javaquestion.xml" file to "Import" filemanager
#    And I press "id_submitbutton"
#    Then I should see "Parsing questions from import file."
#    And I should see "Importing 1 questions from file"
#    And I should see "1. write a function that checks if a given string is a palindrom"
#    And I press "Continue"
#    And I should see "palindrom"

    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher1
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

    # student activity
    When I am on the "Quiz 1" "quiz activity" page logged in as student1
    And I press "Attempt quiz"

    And I set the field with xpath "//input[@type='text']" to "11"
    And I set the field with xpath "//select" to "m"
    And I set the response to
    """
    // This is some test input
    """

    And I press "Finish attempt"

    And I should see "Answer saved" in the "2" "table_row"
    And I should see "Answer saved" in the "1" "table_row"

    And I press "Return to attempt"
    And I should see "This is some test input"
    And the field with xpath "//input[@type='text']" matches value "11"
    And the field with xpath "//select" matches value "m"

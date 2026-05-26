Feature: PREPARE MANUAL TESTS
  Test creating course and quiz for manual testing
  (not to be used for automatic tests)

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
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber | preferredbehaviour | canredoquestions |
      | quiz     | Quiz 1 | Quiz 1 description | C1     | quiz1    | immediatefeedback  | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | clang | 1  | qtype_proforma |
      | cpp   | 1  | qtype_proforma |
      | graderuri_host | http://praktomat:8010  | qtype_proforma |
    And I am on the "Course 1" "core_question > course question bank" page logged in as teacher1

##########################################################################
  @javascript  @_file_upload
  Scenario: Prepare manual tests
##########################################################################
    # CREATE C++ QUESTION
    When I create a new "c++/c" question
#    And I expand all fieldsets

    When I set the following fields to these values:
      | Question name            | C++ Square Root            |
      | Question text            | write a C++ program that calculates square root |
      | Response format          | editor                     |
      | Response filename        | squareroot.cpp                   |
      | Title                    | Google test title               |

    # GoogleTest 1
    And I set the field "testtitle[0]" to "Googletest"
    And I set the codemirror "responsetemplate" to multiline:
"""
#include <math.h>

double squareroot(const double a) {
	if (a < 0) {
		return -1;
	}

	return sqrt(a);
}
"""
    # GoogleTest 1
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/tests.cpp" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/squareroot.h" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/1/CMakeLists.txt" to "testfiles[0]" filemanager by name
    And I set the field "testentrypoint[0]" to "./demo"

    # GoogleTest 2
    And I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "GoogleTest 2"
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/CMakeLists.txt" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/tests2.cpp" to "testfiles[1]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/cpp/2/squareroot.h" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "./demo"

    # close and reopen in order to upload second file (seems to be a bug in test environment)
    And I press "id_submitbutton"

    # CREATE JAVA QUESTION
    When I create a new "java" question
    And I set the following fields to these values:
      | Question name            | java palindrome  |
      | Question text            | palindrome in Java |
      | Response format          | editor                         |
      | Response filename        | MyClass.java                   |

    And I set the field "testtitle[0]" to "Junit #1"
    And I set the codemirror "responsetemplate" to multiline:
"""
public class MyString {

	static public Boolean isPalindrom(String aString) {
		String reverse = new StringBuilder(aString).reverse().toString();
		return (aString.equalsIgnoreCase(reverse));
		// return false;
	}
}

"""
    # JUnit 1
    And I set the field "testtitle[0]" to "Junit 1"
    And I set the field "testweight[0]" to "10"
    And I set the field "testversion[0]" to "4.12"
    And I set the codemirror "testcode_0" to multiline:
    """
import static org.junit.Assert.*;
import org.junit.Test;

public class PalindromTest {
	@Test
	public void testLagertonnennotregal() {
		assertTrue( MyString.isPalindrom("Lagertonnennotregal"));
	}

	@Test
	public void testEmpty() {
		assertTrue( MyString.isPalindrom(""));
	}

	@Test
	public void testFalse1() {
		assertFalse( MyString.isPalindrom("abc123321cbc"));
	}
}
"""

    # JUnit 2
    # add another Junit test
    And I press "id_option_add_fields"
    And I set the field "testtitle[1]" to "Junit 2"
    And I set the field "testweight[1]" to "10"
    And I set the field "testversion[1]" to "4.12"
    And I select "id_testcodeformat_1_2" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/behat/java/Palindrom2Test.java" to "testfiles[1]" filemanager by name
    And I set the field "testentrypoint[1]" to "Palindrom2Test"

    # JUnit 3
    # add another Junit test
    And I press "id_option_add_fields"
    And I set the field "testtitle[2]" to "Junit 3"
    And I set the field "testweight[2]" to "10"
    And I set the field "testversion[2]" to "4.12"
    And I select "id_testcodeformat_2_2" radio button
    # upload JUnit test file
    And I upload "question/type/proforma/tests/fixtures/behat/java/JunitPalindromTest.jar" to "testfiles[2]" filemanager by name
    And I set the field "testentrypoint[2]" to "PalindromTest"

    # Checkstyle
    And I set the field "checkstyle" to "1"
    And I set the field "checkstyleweight" to "5"
    And I set the field "checkstyleversion" to "8.29"
    ## And I set the field "checkstylecode" to "<!-- checkstyle code-->"
    And I set the codemirror "checkstylecode" to multiline:
"""
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE module PUBLIC "-//Checkstyle//DTD Check Configuration 1.3//EN" "https://checkstyle.org/dtds/configuration_1_3.dtd">
<module name="Checker">
  <property name="severity" value="warning"/>
  <module name="TreeWalker">
    <property name="tabWidth" value="4"/>
    <module name="LocalFinalVariableName"/>
    <module name="LocalVariableName"/>
    <module name="MemberName"/>
    <module name="MethodName"/>
    <module name="PackageName">
      <property name="severity" value="warning"/>
    </module>
    <module name="TypeName">
      <property name="severity" value="error"/>
    </module>
    <module name="ParameterNumber">
      <property name="severity" value="warning"/>
    </module>
    <module name="EmptyBlock">
      <property name="severity" value="warning"/>
    </module>
    <module name="LeftCurly">
      <property name="severity" value="info"/>
    </module>
    <module name="NeedBraces">
      <property name="severity" value="error"/>
    </module>
    <module name="EmptyStatement">
      <property name="severity" value="warning"/>
    </module>
    <module name="RightCurly">
      <property name="severity" value="info"/>
    </module>
  </module>
</module>
"""

    And I press "id_submitbutton"

    # CREATE C QUESTION
    When I create a new "c" question
    And I set the following fields to these values:
      | Question name            | c palindrome    |
      | Question text            | c palindrome |
      | Aggregation strategy     | Weighted sum                |
      | Comment                  | a comment               |
      | Response format          | explorer                |
      | Command for executing test | ./palindrome_test     |

    # The default functions do not work for CodeMirror with Javascript.
    # So we must use other functions.
    And I set the field "testtitle[0]" to "C #1"
    And I set the codemirror "responsetemplate" to multiline:
"""
#include <stdio.h>
#include <string.h>
#include "palindrome.h"

char *strrev(char *str)
{
      char *p1, *p2;

      if (! str || ! *str)
            return str;
      for (p1 = str, p2 = str + strlen(str) - 1; p2 > p1; ++p1, --p2)
      {
            *p1 ^= *p2;
            *p2 ^= *p1;
            *p1 ^= *p2;
      }
      return str;
}

int is_palidrome(const char *input) {
    char newstring[100]; // should be allocated...
    strcpy(newstring, input);
    strrev(newstring);
    return (strcmp(input, newstring) == 0);
}
"""

    And I upload "question/type/proforma/tests/fixtures/behat/c/main.c" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/c/CMakeLists.txt" to "testfiles[0]" filemanager by name
    And I upload "question/type/proforma/tests/fixtures/behat/c/palindrome.h" to "testfiles[0]" filemanager by name
    And I set the field "testentrypoint[0]" to "./test1"
    And I press "id_submitbutton"

    # IMPORT
    When I am on the "Course 1" "core_question > course question import" page
    # When I navigate to "Question bank > Import" in current page administration
    And I set the field "id_format_proforma" to "1"
    And I upload "question/type/proforma/tests/fixtures/behat/Palindrom.zip" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I press "Continue"

    And I wait "1000" seconds

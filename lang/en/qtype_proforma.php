<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ProFormA Question Type for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * The ProFormA Question texts
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @copyright  2017 Ostfalia University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ProFormA Task';
$string['pluginname_help'] = 'Question based on a ProFormA task file. Responses are graded automatically by a grader backend system.';
$string['pluginname_link'] = 'question/type/proforma';
$string['pluginnameadding'] = 'Adding a ProFormA question';
$string['pluginnameediting'] = 'Editing a ProFormA question';
$string['pluginnamesummary'] = 'Programming question that will be graded automatically.';

// Capability names.
$string['proforma:runbulktest'] = 'Run the ProFormA bulk test';
$string['proforma:viewsysteminfo'] = 'View ProFormA system information';

$string['allowattachments'] = 'Max. number of uploaded files';
$string['formateditor'] = 'Editor';
$string['formatfilepicker'] = 'File picker';


$string['comment'] = 'Comment';
$string['commentheader'] = 'Comment';
$string['nlines'] = '{$a} lines';
$string['responsefieldlines'] = 'Input box size';
$string['responseformat'] = 'Response format';
$string['responseoptions'] = 'Response Options';
$string['graderoptions_header'] = 'Grader Settings';
$string['responsetemplateheader'] = 'Response Templates';
$string['responsetemplate'] = 'Response template';
$string['responsetemplate_help'] = 'Any text entered here will be displayed in the response input box when a new attempt at the question starts. If more than one template exists only the first one can be edited.';
$string['templateeditor'] = 'Editor';

$string['modelsolution'] = 'Model solution';
$string['modelsolutionheader'] = 'Model Solution';
$string['modelsolution_help'] = 'An exemplary solution for this question. It is displayed to the student when "Right answer" is checked in "Review options" of the quiz.';
$string['msfilename'] = 'File';

$string['taskpath'] = 'Task path';
$string['taskpath_hint'] = $string['taskpath'];
$string['taskpath_hint_help'] = 'location of task zip file (in repository)';
$string['repository'] = 'Repository';
$string['repository_hint'] = $string['repository'];
$string['repository_hint_help'] = 'URI of ProFormA questions repository';

$string['repositoryhost'] = 'Host of repository';
$string['repositoryhost_desc'] = 'Host of repository starting with protocol (https://)';
$string['repositorypath'] = 'Path in repository';
$string['repositorypath_desc'] = 'Path of ProFormA task in Repository';

$string['filename'] = 'Response filename';
$string['filename_hint'] = $string['filename'];
$string['filename_hint_help'] = 'filename used for submitted source code (if necessary consider package name)';

$string['taskfilename'] = 'ProFormA task file';
$string['taskfilename_hint'] = $string['taskfilename'];
$string['taskfilename_hint_help'] = 'Corresponding ProFormA task file';
$string['createdtask_hint'] = $string['taskfilename'];
$string['createdtask_hint_help'] = 'Corresponding ProFormA task file. Question must be saved in order to generate ProFormA task file!';
$string['task_hint'] = $string['taskfilename'];
$string['task_hint_help'] = 'Corresponding ProFormA task file. '
. 'In order to update this file please consider that the updated file must be \'compatible\' with the old one. '
. 'That means: <br>'
. '- same programming language<br>'
. '- same number of tests<br>'
. '- same test types in same order';


$string['highlight'] = 'Syntax highlighting';
$string['highlight_hint'] = $string['highlight'];
$string['highlight_hint_help'] = 'The programming language is used to set syntax highlighting in all editors associated with this question';

$string['proglang'] = 'Programming language';
$string['proglang_hint'] = $string['proglang'];
$string['proglang_hint_help'] = 'Programming language of task. Currently only Java questions can be created.';
$string['other'] = 'other';

$string['version'] = 'Version';


$string['proglangversion'] = 'Programming language version';
$string['proglangversion_hint'] = $string['proglangversion'];
$string['proglangversion_hint_help'] = 'Version der Programmiersprache.';

$string['compile'] = 'Compilation';
$string['syntaxcheck'] = 'Syntax Check';
$string['code'] = 'Source Code';
$string['code_help'] = 'Source Code for JUnit test';

$string['addtest'] = 'Add {$a}';


$string['junit'] = 'JUnit test';
$string['setlx'] = 'SetlX test';
$string['clang'] = 'CUnit test';
$string['cppunittest'] = 'Google Test';
$string['pythonunit'] = 'Python Unittest';

$string['cunit_help'] = 'The test files must contain a Makefile (<code>CMakeLists.txt</code> for <i>cmake</i> possible) and testcode files.<br>
<code>main()</code> must return 0 in case of an error-free test run, otherwise <> 0.<br>
CUnit must be run in <i>basic mode</i>.<br>
All files can be uploaded individually or packed as exactly one zip archive.<br>
If the executable created by the Makefile is <code>test</code>, then the 
command to run the test is <code>./test</code>.';
$string['gtest_help'] = 'The test files must contain a Makefile (<code>CMakeLists.txt</code> for <i>cmake</i> possible) 
and one or more files with testcode.<br>
<code>main()</code> must return 0 in case of an error-free test run, otherwise <> 0.<br>
All files can be uploaded individually or packed as exactly one zip archive.<br>
If the executable created by the Makefile is <code>test</code>, then the 
command to run the test is <code>./test</code>.';


$string['codeempty'] = 'Testcode required';
$string['titleempty'] = 'Title required';
$string['versionrequired'] = 'Version required.';
$string['entrypointrequired'] = 'JUnit entrypoint required.';
$string['executablerequired'] = 'Run command required.';
$string['filenameerror'] = 'Cannot determine classname (filename)';
$string['entrypointerror'] = 'Cannot determine classname (entrypoint)';
$string['sumweightzero'] = 'The sum of all weights must not be zero.';

$string['notaskfile'] = 'Taskfile is missing.';



$string['miscellaneousheader'] = 'Miscellaneous';
$string['defaultpenalty'] = 'Default penalty';
$string['defaultpenalty_desc'] = 'Penalty for each wrong submission (if question behaviour is set to adaptive with penalty)';

$string['grader_heading'] = 'Grader Specific Settings';
$string['graderuri_host'] = 'URI: Protocol and Server';
$string['graderuri_host_desc'] = 'Protocol (Scheme) and Server (Host) Part of Grader URI';
$string['graderuri_path'] = 'URI: Path';
$string['graderuri_path_desc'] = 'Path Part of Grader URI';
$string['c_graderuri_host'] = 'Grader for c';
$string['c_graderuri_host_desc'] = '';
$string['cpp_graderuri_host'] = 'Grader for C++';
$string['cpp_graderuri_host_desc'] = '';
$string['python_graderuri_host'] = 'Grader for Python';
$string['python_graderuri_host_desc'] = '';
$string['alternativegrader'] = 'For (almost) all programming languages an alternative grader can be set.
Only protocol (scheme) and server (host) part of grader URI must be set, no path.';

$string['submissionproformaversion'] = 'ProFormA version';
$string['submissionproformaversion_help'] = 'ProFormA version for communication between Moodle and grader. Version 2.1 requires at least Praktomat 4.9.0.';


$string['feedbackoptions_heading'] = 'Feedback Options';

$string['collapse'] = 'Initially collapse/expand';
$string['admincollapse'] = 'Feedback collapse/expand';
$string['collapse_help'] = 'Some test feedback will be presented as collapsible regions. '
. 'This setting defines the initial state of this region, i.e. if it is visible (expanded) or not (collapsed).';
$string['always_collapse'] = 'collapse';
$string['always_expand'] = 'expand';
$string['expand_student'] = 'expand for students';
$string['expand_teacher'] = 'expand for teachers';
$string['expand_small'] = 'expand for small regions';

$string['inlinemessages'] = 'Show messages in editor';
$string['useembeddedmessages'] = 'Use';
$string['inlinemessages_help'] = 'Show Compiler messages or messages from static code analysis tools inline in editor. May not be possible for all messages.';
$string['inlinemessages_desc'] = 'Show Compiler messages or messages from static code analysis tools inline in editor. Requires CodeMirror. ';
$string['regexpfromgrader'] = 'Use regular expressions from Praktomat';
$string['regexpfromgrader_desc'] = 'To display the compiler messages, the compiler output is parsed using a regular expression. ' .
        'Normally the regular expression should be used by the grader. In case of an error, however, the regular expression of the plugin can be used.';



$string['nocodemirror'] = 'CodeMirror is disabled. So embedding messages into editor will not work.';

/*
$string['admininitiallyembedded'] = 'Initially show messages in editor';
$string['initiallyembedded'] = 'Initially embedded';
$string['initiallyembedded_help'] = 'If set the messages are already embedded when feedback is available.';
*/




$string['javasettings_header'] = 'Java Settings for Java questions created with Moodle';
$string['checkstyleversion'] = 'Checkstyle version';
$string['checkstyleversion_desc'] = 'Comma separated list with Checkstyle versions that are supported by the grader. First one is default.';
$string['javaversion'] = 'Java version';
$string['javaversion_desc'] = 'Comma separated list with Java versions that are supported by the grader. First one is default.';
$string['junitversion'] = 'JUnit version';
$string['junitversion_desc'] = 'Comma separated list with JUnit versions that are supported by the grader. First one is default.';

$string['grading_timeout'] = 'Grading Timeout';
$string['grading_timeout_desc'] = 'Timout for grading request in seconds';

$string['passed'] = 'PASSED';
$string['failed'] = 'FAILED';
$string['gradepassed'] = 'Your answer is correct.';
$string['gradefailed'] = 'Your answer is not completely correct.';
$string['gradepartialpassed'] = 'Your answer is partially correct. For details see information below. ';
$string['gradeinternalerror'] = 'Your answer could not be graded due to an internal error in the grading system.';
$string['testinternalerror'] = 'An internal error occured during test execution:';


$string['usecodemirror'] = 'Use CodeMirror as source code editor. ';
$string['usecodemirror_desc'] = 'For student response and for input of model solution and template. ';
$string['uuid'] = 'UUID';
$string['uuid_hint'] = $string['uuid'];
$string['uuid_hint_help'] = 'universal unique identifier of ProFormA task file';
$string['configmaxbytes'] = 'Max. upload size';
$string['maxbytes'] = 'Max. upload size';
$string['maximumsubmissionsize'] = $string['maxbytes'];
$string['maximumsubmissionsize_help'] = 'Files uploaded by students may be up to this size.';
$string['acceptedfiletypes'] = 'Accepted file types';
$string['acceptedfiletypes_help'] = 'Accepted file types can be restricted by entering a semicolon-separated list of mimetypes, for example \'text/plain; application/java-archive; application/zip; application/xml\'. You may also limit to extensions by including the dot, for example \'.java; .jar\' If the field is left empty, then all file types are allowed..';
$string['nonexistentfiletypes'] = 'The following file types were not recognised: {$a}';
$string['templates'] = 'Additional template files';
$string['templates_hint'] = $string['templates'];
$string['templates_hint_help'] = 'Filenames of additional template files (normally there is only one template file)';
$string['downloads'] = 'Downloadable files';
$string['downloads_hint'] = $string['downloads'];
$string['downloads_hint_help'] = 'Files needed to solve the task (can be downloaded by student)';
$string['modelsolfiles'] = 'Model solution files';
$string['modelsolfiles_hint'] = $string['modelsolfiles'];
$string['modelsolfiles_hint_help'] = 'Model solution files';

$string['attachments'] = 'Downloads:';
$string['questiondefaults'] = 'Default values for new questions';
$string['none'] = 'none';

$string['tests'] = 'Grading';
$string['notests'] = 'Tests are not available => use "all or nothing" ';
$string['testdescription'] = 'Description';
$string['testtype'] = 'Type';
$string['testtitle'] = 'Title';
$string['testcode'] = 'Testcode';
$string['testcodefiles'] = 'Testcode files';
$string['testlabel'] = 'Test';
$string['testlabela'] = '{$a} {no}';
$string['entrypoint'] = 'Entrypoint';
$string['executable'] = 'Command for executing test';
$string['testexecutable_help'] = 'How to execute the test program, e.g. \'./tester\'';


$string['weight'] = 'Weight';
$string['filepickeroptions'] = 'Filepicker options';

$string['all_or_nothing'] = 'All or nothing';
$string['weighted_sum'] = 'Weighted sum';
$string['aggregationstrategy'] = 'Aggregation strategy';
$string['aggregationstrategy_help'] = 'Aggregation strategy used for grading this question';

$string['internaltesterror'] = 'Internal error in a test';
$string['privacy:metadata'] = 'The ProFormA question type plugin does not store any personal data.';

// Version control system.

$string['versioncontrol'] = 'Version control system';
$string['vcsuritemplate'] = 'URI of repository';
$string['vcsuritemplate_help'] = 'location where the student\'s submission can be found. Must contain exactly one placeholder:<br>
{group} = groupname of the group that the student belongs to<br>
{input} = identifier to be entered by student';
// Maybe for future use: '{username} = student\'s login name in Moodle'.

$string['groupname'] = 'Group';

$string['vcslabel'] = 'Label for {input} field';
$string['vcslabel_help'] = 'only needed if  \'URI of repository\' contains {input}';
$string['sampleuri'] = 'Sample URI';

$string['defaultvcsuri'] = 'Default URI template';
$string['defaultvcsuri_desc'] = 'should contain place holders';
$string['vcsfunction'] = 'PHP helper function for resolving \'func\'';
$string['vcsfunction_desc'] = 'can be referenced as {func} resp. if used with other placeholders: {func(input)} or {func(username)}';

$string['vcslabeldefault'] = 'Default label for input field';
$string['vcslabeldefault_desc'] = 'only needed if actual URI templates contain {input}';

$string['vcs_header'] = 'Version control system';
$string['vcs_info'] = 'For use of a version control system that stores the students\' code.
The actual URI to locate the submission is compiled by replacing the placeholder in the URI template:<br>
{group}: name of group that the student belongs to<br>
{input}: generates an input field';
// Maybe for future use: '{username}: takes the student\'s username in Moodle'.

$string['proglang_hdr'] = 'Programming languages';
$string['proglang_hdr_info'] = 'Java is enabled by default. Opt out programming languages that are not used at your site.';

$string['selectlangtitle'] = 'Select programming language';

$string['infotaskupdate'] = 'Please check task or use ProFormA import.';

$string['editorinput'] = 'Editor';
$string['fileinput'] = 'Files';


// Bulk test.
$string['bulktestnomodelsolution'] = 'This question does not have a model solution.';
$string['bulktestnofile'] = 'This question does not have a ProFormA file.';
$string['replacedollarscount'] = 'This category contains {$a} ProFormA questions.';
$string['testpassesandfails'] = '{$a->passes} passes and {$a->fails} failures.';
$string['proformaInstall_testsuite_notests'] = 'Questions with ProFormA file!';
$string['proformaInstall_testsuite_nomodelsolution'] = 'Questions without a model solution: please add one!';
$string['proformaInstall_testsuite_failingtests'] = 'Tests that failed';
$string['proformaInstall_testsuite_fail'] = 'Not all tests passed!';
$string['proformaInstall_testsuite_pass'] = 'All tests passed!';
$string['bulktesttitle'] = 'Running all the question tests in {$a}';
$string['replacedollarsindex'] = 'Contexts with ProFormA questions';
$string['bulktestrun'] = 'Run all the question tests for all the questions in the system (slow, admin only)';
$string['bulktestindextitle'] = 'Run the question tests in bulk';
$string['overallresult'] = 'Overall result';
$string['passed'] = 'passed';
$string['failed'] = 'failed';
$string['bulktestcontinuefromhere'] = 'Run again or resume, starting from here';

// Errors.
$string['errinvalidtask'] = 'File is no ProFormA task file.';
$string['errinvalidproglang'] = 'Programming language in new task is not \'{$a}\'.';
$string['errcounttest'] = 'Number of tests has been changed: {$a}.';
$string['errtestsincompatible'] = 'Test types or order do not match.';
$string['errtaskinvalid'] = 'ProFormA task file is missing or may be corrupt.';
$string['errnotask'] = 'ProFormA task file is missing.';
$string['erroldtask'] = 'Original ProFormA task file cannot be checked.';
$string['errtasknotunique'] = 'ProFormA task file is not unique.';
$string['errinvalidtaskxml'] = 'Task.xml within ProFormA file is invalid.';
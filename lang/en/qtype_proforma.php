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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The ProFormA Question texts
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'ProFormA Task';
$string['pluginname_help'] = 'Question based on a ProFormA task file. Responses are graded automatically by a grader backend system.';
$string['pluginname_link'] = 'question/type/proforma';
$string['pluginnameadding'] = 'Adding a ProFormA question';
$string['pluginnameediting'] = 'Editing a ProFormA question';
$string['pluginnamesummary'] = 'Programming question that will be graded automatically.<br>
Note! New questions must be <b>imported</b> into the question bank and cannot be created this way!<br>
An editor for generating questions is here: https://media.elan-ev.de/proforma/editor/releases.html';
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
$string['modelsolution_help'] = 'An exemplary solution for this question.';
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
$string['filename_hint_help'] = 'filename used for submitted source code';

$string['taskfilename'] = 'Task file';
$string['taskfilename_hint'] = $string['taskfilename'];
$string['taskfilename_hint_help'] = 'Corresponding ProFormA task file';

$string['highlight'] = 'Syntax highlighting';
$string['highlight_hint'] = $string['highlight'];
$string['highlight_hint_help'] = 'The programming language is used to set syntax highlighting in all editors associated with this question';

$string['proglang'] = 'Programming language';
$string['proglang_hint'] = $string['proglang'];
$string['proglang_hint_help'] = 'Programming language of task';
$string['other'] = 'other';

$string['compile'] = 'Compilation';
$string['code'] = 'Source Code';

$string['addjunit'] = 'Add JUnit test';


$string['miscellaneousheader'] = 'Miscellaneous';
$string['defaultpenalty'] = 'Default penalty';
$string['defaultpenalty_desc'] = 'Penalty for each wrong submission (if question behaviour is set to adaptive with penalty)';

$string['grader_heading'] = 'Grader Specific Settings';
$string['graderuri_host'] = 'URI: Protocol and Server';
$string['graderuri_host_desc'] = 'Protocol and Server Part of Grader URI';
$string['graderuri_path'] = 'URI: Path';
$string['graderuri_path_desc'] = 'Path Part of Grader URI';
$string['javafile_without_package'] = 'send Java submission file without package path to grader';
$string['javafile_without_package_desc'] = 'in case of editor submission format teh submiited filename is generated directly from Model solution.
If the filename contains the package path this might lead to grading errors. Set this flag if the package path shall be ignored.';

$string['grading_timeout'] = 'Grading Timeout';
$string['grading_timeout_desc'] = 'Timout for grading request in seconds';

$string['passed'] = 'PASSED';
$string['failed'] = 'FAILED';
$string['gradepassed'] = 'Your answer is correct.';
$string['gradefailed'] = 'Your answer is not completely correct.';
$string['gradepartialpassed'] = 'Your answer is partially correct. For details see information below. ';
$string['gradeinternalerror'] = 'Your answer could not be graded due to an internal error in the grading system.';

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
$string['weight'] = 'weight';
$string['filepickeroptions'] = 'Filepicker options';

$string['all_or_nothing'] = 'All or nothing';
$string['weighted_mean'] = 'Weighted sum';
$string['aggregationstrategy'] = 'Aggregation strategy';
$string['aggregationstrategy_help'] = 'Aggregation strategy used for grading this question';

$string['nocreate'] = 'Please use "import question" in order to create a new Proforma question.<br>
ProFormA questions must be created with an external tool such as https://media.elan-ev.de/proforma/editor/releases.html.';

$string['internaltesterror'] = 'Internal error in a test';
$string['privacy:metadata'] = 'The ProFormA question type plugin does not store any personal data.';

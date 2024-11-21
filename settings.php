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
 * The ProFormA Question configuration settings
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/proforma/locallib.php');

// Grader (Praktomat) settings.
$settings->add(new admin_setting_heading('grader',
        get_string('grader_heading', 'qtype_proforma'), ''));

$settings->add(new qtype_proforma\lib\admin_setting_configproformagrader('qtype_proforma/graderuri_host',
        get_string('graderuri_host', 'qtype_proforma'),
        get_string('graderuri_host_desc', 'qtype_proforma'),
        'http://localhost:8010'));

$settings->add(new admin_setting_configtext('qtype_proforma/graderuri_path',
        get_string('graderuri_path', 'qtype_proforma'),
        get_string('graderuri_path_desc', 'qtype_proforma'),
        '/api/v2/submissions', PARAM_PATH, 80));

$settings->add(new admin_setting_configtext('qtype_proforma/uploaduri_path',
    get_string('uploaduri_path', 'qtype_proforma'),
    get_string('uploaduri_path_desc', 'qtype_proforma'),
    '/api/v2/upload', PARAM_PATH, 80));

$settings->add(new admin_setting_configtext('qtype_proforma/runtest_path',
    get_string('runtesturi_path', 'qtype_proforma'),
    get_string('runtesturi_path_desc', 'qtype_proforma'),
    '/api/v2/runtest', PARAM_PATH, 80));

$settings->add(new admin_setting_configtext('qtype_proforma/grading_timeout',
        get_string('grading_timeout', 'qtype_proforma'),
        get_string('grading_timeout_desc', 'qtype_proforma'), 40,
        PARAM_INT, 3));

$settings->add(new admin_setting_configtext('qtype_proforma/upload_timeout',
    get_string('upload_timeout', 'qtype_proforma'),
    get_string('upload_timeout_desc', 'qtype_proforma'), 300,
    PARAM_INT, 3));

$proformaversions = array(
//        '2.0' => '2.0',
        '2.1_old' => '2.1 ' . get_string('old', 'qtype_proforma'),
        '2.1_new' => '2.1 ' . get_string('new', 'qtype_proforma'),
);
$settings->add(new admin_setting_configselect('qtype_proforma/submissionproformaversion',
        get_string('submissionproformaversion', 'qtype_proforma'),
        get_string('submissionproformaversion_help', 'qtype_proforma'),
        '2.1_new',
        $proformaversions));

$name = new lang_string('taskmaxbytes', 'qtype_proforma');
$description = new lang_string('maximumtasksize_help', 'qtype_proforma');
if (isset($CFG->maxbytes)) {
    $taskmaxbytes = get_config('qtype_proforma', 'taskmaxbytes');
    $element = new admin_setting_configselect('qtype_proforma/taskmaxbytes',
        $name,
        $description,
        $CFG->maxbytes, // min(10485760, $CFG->maxbytes),
        get_max_upload_sizes($CFG->maxbytes, 0, 0, $taskmaxbytes));
    $settings->add($element);
} else {
    $settings->add(new admin_setting_configtext('qtype_proforma/taskmaxbytes',
        $name, $description, 10485760)); // 10MB as default
}

// Use CodeMirror.
$settings->add(new admin_setting_heading('CodeMirror',
        'CodeMirror', ''));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/usecodemirror',
        get_string('usecodemirror', 'qtype_proforma'),
        get_string('usecodemirror_desc', 'qtype_proforma'), 1));

// Misc.
$settings->add(new admin_setting_heading('misc',
        get_string('questiondefaults', 'qtype_proforma'), ''));


$settings->add(new admin_setting_configtext('qtype_proforma/defaultpenalty',
        get_string('defaultpenalty', 'qtype_proforma'),
        get_string('defaultpenalty_desc', 'qtype_proforma'), 0.1));


if (isset($CFG->maxbytes)) {
    $name = new lang_string('maximumsubmissionsize', 'qtype_proforma');
    $description = new lang_string('configmaxbytes', 'qtype_proforma');

    $maxbytes = get_config('qtype_proforma', 'maxbytes');
    $element = new admin_setting_configselect('qtype_proforma/maxbytes',
            $name,
            $description,
            $CFG->maxbytes,
            get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes));
    $settings->add($element);
}

$collapses = array(
    qtype_proforma::ALWAYS_COLLPASE => get_string('always_collapse', 'qtype_proforma'),
    qtype_proforma::ALWAYS_EXPAND => get_string('always_expand', 'qtype_proforma'),
/*    qtype_proforma::EXPAND_STUDENT => get_string('expand_student', 'qtype_proforma'),
    qtype_proforma::EXPAND_TEACHER => get_string('expand_teacher', 'qtype_proforma'),
    qtype_proforma::EXPAND_SMALL => get_string('expand_small', 'qtype_proforma'),*/
);
$settings->add(new admin_setting_configselect('qtype_proforma/expandcollapse',
            get_string('admincollapse', 'qtype_proforma'),
            get_string('collapse_help', 'qtype_proforma'),
            0,
            $collapses));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/inlinemessages',
        get_string('inlinemessages', 'qtype_proforma'),
        get_string('inlinemessages_desc', 'qtype_proforma'), 1));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/regexpfromgrader',
        get_string('regexpfromgrader', 'qtype_proforma'),
        get_string('regexpfromgrader_desc', 'qtype_proforma'), 1));

// Not supported:
/* $settings->add(new admin_setting_configcheckbox('qtype_proforma/initiallyembedded',
        get_string('admininitiallyembedded', 'qtype_proforma'),
        get_string('initiallyembedded_help', 'qtype_proforma'), 0));
 */

$settings->add(new admin_setting_heading('vcs',
        get_string('vcs_header', 'qtype_proforma'),
        get_string('vcs_info', 'qtype_proforma')));

$settings->add(new admin_setting_configtext('qtype_proforma/defaultvcsuri',
        get_string('defaultvcsuri', 'qtype_proforma'),
        get_string('defaultvcsuri_desc', 'qtype_proforma'),
        'https://server/path/to/project/{group}/subfolder', PARAM_TEXT, 80));

$settings->add(new admin_setting_configtext('qtype_proforma/vcslabeldefault',
        get_string('vcslabeldefault', 'qtype_proforma'),
        get_string('vcslabeldefault_desc', 'qtype_proforma'),
        '', PARAM_TEXT, 20));

// Programming languages.
$settings->add(new admin_setting_heading('proglangs',
        get_string('proglang_hdr', 'qtype_proforma'),
        get_string('proglang_hdr_info', 'qtype_proforma')));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/clang',
        'c', '', 1));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/cpp',
    'C++', '', 1));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/setlx',
    'SetlX', '', 0));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/python',
    'Python', get_string('alternativegrader', 'qtype_proforma'), 1));

$settings->add(new qtype_proforma\lib\admin_setting_configproformagrader('qtype_proforma/c_grader',
        get_string('c_graderuri_host', 'qtype_proforma'),
        get_string('c_graderuri_host_desc', 'qtype_proforma'),
        ''));

$settings->add(new qtype_proforma\lib\admin_setting_configproformagrader('qtype_proforma/cpp_grader',
    get_string('cpp_graderuri_host', 'qtype_proforma'),
    get_string('cpp_graderuri_host_desc', 'qtype_proforma'),
    ''));

$settings->add(new qtype_proforma\lib\admin_setting_configproformagrader('qtype_proforma/python_grader',
    get_string('python_graderuri_host', 'qtype_proforma'),
    get_string('python_graderuri_host_desc', 'qtype_proforma'),
    ''));


// Java - JUnit - Checkstyle.
$settings->add(new admin_setting_heading('java',
        get_string('javasettings_header', 'qtype_proforma'), ''));

$settings->add(new admin_setting_configtext('qtype_proforma/javaversion',
        get_string('javaversion', 'qtype_proforma'),
        get_string('javaversion_desc', 'qtype_proforma'), '21, 17, 11, 1.8' ));


$settings->add(new admin_setting_configtext('qtype_proforma/junitversion',
        get_string('junitversion', 'qtype_proforma'),
        get_string('junitversion_desc', 'qtype_proforma'), '4.12, 5'));

$settings->add(new admin_setting_configtext('qtype_proforma/checkstyleversion',
        get_string('checkstyleversion', 'qtype_proforma'),
        get_string('checkstyleversion_desc', 'qtype_proforma'), '10.17, 10.1, 8.29, 8.23'));


// Miscellaneous
$settings->add(new admin_setting_heading('miscellaneous',
    get_string('miscellaneousheader', 'qtype_proforma'), ''));

$settings->add(new admin_setting_configtext('qtype_proforma/explorerautosave',
    get_string('explorerautosave', 'qtype_proforma'),
    get_string('explorerautosave_desc', 'qtype_proforma'),
    '30', PARAM_INT, 5));
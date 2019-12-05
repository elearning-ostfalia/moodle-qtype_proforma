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

// Options for connection to Praktomat/Middleware
$settings->add(new admin_setting_heading('grader',
        get_string('grader_heading', 'qtype_proforma'), ''));

$settings->add(new admin_setting_configtext('qtype_proforma/graderuri_host',
        get_string('graderuri_host', 'qtype_proforma'),
        get_string('graderuri_host_desc', 'qtype_proforma'),
        'http://localhost:8001'));

$settings->add(new admin_setting_configtext('qtype_proforma/graderuri_path',
        get_string('graderuri_path', 'qtype_proforma'),
        get_string('graderuri_path_desc', 'qtype_proforma'),
        '/api/v2/submissions', PARAM_PATH, 80));

$settings->add(new admin_setting_configtext('qtype_proforma/grading_timeout',
        get_string('grading_timeout', 'qtype_proforma'),
        get_string('grading_timeout_desc', 'qtype_proforma'), 40,
        PARAM_INT, 3));

// Use CodeMirror
$settings->add(new admin_setting_heading('CodeMirror',
        'CodeMirror', ''));

$settings->add(new admin_setting_configcheckbox('qtype_proforma/usecodemirror',
        get_string('usecodemirror', 'qtype_proforma'),
        get_string('usecodemirror_desc', 'qtype_proforma'), 1));

// Misc

$settings->add(new admin_setting_heading('misc',
        get_string('questiondefaults', 'qtype_proforma'), ''));


$settings->add(new admin_setting_configtext('qtype_proforma/defaultpenalty',
        get_string('defaultpenalty', 'qtype_proforma'),
        get_string('defaultpenalty_desc', 'qtype_proforma'), 0.1));

// can we use this??? because float format is depending on language (. or ,)
/*
$settings->add(new admin_setting_configtext_with_advanced('qtype_proforma/defaultpenalty',
    get_string('defaultpenalty', 'qtype_proforma'),
    get_string('defaultpenalty_desc', 'qtype_proforma'), 0.1,
    PARAM_FLOAT));
*/

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

// Java - JUnit - Checkstyle

$settings->add(new admin_setting_heading('java',
        get_string('javasettings_header', 'qtype_proforma'), ''));

$settings->add(new admin_setting_configtext('qtype_proforma/javaversion',
        get_string('javaversion', 'qtype_proforma'),
        get_string('javaversion_desc', 'qtype_proforma'), 1.8));


$settings->add(new admin_setting_configtext('qtype_proforma/junitversion',
        get_string('junitversion', 'qtype_proforma'),
        get_string('junitversion_desc', 'qtype_proforma'), 4.12));

$settings->add(new admin_setting_configtext('qtype_proforma/checkstyleversion',
        get_string('checkstyleversion', 'qtype_proforma'),
        get_string('checkstyleversion_desc', 'qtype_proforma'), 8.23));
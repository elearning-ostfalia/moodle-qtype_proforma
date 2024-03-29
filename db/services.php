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
 * ProformA question type webservice functions.
 *
 *
 * @package    qtype_proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    // ProformA question type webservice functions.
    'qtype_proforma_get_task_url' => array(
        'classname'    => 'qtype_proforma\external\taskeditor',
        'methodname'   => 'get_task_url',
        'classpath'    => '',
        'description'  => 'read task from Moodle',
        'type'         => 'read',
        'capabilities' => 'moodle/question:editmine',
        'ajax'         => true,
    ),

    'qtype_proforma_get_junit_versions' => array(
        'classname'    => 'qtype_proforma\external\taskeditor',
        'methodname'   => 'get_junit_versions',
        'classpath'    => '',
        'description'  => 'read JUnit versions from Moodle',
        'type'         => 'read',
        // 'capabilities' => 'moodle/question:editmine',
        'ajax'         => true,
    ),

    'qtype_proforma_get_checkstyle_versions' => array(
        'classname'    => 'qtype_proforma\external\taskeditor',
        'methodname'   => 'get_checkstyle_versions',
        'classpath'    => '',
        'description'  => 'read Checkstyle versions from Moodle',
        'type'         => 'read',
        // 'capabilities' => 'moodle/question:editmine',
        'ajax'         => true,
    ),

    'qtype_proforma_get_java_versions' => array(
        'classname'    => 'qtype_proforma\external\taskeditor',
        'methodname'   => 'get_java_versions',
        'classpath'    => '',
        'description'  => 'read Java versions from Moodle',
        'type'         => 'read',
        // 'capabilities' => 'moodle/question:editmine',
        'ajax'         => true,
    ),
);


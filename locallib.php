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
 * Plugin library
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

namespace qtype_proforma\lib;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/accesslib.php');


/**
 * checks if the current user is an admin (can see more than a teacher)
 *
 * @return bool
 */
function is_admin() {
    global $USER;
    return is_siteadmin($USER);
}

/**
 * checks if the current user is a teacher (can see more than the student)
 *
 * @return bool
 */
function is_teacher() {
    global $COURSE;
    if ($COURSE) {
        $context = \context_course::instance($COURSE->id);
        if ($context) {
            return has_capability('moodle/grade:viewhidden', $context);
        }
    }

    return false;
}

/**
 * @return string returns the name of the group that the current user belongs to
 */
function get_groupname() {
    global $USER, $COURSE;
    // Get all groupings.
    $groupings = groups_get_user_groups($COURSE->id, $USER->id);

    // Count groups and fetch name.
    $count = 0;
    $groupname = '???';
    foreach ($groupings as $grouping) {
        foreach ($grouping as $group) {
            $count++;
            $groupname = groups_get_group_name($group);
        }
    }

    switch ($count) {
        case 0:
            // no group found
            return 'N/A';
        case 1:
            return $groupname;
        default:
            // more than one group found
            break;
    }

    return 'NOT UIQUE';
}


/**
 * Helper function for converting a text area into a CodeMirror window
 *
 * @param $textareaid: html identifier
 * @param string $mode: Syntax highlighting mode
 * @param null $header: header in form
 * @param bool $readonly: is input disabled?
 * @param bool $loadjquery: does jquery need to be loaded by page?
 */
function as_codemirror($textareaid, $mode = 'java', $header = null, $readonly = false, $loadjquery = true) {
    if (get_config('qtype_proforma', 'usecodemirror')) {
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/config.php');
        // load jquery css file for resizable
        if ($loadjquery) {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui-css');
        }

        $moodleversion = $CFG->version;
        // debugging('Moodle Version is ' . $moodleversion);

        if ($moodleversion > 2018051700) {
            // starting from Moodle 3.5 the Codemirror editor width is not resized to parent container.
            // so this must be explicitly be done in Javascript.
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                    array($textareaid, $readonly, $mode, $header, 1));
        } else {
            // In 3.4 resizing must be prohinited because the window is too small
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                    array($textareaid, $readonly, $mode, $header));
        }
    }
}
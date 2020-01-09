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
<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software:
// you can redistribute it and/or modify
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
// along with ProFormA Question Type for Moodle.
// If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the external API for ProformA question.
 *
 * @package    qtype_proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

namespace qtype_proforma\external;

defined('MOODLE_INTERNAL') || die();

// require_once("$CFG->libdir/externallib.php");

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

class upload_task extends /* \core_external\*/ \external_api {
    /**
     * Upload the task
     * @param int $questionid proforma question id
     * @return array result (boolean) with log messages
     */
    public static function execute($questionid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['questionid' => $questionid]);

/*        if ($DB->get_record('qtype_proforma_options', ['id' => $questionid]) == false) {
            throw new invalid_parameter_exception('No ProformA question with id = "{$questionid}" found');
        }
*/
        // now security checks
        // $context = get_context_instance(CONTEXT_COURSE, $group->courseid);
        // self::validate_context($context);
        // require_capability('moodle/course:managegroups', $context);


/*        $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.

        $questionid = array();

        foreach ($params['groups'] as $group) {
            $group = (object)$group;

            if (trim($group->name) == '') {
                throw new invalid_parameter_exception('Invalid group name');
            }
            if ($DB->get_record('groups', ['courseid' => $group->courseid, 'name' => $group->name])) {
                throw new invalid_parameter_exception('Group with the same name already exists in the course');
            }

            // now security checks
            $context = get_context_instance(CONTEXT_COURSE, $group->courseid);
            self::validate_context($context);
            require_capability('moodle/course:managegroups', $context);

            // finally create the group
            $group->id = groups_create_group($group, false);
            $questionid[] = (array) $group;
        }

        $transaction->allow_commit();
*/
        return [
            'questionid' => $questionid,
            'result' => true,
            'message' => 'alles paletti'
        ];
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            "questionid"=> new external_value(PARAM_INT, 'ID of ProformA question', VALUE_REQUIRED),
        ]);
    }

    public static function execute_returns() {
        return new external_function_parameters([
            'questionid' => new external_value(PARAM_INT, 'question id'),
            'result' => new external_value(PARAM_BOOL, 'result'),
            'message' => new external_value(PARAM_RAW, 'upload message'),
        ]);
    }
}

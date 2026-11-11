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
 * @package    qtype_proforma
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
 * checks if the current user is allowed to see ProFormA system information
 * @param int $contextid current course
 * @return bool
 */
function can_view_systeminfo($contextid) {
    $coursecontext = \context::instance_by_id($contextid);
    return has_capability('qtype/proforma:viewsysteminfo', $coursecontext);
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
 * get name of first group or '' if no groups exist.
 *
 * @return string
 */
function get_groupname_sample() {
    global $COURSE;
    // Get all groupings.
    $groups = groups_get_all_groups($COURSE->id);
    if (isset($groups) && count($groups) > 0) {
        // Return name of first group.
        $group = reset($groups);
        return $group->name;
    }

    // No groups available.
    return '';
}
/**
 * Returns the name of the group that the current user belongs to.
 *
 * @param context $context context of the question usage
 * @return string returns the name of the group that the current user belongs to
 */
function get_groupname($context) {
    if (CONTEXT_MODULE != $context->contextlevel || !isset($context->instanceid)) {
        // No quiz context (e.g. in question bank preview).
        return '';
    }

    global $USER;
    [$course, $cm] = get_course_and_cm_from_cmid($context->instanceid, 'quiz');
    // Get the groups the user belongs to (restricted to the grouping of the quiz, if any).
    // Note: groups_get_activity_allowed_groups() is not used because it returns all groups
    // in 'visible groups' mode or for users with 'moodle/site:accessallgroups'.
    $groups = groups_get_all_groups($course->id, $USER->id, $cm->groupingid);
    if (count($groups) > 1) {
        // Not unique => try to restrict by the access restrictions (availability) of the quiz.
        $groups = filter_groups_by_availability($cm, $groups);
    }

    // Count groups and fetch name.
    $groupname = '???';

    switch (count($groups)) {
        case 0:
            // No group found (teachers may enter a group name manually).
            return (is_teacher() || is_admin()) ? '' : 'N/A';
        case 1:
            // Exactly one group found.
            $group = reset($groups);
            $groupname = $group->name;
            break;
        default:
            // More than one group found.
            if (!is_teacher() && !is_admin()) {
                debugging('group is not unique');
            } else {
                $groupname = '';
            }
            break;
    }

    return $groupname;
}

/**
 * Restricts a list of groups to the groups that are required by the access restrictions
 * (availability conditions) of the course module.
 *
 * Only positive (non-negated) 'group' and 'grouping' conditions are considered.
 * If the restrictions do not reference any of the given groups, the list is returned unchanged.
 *
 * @param \cm_info $cm course module
 * @param array $groups groups (indexed by group id)
 * @return array filtered groups (indexed by group id)
 */
function filter_groups_by_availability($cm, array $groups) {
    if (empty($cm->availability)) {
        return $groups;
    }
    $tree = json_decode($cm->availability);
    if (!is_object($tree)) {
        return $groups;
    }

    $groupids = [];
    collect_availability_groupids($tree, $cm->course, $groupids);
    if (count($groupids) == 0) {
        return $groups;
    }

    $filtered = array_intersect_key($groups, $groupids);
    return count($filtered) > 0 ? $filtered : $groups;
}

/**
 * Collects the ids of all groups referenced by positive group or grouping conditions
 * of an availability (sub)tree.
 *
 * @param \stdClass $node availability tree node (decoded JSON)
 * @param int $courseid course id
 * @param array $groupids collected group ids (as keys)
 */
function collect_availability_groupids($node, $courseid, array &$groupids) {
    if (isset($node->op)) {
        if ($node->op !== '&' && $node->op !== '|') {
            // Negated tree ('!&' or '!|') => ignore.
            return;
        }
        foreach ($node->c ?? [] as $child) {
            collect_availability_groupids($child, $courseid, $groupids);
        }
        return;
    }

    if (!isset($node->type) || empty($node->id)) {
        // No group condition or condition without specific group ('any group').
        return;
    }
    switch ($node->type) {
        case 'group':
            $groupids[$node->id] = true;
            break;
        case 'grouping':
            foreach (groups_get_all_groups($courseid, 0, $node->id, 'g.id') as $group) {
                $groupids[$group->id] = true;
            }
            break;
    }
}


/**
 * Helper function for converting a text area into a CodeMirror window
 *
 * @param string $textareaid html identifier
 * @param string $mode Syntax highlighting mode
 * @param string|null $header header in form
 * @param bool $readonly is input disabled?
 * @param bool $loadjquery does jquery need to be loaded by page?
 */
function as_codemirror($textareaid, $mode = 'java', $header = null, $readonly = false, $loadjquery = true) {
    if (get_config('qtype_proforma', 'usecodemirror')) {
        global $PAGE;
        if ($loadjquery) {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            // Load jquery css file for resizable.
            $PAGE->requires->jquery_plugin('ui-css');
        }

        // The Codemirror editor width is not resized to parent container.
        // so this must be done explicitly in Javascript.
        $PAGE->requires->js_call_amd(
            'qtype_proforma/codemirrorif',
            'initCodemirror',
            [$textareaid, $readonly, $mode, $header, 1]
        );
    }
}


require_once($CFG->dirroot . '/lib/adminlib.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/grader_2.php');
/**
 * Helper class for setting the grader URI with connection test
 */
class admin_setting_configproformagrader extends \admin_setting_configtext {
    /**
     * @var type grader instance
     */
    private $grader = null;
    /**
     * @var XML string connection test result
     */
    private $graderoutput = null;
    /**
     * @var int HTTP code of test response
     */
    private $httpcode = null;

    /** @var null Uri for this config grader */
    private $uri = null;

    /**
     * Constructor
     * @param string $name unique ascii name, either 'mysetting' for settings
     * that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultdirectory default directory location
     */
    public function __construct($name, $visiblename, $description, $defaultdirectory) {
        parent::__construct($name, $visiblename, $description, $defaultdirectory, PARAM_RAW, 50);
    }

    /**
     * Returns XHTML for the field
     *
     * Returns XHTML for the field and also checks whether the URI
     * specified in $data is a valid ProFormA grader
     *
     * @param string $data Uri of grader. Is not used. (Connection
     * data is retrieved from actual configuration data.)
     * @param string $query
     * @return string XHTML field
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $context = (object) [
            'id' => $this->get_id(),
            'name' => $this->get_full_name(),
            'size' => $this->size,
            'value' => $data,
            'showvalidity' => !empty($data),
            'valid' => $data && $this->is_proforma_grader(),
            'forceltr' => $this->get_force_ltr(),
            'response' => $this->get_grader_response(),
        ];

        $element = $OUTPUT->render_from_template('qtype_proforma/setting_configproformagrader', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $default, $query);
    }

    /**
     * create grader object
     * @throws \dml_exception
     */
    private function set_grader() {
        if (!isset($this->grader)) {
            $host = trim(get_config('qtype_proforma', $this->name));
            if (strlen($host) > 0) {
                $path = trim(get_config('qtype_proforma', 'graderuri_path'));
                $this->uri = $host . $path;
                $this->grader = new \qtype_proforma_grader_2($this->uri);
            }
        }
    }
    /**
     * returns a text mesage to be displayed in case of an error
     * when testing the grader connection.
     *
     * @return string
     */
    protected function get_grader_response() {
        $this->set_grader();

        if (!isset($this->uri)) {
            return '';
        }
        if (!isset($this->_result)) {
            [$this->graderoutput, $this->httpcode] = $this->grader->test_connection();
        }

        switch ($this->httpcode) {
            case 0:
                return $this->graderoutput;
            case 200:
                // Show grader info if available.
                try {
                    $response = new \SimpleXMLElement($this->graderoutput, LIBXML_PARSEHUGE);
                    $graderinfo = $response->{'response-meta-data'}->{'grader-engine'};
                    return $graderinfo['name'] . ' ' . $graderinfo['version'];
                } catch (Exception $e) {
                    // Ignore exception.
                    return '';
                }
            case 404:
                return 'HTTP status code 404, check URI';
            default:
                return 'HTTP status code ' . $this->httpcode;
        }
    }

    /**
     * tests the connection to the grader and returns true
     * if ok other wise false.
     *
     * @return bool
     */
    protected function is_proforma_grader(): bool {
        $this->set_grader();
        if (!isset($this->grader)) {
            return false;
        }
        if (!isset($this->_result)) {
            [$this->graderoutput, $this->httpcode] = $this->grader->test_connection();
        }
        // Test for HTTP OK.
        return $this->httpcode == 200;
    }
}

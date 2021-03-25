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
 * @param type $contextid: current course
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
 * @global type $COURSE
 * @return string
 */
function get_groupname_sample() {
    global $COURSE;
    // Get all groupings.
    $groups = groups_get_all_groups($COURSE->id);
    if (isset($groups) and count($groups) > 0) {
        // Return name of first group.
        $group = reset($groups);
        return $group->name;
    }

    // No groups available.
    return '';
}
/**
 * @return string returns the name of the group that the current user belongs to
 */
function get_groupname($courseid) {
    global $USER;
    // Get all groups.
    $groups = groups_get_user_groups($courseid, $USER->id);
    // var_dump($groups);

    // Count groups and fetch name.
    $count = 0;
    $groupname = '???';
    if (count($groups) > 0) {
        foreach ($groups[0] as $group) {
            $count++;
            $groupname = groups_get_group_name($group);
        }
    }

    switch ($count) {
        case 0:
            return 'N/A'; // No group found.
        case 1:
            // Exactly one group found.
            break;
        default:
            // More than one group found.
            debugging('group is not unique');
            break;
    }

    return $groupname;
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
        if ($loadjquery) {
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            // Load jquery css file for resizable.
            $PAGE->requires->jquery_plugin('ui-css');
        }

        // Different handling for different moodle versions.
        $moodleversion = $CFG->version;
        if ($moodleversion > 2018051700) {
            // Starting from Moodle 3.5 the Codemirror editor width is not resized to parent container.
            // so this must be done explicitly in Javascript.
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                    array($textareaid, $readonly, $mode, $header, 1));
        } else {
            // In 3.4 resizing must be prohibited because the window is too small.
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                    array($textareaid, $readonly, $mode, $header));
        }
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
    private $_grader = null;
    /**
     * @var XML string connection test result
     */
    private $_graderoutput = null;
    /**
     * @var int HTTP code of test response
     */
    private $_httpcode = null;

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
        $this->_grader = new \qtype_proforma_grader_2();
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
    public function output_html($data, $query='') {
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
     * returns a text mesage to be displayed in case of an error
     * when testing the grader connection.
     *
     * @return string
     */
    protected function get_grader_response() {
        if (!isset($this->_result)) {
            list($this->_graderoutput, $this->_httpcode) = $this->_grader->test_connection();
        }

        switch ($this->_httpcode) {
            case 0:
                return $this->_graderoutput;
            case 200:
                // Show grader info if available.
                try {
                    $response = new \SimpleXMLElement($this->_graderoutput, LIBXML_PARSEHUGE);
                    $graderinfo = $response->{'response-meta-data'}->{'grader-engine'};
                    return $graderinfo['name'] . ' ' . $graderinfo['version'];
                } catch (Exception $e) {
                    // Ignore exception.
                    return '';
                }
            case 404:
                return 'HTTP status code 404, check URI';
            default:
                return 'HTTP status code ' . $this->_httpcode;
        }
    }

    /**
     * tests the connection to the grader and returns true
     * if ok other wise false.
     *
     * @return bool
     */
    protected function is_proforma_grader() : bool {
        if (!isset($this->_result)) {
            list($this->_graderoutput, $this->_httpcode) = $this->_grader->test_connection();
        }
        // Test for HTTP OK.
        return $this->_httpcode == 200;
    }
}


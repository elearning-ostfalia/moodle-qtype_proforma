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
require_once($CFG->dirroot . "/question/engine/bank.php");

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;


class taskeditor extends \external_api {
    /**
     * Get task URL
     * @param int $questionid proforma question id
     * @return array result (boolean) with log messages
     */
    public static function get_task_url($itemid) {
        $params = self::validate_parameters(self::get_task_url_parameters(), ['itemid' => $itemid]);
        if (empty($params) or count($params) == 0) {
            throw new \invalid_parameter_exception('invalid item id');
        }
        $itemid = $params['itemid'];
/*
        $questionid = $params['questionid'];
        $question = \question_bank::load_question($questionid);
        if ($question == null) {
            throw new \invalid_parameter_exception('no question');
        }
        if (get_class($question) != 'qtype_proforma_question') {
            throw new \invalid_parameter_exception('invalid question type');
        }

        // security checks
        $contextid = $DB->get_field('question_categories', 'contextid', array('id'=>$question->category));
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);
        if (isset($context)) {
            self::validate_context($context);
            require_capability('moodle/question:editmine', $context);
        }


        $task = $question->get_task_file();
        if (!$task instanceof \stored_file) {
            throw new \coding_exception("task variable has wrong class");
        }
        $filename = trim($task->get_filename());
        $filearea = trim($task->get_filearea());
        $fileurl = \moodle_url::make_pluginfile_url($contextid, 'qtype_proforma',
            $filearea, $question->id, '/', $filename);
        // return '<a href="' . $url->out() . '">' . $filename . '</a> ';
*/
        global $DB;
        $records = $DB->get_records('files', ['itemid' => $itemid]);
        if ($records == False || count($records) == 0) {
            // Scenario with new task (task does not yet exist)
/*            $fileurl = \moodle_url::make_draftfile_url($itemid, '/', 'nofile');
            $fileurl = $fileurl->out();
            // $fileurl = 'http://www.moodle.org/index.html';

            return [
                'itemid' => $itemid,
                'fileurl' => $fileurl,
                'message' => 'ok'
            ];*/
            throw new \invalid_parameter_exception('invalid task itemid (no draft file)');
        }

//        var_dump($records);
        $filename = '';
        $filepath = '';
        foreach($records as $record) {
            if ($record->filearea != 'draft') {
                throw new \invalid_parameter_exception('invalid task itemid (no draft file)');
            }
            if ($record->component != 'user') {
                throw new \invalid_parameter_exception('invalid task itemid (no user file)');
            }
            if ($record->filename == '.') {
                // skip folder record
                continue;
            }
            $filepath = $record->filepath;
            $filename = $record->filename;
        }

        $fileurl = \moodle_url::make_draftfile_url($itemid, $filepath, $filename);
        $fileurl = $fileurl->out();
        // $fileurl = 'http://www.moodle.org/index.html';

        return [
            'itemid' => $itemid,
            'fileurl' => $fileurl,
            'message' => 'ok'
        ];
    }

    /**
     * Get supported JUnit versions
     * @return array with Junit Versions
     */
    public static function get_junit_versions() {
        require_login();

        $versionlist = get_config('qtype_proforma', 'junitversion');
        $versions = [];
        foreach (explode(',', $versionlist) as $version) {
            $versions[] = trim($version);
        }

        return [ 'junitversions' => $versions ];
    }

    /**
     * Get supported Java versions
     * @return array with Junit Versions
     */
    public static function get_java_versions() {
        require_login();

        $versionlist = get_config('qtype_proforma', 'javaversion');
        $versions = [];
        foreach (explode(',', $versionlist) as $version) {
            $versions[] = trim($version);
        }

        return [ 'javaversions' => $versions ];
    }

    /**
     * Get supported Checkstyle versions
     * @return array with Junit Versions
     */
    public static function get_checkstyle_versions() {
        require_login();

        $versionlist = get_config('qtype_proforma', 'checkstyleversion');
        $versions = [];
        foreach (explode(',', $versionlist) as $version) {
            $versions[] = trim($version);
        }

        return [ 'checkstyleversions' => $versions ];
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_task_url_parameters() {
        return new external_function_parameters([
            "itemid"=> new external_value(PARAM_INT, 'draft ID of ProformA question', VALUE_REQUIRED),
        ]);
    }

    public static function get_junit_versions_parameters() {
        return new external_function_parameters([
        ]);
    }

    public static function get_checkstyle_versions_parameters() {
        return new external_function_parameters([
        ]);
    }

    public static function get_java_versions_parameters() {
        return new external_function_parameters([
        ]);
    }

    public static function get_task_url_returns() {
        return new external_function_parameters([
            'itemid' => new external_value(PARAM_INT, 'draft itemid'),
            'fileurl' => new external_value(PARAM_URL, 'Taskfile URL'),
            'message' => new external_value(PARAM_RAW, 'error message if any'),
        ]);
    }

    public static function get_junit_versions_returns() {
        return new external_function_parameters (
            array(
                'junitversions' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'JUnit version',
                    VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'Array of JUnit versions', VALUE_DEFAULT, array()),
            )
        );
    }

    public static function get_checkstyle_versions_returns() {
        return new external_function_parameters (
            array(
                'checkstyleversions' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Checkstyle version',
                        VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'Array of Checkstyle versions', VALUE_DEFAULT, array()),
            )
        );
    }

    public static function get_java_versions_returns() {
        return new external_function_parameters (
            array(
                'javaversions' => new external_multiple_structure(
                    new external_value(PARAM_TEXT, 'Java version',
                        VALUE_REQUIRED, '', NULL_NOT_ALLOWED),
                    'Array of Java versions', VALUE_DEFAULT, array()),
            )
        );
    }
}

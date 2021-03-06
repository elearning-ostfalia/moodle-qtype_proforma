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
 * The ProFormA Question Restoration functions
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * restore plugin class that provides the necessary information
 * needed to restore one proforma qtype plugin
 */
class restore_qtype_proforma_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        return array(
                new restore_path_element('proforma', $this->get_pathfor('/proforma'))
        );
    }

    /**
     * Process the qtype/proforma element
     */
    public function process_proforma($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        if (!isset($data->responsetemplate)) {
            $data->responsetemplate = '';
        }
        /*
        if (!isset($data->responserequired)) {
            $data->responserequired = 1;
        }
        if (!isset($data->attachmentsrequired)) {
            $data->attachmentsrequired = 0;
        }*/

        // Detect if the question is created or mapped.
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question')) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_proforma too.
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record('qtype_proforma_options', $data);
            $this->set_mapping('qtype_proforma', $oldid, $newitemid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {
        return array(
                new restore_decode_content('qtype_proforma_options', 'comment', 'qtype_proforma'),
        );
    }

    /**
     * When restoring old data, that does not have the proforma options information
     * in the XML, supply defaults.
     */
    protected function after_execute_question() {
        global $DB;

        $proformaswithoutoptions = $DB->get_records_sql("
                    SELECT *
                      FROM {question} q
                     WHERE q.qtype = ?
                       AND NOT EXISTS (
                        SELECT 1
                          FROM {qtype_proforma_options}
                         WHERE questionid = q.id
                     )
                ", array('proforma'));

        foreach ($proformaswithoutoptions as $q) {
            throw new coding_exception('qtype_proforma_options do not exist for question ' . $q->id);

            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->responseformat = 'editor';
            $defaultoptions->responsefieldlines = 15;
            $defaultoptions->attachments = 0;
            $defaultoptions->comment = '';
            $defaultoptions->commentformat = FORMAT_HTML;
            $defaultoptions->responsetemplate = '';
            $DB->insert_record('qtype_proforma_options', $defaultoptions);
        }
    }
}

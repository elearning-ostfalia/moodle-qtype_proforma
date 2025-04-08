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

define("PROFORMA_OPTION", "proforma");

/**
 * restore plugin class that provides the necessary information
 * needed to restore one proforma qtype plugin
 */
class restore_qtype_proforma_plugin extends restore_qtype_plugin {

    //TODO: proforma_option in proforma umbenennen

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        $paths = [];

        // Add options to the restore structure.
        $elename = PROFORMA_OPTION;
        $elepath = $this->get_pathfor('/' . PROFORMA_OPTION);
        // We used get_recommended_name() so this works.
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And return the paths.
//        return array(
//                new restore_path_element('proforma', $this->get_pathfor('/proforma'))
//        );
    }

    public function process_proforma($data) {
        global $DB;

        $data = (object)$data;

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to insert a new options record.
        if ($questioncreated) {
            $data->questionid = $newquestionid;

            // Insert the record.
            $DB->insert_record("qtype_proforma_options", $data);
        }
        // Nothing to remap if the question already existed.
    }


        /**
     * Process the qtype/proforma element
     */
        /*
     public function process_proforma($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        if (!isset($data->responsetemplate)) {
            $data->responsetemplate = '';
        }

        //if (!isset($data->responserequired)) {
        //    $data->responserequired = 1;
        //}
        //if (!isset($data->attachmentsrequired)) {
        //    $data->attachmentsrequired = 0;
        // }

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
*/
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
                    SELECT q.*
                      FROM {question} q
                      JOIN {backup_ids_temp} bi ON bi.newitemid = q.id
                 LEFT JOIN {qtype_proforma_options} qeo ON qeo.questionid = q.id
                     WHERE q.qtype = ?
                       AND qeo.id IS NULL
                       AND bi.backupid = ?
                       AND bi.itemname = ?
                   ", array('proforma', $this->get_restoreid(), 'question_created'));

        foreach ($proformaswithoutoptions as $q) {
            throw new coding_exception('qtype_proforma_options do not exist for question ' . $q->id);
/*
            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->responseformat = 'editor';
            $defaultoptions->responsefieldlines = 15;
            $defaultoptions->attachments = 0;
            $defaultoptions->comment = '';
            $defaultoptions->commentformat = FORMAT_HTML;
            $defaultoptions->responsetemplate = '';
            $DB->insert_record('qtype_proforma_options', $defaultoptions);*/
        }
    }

    #[\Override]
    public static function convert_backup_to_questiondata(array $backupdata): \stdClass {
        $questiondata = parent::convert_backup_to_questiondata($backupdata);
//        var_dump($questiondata);
        $questiondata->options = (object) $backupdata["plugin_qtype_proforma_question"][PROFORMA_OPTION][0];
        return $questiondata;
    }


    #[\Override]
    protected function define_excluded_identity_hash_fields(): array {
        return [
            '/options/id',
            '/options/questionid',
        ];
    }

}

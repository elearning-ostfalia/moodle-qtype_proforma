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
// along with ProFormA Question Type for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The ProFormA Question Backup Functions  
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');


/**
 * Provides the information to backup proforma questions
 *
 */
class backup_qtype_proforma_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'proforma');

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures.
        $dummy = new qtype_proforma();

        $extra_fields = $dummy->extra_question_fields();
        array_shift($extra_fields); // remove first element (= table name)

        $extra_fields[] = 'comment';
        $extra_fields[] = 'commentformat';

        $proforma = new backup_nested_element('proforma', array('id'), $extra_fields);

        // Now the own qtype tree.
        $pluginwrapper->add_child($proforma);

        // Set source to populate the data.
        $proforma->set_source_table('qtype_proforma_options',
                array('questionid' => backup::VAR_PARENTID));

        // Don't need to annotate ids nor files.

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        $result = array (
                qtype_proforma::FILEAREA_COMMENT => 'question_created',
                qtype_proforma::FILEAREA_TASK => 'question_created',
        );

        foreach (qtype_proforma::fileareas_with_model_solutions() as $filearea => $value) {
            $result[$filearea] = 'question_created';
        }

        return $result;
    }
}

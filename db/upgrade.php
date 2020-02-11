<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * ProFormA question type upgrade code.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the proforma question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_proforma_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager ();
    $table = new xmldb_table('qtype_proforma_options');

    if ($oldversion < 2017122001) {
        // Define field filetypes to be added to question_proforma_options.
        $field = new xmldb_field('filetypes', XMLDB_TYPE_TEXT, null, null, null, null, null, 'maxbytes');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2017122001, 'qtype', 'proforma');
    }

    if ($oldversion < 2018010500) {
        // rename taskfilename to responsefilename
        $field = new xmldb_field('taskfilename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'taskpath');

        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'responsefilename');
        }

        // add new fields taskfilename (with different meaning) and downloads
        $field = new xmldb_field('taskfilename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('downloads', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018010500, 'qtype', 'proforma');
    }

    if ($oldversion < 2018011000) {
        // rename taskfilename to responsefilename
        $field = new xmldb_field('taskfilename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'taskpath');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, 'taskfilename');
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018011000, 'qtype', 'proforma');
    }

    if ($oldversion < 2018011700) {
        // rename taskfilename to responsefilename
        $field = new xmldb_field('taskfilename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'taskpath');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, 'taskfilename');
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018011700, 'qtype', 'proforma');
    }

    if ($oldversion < 2018012300) {
        // split downloads into seperate fields
        $field = new xmldb_field('downloads', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, 'downloads');
        }

        $field = new xmldb_field('templates', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('instructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'templates');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('libraries', XMLDB_TYPE_TEXT, null, null, null, null, null, 'instructions');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018012300, 'qtype', 'proforma');
    }

    if ($oldversion < 2018041200) {
        // split downloads into seperate fields
        $field = new xmldb_field('modelsolfiles', XMLDB_TYPE_TEXT, null, null, null, null, null, 'libraries');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('taskfilename', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018041200, 'qtype', 'proforma');
    }

    if ($oldversion < 2018051100) {
        // - remove modelsolution
        // - add taskstorage
        // - rename graderinfo -> comment
        $field = new xmldb_field('modelsolution');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field, 'modelsolution');
        }

        $field = new xmldb_field('taskstorage', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'taskfilename');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('graderinfo', XMLDB_TYPE_TEXT, null, null, null, null, null, 'filetypes');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'comment');
        }
        $field = new xmldb_field('graderinfoformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 0, 'comment');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'commentformat');
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018051100, 'qtype', 'proforma');
    }

    if ($oldversion < 2018051101) {

        // INTERNAL
        $field = new xmldb_field('inputwithfile');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018051101, 'qtype', 'proforma');
    }

    if ($oldversion < 2018121800) {
        // grading hints
        $field = new xmldb_field('gradinghints', XMLDB_TYPE_TEXT, null, null, null, null, null, 'programminglanguage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018121800, 'qtype', 'proforma');
    }

    if ($oldversion < 2018121901) {
        // grading hints
        $field = new xmldb_field('downloads', XMLDB_TYPE_TEXT, null, null, null, null, null, 'taskstorage');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('displayfiles', XMLDB_TYPE_TEXT, null, null, null, null, null, 'downloads');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // merge columns instructions and libraries to downloads
        $DB->execute('UPDATE {qtype_proforma_options} '.
                'SET downloads = ' .
                $DB->sql_concat(' instructions ', '","' , 'libraries') .
                ' WHERE libraries is not null and instructions is not null');
        $DB->execute('UPDATE {qtype_proforma_options} '.
                        'SET downloads = libraries ' .
                        'WHERE instructions is null or instructions = ""');
        $DB->execute('UPDATE {qtype_proforma_options} '.
                'SET downloads = instructions ' .
                'WHERE libraries is null or libraries = ""');

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018121901, 'qtype', 'proforma');
    }

    if ($oldversion < 2018122107) {
        // proformaversion

        // maybe default values are not set!!
        $field = new xmldb_field('proformaversion', XMLDB_TYPE_TEXT, null, null, null, null, null, 'gradinghints');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('aggregationstrategy', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, 1, 'modelsolfiles');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->execute('UPDATE {qtype_proforma_options} '.
                'SET proformaversion = "1.0.1" ' .
                'WHERE proformaversion is null');

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018122107, 'qtype', 'proforma');
    }

    if ($oldversion < 2018122701) {
        require_once(__DIR__ . '/upgradelib.php');
        // merge fileareas to download
        update_proforma_download_filearea();
        // DO NOT DO THIS unless the hash for the files is converted!
        // $DB->execute('UPDATE {files} '.
        // 'SET filearea = "download" ' .
        // 'WHERE (filearea = "library" or filearea = "instruction") and component ="qtype_proforma"');

        $field = new xmldb_field('instructions', XMLDB_TYPE_TEXT, null, null, null, null, null, 'templates');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'instructions_unused');
        }
        $field = new xmldb_field('libraries', XMLDB_TYPE_TEXT, null, null, null, null, null, 'instructions');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'libraries_unused');
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018122701, 'qtype', 'proforma');
    }

    if ($oldversion < 2018122706) {
        require_once(__DIR__ . '/upgradelib.php');
        // get grading hints form file
        initialise_proforma_gradinghints();

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2018122706, 'qtype', 'proforma');
    }

    if ($oldversion < 2019111901) {
        // do not force taskrepository and taskpath to have a value.
        $field = new xmldb_field('taskrepository', XMLDB_TYPE_TEXT, null, null, null, null, null, 'responsetemplate');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }
        $field = new xmldb_field('taskpath', XMLDB_TYPE_TEXT, null, null, null, null, null, 'responsetemplate');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2019111901, 'qtype', 'proforma');
    }

    // Version control access
    if ($oldversion < 2020021100) {
        // version control fields
        $field = new xmldb_field('vcsuritemplate', XMLDB_TYPE_TEXT, null, null, null, null, null, 'responsetemplate');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('vcslabel', XMLDB_TYPE_TEXT, null, null, null, null, null, 'vcsuritemplate');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // ProFormA savepoint reached.
        upgrade_plugin_savepoint(true, 2020021100, 'qtype', 'proforma');
    }


    return true;
}

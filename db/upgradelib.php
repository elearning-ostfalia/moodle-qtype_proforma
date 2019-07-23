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
 * Upgrade library code for the proforma question type.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();


// copy SimpleXmlWriter class into this file because other plugin code is
// not available during update!
// rename to UpgradeSimpleXmlWriter in order to avoid name clashes
class UpgradeSimpleXmlWriter extends XMLWriter {

    public function create_attribute($name, $value) {
        $this->startAttribute($name);
        $this->text($value);
        $this->endAttribute();
    }

    public function create_childelement_with_text($name, $text) {
        $this->startElement($name);
        $this->text($text);
        $this->endElement();
    }
}

function extract_proformatask($category, $questionid, $taskfilename) {

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    echo 'Q/C/T=' . $questionid . ' / ' . $category . ' / ' . $taskfilename . '<br>';
    $file = $fs->get_file($category, 'qtype_proforma', 'task',
            $questionid, '/', $taskfilename);
    if (!$file) {
        echo '<red><b>ERROR: could not find task file ' . $taskfilename . '</b></red><br>';
        return null;
    }

    $uniquecode = time();
    $tempdir = make_temp_directory('proforma_import/' . $uniquecode);
    try {
        $files = $file->extract_to_pathname(get_file_packer('application/zip'), $tempdir);
        if (!$files) {
            throw new coding_exception("could not extract zip file");
        }

        $filenames = array();
        $iterator = new DirectoryIterator($tempdir);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_BASENAME)) == 'task.xml') {
                $filenames[] = $fileinfo->getFilename();
            }
        }
        if (!$filenames) {
            throw new moodle_exception(get_string('noproformafile', 'qtype_proforma'));
        }

        $contents = file_get_contents($tempdir . '/' . $filenames[0]);

    } catch (Exception $e) {
        fulldelete($tempdir);
        throw $e;
    } finally {
        fulldelete($tempdir);
    }

    $task = new SimpleXMLElement($contents);
    unset($contents); // No need to keep this in memory.

    $xw = new UpgradeSimpleXmlWriter();
    $xw->openMemory();

    $xw->setIndent(1);
    $xw->setIndentString(' ');

    $xw->startDocument('1.0', 'UTF-8');

    $xw->startElement('grading-hints');
    // $xw->createAttribute('xmlns', 'urn:proforma:v2.0');

    $xw->startElement('root');
    $xw->create_attribute('function', 'sum');

    foreach ($task->tests->test as $test) {
        $xw->startElement('test-ref');
        $xw->create_attribute('ref', (string) $test['id']); // $id);
        $xw->create_attribute('weight', '-1');
        $xw->create_childelement_with_text('title', (string) $test->title);
        $xw->endElement(); // test-ref
    }

    $xw->endElement(); // root
    $xw->endElement(); // grading-hints

    $xw->endDocument();
    $gradinghints = $xw->outputMemory();
    return $gradinghints;
}

function initialise_proforma_gradinghints() {
    global $DB;

    $questions = $DB->get_recordset_sql("SELECT * " .
            "FROM {qtype_proforma_options} " .
            "WHERE gradinghints is null and taskstorage = 1");
    foreach ($questions as $question) {
        if (isset($question->gradinghints) && strlen(trim($question->gradinghints)) > 0) {
            // grading hints are set => finished
            continue;
        }

        // grading hints are missing
        $contextid = get_proforma_contextid($DB, $question->questionid);
        $gradinghints = extract_proformatask($contextid, $question->questionid, $question->taskfilename);
        if (isset($gradinghints)) {
            echo '<xmp>' . $gradinghints . '</xmp><br>';
        }
        $DB->execute('UPDATE {qtype_proforma_options} ' .
                'SET gradinghints = ? WHERE gradinghints is null and questionid = ?',
                array($gradinghints, $question->questionid));
    }
}

function update_proforma_download_filearea() {
    global $DB;

    $fs = get_file_storage();
    // read questions
    $questions = $DB->get_records('qtype_proforma_options');
    foreach ($questions as $question) {
        if (!isset($question->instructions) || strlen($question->instructions) == 0) {
            if (!isset($question->libraries) || strlen($question->libraries) == 0) {
                continue;
            }
        }
        $itemid = $question->questionid;
        echo 'Q/C =' . $itemid;
        // read contextid
        $contextid = get_proforma_contextid($DB, $itemid);
        echo '/' . $contextid . '<br>';

        $files = explode(',', $question->instructions);
        if (count($files) > 0) {
            echo ' instructions=' . $question->instructions . PHP_EOL;
            $count = 0;
            $oldfiles = $fs->get_area_files($contextid, 'qtype_proforma', 'instruction', $itemid, 'id', false);
            foreach ($oldfiles as $oldfile) {
                echo "FS[" . $oldfile->get_filename() . "]";
                $filerecord = new stdClass();
                $filerecord->filearea = 'download';
                $fs->create_file_from_storedfile($filerecord, $oldfile);
                $count += 1;
            }
            if ($count) {
                $fs->delete_area_files($contextid, 'qtype_proforma', 'instruction', $itemid);
                if ($count <> count($files)) {
                    echo 'WARNING: INS count ' . $count . '<> count ' . count($files) . '<br>';
                }
            }
        }
        echo '<br>';

        $files = explode(',', $question->libraries);
        if (count($files) > 0) {
            echo ' libs=' . $question->libraries . PHP_EOL;

            $count = 0;
            $oldfiles = $fs->get_area_files($contextid, 'qtype_proforma', 'library', $itemid, 'id', false);
            foreach ($oldfiles as $oldfile) {
                echo "FS[" . $oldfile->get_filename() . "]";

                $filerecord = new stdClass();
                $filerecord->filearea = 'download';
                $fs->create_file_from_storedfile($filerecord, $oldfile);
                $count += 1;
            }
            if ($count) {
                $fs->delete_area_files($contextid, 'qtype_proforma', 'instruction', $itemid);
                if ($count <> count($files)) {
                    echo 'WARNING: LIB count ' . $count . '<> count ' . count($files) . '<br>';
                }
            }
        }
        echo '<br>';
    }
}

/**
 * @param $DB
 * @param $itemid
 * @return null
 * @throws moodle_exception
 */
function get_proforma_contextid($DB, $itemid) {

    $contextids = $DB->get_recordset_sql(
            "SELECT distinct(contextid) " .
            "FROM {files} " .
            "WHERE component = 'qtype_proforma' and itemid = ?", array($itemid));

    if (count($contextids) !== 1) {
        $contextids->close();
        throw new moodle_exception('database inconsistent: could not find contextid for question ' . $itemid);
    }

    foreach ($contextids as $id) {
        $contextid = $id->contextid;
    }
    $contextids->close();

    if (!isset($contextid)) {
        throw new moodle_exception('database inconsistent: could not find contextid for question ' . $itemid);
    }
    return $contextid;
}


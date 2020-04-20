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

/**
 * rename old file in filearea to new filename (filepath is stored filepath)
 * @param $questionid
 * @param $filename
 * @param $filearea
 * @throws coding_exception
 * @throws dml_exception
 */
function update_filename($questionid, $filename, $filearea) {
    global $DB;

    $count = 0;
    $filename = trim($filename);
    $oldfilename = trim(clean_param($filename, PARAM_FILE));
    $pathparts = pathinfo('/'. $filename);
    $dirname = $pathparts['dirname'];
    $basename = $pathparts['basename'];
    if ($dirname[strlen($dirname) - 1] !== '/') {
        $dirname = $dirname . '/';
    }

    // Get associated file from {files} table
    $files  = $DB->get_recordset_sql("SELECT * " .
            "FROM {files} " .
            "WHERE itemid = :itemid " .
            "AND filename = :filename " .
            "AND filearea = :filearea ",
            ['itemid' => $questionid, 'filearea' => $filearea, 'filename' => $oldfilename]);

    foreach ($files as $file) {
        // echo 'F=(' . $file->filearea . ') ' . $file->filepath . ' '. $file->filename . '<br>';
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($file->id);
        try {
            // Rename file.
            $file->rename($dirname, $basename);
        } catch (file_exception $e) {
            echo 'ERROR OCCURED:' . $e->getMessage() . '<br>';
        }
        $count++;
    }
    $files->close();

    if ($count == 0) {
        // check for new format:
        $files  = $DB->get_recordset_sql("SELECT * " .
                "FROM {files} " .
                "WHERE itemid = :itemid " .
                "AND filename = :filename " .
                "AND filepath = :filepath " .
                "AND filearea = :filearea ",
                ['itemid' => $questionid, 'filearea' => $filearea, 'filename' => $basename, 'filepath' => $dirname]);

        foreach ($files as $file) {
            // Filename is already in new format => ignore.
            // echo 'NEW F=(' . $file->filearea . ') ' . $file->filepath . ' '. $file->filename . '<br>';
            $count++;
        }
        $files->close();
    }

    if ($count != 1) {
        echo 'WARNING: number of files found for "' . $filename . '" ('. $oldfilename . ') in ('. $filearea . ') is ' . $count .
                ' (Question id=' . $questionid . ')<br>';
        // debugging('number of files found for "' . $filename . '" ('. $oldfilename . ') in ('. $filearea . ') is ' . $count);
    }
}

/**
 * converts the filenames of all files to the proper filename (relative path move to in filepath)
 * Sample:
 * old: / - and deostfaliafilename.java (path - filename)
 * new: de/ostfalia/ - filename.java
 *
 * @throws dml_exception
 */
function update_filenames() {
    global $DB;

    // echo 'start updating filenames <br>';
    // Get all questions with a '/' in the filename list.
    $sql = "FROM {qtype_proforma_options} " .
           "WHERE  modelsolfiles  LIKE '%/%' " .
           "OR templates  like '%/%' " .
           "OR downloads like '%/%'";
    $total = $DB->count_records_sql("SELECT COUNT(*) " . $sql);
    $questions = $DB->get_recordset_sql("SELECT * " . $sql);
    $i = 0;
    $pbar = new progress_bar('', $total, true);
    foreach ($questions as $question) {
        // echo 'Q=' . $question->questionid  . ' - ' . $question->modelsolfiles. ' - ' . $question->templates . ' - ' . $question->downloads . '<br>';

        // Examine modelsol filenames:
        foreach (explode(',', $question->modelsolfiles) as $file) {
            if (strpos($file, '/') != false) {
                update_filename($question->questionid, $file, 'modelsol');
            }
        }
        foreach (explode(',', $question->templates) as $file) {
            if (strpos($file, '/') != false) {
                update_filename($question->questionid, $file, 'template');
            }
        }
        foreach (explode(',', $question->downloads) as $file) {
            if (strpos($file, '/') != false) {
                update_filename($question->questionid, $file, 'download');
            }
        }
        $i++;
        $pbar->update($i, $total, "Updating ProFormA filename: $i/$total.");
        // echo '<br>';
    }
    $questions->close();
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


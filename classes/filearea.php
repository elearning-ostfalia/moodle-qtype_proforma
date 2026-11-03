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
 * abstraction of a Moodle filearea in order to ease handling
 *
 * @package    qtype_proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/**
 * Abstraction of a Moodle filearea in order to ease handling.
 */
class qtype_proforma_filearea {
    /**
     * @var string|null name of filearea
     */
    private $name = null;

    /**
     * qtype_proforma_filearea constructor.
     *
     * @param string $name name of qtype_proforma filearea
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * return the name of the filearea
     * @return name|null
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Helper function for splitting filename into filepath and basename for use
     * in file_storage function of Moodle
     * @param string $filename
     * @return array
     */
    public static function split_filename($filename) {
        $pathparts = pathinfo('/' . $filename);
        $filepath = $pathparts['dirname'];
        $filename = $pathparts['basename'];
        if ($filepath[strlen($filepath) - 1] !== '/') {
            $filepath = $filepath . '/';
        }
        return [$filepath, $filename];
    }

    /**
     * for data_preprocessing
     *
     * @param int $contextid
     * @param stdClass $question
     */
    public function prepare_draft($contextid, &$question) {
        $draftid = file_get_submitted_draft_itemid($this->name);
        // For new questions the question id is undefined.
        $questionid = isset($question->id) ? $question->id : null;

        file_prepare_draft_area(
            $draftid,
            $contextid,
            'qtype_proforma',
            $this->name,
            $questionid,
            ['subdirs' => 1]
        );
        $attribute = $this->name;
        $question->$attribute = $draftid;
    }

    /**
     * get all files in filearea
     *
     * @param int $contextid
     * @param int $questionid
     * @return array
     * @throws coding_exception
     */
    public function get_files($contextid, $questionid) {
        $fs = get_file_storage();
        $proformafiles = $fs->get_area_files($contextid, 'qtype_proforma', $this->name, $questionid);
        $files = [];
        foreach ($proformafiles as $file) {
            if ($file->get_filename() != '.' && $file->get_filename() != '..') {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * generates the filename for the user interface
     * @param stored_file $file
     * @return string filename
     */
    private static function get_visible_filename($file) {
        $filename = $file->get_filename();
        $path = $file->get_filepath();
        if ($path == '/') {
            return $filename;
        }

        // Remove leading slash.
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }
        return $path . $filename;
    }

    /**
     * get string with filename list
     *
     * @param int $contextid
     * @param int $questionid
     * @return string
     * @throws coding_exception
     */
    public function get_files_as_stringlist($contextid, $questionid) {
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($contextid, 'qtype_proforma', $this->name, $questionid);
        $files = [];
        foreach ($draftfiles as $file) {
            $filename = $file->get_filename();
            if ($filename != '.' && $filename != '..') {
                $files[] = self::get_visible_filename($file);
            }
        }
        return implode(', ', $files);
    }

    /**
     * returns the filenames as link list
     *
     * @param int $contextid
     * @param int $questionid
     * @return string
     * @throws coding_exception
     */
    public function get_files_as_links($contextid, $questionid) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'qtype_proforma', $this->name, $questionid);
        $links = [];
        foreach ($files as $file) {
            if ($file->get_filename() != '.' && $file->get_filename() != '..') {
                $filename = self::get_visible_filename($file);
                $url = moodle_url::make_pluginfile_url(
                    $contextid,
                    'qtype_proforma',
                    $this->name,
                    $questionid,
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $link = '<a href=' . $url->out() . '>' . $filename . '</a>';
                $links[] = $link;
            }
        }
        return implode(', ', $links);
    }


    /** save draft files to filearea and create value for database column
     * (todo: do we really need a database column for that? redundant)
     *
     * @param stdClass $formdata
     * @param stdClass $options
     * @param string|null $dbcolumn
     * @throws coding_exception
     */
    public function save_draft($formdata, &$options, $dbcolumn) {
        $attribute = $this->name;
        if (isset($formdata->$attribute)) {
            // Save draft files in filearea.
            // 'subdirs' must be set to true because the Java (model solution)
            // files might be stored in subfolders (package path).
            $saveoptions = [];
            $saveoptions['subdirs'] = true;
            file_save_draft_area_files(
                $formdata->$attribute,
                $formdata->context->id,
                'qtype_proforma',
                $this->name,
                $formdata->id,
                $saveoptions
            );
            // Create list of filenames.
            if (isset($dbcolumn)) {
                $options->$dbcolumn = $this->get_files_as_stringlist(
                    $formdata->context->id,
                    $formdata->id
                );
            }
        }
    }

    /**
     * Save text as file with given filename in a user draft area.
     *
     * @param int $contextid user context id
     * @param int $itemid draft item id
     * @param string $filename filename (may include path)
     * @param string $content file content
     * @return bool true if file was created
     */
    public function save_text_to_draft($contextid, $itemid, string $filename, string $content) {
        $fs = get_file_storage();
        if (!empty($content)) {
            [$filepath, $basename] = self::split_filename($filename);
            $filerecord = [
                    'contextid' => $contextid,
                    'component' => 'user',
                    'filearea' => 'draft',
                    'itemid' => $itemid,
                    'filepath' => $filepath,
                    'filename' => $basename,
            ];
            $fs->create_file_from_string($filerecord, $content);
            return true;
        }
        return false;
    }

    /** save text as file with given filename in filearea
     *
     * @param int $contextid
     * @param int|null $itemid ($question->id)
     * @param string $filename
     * @param string $content
     * @return bool
     * @throws \coding_exception
     */
    public function save_textfile($contextid, $itemid, string $filename, string $content) {
        $filearea = $this->name;
        $cleanfilearea = true;
        $fs = get_file_storage();
        // Delete old file (i.e. delete file in same filearea with same name).
        if (!is_null($itemid)) {
            if ($files = $fs->get_area_files($contextid, 'qtype_proforma', $filearea, $itemid)) {
                $cleanfilename = clean_param($filename, PARAM_FILE);
                foreach ($files as $file) {
                    if ($cleanfilearea) {
                        // Clean all files for this question in this filearea.
                        $file->delete();
                    } else {
                        if ($cleanfilename === $file->get_filename()) {
                            $file->delete();
                        }
                    }
                }
            }
        }

        if (!empty($content)) {
            [$filepath, $basename] = self::split_filename($filename);
            $filerecord = [
                    'contextid' => $contextid,
                    'component' => 'qtype_proforma',
                    'filearea' => $filearea,
                    'itemid' => $itemid,
                    'filepath' => $filepath,
                    'filename' => $basename,
            ];
            $fs->create_file_from_string($filerecord, $content);
            return true;
        }
        return false;
    }

    /**
     * Helper function for reading a text file stored 'in Moodle'.
     *
     * @param int $contextid
     * @param string $filename
     * @param int $itemid
     * @return string file content
     */
    public function read_file_content($contextid, $filename, $itemid) {
        $file = $this->get_file($contextid, $filename, $itemid);
        if (!$file) {
            return 'file not found';
        }
        return $file->get_content();
    }

    /**
     * Returns the file with the given filename.
     *
     * @param int $contextid
     * @param string $filename
     * @param int $itemid
     * @return bool|stored_file
     */
    public function get_file($contextid, $filename, $itemid) {
        $filearea = $this->name;
        $fs = get_file_storage();

        [$filepath, $basename] = self::split_filename($filename);
        // Prepare file record object.
        $fileinfo = [
                'contextid' => $contextid,
                'component' => 'qtype_proforma',
                'filearea' => $filearea,
                'itemid' => $itemid,
                'filepath' => $filepath,
                'filename' => $basename,
        ];
        // Get file.
        $file = $fs->get_file(
            $fileinfo['contextid'],
            $fileinfo['component'],
            $fileinfo['filearea'],
            $fileinfo['itemid'],
            $fileinfo['filepath'],
            $fileinfo['filename']
        );
        return $file;
    }
}

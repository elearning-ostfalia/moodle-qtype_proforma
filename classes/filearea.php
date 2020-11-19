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
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();


class qtype_proforma_filearea {

    /**
     * @var null name of filearea
     */
    private $_name = null;

    /**
     * qtype_proforma_filearea constructor.
     *
     * @param $name name of qtype_proforma filearea
     */
    public function __construct($name) {
        $this->_name = $name;
    }

    /**
     * return the name of the filearea
     * @return name|null
     */
    public function get_name() {
        return $this->_name;
    }

    /**
     * Helper function for splitting filename into filepath and basename for use
     * in file_storage function of Moodle
     * @param $filename
     * @return array
     */
    static public function split_filename($filename) {
        $pathparts = pathinfo('/'. $filename);
        $filepath = $pathparts['dirname'];
        $filename = $pathparts['basename'];
        if ($filepath[strlen($filepath) - 1] !== '/') {
            $filepath = $filepath . '/';
        }
        return array($filepath, $filename);
    }

    /**
     * for data_preprocessing
     *
     * @param $contextid
     * @param $question
     */
    public function prepare_draft($contextid, &$question) {
        $draftid = file_get_submitted_draft_itemid($this->_name);
        /* if (!is_numeric($draftid)) {
            throw new coding_exception('qtype_proforma_filearea: invalid draftid');
        }
        if (!is_numeric($contextid)) {
            throw new coding_exception('qtype_proforma_filearea: invalid $contextid');
        } */
        // for new questions the question id is undefined
        $questionid = isset($question->id) ? $question->id : null;

        file_prepare_draft_area($draftid, $contextid, 'qtype_proforma', $this->_name,
                $questionid, array('subdirs' => 1));
        $attribute = $this->_name;
        $question->$attribute = $draftid;
    }

    /**
     * get all files in filearea
     *
     * @param $contextid
     * @param $questionid
     * @return array
     * @throws coding_exception
     */
    public function get_files($contextid, $questionid) {
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($contextid, 'qtype_proforma', $this->_name, $questionid);
        $files = array();
        foreach ($draftfiles as $file) {
            if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                $files[] = $file;
            }
        }
        return $files;
    }

    /**
     * generates the filename for the user interface
     * @param $file object
     * @return string filename
     */
    private static function get_visible_filename($file) {
        $filename = $file->get_filename();
        $path = $file->get_filepath();
        if ($path == '/') {
            // debugging('filename: ' . $filename);
            return $filename;
        }

        // remove starting slash
        if ($path[0] == '/') {
            $path = substr($path, 1);
        }
        // debugging('filename: ' .  $path . $filename);
        return $path . $filename;
    }
    /**
     * get string with filename list
     *
     * @param $contextid
     * @param $questionid
     * @return string
     * @throws coding_exception
     */
    public function get_files_as_stringlist($contextid, $questionid) {
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($contextid, 'qtype_proforma', $this->_name, $questionid);
        $files = array();
        foreach ($draftfiles as $file) {
            $filename = $file->get_filename();
            if ($filename != '.' and $filename != '..') {
                $files[] = self::get_visible_filename($file);
            }
        }
        return implode(', ', $files);
    }

    /**
     * returns the filenames as link list
     *
     * @param $contextid
     * @param $questionid
     * @return string
     * @throws coding_exception
     */
    public function get_files_as_links($contextid, $questionid) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'qtype_proforma', $this->_name, $questionid);
        $links = array();
        foreach ($files as $file) {
            if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                $filename = self::get_visible_filename($file);
                $url = moodle_url::make_pluginfile_url($contextid, 'qtype_proforma',
                        $this->_name, $questionid, $file->get_filepath(), $file->get_filename());
                // $this->_name, $questionid, '/', $filename);
                $link = '<a href=' . $url->out() . '>' . $filename . '</a>';
                $links[] = $link;
            }
        }
        return implode(', ', $links);
    }


    /** save draft files to filearea and create value for database column
     * (todo: do we really need a database column for that? redundant)
     *
     * @param $formdata
     * @param $options
     * @param $dbcolumn
     * @throws coding_exception
     */
    public function save_draft($formdata, &$options, $dbcolumn) {
        $attribute = $this->_name;
        if (isset($formdata->$attribute)) {
            // Save draft files in filearea.
            // 'subdirs' must be set to true because the Java (model solution)
            // files might be stored in subfolders (package path).
            $saveoptions = array();
            $saveoptions['subdirs'] = true;
            file_save_draft_area_files($formdata->$attribute, $formdata->context->id,
                    'qtype_proforma', $this->_name,  $formdata->id, $saveoptions);
            // Create list of filenames.
            if (isset($dbcolumn)) {
                $options->$dbcolumn = $this->get_files_as_stringlist($formdata->context->id,
                        $formdata->id);
            }
        }
    }
    /** save text as file with given filename in filearea
     *
     * @param $contextid
     * @param $questionid
     * @param string $filename
     * @param string $text
     * @throws coding_exception
     */
    /*public function save_textfile($contextid, $itemid, string $filename, string $content) {
        $this->save_as_file($contextid, $filename, $content, $itemid);
    }*/

    /** save text as file with given filename in filearea
     *
     * @param $contextid
     * @param $filearea
     * @param $filename
     * @param $content
     * @param $itemid ($question->id)
     * @param bool $cleanfilearea
     * @return bool
     * @throws \coding_exception
     */
    public function save_textfile($contextid, $itemid, string $filename, string $content) {
        $filearea = $this->_name;
        $cleanfilearea = true;
        $fs = get_file_storage();
        // delete old file
        if (!is_null($itemid)) {
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($contextid, 'qtype_proforma', $filearea, $itemid)) {
                $cleanfilename = clean_param($filename, PARAM_FILE);
                foreach ($files as $file) {
                    if ($cleanfilearea) {
                        // clean all files for this question in this filearea
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
            list($filepath, $basename) = self::split_filename($filename);
            $filerecord = array(
                    'contextid' => $contextid,
                    'component' => 'qtype_proforma',
                    'filearea' => $filearea,
                    'itemid' => $itemid,
                    'filepath' => $filepath,
                    'filename' => $basename,
            );
            $fs->create_file_from_string($filerecord, $content);
            return true;
        }
        return false;
    }

    /**
     * Helper function for reading a text file stored 'in Moodle'.
     *
     * @param $contextid
     * @param $filename
     * @param $itemid
     * @return string file content
     */
    public function read_file_content($contextid, $filename, $itemid) {
        /*
        $filearea = $this->_name;
        $fs = get_file_storage();

        list($filepath, $basename) = self::split_filename($filename);
        // Prepare file record object
        $fileinfo = array(
                'contextid' => $contextid,
                'component' => 'qtype_proforma',
                'filearea' => $filearea,
                'itemid' => $itemid,
                'filepath' => $filepath,
                'filename' => $basename,
        );
        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        */
        $file = $this->get_file($contextid, $filename, $itemid);
        if (!$file) {
            return 'file not found';
        }
        return $file->get_content();
    }

    /** returns the file with the given filename
     * @param $contextid
     * @param $filename
     * @param $itemid
     * @return bool|stored_file
     */
    public function get_file($contextid, $filename, $itemid) {
        $filearea = $this->_name;
        $fs = get_file_storage();

        list($filepath, $basename) = self::split_filename($filename);
        // Prepare file record object
        $fileinfo = array(
                'contextid' => $contextid,
                'component' => 'qtype_proforma',
                'filearea' => $filearea,
                'itemid' => $itemid,
                'filepath' => $filepath,
                'filename' => $basename,
        );
        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        return $file;
    }
}
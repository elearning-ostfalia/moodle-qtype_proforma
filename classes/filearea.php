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
     * for data_preprocessing
     *
     * @param $contextid
     * @param $question
     */
    public function on_preprocess($contextid, &$question) {
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
                $questionid, array('subdirs' => 0));
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
            if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                $files[] = $file->get_filename();
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
                $url = moodle_url::make_pluginfile_url($contextid, 'qtype_proforma',
                        $this->_name, $questionid, '/', $file->get_filename());
                $link = '<a href=' . $url->out() . '>' . $file->get_filename() . '</a>';
                $links[] = $link;
            }
        }
        return implode(', ', $links);
    }


    /*
     * save draft files to filearea and create value for database column
     * (todo: do we really need a database column for that? redundant)
     */
    public function on_save($formdata, &$options, $dbcolumn) {
        $attribute = $this->_name;
        if (isset($formdata->$attribute)) {
            // Save draft files in filearea.
            file_save_draft_area_files($formdata->$attribute, $formdata->context->id,
                    'qtype_proforma', $this->_name,  $formdata->id);
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
    public function save_textfile($contextid, $questionid, string $filename, string $text) {
        qtype_proforma\lib\save_as_file($contextid, $this->_name,
                $filename, $text, $questionid, true);
    }
}
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
     * @var null identifier for a user draft area (if created)
     */
    private $_draftid = null;

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
     * @param $context_id
     * @param $question
     */
    public function preprocess($context_id, &$question) {
        $this->_draftid = file_get_submitted_draft_itemid($this->_name);
        file_prepare_draft_area($this->_draftid, $context_id, 'qtype_proforma', $this->_name,
                $question->id, array('subdirs' => 0));
        $attribute = $this->_name;
        $question->$attribute = $this->_draftid;
    }

    /**
     * get all files in filearea
     *
     * @param $context_id
     * @param $question_id
     * @return array
     * @throws coding_exception
     */
    public function get_files($context_id, $question_id) {
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($context_id, 'qtype_proforma', $this->_name, $question_id);
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
     * @param $context_id
     * @param $question_id
     * @return string
     * @throws coding_exception
     */
    public function get_files_as_stringlist($context_id, $question_id) {
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($context_id, 'qtype_proforma', $this->_name, $question_id);
        $files = array();
        foreach ($draftfiles as $file) {
            if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                $files[] = $file->get_filename();
            }
        }
        return implode(',', $files);
    }

    /**
     * save (= copy/move?) draft files as component files.
     * @param $draft_id
     * @param $context_id
     * @param $question_id
     */
    public function save_draft_files($draft_id, $context_id, $question_id) {
        file_save_draft_area_files($draft_id, $context_id,
                'qtype_proforma', $this->_name, $question_id);
    }

    /** save text as file with given filename in filearea
     *
     * @param $context_id
     * @param $question_id
     * @param string $filename
     * @param string $text
     * @throws coding_exception
     */
    public function save_textfile($context_id, $question_id, string $filename, string $text) {
        qtype_proforma\lib\save_as_file($context_id, $this->_name,
                $filename, $text, $question_id, true);
    }
}
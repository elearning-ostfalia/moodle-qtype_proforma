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
 * The ProFormA Question definition
 *
 * @package    qtype_proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @copyright  2005 Mark Nielsen for ???
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Mark Nielsen, K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/java_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/setlx_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/python_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/proforma_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/question.php');

/**
 * The proforma question type.
 *
 */
class qtype_proforma extends question_type {

    // We use separate file areas to be able to handle different purposes
    // differently in the future.

    /**
     * Filearea for code templates.
     */
    const FILEAREA_TEMPLATE = 'template';
    /**
     * Filearea for question attachments downloadable by student.
     */
    const FILEAREA_DOWNLOAD = 'download';
    /**
     * Filearea for files that are visible to students (currently not used).
     */
    const FILEAREA_DISPLAY = 'display';
    /**
     * Filearea for model solution files.
     */
    const FILEAREA_MODELSOL = 'modelsol';
    /**
     * Filearea for task file.
     */
    const FILEAREA_TASK = 'task';
    /**
     * Filearea for files needed by comment field.
     */
    const FILEAREA_COMMENT = 'comment';


    // Where is taskfile stored?
    /**
     * Taskfile is imported and then stored as file in Moodle Data.
     */
    const PERSISTENT_TASKFILE = 1;
    /**
     * Taskfile is stored in external repostory.
     * Currently not supported.
     */
    const REPOSITORY = 2;
    /**
     * Question is created in Moodle Form Editor.
     * Taskfile could be created on the fly and is only stored for caching purposes.
     */
    /** task file that is not yet completed */
    const SELECT_TASKFILE = 0;
    const VOLATILE_TASKFILE = 3; // Deprecated: Use JAVA_TASKFILE instead.
    /** Java task file */
    const JAVA_TASKFILE = self::VOLATILE_TASKFILE;
    /** SetlX task file */
    const SETLX_TASKFILE = 4;
    /** c task file */
    const C_TASKFILE = 5;
    /** C++ task file */
    const CPP_TASKFILE = 6;
    /** Python task file */
    const PYTHON_TASKFILE = 7;

    // Collapse options
    /**
     * always collapse feedback
     */
    const ALWAYS_COLLPASE = 0;
    /**
     * always expand feedback (for simple tests)
     */
    const ALWAYS_EXPAND = 1;
    /**
     * expand feedback only for students view, collapse for teacher view
     */
    const EXPAND_STUDENT = 2;
    /**
     * expand feedback only for teacher view, collapse for student view
     */
    const EXPAND_TEACHER = 3;
    /**
     * expand feedback only for small content of collapsible region
     * (what is small?)
     */
    const EXPAND_SMALL = 4;


    // How is the mark calculated?
    /**
     * 1 in case all tests have been passed, otherwise 0.
     */
    const ALL_OR_NOTHING = 1;
    /**
     * Weighted sum of all test results.
     */
    const WEIGHTED_SUM = 2;

    // Response Options.
    /** File upload. */
    const RESPONSE_FILEPICKER = 'filepicker';
    /** Editor. */
    const RESPONSE_EDITOR = 'editor';
    /** Get submission from version control. */
    const RESPONSE_VERSION_CONTROL = 'versioncontrol';
    /** Get submission from kind of explorer. */
    const RESPONSE_EXPLORER = 'explorer';

    // Version Control Systems.
    const VCS_GIT = 1;
    const VCS_SVN = 2;

    /**
     * Function returns array with fileareas containing files visible to students.
     *
     * @return array with filearea data
     */
    public static function fileareas_for_studentfiles() {
        return array(
                self::FILEAREA_TEMPLATE => array(
                        "files" => "templatefiles",
                        "dbcolumn" => "templates",
                        "formlist" => "templatelist",
                ),
                self::FILEAREA_DOWNLOAD => array(
                        "files" => "downloadfiles", // Tag in xml export.
                        "dbcolumn" => "downloads", // Name of question attribute resp. database column.
                        "formlist" => "downloadlist", // Name of bound input in edit form.
                )
        );
    }

    /**
     * Function contains all fileareas containing files visible to students
     * and the filearea with the model solution files.
     *
     * @return array with filearea data
     */
    public static function fileareas_with_model_solutions() {
        $fileareas = self::fileareas_for_studentfiles();
        $fileareas[self::FILEAREA_MODELSOL] = array(
                "files" => "modelsolutionfiles",
                "dbcolumn" => "modelsolfiles",
                "formlist" => "modelsollist",
        );
        return $fileareas;
    }

    /**
     * Returns all ProFormA specific fileareas.
     *
     * @return array
     */
    public static function proforma_fileareas() {
        $fileareas = self::fileareas_with_model_solutions();
        $fileareas[self::FILEAREA_TASK] = array(
                "files"    => "task",
                "dbcolumn" => "taskfilename",
        );
        return $fileareas;
    }

    /**
     * Defines the table which extends the question table. This allows the base questiontype
     * to automatically save, backup and restore the extra fields.
     *
     * @return an array with the table name (first) and then the column names (apart from id and questionid)
     */
    public function extra_question_fields() {
        $result = array('qtype_proforma_options',
                'uuid',
                'proformaversion',

                'taskrepository',
                'taskpath',

                'taskfilename',
                'responsefilename',
                'programminglanguage',
                'responsetemplate',

                'responseformat',
                'responsefieldlines',
                'attachments',
                'maxbytes',
                'filetypes',
                'taskstorage',

                'aggregationstrategy',
                'gradinghints',

                'vcsuritemplate',
                'vcssystem',
                'vcslabel',

                'expandcollapse',
                'inlinemessages',
                // 'initiallyinline'
                // Attributes 'comment' and 'commentformat' build an array => do not add.
                // 'comment',
                // 'commentformat',
        );

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $result[] = $value['dbcolumn'];
        }

        return $result;
    }

    /**
     * Returns response variable names that may have associated files.
     *
     * @return array
     */
    public function response_file_areas() {
        return array(ATTACHMENTS, ANSWER);
    }

    /**
     * Loads the proforma question data from database.
     *
     * @param object $question
     */
    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_proforma_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }


    /**
     * This function is used to store form data into database AND
     * to store data from question bank import into database.
     *
     * @param object $formdata
     * @throws coding_exception
     */
    public function save_question_options($formdata) {
        global $DB;

        if (isset($formdata->original_template)) {
            // Workaround for a bug (?) in the BEHAT test environment in Moodle 3.6 and later:
            // The Moodle test environment also uses a property
            // named template. So we have problems with a name clash
            // which is handled with an extra property original_template
            // that holds the correct value.
            $formdata->behat_template = $formdata->template;
            $formdata->template = $formdata->original_template;
            if (empty($formdata->template)) {
                // Unset variable if empty in order to avoid problems.
                unset($formdata->template);
            }
        }

        // Save parent data and extra fields.
        parent::save_question_options($formdata);
        $this->save_hints($formdata, false);

        $options = $DB->get_record('qtype_proforma_options', array('questionid' => $formdata->id));
        if (!$options) {
            throw new coding_exception('proforma: save_question_options no database record available');
        }

        // Polymorphic behaviour.
        switch ($formdata->taskstorage) {
            case self::PERSISTENT_TASKFILE:
                $editor = new proforma_form_creator($formdata);
                break;
            case self::JAVA_TASKFILE:
                // Handle 'save' from editor.
                $editor = new java_form_creator($formdata);
                break;
            case self::SETLX_TASKFILE:
                $editor = new setlx_form_creator($formdata);
                break;
            case self::C_TASKFILE:
                $editor = new c_form_creator($formdata);
                break;
            case self::CPP_TASKFILE:
                $editor = new c_form_creator($formdata);
                break;
            case self::PYTHON_TASKFILE:
                $editor = new python_form_creator($formdata);
                break;
            case self::REPOSITORY:
            default:
                throw new coding_exception('proforma: unsupported taskstorage ' . $formdata->taskstorage);
        }

        $editor->save_question_options($options);

        /* we need a different handling for comment:
         *  - array with comment (text, format)
         *  - comment contains only flat text with seperate variable 'commentformat'
         *  (The handling is kept here because import_or_save_files is protected)*/

        if (!empty($formdata->comment['format'])) {
            // Option 'comment' is array (when data comes from form input).
            $options->comment = $this->import_or_save_files($formdata->comment,
                    $formdata->context, 'qtype_proforma', 'comment', $formdata->id);
            $options->commentformat = $formdata->comment['format'];
        } else {
            // Data comes from file import, different internal structure.
            $options->comment = $formdata->comment;
            $options->commentformat = $formdata->commentformat;
        }

        $DB->update_record('qtype_proforma_options', $options);

        if (isset($formdata->original_template)) {
            // Workaround for a bug (?) in the BEHAT test environment.
            // See above.
            $formdata->template = $formdata->behat_template;
        }
    }

    /**
     * initialise a question instance (data not covered by extra_fields??)
     *
     * @param question_definition $question
     * @param object $questiondata
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->comment = $questiondata->options->comment;
        $question->commentformat = $questiondata->options->commentformat;
    }

    /**
     * Delete a question.
     *
     * (parent function also calls delete_files)
     * @param $questionid
     * @param int $contextid
     */
    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_proforma_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public static function response_formats() {
        return array(
            self::RESPONSE_EDITOR => get_string('formateditor', 'qtype_proforma'),
            self::RESPONSE_FILEPICKER => get_string('formatfilepicker', 'qtype_proforma'),
            self::RESPONSE_VERSION_CONTROL => get_string('versioncontrol', 'qtype_proforma'),
            self::RESPONSE_EXPLORER => get_string('formatexplorer', 'qtype_proforma'),
        );
    }

    /**
     * @return array the supported programming languages
     */
    public static function get_proglang_options() {
        return array(
                'java' => "Java",
                'python' => "Python",
                'setlx' => 'SetlX',
                'cpp' => 'C/C++',
                'c' => 'C',
                'none' => get_string('none', 'qtype_proforma'),
        );
    }

    /**
     * @return array the choices that should be offered for the input box size.
     */
    public function response_sizes() {
        $choices = array();
        for ($lines = 5; $lines <= 40; $lines += 5) {
            $choices[$lines] = get_string('nlines', 'qtype_proforma', $lines);
        }
        return $choices;
    }

    /**
     * @return array the choices that should be offered for the number of attachments.
     */
    public function attachment_options() {
        return array(
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
            // Currently unlimited is disabled.
            // To enable set -1 => get_string('unlimited').
        );
    }

    /**
     * Move all files belonging when a question is moved
     *
     * @param int $questionid
     * @param int $oldcontextid
     * @param int $newcontextid
     */
    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $fs = get_file_storage();
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_proforma', self::FILEAREA_COMMENT, $questionid);
        $fs->move_area_files_to_new_context($oldcontextid,
                $newcontextid, 'qtype_proforma', self::FILEAREA_TASK, $questionid);

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $fs->move_area_files_to_new_context($oldcontextid,
                    $newcontextid, 'qtype_proforma', $filearea, $questionid);
        }
    }

    /**
     * Delete all files belongig to a question.
     *
     * @param int $questionid
     * @param int $contextid
     */
    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_proforma', self::FILEAREA_COMMENT, $questionid);
        $fs->delete_area_files($contextid, 'qtype_proforma', self::FILEAREA_TASK, $questionid);

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $fs->delete_area_files($contextid, 'qtype_proforma', $filearea, $questionid);
        }
    }

    /**
     * Whether this question type can perform a frequency analysis of student
     * responses.
     *
     * If this method returns true, you must implement the get_possible_responses
     * method, and the question_definition class must implement the
     * classify_response method.
     *
     * @return bool whether this report can analyse all the student responses
     * for things like the quiz statistics report.
     */
    public function can_analyse_responses() {
        return false;
    }



    /******************** IMPORT/EXPORT FUNCTIONS ***************************/

    // The Moodle Core only supports export for XML and gift format for plugins.
    // The gift format is so simple that it does not make sense to support it.
    // Import is only supported for plugins for XML (?). I have not checked all import formats
    // but gift is not supported so I assume that the others are not supported either.

    /**
     * exports question from question bank to moodle xml
     *
     * @param $data
     * @param $question
     * @param qformat_xml $format
     * @param null $extra
     * @return object
     * @throws coding_exception
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {

        if ($extra != null) {
            throw new coding_exception("proforma:import_from_xml: invalid 'extra' parameter");
        }

        // Set empty answer array in order to prevent error message
        // in call of base class function.
        $data['#']['answer'] = array();
        $qo = parent::import_from_xml($data, $question, $format, $extra);
        // Remember that we come from import.
        $qo->import_process = true;

        // Import hints (is unfortunately not imported by base function).
        $format->import_hints($qo, $data, true, false);

        // Restore files in grader info.
        $comment = $format->import_text_with_files($data,
                array('#', 'comment', 0));
        $qo->comment = $comment['text'];
        $qo->commentformat = $comment['format'];
        // Set defaults if missing
        if (empty($qo->expandcollapse)) {
            $qo->expandcollapse = 0;
        }
        if (empty($qo->inlinemessages)) {
            $qo->inlinemessages = 0;
        }
        if ($qo->responseformat == self::RESPONSE_VERSION_CONTROL and empty($qo->vcssystem)) {
            $qo->vcssystem = qtype_proforma::VCS_SVN;
        }
        // todo: restore $comment['itemid']
        // if (!empty($comment['itemid'])) {
        // $qo->commentitemid = $comment['itemid'];

        // Import files.
        foreach (self::proforma_fileareas() as $filearea => $value) {
            $datafiles = $format->getpath($data,
                    array('#', $value["files"], 0, '#', 'file'), array());
            if (is_array($datafiles)) {
                // Seems like a non-array does occur in some versions of PHP!
                // check for import with old style filenames.
                $dbcolumn = $value['dbcolumn'];
                foreach (explode(',', $qo->$dbcolumn) as $filename) {
                    $filename = trim($filename);
                    if (strpos($filename, '/') != false) {
                        $oldfilename = trim(clean_param($filename, PARAM_FILE));
                        foreach ($datafiles as &$file) { // Note: use reference here!
                            $originalfilename = $format->getpath($file, array('@', 'name'), '', true);
                            if ($originalfilename == $oldfilename) {
                                // Modify filename and path.
                                $pathparts = pathinfo('/'. $filename);
                                $dirname = trim($pathparts['dirname']);
                                $basename = trim($pathparts['basename']);
                                if ($dirname[strlen($dirname) - 1] !== '/') {
                                    $dirname = $dirname . '/';
                                }
                                // $originalfilepath = $format->getpath($file, array('@', 'path'), '/', true);
                                $file['@']['name'] = $basename;
                                $file['@']['path'] = $dirname;
                            }
                        }
                    }
                }
                $qo->$filearea = $format->import_files_as_draft($datafiles);
            }
        }
        return $qo;
    }

    /**
     * Export question to the Moodle XML format
     *
     * @param $question
     * @param qformat_xml $format
     * @param null $extra
     * @return string
     */
    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        // Shall vcsuritemplate and vcslabel be deleted from extra field array because
        // they belong to the course and not to the question???

        // Copy the question so we can modify it for export
        // (Just in case the original gets used elsewhere).
        $questiontoexport = $question; // clone $question;

        $expout = parent::export_to_xml($questiontoexport, $format, $extra);
        // $expout .= "    <DUMMY " . $questiontoexport->modelsolution . ">\n";

        $expout .= "    <comment {$format->format($questiontoexport->options->commentformat)}>\n";
        $expout .= $format->writetext($questiontoexport->options->comment, 3);
        // $expout .= $format->write_files($questiontoexport->options->questiontextfiles);
        $expout .= "    </comment>\n";

        // Export files.
        $fs = get_file_storage();
        $contextid = $question->contextid;

        foreach (self::proforma_fileareas() as $filearea => $value) {
            $datafiles = $fs->get_area_files(
                    $contextid, 'qtype_proforma', $filearea, $question->id);
            $expout .= '<' . $value['files'] . '>' . $format->write_files($datafiles) . "</" . $value['files'] . ">\n";
        }
        $datafiles = $fs->get_area_files(
                $contextid, 'qtype_proforma', self::FILEAREA_COMMENT, $question->id);
        $expout .= '<commentfiles>' . $format->write_files($datafiles) . "</commentfiles>\n";

        return $expout;
    }
}

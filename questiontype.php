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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * The ProFormA Question definition
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2005 Mark Nielsen
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Mark Nielsen, K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/moodlelib.php');
require_once($CFG->dirroot . '/question/type/proforma/proformatask.php');

/**
 * The proforma question type.
 *
 */
class qtype_proforma extends question_type {

    // we use separate file areas to be able to handle different purposes
    // differently in the future
    const FILEAREA_TEMPLATE = 'template';
    const FILEAREA_DOWNLOAD = 'download';
    const FILEAREA_DISPLAY = 'display';

    const FILEAREA_MODELSOL = 'modelsol';

    const FILEAREA_TASK = 'task';

    const FILEAREA_COMMENT = 'comment';


    // Where is taskfile stored?
    // - Taskfile is imported and then stored as file in Moodle Data.
    const PERSISTENT_TASKFILE = 1;
    // - Taskfile is stored in external repostory.
    // Currently not supported.
    const REPOSITORY = 2;
    // - Question is created in Moodle Form Editor.
    // Taskfile could be created on the fly and is only stored for caching purposes.
    const VOLATILE_TASKFILE = 3;

    // How is the mark calculated?
    const ALL_OR_NOTHING = 1;
    const WEIGHTED_SUM = 2;

    public static $testmode = false;

    const RESPONSE_FILEPICKER = 'filepicker';
    const RESPONSE_EDITOR = 'editor';

    // static array for handling file areas
    public static function fileareas() {
        return array(
                self::FILEAREA_TEMPLATE => array(
                        "formid" => "templateid",
                        "files" => "templatefiles",
                        "questionlist" => "templates",
                        "formlist" => "templatelist"
                ),
                self::FILEAREA_DOWNLOAD => array(
                        "formid" => "downloadid", // id created in proforma format after import
                        "files" => "downloadfiles", // tag in xml export
                        "questionlist" => "downloads", // name of question attribute resp. database column
                        "formlist" => "downloadlist" // name of bound input  in edit form
                )
        );
    }

    public static function fileareas_with_model_solutions() {
        $fileareas = self::fileareas();
        $fileareas[self::FILEAREA_MODELSOL] = array(
                "formid" => "modelsolid",
                "files" => "modelsolutionfiles",
                "questionlist" => "modelsolfiles",
                "formlist" => "modelsollist"
        );
        return $fileareas;
    }

    public static function all_fileareas() {
        $fileareas = self::fileareas_with_model_solutions();
        $fileareas[self::FILEAREA_TASK] = array(/*
                "formid" => "taskfiledraftid",
                "files" => "modelsolutionfiles",
                //" questionlist" => "modelsolfiles",
                // "formlist" => "modelsollist"
                */
        );
        $fileareas[self::FILEAREA_COMMENT] = array(/*
                "formid" => "commentid",
                "files" => "modelsolutionfiles",
                // "questionlist" => "modelsolfiles",
                // "formlist" => "modelsollist"
                */
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
                // 'comment', // is an array => do not add
                // 'commentformat',
        );

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $result[] = $value['questionlist'];
        }

        return $result;
    }

    public function response_file_areas() {
        return array('attachments', 'answer');
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_proforma_options',
                array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }


    /**
     * this function is used to store form data into database AND
     * to store data from question bank import into database.
     *
     * @param object $formdata
     * @throws coding_exception
     */
    public function save_question_options($formdata) {
        global $DB;
        $context = $formdata->context;

        parent::save_question_options($formdata);
        $this->save_hints($formdata, false);

        $options = $DB->get_record('qtype_proforma_options', array('questionid' => $formdata->id));
        if (!$options) {
            throw new coding_exception('proforma: save_question_options invalid branch');
        }
        switch ($formdata->taskstorage) {
            case qtype_proforma::PERSISTENT_TASKFILE:
                break;
            case qtype_proforma::VOLATILE_TASKFILE:
                $taskfile = qtype_proforma_proforma_task::create_java_task_file($formdata);
                $options->taskfilename = 'task.xml';
                qtype_proforma_proforma_task::store_task_file($taskfile, $options->taskfilename,
                        $context->id, $formdata->id);
                break;
            case qtype_proforma::REPOSITORY:
            default:
                throw new coding_exception('proforma: unsupported taskstorage ' . $formdata->taskstorage);
        }

        $options->gradinghints = qtype_proforma_proforma_task::create_grading_hints($formdata);

        /*        $hint->hint = $this->import_or_save_files($formdata->hint[$i],
                    $context, 'question', 'hint', $hint->id);
                $hint->hintformat = $formdata->hint[$i]['format'];*/

        // we need a different handling for different variable structure for comment:
        // - array with comment (text, format)
        // - comment contains only flat text with seperate variable 'commentformat'
        if (!empty($formdata->comment['format'])) {
            // $formdata->comment is array (when data comes from form input)
            $options->comment = $this->import_or_save_files($formdata->comment,
                    $context, 'qtype_proforma', 'comment', $formdata->id);
            $options->commentformat = $formdata->comment['format'];
        } else {
            // data comes from file import, different internal structure :-(
            $options->comment = $formdata->comment;
            $options->commentformat = $formdata->commentformat;

        }

        if (isset($formdata->taskfiledraftid)) {
            /* $options->link = */
            file_save_draft_area_files($formdata->taskfiledraftid,
                    $context->id, 'qtype_proforma', self::FILEAREA_TASK, $formdata->id);
        } else if (isset($formdata->taskfile)) {
            question_bank::get_qtype('qtype_proforma')->import_file(
                    $this->importcontext, 'qtype_proforma', 'task', $options->id, $formdata->taskfile);
        }

        // store response template as file (it is stored as file and as member variable
        // in order to support file download and editor template in student view)

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $property = $value['formid'];
            if (isset($formdata->$property)) {
                file_save_draft_area_files($formdata->$property,
                        $context->id, 'qtype_proforma', $filearea, $formdata->id);
            }
        }

        // note! at first store draft files, then override first template file
        if (empty($formdata->templates)) {
            // no templates yet defined but the teacher has entered a template text
            if (!empty($formdata->responsetemplate)) {
                // handle situation where the template is created in moodle for the first time:
                // set dummy template name and store file
                $options->templates = $formdata->templates = 'template.txt';
                $this->save_as_file($context->id, self::FILEAREA_TEMPLATE,
                        $options->templates /*$formdata->responsefilename*/, $formdata->responsetemplate, $formdata->id);
            }

        } else {
            // todo: if $formdata->responsetemplate is empty
            // then delete file and remove filename from template list
            // (coulde be deleted in a row...)
            $templates = explode(',', $formdata->templates);
            if (!$this->save_as_file($context->id, self::FILEAREA_TEMPLATE,
                    $templates[0] /*$formdata->responsefilename*/, $formdata->responsetemplate, $formdata->id)) {
                // no file was stored => delete filename from list
                array_shift($templates);
                $options->templates = $formdata->templates = implode(',', $templates);
                if (count($templates) > 0) {
                    // set text of $formdata->responsetemplate to text of first element
                    // todo: remove variable responsetemplate from database
                    $options->responsetemplate = $formdata->responsetemplate = self::read_file_content($context->id,
                            self::FILEAREA_TEMPLATE, $templates[0], $formdata->id);
                }
            }
        }

        /*
                if (isset($formdata->instructionid)) {
                    file_save_draft_area_files($formdata->instructionid,
                            $context->id, 'qtype_proforma', self::FILEAREA_INSTRUCTION, $formdata->id);
                }
                if (isset($formdata->libraryid)) {
                    file_save_draft_area_files($formdata->libraryid,
                            $context->id, 'qtype_proforma', self::FILEAREA_LIBRARY, $formdata->id);
                }
                if (isset($formdata->modelsolid)) {
                    file_save_draft_area_files($formdata->modelsolid,
                            $context->id, 'qtype_proforma', self::FILEAREA_MODELSOL, $formdata->id);
                }
        */

        $DB->update_record('qtype_proforma_options', $options);
    }

    public static function read_file_content($contextid, $filearea, $filename, $itemid) {
        $fs = get_file_storage();

        // Prepare file record object
        $fileinfo = array(
                'contextid' => $contextid,
                'component' => 'qtype_proforma',
                'filearea' => $filearea,
                'itemid' => $itemid,
                'filepath' => '/',
                'filename' => $filename,
        );
        // Get file
        $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);

        if (!$file) {
            return 'file not found';
        }
        return $file->get_content();
    }

    protected function save_as_file($contextid, $filearea, $filename, $content, $itemid) {
        $fs = get_file_storage();
        // delete old file
        if (!is_null($itemid)) {
            // echo 'delete file "'. $filename . '"<br>';
            $fs = get_file_storage();
            // echo 'save_as_file: ' . $contextid . '/qtype_proforma/' . $filearea . '/' . $itemid . '<br>';
            if ($files = $fs->get_area_files($contextid, 'qtype_proforma', $filearea, $itemid)) {
                $cleanfilename = clean_param($filename, PARAM_FILE);
                // echo 'check for ' . $cleanfilename . '<br>';
                foreach ($files as $file) {
                    if ($cleanfilename === $file->get_filename()) {
                        // $output1 =  'save_as_file: delete "' . $file->get_filename() . '"<br>';
                        // echo $output1;
                        $file->delete();
                    }
                }
            }
        }

        if (!empty($content)) {
            $filerecord = array(
                    'contextid' => $contextid,
                    'component' => 'qtype_proforma',
                    'filearea' => $filearea,
                    'itemid' => $itemid,
                    'filepath' => '/',
                    'filename' => $filename,
            );
            $fs->create_file_from_string($filerecord, $content);
            return true;
        }
        return false;
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->comment = $questiondata->options->comment;
        $question->commentformat = $questiondata->options->commentformat;

    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_proforma_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }

    /**
     * @return array the different response formats that the question type supports.
     * internal name => human-readable name.
     */
    public function response_formats() {
        return array(
            // editor
                'editor' => get_string('formateditor', 'qtype_proforma'),
            // filepicker
                'filepicker' => get_string('formatfilepicker', 'qtype_proforma'),

            // editor with no codemirror
            // 'monospaced' => get_string('formatmonospaced', 'qtype_proforma'),
        );
    }

    public function get_proglang_options() {
        return array(
                'java' => "Java",
                'python' => "Python",
                'setlx' => 'SetlX',
                'c' => 'c',
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
            // 0 => get_string('no'),
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
            // -1 => get_string('unlimited'),
        );
    }

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

        /*
                $fs->move_area_files_to_new_context($oldcontextid,
                        $newcontextid, 'qtype_proforma', self::FILEAREA_MODELSOL, $questionid);
        */
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'qtype_proforma', self::FILEAREA_COMMENT, $questionid);
        $fs->delete_area_files($contextid, 'qtype_proforma', self::FILEAREA_TASK, $questionid);

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $fs->delete_area_files($contextid, 'qtype_proforma', $filearea, $questionid);
        }
        /*
                $fs->delete_area_files($contextid, 'qtype_proforma', self::FILEAREA_MODELSOL, $questionid);
        */
    }

    /*
        public static function extract_data_from_taskfile($contents) {
            $xmldoc = new DOMDocument;
            if (!$xmldoc->loadXML($contents)) { //}, LIBXML_NOERROR )) {
                throw new coding_exception("task file is not xml");
            }

            $tests = array();
            $gradinghints = array();

            $xpath = new DOMXPath($xmldoc);

            $xpath->registerNamespace('dns2','urn:proforma:v2.0');
            $xpathresult=$xpath->query('//dns2:task/dns2:tests/dns2:test');

            $xpathtitle = 'dns2:title';
            $xpathdescription = 'dns2:description';
            if ($xpathresult->length === 0) {
                // try version
                $xpath->registerNamespace('dns1','urn:proforma:task:v1.0.1');
                $xpathresult=$xpath->query('//dns1:task/dns1:tests/dns1:test');
                if ($xpathresult->length === 0) {
                    //debugging('no tests found in task file');
                    //throw new moodle_exception('no tests found in task file');
                }
                $xpathtitle = 'dns1:title';
                $xpathdescription = 'dns1:description';
            }


            foreach ($xpathresult as $test) {
                $titles = $xpath->query($xpathtitle, $test);
                $contents = $xpath->query($xpathdescription, $test);

                $testobject = array();
                $testobject['id'] = $test->attributes['id']->nodeValue;
                $testobject['title'] = $titles->item(0)->textContent;
                // optional:
                if ($contents->length > 0)
                    $testobject['description'] = $contents->item(0)->textContent;

                $tests[$testobject['id']] = $testobject;
            }

            // read grading hints (supported from version 2 on)
            $gradfunction=$xpath->query('//dns2:grading-hints/dns2:root/@function');

            $xpathresult=$xpath->query('//dns2:grading-hints/dns2:root/dns2:test-ref');
            foreach ($xpathresult as $test) {
                $testobject = array();
                $testobject['ref'] = $test->getAttribute('ref');
                $testobject['weight'] = $test->getAttribute('weight');
                $gradinghints[$testobject['ref']] = $testobject;
            }

            return array($tests, $gradinghints);
        }
    */

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

    // the Moodle Core only supports export for XML and gift format for plugins.
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

        $data['#']['answer'] = array(); // set empty answer array in order to prevent error message
        // in call of base class function
        $qo = parent::import_from_xml($data, $question, $format, $extra);

        // import hints (is unfortunately not imported by base function)
        $format->import_hints($qo, $data, true, false);
        // $format->get_format($question->questiontextformat));

        // Restore files in grader info
        $comment = $format->import_text_with_files($data,
                array('#', 'comment', 0)); // $qo->comment, $format->get_format($qo->commentformat));
        $qo->comment = $comment['text'];
        $qo->commentformat = $comment['format'];
        // todo: restore $comment['itemid']
        // if (!empty($comment['itemid'])) {
        // $qo->commentitemid = $comment['itemid'];

        // import files
        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $datafiles = $format->getpath($data,
                    array('#', $value["files"], 0, '#', 'file'), array());
            if (is_array($datafiles)) { // Seems like a non-array does occur in some versions of PHP!
                $property = $value["formid"];
                $qo->$property = $format->import_files_as_draft($datafiles);
            }
        }

        $datafiles = $format->getpath($data,
                array('#', 'task', 0, '#', 'file'), array());
        if (is_array($datafiles)) { // Seems like a non-array does occur in some versions of PHP!
            $qo->taskfiledraftid = $format->import_files_as_draft($datafiles);
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
        global $COURSE;
        /*        if ($extra !== null) {
                    throw new coding_exception("proforma:export_to_xml: Unexpected parameter");
                }
        */
        // Copy the question so we can modify it for export
        // (Just in case the original gets used elsewhere).
        $questiontoexport = $question; // clone $question;

        $expout = parent::export_to_xml($questiontoexport, $format, $extra);
        // $expout .= "    <DUMMY " . $questiontoexport->modelsolution . ">\n";

        $expout .= "    <comment {$format->format($questiontoexport->options->commentformat)}>\n";
        $expout .= $format->writetext($questiontoexport->options->comment, 3);
        // $expout .= $format->write_files($questiontoexport->options->questiontextfiles);
        $expout .= "    </comment>\n";

        // export files
        $fs = get_file_storage();
        $contextid = $question->contextid;

        foreach (self::fileareas_with_model_solutions() as $filearea => $value) {
            $datafiles = $fs->get_area_files(
                    $contextid, 'qtype_proforma', $filearea, $question->id);
            $expout .= '<' . $value['files'] . '>' . $format->write_files($datafiles) . "</" . $value['files'] . ">\n";
        }

        $datafiles = $fs->get_area_files(
                $contextid, 'qtype_proforma', self::FILEAREA_TASK, $question->id);
        $expout .= '<task>' . $format->write_files($datafiles) . "</task>\n";
        $datafiles = $fs->get_area_files(
                $contextid, 'qtype_proforma', self::FILEAREA_COMMENT, $question->id);
        $expout .= '<commentfiles>' . $format->write_files($datafiles) . "</commentfiles>\n";

        return $expout;
    }

    public static function as_codemirror($textarea_id,$mode = 'java', $header = null) {
        if (get_config('qtype_proforma', 'usecodemirror')) {
            $WRITABLE = 0;

            global $PAGE, $CFG;
            require_once($CFG->dirroot . '/config.php');
            // TODO: move READONLY and WRITABLE to common class
            // TODO: where does textarea identifier come from?
            $moodleversion = $CFG->version;
            // debugging('Moodle Version is ' . $moodleversion);

            if ($moodleversion > 2018051700) {
                // starting from Moodle 3.5 the Codemirror editor width is not resized to parent container.
                // so this must be explicitly be done in Javascript.
                $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                        array($textarea_id, $WRITABLE, $mode, $header, 1));
            } else {
                // In 3.4 resizing must be prohinited because the window is too small
                $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'init_codemirror',
                        array($textarea_id, $WRITABLE, $mode, $header));
            }
        }
    }

}

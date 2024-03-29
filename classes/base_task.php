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
 * ProFormA task file
 *
 * @package    qtype_proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
defined('MOODLE_INTERNAL') || die();

/*
 * abstract class for creating and reading ProFormA task files.
 * Note that this class is stateless i.e. has no member variables.
 */

abstract class qtype_proforma_base_task {
    protected $_reservedids;

    /**
     * constructor
     * @param $reservedids
     */
    public function __construct($reservedids = []) {
        $this->_reservedids = $reservedids;
    }
    /**
     * returns false if the task is imported and cannot be modified,
     * returns true if the task is created and can be edited inside Moodle.
     *
     * @return boolean
     */
    public function can_be_edited() {
        return true;
    }

    /**
     * Create UUID
     *
     * http://www.seanbehan.com/how-to-generate-a-uuid-in-php/
     *
     * @return string
     */
    private static function uuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * is testcode for given test index set?
     *
     * @param $formdata
     * @param $index
     * @return bool
     */
    protected function is_test_set($formdata, $index) {
        // Check format: editor or filemanager?
        $editor = true;
        if (isset($formdata->testcodeformat[$index]) and
            $formdata->testcodeformat[$index] == base_form_creator::TESTCODE_FILES) {
                $editor = false;
        }

        if ($editor) {
            // Editor for testcode input.
            // Check if editor test is not empty.
            return isset($formdata->testcode[$index]) &&
                strlen(trim($formdata->testcode[$index]));
        }

        // Filemanager for testcode input:
        // Check if at least one file is uploaded.
        global $USER;
        $usercontext = context_user::instance($USER->id);
        $draftitemid = $formdata->testfiles[$index];
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id');
        return count($draftfiles) > 1;
    }

    // Override for creating task.

    /**
     * Add extra namespaces
     * @param $xw
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)*
     */
    protected function add_namespace_to_xml(SimpleXmlWriter $xw) {
    }

    /**
     * set formdata from grading hints
     *
     * @param question $question
     * @param test $ref
     * @param test $weight
     * @return bool
     */
    protected function set_formdata_from_gradinghints($question, $ref, $weight) {
        return false;
    }

    /**
     * Add test specific data to LMS internal grading hints
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_lms_grading_hints(SimpleXmlWriter $xw, $formdata) {
        if (!isset($formdata->testid)) {
            return;
        }
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                $xw->startElement('test-ref');
                $xw->create_attribute('ref', $formdata->testid[$index]);
                if (array_key_exists($index, $formdata->testweight)) {
                    $xw->create_attribute('weight', $formdata->testweight[$index]);
                } else {
                    $xw->create_attribute('weight', '-1');
                }
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('description', $formdata->testdescription[$index]);
                $xw->create_childelement_with_text('test-type', $formdata->testtype[$index]);
                $xw->endElement(); // End tag test-ref.
            }
        }
    }

    /** create task.xml from formdata
     *
     * @param $formdata
     * @return string
     */
    public function create_task_file($formdata) {
        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(true);
        $xw->setIndentString('    ');

        $xw->startDocument('1.0', 'UTF-8');

        $xw->startElement('task');
        $xw->create_attribute('xmlns', 'urn:proforma:v2.0');
        $xw->create_attribute('lang', 'de'); // TODO.
        $xw->create_attribute('uuid', self::uuid());
        // Override!
        $this->add_namespace_to_xml($xw);

        $xw->create_childelement_with_text('title', $formdata->name);
        if (is_string($formdata->questiontext)) {
            $xw->create_childelement_with_text('description', $formdata->questiontext);
        } else {
            $xw->create_childelement_with_text('description', $formdata->questiontext['text']);
        }
        $xw->startElement('proglang'); // Not needed for grader.
        $this->add_programming_language_to_xml($xw, $formdata);
        $xw->endElement(); // End tag submission-restrictions.

        $xw->startElement('submission-restrictions'); // Not needed for grader.
        $xw->endElement(); // End tag submission-restrictions.

        $xw->startElement('files');
        $this->add_files_to_xml($xw, $formdata);

        // Create dummy model solution file.
        $xw->startElement('file');
        $xw->create_attribute('id', 'MS');
        $xw->create_attribute('used-by-grader', 'false');
        $xw->create_attribute('visible', 'no');
        $xw->startElement('embedded-txt-file');
        $xw->create_attribute('filename', 'modelsolution.java');
        // Write at least one byte into model solution in order to avoid problems with empty files.
        $xw->text('// no model solution available ');
        $xw->endElement(); // End tag embedded-txt-file.
        $xw->endElement(); // End tag file.

        $xw->endElement(); // End tag files.

        $xw->startElement('model-solutions');
        $xw->startElement('model-solution');
        $xw->create_attribute('id', '1');
        $xw->startElement('filerefs');
        $xw->startElement('fileref');
        $xw->create_attribute('refid', 'MS');
        $xw->endElement(); // End tag fileref.
        $xw->endElement(); // End tag filerefs.
        $xw->endElement(); // End tag model-solution.
        $xw->endElement(); // End tag model-solutions.

        $xw->startElement('tests');
        $this->add_tests_to_xml($xw, $formdata);
        $xw->endElement(); // End tag tests.

        $xw->startElement('grading-hints');
        $xw->startElement('root'); // Not needed for grader.
        $xw->endElement(); // End tag root.
        $xw->endElement(); // End tag grading-hints.

        $xw->startElement('meta-data');
        $xw->endElement(); // End tag meta-data.

        $xw->endElement(); // End tag task.

        $xw->endDocument();

        $taskfile = $xw->outputMemory();
        return $taskfile;
    }

    /**
     * Create LMS internal grading hints.
     *
     * @param $formdata
     * @param bool $withprolog
     * @return string
     */
    public function create_lms_grading_hints($formdata) {

        if (!empty($formdata->gradinghints)) {
            return $formdata->gradinghints;
        }
        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(1);
        $xw->setIndentString(' ');

        $xw->startDocument('1.0', 'UTF-8');

        $xw->startElement('grading-hints');
        $xw->startElement('root');
        $xw->create_attribute('function', 'sum');
        $this->add_tests_to_lms_grading_hints($xw, $formdata);
        $xw->endElement(); // End tag root.
        $xw->endElement(); // End tag grading-hints.

        $xw->endDocument();
        $gradinghints = $xw->outputMemory();

        return $gradinghints;
    }

    /**
     * Store task file in Moodle.
     *
     * @param $content task file (xml)
     * @param $filename task filename
     * @param $draftitemid draftid
     * @throws coding_exception
     */
    public static function store_task_file($content, $filename, $contextid, $questionid) {
        if ($filename == null) {
            throw new coding_exception('cannot create task file because of missing filename');
        }

        $fs = get_file_storage();
        // Prepare file record object.

        $fileinfo = array(
            'contextid' => $contextid, // Category id.
            'component' => 'qtype_proforma',
            'filearea' => qtype_proforma::FILEAREA_TASK,
            'itemid' => $questionid,
            'filepath' => '/',
            'filename' => $filename);

        // Delete old file if any.
        $fs->delete_area_files($contextid, 'qtype_proforma', qtype_proforma::FILEAREA_TASK, $questionid);
        /* $storedfile = */
        $fs->create_file_from_string($fileinfo, $content);
    }

    /**
     * extract form data from LMS internal grading hints
     * @param $question
     * @param $mform
     */
    public function extract_formdata_from_gradinghints($question, $mform) {
        $question->testtitle = array();
        $question->testdescription = array();
        $question->testtype = array();
        $question->testweight = array();
        $question->testid = array();

        if (empty($question->gradinghints)) {
            // Nothing to be done.
            return;
        }

        $xmldoc = new DOMDocument;

        if (!$xmldoc->loadXML($question->gradinghints)) {
            // Fatal error: grading hints cannot be loaded!
            debugging('gradinghints are not valid XML');
            debugging($question->gradinghints);
            return;
        }

        $xpath = new DOMXPath($xmldoc);
        $xpathresult = $xpath->query('//grading-hints/root/test-ref');
        $key = 0;
        if ($xpathresult->length == 0) {
            // No debug message. This situation can occur when input is not validated successfully.
            // debugging('no tests in gradinghints found ' . $question->gradinghints);
            return;
        }

        // Preset compile and checkstyle checkboxes as not checked.
        $question->compile = 0;
        $question->checkstyle = 0;

        foreach ($xpathresult as $testgrading) {
            $ref = $testgrading->getAttribute('ref');
            $weight = $testgrading->getAttribute('weight');
            $titles = $xpath->query('title', $testgrading);
            if ($titles->length > 0) {
                $title = $titles->item(0)->textContent;
            } else {
                $title = 'Title ' . $ref;
            }
            $descriptions = $xpath->query('description', $testgrading);
            if ($descriptions->length > 0) {
                $description = $descriptions->item(0)->textContent;
            } else {
                $description = '';
            }
            $testtypes = $xpath->query('test-type', $testgrading);
            if ($testtypes->length > 0) {
                $testtype = $testtypes->item(0)->textContent;
            } else {
                $testtype = '';
            }

            /*
              // from edit_numerical_form
              // See comment in the parent method about this hack:
              // Evil hack alert. Formslib can store defaults in two ways for
              // repeat elements:
              //   ->_defaultValues['fraction[0]'] and
              //   ->_defaultValues['fraction'][0].
              // The $repeatedoptions['fraction']['default'] = 0 bit above means
              // that ->_defaultValues['fraction[0]'] has already been set, but we
              // are using object notation here, so we will be setting
              // ->_defaultValues['fraction'][0]. That does not work, so we have
              // to unset ->_defaultValues['fraction[0]'].
              unset($this->_form->_defaultValues["testtitle[{$key}]"]);
             */
            unset($mform->_defaultValues["testtitle[{$key}]"]);
            unset($mform->_defaultValues["testid[{$key}]"]);
            unset($mform->_defaultValues["testweight[{$key}]"]);
            unset($mform->_defaultValues["testdescription[{$key}]"]);
            unset($mform->_defaultValues["testtype[{$key}]"]);
            if (!$this->set_formdata_from_gradinghints($question, $ref, $weight)) {
                $question->testid[] = $ref;
                $question->testtitle[] = $title;
                $question->testdescription[] = $description;
                $question->testtype[] = $testtype;
                $question->testweight[] = $weight;
            }
            $key++;
        }
    }

    /**
     * returns the task.xml.
     * Does not work for zipped task files!
     *
     * @param $category
     * @param $question
     * @return string
     * @throws moodle_exception
     */
    protected function get_task_xml($category, $question) {
        $fs = get_file_storage();
        $file = $fs->get_file($category, 'qtype_proforma', qtype_proforma::FILEAREA_TASK,
        $question->id, '/', $question->taskfilename);
        if (!$file) {
            throw new moodle_exception("proforma task not found");
        }

        return $file->get_content();
    }

    /**
     * extract formdata from taskfile
     *
     * @param $category
     * @param $question
     */
    public function extract_formdata_from_taskfile($category, $question) {
        $content = $this->get_task_xml($category, $question);

        $task = new SimpleXMLElement($content, LIBXML_PARSEHUGE);
        // Read programming language version.
        $question->proglangversion = (string)$task->proglang['version'];
        // Read programming language.
        $question->proglang = (string)$task->proglang;

        // Read files.
        foreach ($task->files->file as $file) {
            $fileobject = array();
            $fileobject['id'] = (string)$file['id'];
            if (isset($file->{'embedded-txt-file'})) {
                $code = $file->{'embedded-txt-file'};
                $fileobject['filename'] = (string)$code['filename'];
                $fileobject['code'] = (string)$code;
            } else {
                if (isset($file->{'embedded-bin-file'})) {
                    // Create file in draft area.
                    $binaryfile = $file->{'embedded-bin-file'};
                    $fileobject['filename'] = (string)$binaryfile['filename'];
                    // Remember SimpleXmlElement node for later use (draft are storage).
                    $fileobject['xmlelement'] = $binaryfile;
                }
            }

            $files[$fileobject['id']] = $fileobject;
        }

        // Read tests.
        $index = 0;
        foreach ($task->tests->test as $test) {
            $this->extract_formdata_from_taskfile_test($question, $test, $files, $index);
        }
    }

    /**
     * called by extract_formdata_from_taskfile in order to
     * extract form data from task test.
     * Override if needed!
     *
     * @param type $question: return instance
     * @param type $test: test entity from task
     * @param type $files: files array
     * @param type $index: index of next unit test (in/out)
     */
    protected function extract_formdata_from_taskfile_test($question, $test, $files, &$index) {
        /*foreach ($this->_reservedids as $reserved) {
            if ($reserved == $test['id']) {
                return;
            }
        }*/

        // Default implementation:
        // only unit tests are available. No other test.
        $code = null;
        $filearea = null;
        $filetype = null;
        foreach ($test->{'test-configuration'}->filerefs as $filerefs) {
            foreach ($filerefs->fileref as $fileref) {
                $refid = (string) $fileref['refid'];
                $fileobject = $files[$refid];
                if (isset($fileobject['code'])) {
                    if (isset($filetype)) {
                        debugging('inconsistent task file: embedded-txt-file and embedded-bin-file mixed');
                    }
                    $filetype = base_form_creator::TESTCODE_EDITOR;
                    if (isset($code)) {
                        // We must not have more than one embedded text file belonging to a test.
                        debugging('inconsistent task file: embedded-txt-file is used more than once');
                    }
                    $code = (string) $fileobject['code'];
                } else {
                    // Binary file.
                    if (isset($code)) {
                        debugging('inconsistent task file: embedded-txt-file and embedded-bin-file mixed for ' . (string)$test['id']);
                    }
                    $filetype = base_form_creator::TESTCODE_FILES;
                    // Store in draft area.
                    $attribute = 'testfiles[' . $index . ']';
                    global $USER;
                    $contextid = context_user::instance($USER->id)->id;
                    if (!isset($filearea)) {
                        // Prepare draft file area for this test.
                        $filearea = new qtype_proforma_filearea($attribute);
                        $filearea->prepare_draft($contextid, $question);
                    }
                    $text = base64_decode($fileobject['xmlelement']);
                    $filearea->save_text_to_draft($contextid, $question->$attribute,
                        $fileobject['filename'], $text);
                }
            }
        }
        // debugging('set testcodeformat_' . $index . ' to ' . $filetype);
        $question->testcodeformat[$index] = $filetype;
        // $question->testcodearray[$index] = $question->testcodeformat[$index];
        if (isset($code)) {
            $question->testcode[$index] = $code;
        }
        $index++;
    }

    /**
     * return the testfile from draft area
     *
     * @param type $formdata
     * @param int $testindex
     * @return type
     * @global type $USER
     */
    protected static function _get_draft_testfiles($formdata, $testindex) {
        global $USER;
        $usercontext = context_user::instance($USER->id);

        $draftitemid = $formdata->testfiles[$testindex];
        $fs = get_file_storage();
        return $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
    }

    /**
     * Default implementaion of adding tests to XML
     * @param SimpleXmlWriter $xw
     * @param $formdata
     * @return void
     * @throws coding_exception
     */
    protected function add_tests_to_xml(SimpleXmlWriter $xw, $formdata, $testtype = 'unittest') {
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                $xw->startElement('test');
                $xw->create_attribute('id', $formdata->testid[$index]);
                $xw->create_childelement_with_text('title', $formdata->testtitle[$index]);
                $xw->create_childelement_with_text('test-type', $testtype);
                $xw->startElement('test-configuration');
                $xw->startElement('filerefs');
                if ($formdata->testcodeformat[$index] == base_form_creator::TESTCODE_EDITOR) {
                    $xw->startElement('fileref');
                    $xw->create_attribute('refid', $formdata->testid[$index]);
                    $xw->endElement(); // End tag fileref.
                } else {
                    $counter = 1;
                    foreach (qtype_proforma_base_task::_get_draft_testfiles($formdata, $index) as $draftfile) {
                        $xw->startElement('fileref');
                        $xw->create_attribute('refid', $formdata->testid[$index] . '-' . $counter);
                        $xw->endElement(); // End tag fileref.
                        $counter++;
                    }
                }

                $xw->endElement(); // End tag filerefs.

                $this->add_unittest_to_xml($xw, $index, $formdata);

                $xw->endElement(); // End tag test-configuration.

                $xw->endElement(); // End tag test.
            }
        }
    }

    protected function get_testfilename($index, $id, $code) {
        throw new coding_exception('missing implementation of get_testfilename');
    }

    protected function add_unittest_to_xml(SimpleXmlWriter $xw, $index, $formdata) {
        throw new coding_exception('missing implementation of add_testframework_to_xml');
    }

    /**
     * get number of Unit tests without any other test types.
     *
     * @param $gradinghints
     * @return int
     */
    public function get_count_unit_tests($gradinghints) {
        if (!$gradinghints) {
            return 0;
        }
        $gh = new SimpleXMLElement($gradinghints, LIBXML_PARSEHUGE);
        $count = 0;
        foreach ($gh->root->{'test-ref'} as $test) {
            $id = (string)$test['ref'];
            $reserved = false;
            foreach ($this->_reservedids as $resid) {
                if ($resid == $id) {
                    $reserved = true;
                }
            }
            if (!$reserved) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * add files to XML output.
     *
     * @param $xw
     * @param $formdata
     */
    protected function add_files_to_xml(SimpleXmlWriter $xw, $formdata) {
        // Create Unit test files.
        $count = count($formdata->testid);
        for ($index = 0; $index < $count; $index++) {
            $id = $formdata->testid[$index];
            if ($id !== '' && $this->is_test_set($formdata, $index)) {
                if ($formdata->testcodeformat[$index] == base_form_creator::TESTCODE_EDITOR) {
                    $xw->startElement('file');
                    $xw->create_attribute('id', $formdata->testid[$index]);
                    $xw->create_attribute('used-by-grader', 'true');
                    $xw->create_attribute('visible', 'no');

                    $xw->startElement('embedded-txt-file');
                    $code = $formdata->testcode[$index];
                    $filename = $this->get_testfilename($index, $id, $code);
                    // $filename = self::get_java_file($code);
                    $xw->create_attribute('filename', $filename);
                    $xw->text($formdata->testcode[$index]);
                    $xw->endElement(); // End tag embedded-txt-file.
                    $xw->endElement(); // End tag file.
                } else {
                    // Handle uploaded test files.
                    $counter = 1;
                    foreach (qtype_proforma_base_task::_get_draft_testfiles($formdata, $index) as $draftfile) {
                        $xw->startElement('file');
                        $xw->create_attribute('id', $formdata->testid[$index] . '-' . $counter);
                        $xw->create_attribute('used-by-grader', 'true');
                        $xw->create_attribute('visible', 'no');
                        $xw->startElement('embedded-bin-file');
                        $xw->create_attribute('filename', $draftfile->get_filename());
                        $xw->text(base64_encode($draftfile->get_content()));
                        $xw->endElement(); // End tag embedded-bin-file.
                        $xw->endElement(); // End tag file.
                        $counter++;
                    }
                }
            }
        }
    }

}

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
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/simplexmlwriter.php');

/*
 * (class for handling ProFormA tasks for differnt programming languages
 * (i.e. create task and extract data for editor)
 */
class qtype_proforma_proforma_task {

    // http://www.seanbehan.com/how-to-generate-a-uuid-in-php/
    private static function uuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // override for creating task
    protected function add_namespace_to_xml($xw) {
    }
    protected function add_programming_language_to_xml($xw, $formdata) {
    }
    protected function add_testfiles_to_xml($xw, $formdata) {
    }
    protected function add_tests_to_xml($xw, $formdata) {
    }

    /**
     * @param $question question object
     * @param $ref test ref
     * @param $weight test weight
     * @return bool true if handled otherweise false
     */
    protected function set_formdata_from_gradinghints($question, $ref, $weight) {
        return false;
    }


    /** default implementation for imported taskfiles
     * @param $xw
     * @param $formdata
     */
    protected function add_tests_to_lms_grading_hints($xw, $formdata) {
        for ($index = 0; $index < count($formdata->testid); $index++) { // $formdata->testid as $id) {
            $id = $formdata->testid[$index];
            if ($id !== '') {
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
                $xw->endElement(); // test-ref
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
        $xw->create_attribute('lang', 'de'); // TODO
        $xw->create_attribute('uuid', self::uuid());
        // override
        $this->add_namespace_to_xml($xw);

        $xw->create_childelement_with_text('title', $formdata->name);
        $xw->create_childelement_with_text('description', $formdata->questiontext);
        $xw->startElement('proglang'); // not needed for grader
        $this->add_programming_language_to_xml($xw, $formdata);
        $xw->endElement(); // submission-restrictions

        $xw->startElement('submission-restrictions'); // not needed for grader
        $xw->endElement(); // submission-restrictions

        $xw->startElement('files');
        $this->add_testfiles_to_xml($xw, $formdata);

        // create dummy model solution file
        $xw->startElement('file');
        $xw->create_attribute('id', 'MS'); // $id);
        $xw->create_attribute('used-by-grader', 'false');
        $xw->create_attribute('visible', 'no');
        $xw->startElement('embedded-txt-file');
        $xw->create_attribute('filename', 'modelsolution.java');
        $xw->text('// no model solution available '); // write at least one byte in order to avoid problems with empty files
        $xw->endElement(); // embedded-txt-file
        $xw->endElement(); // file

        $xw->endElement(); // files

        $xw->startElement('model-solutions');
        $xw->startElement('model-solution');
        $xw->create_attribute('id', '1');
        $xw->startElement('filerefs');
        $xw->startElement('fileref');
        $xw->create_attribute('refid', 'MS');
        $xw->endElement(); // fileref
        $xw->endElement(); // filerefs
        $xw->endElement(); // model-solution
        $xw->endElement(); // model-solutions

        $xw->startElement('tests');
        $this->add_tests_to_xml($xw, $formdata);
        $xw->endElement(); // tests

        $xw->startElement('grading-hints');
        $xw->startElement('root'); // not needed for grader
        $xw->endElement(); // root
        $xw->endElement(); // grading-hints

        $xw->startElement('meta-data');
        $xw->endElement(); // meta-data

        $xw->endElement(); // task

        $xw->endDocument();

        $taskfile = $xw->outputMemory();
        return $taskfile;
    }

    public function create_lms_grading_hints($formdata, $withprolog = true) {

        if (isset($formdata->gradinghints) && strlen($formdata->gradinghints) > 0) {
            return $formdata->gradinghints;
        }
        $xw = new SimpleXmlWriter();
        $xw->openMemory();

        $xw->setIndent(1);
        $xw->setIndentString(' ');

        if ($withprolog) {
            $xw->startDocument('1.0', 'UTF-8');
        } else {
            $xw->startDocument();
        }

        $xw->startElement('grading-hints');
        // $xw->createAttribute('xmlns', 'urn:proforma:v2.0');

        $xw->startElement('root');
        $xw->create_attribute('function', 'sum');

        $this->add_tests_to_lms_grading_hints($xw, $formdata);

        $xw->endElement(); // root
        $xw->endElement(); // grading-hints

        $xw->endDocument();
        $gradinghints = $xw->outputMemory();
        // debugging($gradinghints);

        return $gradinghints;
    }

    /**
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
        // Prepare file record object

        $fileinfo = array(
                'contextid' => $contextid, // category id
                'component' => 'qtype_proforma',
                'filearea' => qtype_proforma::FILEAREA_TASK,
                'itemid' => $questionid,           // question id
                'filepath' => '/',
                'filename' => $filename);

        // delete old file if any
        $fs->delete_area_files($contextid, 'qtype_proforma', qtype_proforma::FILEAREA_TASK, $questionid);
        /*$storedfile = */
        $fs->create_file_from_string($fileinfo, $content);
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
    public function extract_formdata_from_gradinghints($question, $mform) {
        $question->testtitle = array();
        $question->testdescription = array();
        $question->testtype = array();
        $question->testweight = array();
        $question->testid = array();

        if (!isset($question->gradinghints)) {
            // nothing to be done
            return;
        }

        $xmldoc = new DOMDocument;

        if (!$xmldoc->loadXML($question->gradinghints)) {
            debugging('gradinghints is not valid XML');
            return; // 'INTERNAL ERROR: $taskresult is not XML';
        }

        $xpath = new DOMXPath($xmldoc);
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');
        $xpathresult = $xpath->query('//grading-hints/root/test-ref');
        $key = 0;
        if ($xpathresult->length == 0) {
            debugging('no tests in gradinghints found');
            return;
        }

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

    public function get_count_tests($gradinghints) {
        if (!$gradinghints) {
            return 0;
        }
        $xmldoc = new DOMDocument;

        if (!$xmldoc->loadXML($gradinghints )) {
            debugging('gradinghints is not valid XML');
            return 0; // 'INTERNAL ERROR: $taskresult is not XML';
        }

        $xpath = new DOMXPath($xmldoc);
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');
        $xpathresult = $xpath->query('//grading-hints/root/test-ref');
        return $xpathresult->length;
    }

    // override
    public function get_count_unit_tests($gradinghints) {
        return $this->get_count_tests($gradinghints);
    }


    protected function get_task_xml($category, $question) {
        $fs = get_file_storage();
        $file = $fs->get_file($category, 'qtype_proforma', qtype_proforma::FILEAREA_TASK,
                $question->id, '/' , $question->taskfilename);
        if (!$file) {
            throw new moodle_exception("proforma task not found");
        }

        return $file->get_content();
    }
}



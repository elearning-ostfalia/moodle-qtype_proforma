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
 * class for creating edit forms for imported ProFormA tasks
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/base_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/proforma_task.php');

/**
 * This class creates the edit form fields for imported ProFormA tasks.
 */
class proforma_form_creator extends base_form_creator {

    /**
     * proforma_form_creator constructor.
     *
     * @param $form
     */
    public function __construct($form) {
        parent::__construct($form, new qtype_proforma_proforma_task());
        $this->_responseformats = qtype_proforma::response_formats();
        $this->_tasktype = qtype_proforma::PERSISTENT_TASKFILE;
        // echo $this->_taskhandler->can_be_edited();
        // $this->_unittestlabel = get_string('setlx', 'qtype_proforma');
        $this->_testcode = false;
        $this->_testfiles = false;
    }

    /**
     * validate field values
     *
     * @param qtype_proforma_edit_form $editor actual editor instance
     * @param $fromform Validation argument
     * @param $files Validation argument
     * @param $errors Array with error messages (so far)
     * @return array with error messages
     */
    public function validation(qtype_proforma_edit_form &$editor, $fromform, $files, $errors) {
        $errors = parent::validation($editor, $fromform, $files, $errors);
/*
        if ($fromform['aggregationstrategy'] == qtype_proforma::WEIGHTED_SUM) {
            $repeats = count($fromform["testweight"]);
            $sumweight = 0;
            for ($i = 0; $i < $repeats; $i++) {
                $sumweight += $fromform["testweight"][$i];
            }
            if ($repeats > 0 && $sumweight == 0) {
                // Error message must be attached to testoptions group.
                // Otherwise it is not visible.
                $errors['testoptions[0]'] = get_string('sumweightzero', 'qtype_proforma');
            }
        }
*/

        if ((!isset($fromform['taskeditor'])) or (!$fromform['taskeditor'])) {
            $errors = $this->validate_taskfile($editor, $fromform, $errors);
        }

        return $errors;
    }

    protected function validate_unittest(qtype_proforma_edit_form $editor, $fromform, $files, $i, $errors) {
        return array($errors, true);
    }


    /**
     * Checks if the replaced taskfile is valid
     *
     * @param qtype_proforma_edit_form $editor actual editor instance
     * @param type $fromform
     * @param type $errors
     * @return type $errors
     */
    protected function validate_taskfile(qtype_proforma_edit_form &$editor, $fromform, $errors) {
        // Get Taskfile from draft area.
        $draftid = $fromform[qtype_proforma::FILEAREA_TASK];
        global $USER;
        $context = context_user::instance($USER->id);
        $fs = get_file_storage();
        $draftfiles = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'itemid', false);
        if (count($draftfiles) != 1) {
            // Must not be more than 1! Should be guaranteed by filemanager.
            $errors[qtype_proforma::FILEAREA_TASK] = get_string('required');
            return $errors;
        }
        $draftfile = reset($draftfiles);

        // Has taskfile changed (different content hash)?
        // Get old file.
        $taskfiles = $fs->get_area_files($editor->context->id, 'qtype_proforma',
            qtype_proforma::FILEAREA_TASK, $fromform['id'], false, false);
        if (count($taskfiles) == 1) {
            // There is an old taskfile. Check if it is compatible.
            $taskfile = reset($taskfiles); // Get first item in array.
            if ($taskfile->get_contenthash() != $draftfile->get_contenthash()) {
                // File has been changed => check if content is compatible.
                return $this->check_if_taskfiles_are_compatible($editor, $draftfile, $taskfile, $errors);
            }
        } else {
            if (count($taskfiles) > 1) {
                debugging('old taskfile not unique => cannot validate');
            }
        }

        return $errors;
    }

    /**
     * checks if two task files are compatble
     *
     * @param qtype_proforma_edit_form $editor actual editor instance
     * @param type $draftfile
     * @param type $taskfile
     * @param type $errors
     * @return type $errors
     */
    protected function check_if_taskfiles_are_compatible(qtype_proforma_edit_form &$editor, $draftfile, $taskfile, $errors) {
        // Extract relevent data for both files.

        try {
            // Get task.xml from draft file.
            $taskfiledraft = $this->get_task_xml($draftfile);
            // Extract relevant data for validation checks.
            $datadraft = qtype_proforma_proforma_task::extract_validation_data_from_taskfile($taskfiledraft);
        } catch (invalid_task_exception $ex) {
            $errors[qtype_proforma::FILEAREA_TASK] = $ex->getMessage();
            return $errors;
        } catch (Exception $ex) {
            $errors[qtype_proforma::FILEAREA_TASK] = $ex;
            return $errors;
        }

        try {
            // Get task.xml from old file.
            $taskfileold = $this->get_task_xml($taskfile);
            // Extract test data from internal grading hints.
            $gradinghints = $editor->get_question()->options->gradinghints;
            $datagh = qtype_proforma_proforma_task::extract_validation_data_from_gradinghints($gradinghints);
            // Extract test data from old task.
            $dataold = qtype_proforma_proforma_task::extract_validation_data_from_taskfile($taskfileold);
            if (!empty(array_diff($datagh->test, $dataold->test))) {
                debugging('old task does not fit grading hints');
            }
        } catch (Exception $ex) {
            // Errors handling old task are converted to one single message.
            // No details.
            $errors[qtype_proforma::FILEAREA_TASK] = get_string('erroldtask', 'qtype_proforma');
            debugging($ex);
            return $errors;
        }

        $message = array();
        // Compare programming language.
        if ($datadraft->proglang != $dataold->proglang) {
            $message[] = get_string('errinvalidproglang', 'qtype_proforma', $dataold->proglang);
        }

        // Compare number of tests.
        if (count($datadraft->test) != count($datagh->test)) {
            $message[] = get_string('errcounttest', 'qtype_proforma', count($datadraft->test));
            foreach ($datadraft->test as $key => $value) {
                $message[] = 'Test [' . $key . '] => \'' . $value . '\'';
            }
        } else {
            // Compare test id and test type.
            if (!empty(array_diff_assoc($datadraft->test, $datagh->test))) {
                $message[] = get_string('errtestsincompatible', 'qtype_proforma');
                foreach ($datadraft->test as $key => $value) {
                    $message[] = 'Test [' . $key . '] => \'' . $value . '\'';
                }
            }
        }

        if (count($message) > 0) {
            // Add more information.
            $message[] = get_string('infotaskupdate', 'qtype_proforma');
            $errors[qtype_proforma::FILEAREA_TASK] = implode('<br>', $message);
        } else {
            // Update ProFormA data from draft zip file.
            // Data will be discarded if errors occure!
            $uuid =&$editor->get_form()->getElement('uuid');
            $uuid->_attributes['value'] = $datadraft->uuid;
            $proformaversion =&$editor->get_form()->getElement('proformaversion');
            $proformaversion->_attributes['value'] = $datadraft->proformaversion;
        }

        return $errors;
    }

    /**
     * gets the task.xml file from a task file stored in Moodle
     *
     * @param type $moodlefile
     * @return type
     */
    protected function get_task_xml($moodlefile) {
        $filename = pathinfo($moodlefile->get_filename(), PATHINFO_BASENAME);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'xml': // XML file.
                return $moodlefile->get_content();
            case 'zip': // Zip file.
                return $this->get_task_xml_from_zip($moodlefile);
            default:
                // Unsupported file format.
                throw new invalid_task_exception(get_string('errinvalidtask', 'qtype_proforma'));
        }
    }

    /**
     * gets the task.xml file from a task file stored as draft in Moodle
     * @param type $draftfile
     * @return boolean
     * @throws Exception
     */
    protected function get_task_xml_from_zip($draftfile) {
        // Create temporary folder for extracted zip file.
        $uniquecode = time();
        $tempdir = make_temp_directory('proforma_import/' . $uniquecode);

        try {
            // Extract zip content to temporary folder.
            $packer = get_file_packer('application/zip');
            if (!$packer->extract_to_pathname($draftfile, $tempdir)) {
                throw new Exception(get_string('cannotunzip', 'question'));
            }
            // Look for task.xml.
            $taskfiles = array();
            $iterator = new DirectoryIterator($tempdir);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile() &&
                    strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_BASENAME)) == 'task.xml') {
                        $taskfiles[] = $fileinfo->getFilename();
                }
            }

            switch (count($taskfiles)) {
                case 1:
                    // Return content of task.xml.
                    return file_get_contents($tempdir . '/' . $taskfiles[0]);
                case 0:
                    throw new invalid_task_exception(get_string('errnotask', 'qtype_proforma'));
                default:
                    throw new invalid_task_exception(get_string('errtasknotunique', 'qtype_proforma'));
            }
        } finally {
            // Delete all content in temporary folder.
            fulldelete($tempdir);
        }
    }

    /**
     * Create links for model solution files.
     */
    protected function add_modelsolution() {
        $mform = $this->_form;
        // Model Solution files (instead of modelsollist we show links).
        $mform->addElement('static', 'mslinks', get_string('modelsolfiles', 'qtype_proforma'), '');
        $mform->addHelpButton('mslinks', 'modelsolfiles_hint', 'qtype_proforma');
    }

    /**
     * Add response template editor for main template and links for further templates.
     *
     * @param $question
     */
    protected function add_responsetemplate($question) {
        $mform = $this->_form;
        parent::add_responsetemplate($question);
        // Further templates (there should be no other templates).
        $this->add_static_text($question, 'furtherTemplates', get_string('templates', 'qtype_proforma'),
                'templates');
        $mform->addHelpButton('furtherTemplates', 'templates_hint', 'qtype_proforma');
    }

    /**
     * Display grader settings.
     *
     * @param $question
     */
    public function add_grader_settings($question, $context) {
        // ProFormA fields.
        $mform = $this->_form;
        $mform->addElement('header', 'graderoptions_header',
            get_string('graderoptions_header', 'qtype_proforma'));
        // $mform->setExpanded('graderoptions_header');

        // Task Filename.
        // Remove hidden element in base class.
        $tasksize = get_config('qtype_proforma', 'taskmaxbytes');

        $mform->removeElement(qtype_proforma::FILEAREA_TASK);
        $mform->addElement('filemanager', qtype_proforma::FILEAREA_TASK,
            get_string('taskfilename', 'qtype_proforma'), null, [
                'subdirs' => false,
                'maxfiles' => 1,
                'accepted_types' => array('.zip', '.xml'),
                'maxbytes' => $tasksize
            ]
        );
        $mform->addHelpButton(qtype_proforma::FILEAREA_TASK, 'task_hint', 'qtype_proforma');

        // UUID.
        $this->add_static_field($question, 'uuid', get_string('uuid', 'qtype_proforma'), 40);
        $mform->addHelpButton('uuid', 'uuid_hint', 'qtype_proforma');

        // Show Proforma version.
        $this->add_static_field($question, 'proformaversion', 'ProFormA Version', 6);

        if (!isset($question->id)) {
            // For new questions we do not provide an upload button.
            return;
        }

        // Add upload button.
        $mform->addElement('button', 'uploadbutton', get_string('upload', 'qtype_proforma'));
        // Add js.
        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_proforma/logmonitor', 'uploadToGrader', array('id_uploadbutton'));
    }

    /**
     * Modify respeatoptions
     *
     * @param $repeatoptions
     */
    protected function adjust_test_repeatoptions(&$repeatoptions) {
        // DISABLE testtype and test identifier for imported tasks.
        // Do not hide as in base class!
        $repeatoptions['testid']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        $repeatoptions['testtype']['disabledif'] = array('aggregationstrategy', 'neq', 111);
        // Hide weight for case of all-or-nothing.
        $repeatoptions['testweight']['hideif'] = array('aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);
    }

    /**
     * Modify repeatarray in add_tests.
     *
     * @param $repeatarray
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function adjust_test_repeatarray(&$repeatarray) {
    }
    /**
     * Add test section
     *
     * @param $question
     * @param $questioneditform
     * @return int
     */
    protected function add_tests($question, $questioneditform) {
        return $this->add_test_fields($question, $questioneditform, 'unittest');
    }

    public function add_test_settings($question, $questioneditform) {
        parent::add_test_settings($question, $questioneditform);
        $this->add_detail_edit_button();
    }

    /**
     * @param $question
     * @param $questioneditform
     * @param $testtype
     * @return int|null
     */
    protected function add_test_fields($question, $questioneditform, $testtype) {
        if (!isset($question->id)) {
            // New question => no tests yet.
            return 0;
        }
        return parent::add_test_fields($question, $questioneditform, $testtype);
    }

    /**
     * Prepare question to fit form field names and values.
     *
     * @param $question
     * @param category $cat
     * @param MoodleQuickForm $form
     * @param qtype_proforma_edit_form $editor
     */
    public function data_preprocessing(&$question, $cat, qtype_proforma_edit_form $editor) {
        parent::data_preprocessing($question, $cat, $editor);
        $form = $editor->get_form();

        if (empty($question->options)) {
            $question->furtherTemplates = '';
            $question->firstTemplate = '';
        }

        // Create template list with all template files without the first one
        // which gets its own editor
        // (normally there should be only one template if no filepicker is used).
        $alltemplates = explode(',', $question->templates);
        $question->firstTemplate = array_shift($alltemplates);
        $question->furtherTemplates = implode(',', $alltemplates);
        if (strlen($question->furtherTemplates) == 0) {
            $form->removeElement('furtherTemplates');
        }

        // Create links for model solution.
        $msfilearea = new qtype_proforma_filearea(qtype_proforma::FILEAREA_MODELSOL);
        if (isset($question->id)) {
            $question->mslinks = $msfilearea->get_files_as_links($question->contextid,
                $question->id);
        } else {
            // Create new ProFormA question => no question available.
            $question->mslinks = '';
        }

        // Extract internal grading hints.
        $this->_taskhandler->extract_formdata_from_gradinghints($question, $form);
    }

    protected function preset_formdata(&$question, $cat, qtype_proforma_edit_form $editor) {
    }

    /**
     * handle polymorphic behaviour when saving a question
     *
     * @param $formdata
     * @param $options
     */
    public function save_question_options(&$options) {
        $formdata = $this->_form;
        if (isset($formdata->taskfiledraftid)) {
            // Special handling for proforma import (interim solution):
            // rename draftid property.
            throw new moodle_exception('your qformat_proforma plugin is outdated, please upgrade!');
            $formdata->task = $formdata->taskfiledraftid;
            $formdata->modelsol = $formdata->modelsolid;
            $formdata->download = $formdata->downloadid;
            $formdata->template = $formdata->templateid;
        }
        parent::save_question_options($options);
    }

    /**
     * Do form definitions things that need to be done when data is set
     */
    public function definition_after_data() {
        parent::definition_after_data();
        // Resize disabled fields to fit value.
        $i = 0;
        while ($this->_form->elementExists('testoptions[' . $i . ']')) {
            $group = $this->_form->getElement('testoptions[' . $i . ']');
            $elements = $group->getElements();
            // There seems to be no simple solution for finding a field.
            foreach ($elements as $element) {
                $name = $element->getName();
                if (($name == 'testid[' . $i . ']') || ($name == 'testtype[' . $i . ']')) {
                    $value = $element->getValue();
                    $element->setSize(strlen($value));
                }
            }
            $i ++;
        }
    }
}
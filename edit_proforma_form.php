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
 * editiing form for ProFormA question
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/classes/proforma_formcreator.php');
require_once($CFG->dirroot . '/question/type/proforma/classes/java_formcreator.php');

// ProFormA question type editing form.
class qtype_proforma_edit_form extends question_edit_form {

    protected $volatiletask = false;
    protected $formcreator = null;

    protected function definition() {
        if (empty($this->question->options)) {
            // create question (derived class would be better..)
            $this->volatiletask = true;
            $this->formcreator = new proforma_form_creator($this->_form);
        }
        parent::definition();
    }
    /*
    public function set_data($question) {
        if ($this->nocreate) {
            //parent::parent::set_data($question);
            if (is_object($question)) {
                $default_values = (array)$question;
            }
            $this->_form->setDefaults($default_values);
            return;
        } else {
            parent::set_data($question);
        }
    }
    */
    public function validation($fromform, $files) {
        $errors = parent::validation($fromform, $files);
        return $this->formcreator->validation($fromform, $files, $errors);
    }

    /**
     * comment from abstract super method: Override this in the subclass to question type name.
     * @return the question type name, should be the same as the name() method
     *      in the question type class.
     */
    public function qtype() {
        return 'proforma';
    }

    /**
     * Get the type sets passed.
     *
     * @param string $types The space , ; separated list of types
     * @return array('groupname', 'mime/type', ...)
     */
    /*
     private function get_typesets($types) {
        $sets = array();
        if (!empty($types)) {
            $sets = preg_split('/[\s,;:"\']+/', $types, null, PREG_SPLIT_NO_EMPTY);
        }
        return $sets;
    }
    */


    public function definition_after_data() {
        // debugging('called');
    }

    /**
     * Add any question-type specific form fields.
     *
     * @param object $mform the form being built.
     */
    protected function definition_inner($mform) {
        $qtype = question_bank::get_qtype('proforma');

        if (!$this->volatiletask) {
            // check if question was created by moodle
            $this->volatiletask = isset($this->question->options->taskstorage) &&
                    $this->question->options->taskstorage == qtype_proforma::VOLATILE_TASKFILE;
        }
        if ($this->volatiletask) {
            // question was created by form editor
            $this->formcreator = new java_form_creator($this->_form);
        } else {
            // question was imported
            $this->formcreator = new proforma_form_creator($this->_form);
        }

        $this->formcreator->add_questiontext_attachments($this->question);
        $this->formcreator->add_proglang_selection($this->question);

        $this->formcreator->add_response_options($this->question, $qtype);

        $this->formcreator->add_test_settings($this->question, $this);

        $this->formcreator->add_grader_settings($this->question);

        // Internal description (Comment)
        $mform->addElement('header', 'commentheader', get_string('commentheader', 'qtype_proforma'));
        // $mform->setExpanded('commentheader');
        $mform->addElement('editor', 'comment', get_string('comment', 'qtype_proforma'),
                array('rows' => 10), $this->editoroptions);

        // Attention! the following assignment is put at the very end of the function
        // in order to avoid problems with a call to repeat_elements which
        // crashes in case of previous closures.
        $mform->addFormRule(function ($values, $files) {
            if (empty($values['filetypes'])) {
                return true;
            }
            // .py is not recognised => do not check extensions!
            // TODO: check valid format: ; separated + . with extension
            return true;
        });
    }

    private function create_downloadlist($qelement, $oelement) {
        $qelement = $oelement;
        if (isset($qelement)) {
            $list = array();
            foreach (explode(',', $qelement) as $download) {
                $list[] = $download;
            }
            $downloadlist = implode(', ', $list);
            return $downloadlist;
        }
        return '';
    }

    /**
     * Perform any preprocessing needed on the data passed to {@link set_data()}
     * before it is used to initialise the form.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question); // TODO das muss ohne gehen

        if (empty($question->options)) {
            // preset all fields that can be disabled in the form. Otherwise they may be missing
            // somewhere! (resulting in an exception)
            $question->maxbytes = 0;
            $question->attachments = 0;
            $question->filetypes = '';
            $question->taskfilename = '';

            foreach (qtype_proforma::fileareas_with_model_solutions() as $filearea => $value) {
                $property = $value["questionlist"];
                // echo 'data_preprocessing ' . $filearea . ' => ' . $property . ' <br>';
                $question->$property = '';
            }

            $question->furtherTemplates = '';
            $question->firstTemplate = '';
            return $question;
        }

        global $USER;
        $cat = $question->category;
        foreach (explode(',', $question->category) as $category) {
            $cat = $category;
        }

        if (isset($this->question->options->taskstorage) &&
                $this->question->options->taskstorage == qtype_proforma::VOLATILE_TASKFILE) {
            // retrieve files content from task
            $this->volatiletask = true;
            $taskfilehandler = new qtype_proforma_java_task;
            //$this->formcreator = new java_form_creator($this->_form);
            $taskfilehandler->extract_formdata_from_taskfile($cat, $question);
        } else {
            $this->volatiletask = false;
            $taskfilehandler = new qtype_proforma_proforma_task;
            //$this->formcreator = new proforma_form_creator($this->_form);

            // create lists for download files
            foreach (qtype_proforma::fileareas_with_model_solutions() as $filearea => $value) {
                $property1 = $value['formlist'];
                $property2 = $value['questionlist'];

                $question->$property1 = $this->create_downloadlist($question->$property2,
                        $question->options->$property2);
            }

            // create template list with all template files without the first one
            // which gets its own editor
            // (normally there should be only one template if no filepicker is used)
            $alltemplates = explode(',', $question->templates);
            $question->firstTemplate = array_shift($alltemplates);
            $question->furtherTemplates = implode(',', $alltemplates);

            if (strlen($question->furtherTemplates) == 0) {
                $this->_form->removeElement('furtherTemplates');
            }
        }

        // special handling for comment
        $draftid = file_get_submitted_draft_itemid('comment');
        $question->comment = array();
        $question->comment['text'] = file_prepare_draft_area(
            $draftid,           // Draftid
            $this->context->id, // context
            'qtype_proforma',      // component
                qtype_proforma::FILEAREA_COMMENT,       // filarea
            !empty($question->id) ? (int) $question->id : null, // itemid
            $this->fileoptions, // options
            $question->options->comment // text.
        );
        $question->comment['format'] = $question->options->commentformat;
        $question->comment['itemid'] = $draftid;

        if (!empty($question->taskfilename)) {
            // create temporary link for task file (does not belong to question class)
            // $draftid = file_get_submitted_draft_itemid('questiontext');
            // $question->link = '<a href="@@PLUGINFILE@@/'.$question->taskfilename.'">'. $question->taskfilename .'</a> ';
            $url = moodle_url::make_pluginfile_url($cat, 'qtype_proforma',
                    qtype_proforma::FILEAREA_TASK, $question->id, '/', $question->taskfilename);
            $question->link = '<a href=' . $url->out() . '>' . $question->taskfilename . '</a> ';
        }

        $taskfilehandler->extract_formdata_from_gradinghints($question, $this->_form);

        if ($this->volatiletask) {
            $draftitemid = file_get_submitted_draft_itemid('modelsolfilemanager');
            file_prepare_draft_area($draftitemid, $this->context->id, 'qtype_proforma', qtype_proforma::FILEAREA_MODELSOL,
                    $question->id, array('subdirs' => 0));
            $question->modelsolfilemanager = $draftitemid;
            $fs = get_file_storage();
            $draftfiles = $fs->get_area_files($this->context->id, 'qtype_proforma', qtype_proforma::FILEAREA_MODELSOL, $question->id);
            $files = array();
            foreach ($draftfiles as $file) {
                if ($file->get_filename() != '.' and $file->get_filename() != '..') {
                    $files[] = $file;
                }
            }
            if (count($files) === 1) {
                $question->modelsolution = $files[0]->get_content();
            }
        } else if (!empty($question->modelsolfiles)) {
            $question->mslinks = '';
            foreach (explode(',', $question->modelsolfiles) as $ms) {
                $url = moodle_url::make_pluginfile_url($cat, 'qtype_proforma',
                        qtype_proforma::FILEAREA_MODELSOL, $question->id, '/', $ms);
                $question->mslinks = $question->mslinks . '<a href=' . $url->out().'>'. $ms .'</a> ';
            }
        }

        return $question;
    }
}

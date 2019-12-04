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


// ProFormA question type editing form.
class qtype_proforma_edit_form extends question_edit_form {

    protected $volatiletask = false;

    protected function definition() {
        $mform = $this->_form;
        if (empty($this->question->options)) {
            // create question (derived class would be better..)
            $this->volatiletask = true;
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

        /*
        if ($fromform['responseformat'] != 'editor' && !$fromform['attachments']) {
            $errors['attachments'] = get_string('mustattach', 'qtype_proforma');
        }
        */
        if (isset($fromform->checkstyle) and $fromform->checkstyle == 1) {
            // checkstyle code muse be set
        }

        return $errors;
    }

    /**
     * comment from abstract super method: Override this in the subclass to question type name.
     * @return the question type name, should be the same as the name() method
     *      in the question type class.
     */
    public function qtype() {
        return 'proforma';
    }

    private function add_test_weight_option(&$testoptions, $prefix, $defaultweight, $withtitle = false) {
        $mform = $this->_form;
        if ($withtitle) {
            $testoptions[] = $mform->createElement('text', $prefix . 'title',
                    get_string('testtitle', 'qtype_proforma'), array('size' => 60));
            $mform->setType($prefix . 'title', PARAM_TEXT);
        }

        $testoptions[] = $mform->createElement('text', $prefix . 'weight',
                get_string('weight', 'qtype_proforma'), array('size' => 2));
        $mform->setType($prefix . 'weight', PARAM_FLOAT);
        $mform->setDefault($prefix . 'weight', $defaultweight);
    }

    /**
     * @param $mform
     * @param $qtype
     * @param $CFG
     * @param $COURSE
     */
    protected function add_response_options($mform, $qtype) {

        global $CFG, $COURSE;

        $defaultmaxsubmissionsizebytes = get_config('maxsubmissionsizebytes');
        // $defaultfiletypes = (string)get_config('filetypeslist');
        //
        // Response Options
        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_proforma'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_proforma'), $qtype->response_formats());
        $mform->setDefault('responseformat', 'editor');
        // disable only if responseformat is filepicker!!
        // $mform->disabledIf('responseformat', 'attachments', 'neq', '1');

        // EDITOR OPTIONS
        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_proforma'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', 15);
        $mform->hideIf('responsefieldlines', 'responseformat', 'eq', 'filepicker');

        // FILEPICKER OPTIONS
        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes,
                get_config('qtype_proforma', 'maxbytes'));

        $name1 = get_string('maximumsubmissionsize', 'qtype_proforma');
        $name2 = get_string('acceptedfiletypes', 'qtype_proforma');

        $filepickeroptions = array();
        $filepickeroptions[] = $mform->createElement('select', 'attachments',
                get_string('allowattachments', 'qtype_proforma'), $qtype->attachment_options());
        $filepickeroptions[] = $mform->createElement('select', 'maxbytes', $name1, $choices);
        $filepickeroptions[] = $mform->createElement('text', 'filetypes', $name2);
        $mform->addGroup($filepickeroptions, 'filepickergroup',  get_string('filepickeroptions', 'qtype_proforma'), array(' '), false);
        $mform->hideIf('filepickergroup', 'responseformat', 'eq', 'editor');
        $mform->addHelpButton('filepickergroup', 'acceptedfiletypes', 'qtype_proforma');

        $mform->setType('filetypes', PARAM_RAW);

        // Programming Language
        $mform->addElement('select', 'programminglanguage',
                get_string('highlight', 'qtype_proforma'), $qtype->get_proglang_options());
        $mform->addHelpButton('programminglanguage', 'highlight_hint', 'qtype_proforma');
        $mform->setDefault('programminglanguage', 'java');

        // response template
        $mform->addElement('textarea', 'responsetemplate', get_string('responsetemplate', 'qtype_proforma'), 'rows="20" cols="80"');
        if (get_config('qtype_proforma', 'usecodemirror')) {
            qtype_proforma::as_codemirror('id_responsetemplate', 'java', 'id_responsetemplateheader');
            global $PAGE;
            $PAGE->requires->js_call_amd('qtype_proforma/codemirrorif', 'switch_mode',
                    array('id_programminglanguage', 'id_responsetemplate'));
        }
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_proforma');

        // Response Filename
        if ($this->volatiletask) {
            $mform->addElement('text', 'responsefilename', get_string('filename', 'qtype_proforma'), array('size' => '60'));
            $mform->addRule('responsefilename', null, 'required', null, 'client');
        } else {
            // Further templates (there should be no other templates)
            $this->add_static_field($mform, 'furtherTemplates', get_string('templates', 'qtype_proforma'),
                    'templates');

            $mform->addHelpButton('furtherTemplates', 'templates_hint', 'qtype_proforma');

            $this->add_static_field($mform, 'responsefilename', get_string('filename', 'qtype_proforma'));
        }
        $mform->hideIf('responsefilename', 'responseformat', 'neq', 'editor');
        $mform->setType('responsefilename', PARAM_TEXT);
        $mform->addHelpButton('responsefilename', 'filename_hint', 'qtype_proforma');
    }

    /**
     * Get the type sets passed.
     *
     * @param string $types The space , ; separated list of types
     * @return array('groupname', 'mime/type', ...)
     */
/*    private function get_typesets($types) {
        $sets = array();
        if (!empty($types)) {
            $sets = preg_split('/[\s,;:"\']+/', $types, null, PREG_SPLIT_NO_EMPTY);
        }
        return $sets;
    }
*/

    private function add_static_field($mform, $field, $label, $sizefield = null) {
        // $mform->addElement('static', $field, $label);
        if (isset($sizefield)) {
            if (isset($this->question->options->$sizefield)) {
                $value = $this->question->options->$sizefield;
                $attributes = array('size' => strlen($this->question->options->$sizefield));
            } else if (isset($this->question->$sizefield)) {
                $value = $this->question->$sizefield;
                $attributes = array('size' => strlen($this->question->$sizefield));
            }
            // create hidden elements
            // Ich dachte, die kriege ich üebr den behat-test, aber das funzt nicht

            // $mform->addElement('hidden', $sizefield, $value);
            // $mform->setType($sizefield, PARAM_TEXT);
        } else {
            $attributes = array('size' => strlen($this->question->options->$field));
        }

        if (isset($attributes) && count($attributes) > 0) {
            $mform->addElement('static', $field, $label, $attributes, '');
        } else {
            $mform->addElement('static', $field, $label);
        }

        $mform->setType($field, PARAM_TEXT);
        // $mform->getElement($field)->setPersistantFreeze(false);

        // as hidden field???
        // $mform->addElement('hidden', $field);

        // $mform->disabledIf($field, 'responseformat', 'neq', 'always');
        // $mform->freeze($field);

        // $mform->freeze();
        // $mform->setConstants(array('taskpath' => $norepeats));
    }

    protected function add_grader_settings($mform) {
        $taskisimported = isset($this->question->options->taskstorage) &&
                $this->question->options->taskstorage == qtype_proforma::PERSISTENT_TASKFILE;

        if (!$taskisimported)
            return;

        // ProFormA fields
        $mform->addElement('header', 'graderoptions_header', get_string('graderoptions_header', 'qtype_proforma'));

        // Task Filename
        if ($taskisimported || $this->volatiletask) {
            $mform->addElement('static', 'link', get_string('taskfilename', 'qtype_proforma'), '');
            $mform->setType('link', PARAM_TEXT);
            $mform->addHelpButton('link', 'taskfilename_hint', 'qtype_proforma');
        } else {
            // Repository
            $mform->addElement('text', 'taskrepository', get_string('repository', 'qtype_proforma'), array('size' => '60'));
            $mform->setType('taskrepository', PARAM_TEXT);
            $mform->addRule('taskrepository', null, 'required', null, 'client');
            $mform->addHelpButton('taskrepository', 'repository_hint', 'qtype_proforma');
            // $mform->setDefault('taskrepository', get_config('qtype_proforma', 'repositoryhost'));
            // $mform->hardFreeze('taskrepository');
            // Task Path
            $mform->addElement('text', 'taskpath', get_string('taskpath', 'qtype_proforma'), array('size' => '80'));
            $mform->setType('taskpath', PARAM_TEXT);
            $mform->addRule('taskpath', null, 'required', null, 'client');
            $mform->addHelpButton('taskpath', 'taskpath_hint', 'qtype_proforma');
            // $mform->setDefault('taskpath', '');
            // $mform->hardFreeze('taskpath');
        }

        // UUID
        if (!$taskisimported) { // !isset($this->question->id)) {
            // create new question
            $mform->addElement('text', 'uuid', get_string('uuid', 'qtype_proforma'), array('size' => '60'));
            $mform->addRule('uuid', null, 'required', null, 'client');
        } else {
            // change existing question => do not edit UUID
            $this->add_static_field($mform, 'uuid', get_string('uuid', 'qtype_proforma'));
            // $mform->addRule('uuid', null, 'required', null, 'client');
            // $mform->addElement('static', 'uuid', get_string('uuid', 'qtype_proforma'));
        }
        $mform->setType('uuid', PARAM_TEXT);
        $mform->addHelpButton('uuid', 'uuid_hint', 'qtype_proforma');

        $mform->addElement('static', 'proformaversion', 'ProFormA Version');
    }

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
            // create new question
            $mform->addElement('hidden', 'taskstorage', qtype_proforma::VOLATILE_TASKFILE);
            $proglangooptions = array('Java', get_string('other', 'qtype_proforma'));
            $mform->addElement('select', 'proglang',
                    get_string('proglang', 'qtype_proforma'), $proglangooptions);
            $mform->addHelpButton('proglang', 'proglang_hint', 'qtype_proforma');

            // override default penalty
            $mform->setDefault('proglang', 'Java');
        } else {
            $mform->addElement('hidden', 'taskstorage', qtype_proforma::PERSISTENT_TASKFILE);

            // Attachments for Question Text (Downloads)
            $this->add_static_field($mform, 'downloadlist', get_string('downloads', 'qtype_proforma'),
                    'downloads');
            $mform->addHelpButton('downloadlist', 'downloads_hint', 'qtype_proforma');

            // Model Solution files (instead of modelsollist we show links)
            $mform->addElement('static', 'mslinks', get_string('modelsolfiles', 'qtype_proforma'), '');
            $mform->addHelpButton('mslinks', 'modelsolfiles_hint', 'qtype_proforma');
            // $mform->addHelpButton('mslinks', 'modelsolfiles_hint', 'qtype_proforma');
        }
        $mform->setType('taskstorage', PARAM_RAW);

        $this->add_response_options($mform, $qtype);

        $this->add_test_settings();

        if (!$this->volatiletask) {
            $this->add_grader_settings($mform);
        }

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

    private function get_count_unittests($taskhandler) {
        $repeats = 0;
        // Get number of unit tests from (lms) grading hints.
        // In case of an imported task this ist the number of all tests (not just unit tests).
        if (isset($this->question) && isset($this->question->options) && isset($this->question->options->gradinghints)) {
            $repeats = $taskhandler->get_count_unit_tests($this->question->options->gradinghints);
        }

        if ($this->volatiletask) {
            // In case of manually added unit tests we need to know how many tests are actually present:
            // (unfortunately there is no function to get this from Moodle core)
            $currentrepeats = optional_param('option_repeats', 1, PARAM_INT);
            $addfields = optional_param('option_add_fields', '', PARAM_TEXT);
            if (!empty($addfields)) {
                $currentrepeats += 1;
            }
            if ($currentrepeats > $repeats) {
                $repeats = $currentrepeats;
            }
        }
        return $repeats;
    }

    protected function add_test_settings() {
        $mform = $this->_form;
        $taskhandler = null;

        // Header.
        $mform->addElement('header', 'test_header', get_string('tests', 'qtype_proforma'));
        $mform->setExpanded('test_header');

        // Aggreagation strategy.
        $aggregationstrategy = array(
                qtype_proforma::ALL_OR_NOTHING => get_string('all_or_nothing', 'qtype_proforma'),
                qtype_proforma::WEIGHTED_SUM  => get_string('weighted_sum', 'qtype_proforma')
        );
        $mform->addElement('select', 'aggregationstrategy',
                get_string('aggregationstrategy', 'qtype_proforma'), $aggregationstrategy);
        $mform->addHelpButton('aggregationstrategy', 'aggregationstrategy', 'qtype_proforma');
        $mform->setDefault('aggregationstrategy', qtype_proforma::WEIGHTED_SUM);

        // Tests
        $this->add_tests($mform);

        // Penalty
        $penalties = array(
                1.0000000,
                0.5000000,
                0.3333333,
                0.2500000,
                0.2000000,
                0.1000000,
                0.0000000
        );
        if (!empty($this->question->penalty) && !in_array($this->question->penalty, $penalties)) {
            $penalties[] = $this->question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
                get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions);
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'question');
        $mform->setDefault('penalty', get_config('qtype_proforma', 'defaultpenalty'));
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
            $taskfilehandler = new qtype_proforma_java_task;
            $taskfilehandler->extract_formdata_from_taskfile($cat, $question);
        } else {
            $taskfilehandler = new qtype_proforma_proforma_task;
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

        /*
                    if (!isset($question->tests)) {
                        $this->extract_data_from_taskfile($cat, $question);
                    }
                    if (isset($question->tests)) {
                        $key = 0;
                        foreach ($question->tests as $test) {
                            unset($this->_form->_defaultValues["testtitle[{$key}]"]);
                            unset($this->_form->_defaultValues["testid[{$key}]"]);
                            $key++;
                            $question->testid[] = $test['id'];
                            $question->testtitle[] = $test['title'];
                        }
                    }

                    if (isset($question->gradinghints)) {
                        $key = 0;
                        foreach ($question->gradinghints as $gh) {
                            unset($this->_form->_defaultValues["testweight[{$key}]"]);
                            $key++;
                            $question->testweight[] = $gh['weight'];
                        }
                    }
        */

        if (!empty($question->modelsolfiles)) {
            $question->mslinks = '';
            foreach (explode(',', $question->modelsolfiles) as $ms) {
                $url = moodle_url::make_pluginfile_url($cat, 'qtype_proforma',
                        qtype_proforma::FILEAREA_MODELSOL, $question->id, '/', $ms);
                $question->mslinks = $question->mslinks . '<a href=' . $url->out().'>'. $ms .'</a> ';
            }
        }

        return $question;
    }

    /** create
     * - test overview in case of imported task and
     * - test edit fields for tasks created with Moodle
     * @param $mform
     */
    protected function add_tests($mform) {
        $taskhandler = null;
        // Compilation test options for Java.
        if ($this->volatiletask) {
            $taskhandler = new qtype_proforma_java_task();
            $compilegroup = array();
            $compilegroup[] =& $mform->createElement('advcheckbox', 'compile', '', '');
            $this->add_test_weight_option($compilegroup, 'compile', '0');
            $mform->addGroup($compilegroup, 'compilegroup', get_string('compile', 'qtype_proforma'), ' ', false);
            $mform->hideIf('compileweight', 'compile');
            $mform->setDefault('compile', 1);
        } else {
            $taskhandler = new qtype_proforma_proforma_task();
        }

        // retrieve number of tests (resp. unit tests)
        $repeats = $this->get_count_unittests($taskhandler);
        if ($repeats > 0) {
            // Unit tests resp. tests from imported task
            // Create row:
            $testoptions = array();
            $this->add_test_weight_option($testoptions, 'test', '1', true);
            $testoptions[] = $mform->createElement('text', 'testid', 'Id', array('size' => 3));
            $testoptions[] = $mform->createElement('text', 'testtype',
                    get_string('testtype', 'qtype_proforma'), array('size' => 80));
            $testoptions[] = $mform->createElement('text', 'testdescription',
                    get_string('testdescription', 'qtype_proforma'), array('size' => 80));

            if ($this->volatiletask) {
                // Add textarea for unit test code.
                $testoptions[] = $mform->createElement('textarea', 'testcode',
                        get_string('code', 'qtype_proforma'), 'rows="20" cols="80"');
                $label = get_string('junittestlabel', 'qtype_proforma'); // use different label
            } else {
                $label = get_string('testlabel', 'qtype_proforma');
            }

            $repeatarray = array();
            $repeatarray[] = $mform->createElement('group', 'testoptions', $label, $testoptions, null, false);
            $repeatoptions = array();
            $repeatoptions['testweight']['default'] = 1;
            $repeatoptions['testtitle']['default'] = get_string('junittesttitle', 'qtype_proforma');
            $repeatoptions['testdescription']['default'] = '';
            //$repeateloptions['testfilename']['default'] = '';
            $repeatoptions['testtype']['default'] = 'unittest'; // JAVA-JUNIT
            $repeatoptions['testid']['default'] = '{no}'; // JAVA-JUNIT

            // $repeateloptions['testweight']['rule'] = 'numeric';
            if ($this->volatiletask) {
                // Hide testtype and test identifier for unit tests.
                // So far (Moodle 3.6) hideif is not implemented for groups => quickhack.
                // (needed from creating grading hints)
                for ($i = 0; $i < $repeats; $i++) {
                    $mform->hideif('testtype[' . $i . ']', 'aggregationstrategy', 'neq', 111);
                    $mform->hideif('testid[' . $i . ']', 'aggregationstrategy', 'neq', 111);
                }
                // does not work
                //$repeatoptions['testtitle']['rule'] = 'required'; // array(null, 'required', null, 'client');
            } else {
                // disable testtype and test identifier for imported tasks
                $repeatoptions['testid']['disabledif'] = array('aggregationstrategy', 'neq', 111);
                $repeatoptions['testtype']['disabledif'] = array('aggregationstrategy', 'neq', 111);
            }

            // $repeateloptions['testweight']['helpbutton'] = 'Hilfetext';
            $mform->setType('testdescription', PARAM_TEXT);
            $mform->setType('testtitle', PARAM_TEXT);
            $mform->setType('testweight', PARAM_FLOAT);
            $mform->setType('testid', PARAM_RAW);
            $mform->setType('testtype', PARAM_RAW);
            // $mform->addRule('testtitle', null, 'required', null, 'client');
            //$mform->setType('testfilename', PARAM_TEXT);

            // $mform->disabledIf('testweight', 'aggregationstrategy', 'neq', qtype_proforma::WEIGHTED_SUM);

            $this->repeat_elements($repeatarray, $repeats,
                    $repeatoptions, 'option_repeats', 'option_add_fields',
                    1, get_string('addjunit', 'qtype_proforma'), true);

            if ($this->volatiletask) {
                // Set CodeMirror for unit test code.
                for ($i = 0; $i < $repeats; $i++) {
                    qtype_proforma::as_codemirror('id_testcode_' . $i);
                    //$mform->addRule('testcode_' . $i, null, 'required', null, 'client');
                }
            } else {
                // Remove button for adding new test elements.
                $mform->removeElement('option_add_fields');
            }
        } else {
            $mform->addElement('static', 'no_tests', get_string('notests', 'qtype_proforma'), '');
        }

        if ($this->volatiletask) {
            // Create a Checkstyle test (not part of the repeat group).
            $testoptions = array();
            $testoptions[] =& $mform->createElement('advcheckbox', 'checkstyle', '', '');
            $this->add_test_weight_option($testoptions, 'checkstyle', '0.2');
            $mform->addGroup($testoptions, 'checkstyleoptions', 'Checkstyle',
                    array(' '), false);

            $mform->addElement('textarea', 'checkstylecode', '', 'rows="20" cols="80"');
            qtype_proforma::as_codemirror('id_checkstylecode', 'xml');
            $mform->hideIf('checkstyleweight', 'checkstyle');
            $mform->hideIf('checkstylecode', 'checkstyle');
        }
    }
}

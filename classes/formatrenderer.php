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
 * The ProFormA Question format renderer classes (code bases upon essay question renderer from Moodle core)
 *
 * @package    qtype_proforma
 * @copyright  2009 The Open University
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 *             (The Open University for essay base)
 */

// Load JQuery for CodeMirror resizing. This cannot be done
// inside a function.

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/proforma/locallib.php');
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');

global $PAGE;
if (!$PAGE->requires->is_head_done()) {
    // This is_head_done check avoids a debugging error message telling us
    // that we cannot add jquery after starting page output.

    // But: if the jquery calls are missing then Codemirror resizing does not work.
    // This happens for the preview window for a specific step in the grades history :-(.
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    $PAGE->requires->css('/question/type/proforma/amd/src/editor.css');
    // Other codemirror themes.
    $PAGE->requires->css('/question/type/proforma/amd/src/abcdef.css');
    // Main Codemirror theme is defined in plugin styles.css to have the definitions
    // in the review history windows.
    // Otherwise the question history window looks a bit strange.
    // $PAGE->requires->css('/question/type/proforma/amd/src/darcula.css');
}

/**
 * Abstract base class for all format renderer.
 */
abstract class qtype_proforma_format_renderer_base extends plugin_renderer_base {

    // Hack.
    public static $codemirrorid = null;

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        // Reset codemirror variable because feedback renderer
        // does not know formatrenderer instance.
        self::$codemirrorid = null;
        parent::__construct($page, $target);
    }

    abstract public function response_area_input($qa, $step, /*question_display_options*/ $options);
    abstract protected function class_name();
    abstract public function response_area_read_only($qa, $step, $options);
    /**
     * @return bool false: the student submission can have no attachments
     */
    public function can_have_attachments() {
        return false;
    }
    abstract public function answerfieldname();
}

/**
 * A renderer for questions where the student needs to upload files
 */
class qtype_proforma_format_filepicker_renderer extends qtype_proforma_format_renderer_base {

    /**
     * returns the html fragment for the reponse area in readonly mode
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_read_only($qa, $step, $options) {
        // return '';
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $output = array();

        foreach ($files as $file) {
            $output[] = html_writer::tag('p', html_writer::link($qa->get_response_file_url($file),
                $this->output->pix_icon(file_file_icon($file), get_mimetype_description($file),
                    'moodle', array('class' => 'icon')) . ' ' . s($file->get_filename())));
        }
        return implode($output);
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_input($qa, $step, /*question_display_options*/ $options) {
        $question = $qa->get_question();
        global $CFG;
        require_once($CFG->dirroot . '/lib/form/filemanager.php');

        $pickeroptions = new stdClass();
        $pickeroptions->mainfile = null;
        $pickeroptions->maxfiles = $question->attachments;
        // $pickeroptions->areamaxbytes = $question->maxbytes;
        $pickeroptions->maxbytes = $question->maxbytes;
        // $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);
        $pickeroptions->context = $options->context;
        $pickeroptions->accepted_types = $question->filetypes;
        $pickeroptions->return_types = FILE_INTERNAL | FILE_CONTROLLED_LINK;

        $pickeroptions->itemid = $qa->prepare_response_files_draft_itemid(
            'attachments', $options->context->id);

        $fm = new form_filemanager($pickeroptions);
        $filesrenderer = $this->page->get_renderer('core', 'files');
        return $filesrenderer->render($fm) . html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $pickeroptions->itemid));


//         return '';
    }

    /**
     * @return bool true: the student submission can have attachments
     */
    public function can_have_attachments() {
        return true;
    }

    /** @return string returns the class name */
    protected function class_name() {
        return 'qtype_proforma_filepicker';
    }

    /**
     * @return string: returns the name of the answer step field
     */
    public function answerfieldname() {
        return ANSWER; // Attachments are not stored here.
    }
}

/**
 * A renderer for questions where the student enters text into editor
 */
class qtype_proforma_format_editor_renderer extends qtype_proforma_format_renderer_base {

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_input($qa, $step, /*question_display_options*/ $options) {
        $name = ANSWER;
        $question = $qa->get_question();
        $lines = $question->responsefieldlines;
        $mode = $question->programminglanguage;
        $inputname = $qa->get_qt_field_name($name);
        $id = $this->get_textarea_id($qa);

        $attributes = array();
        $attributes['name'] = $inputname;
        $attributes['id'] = $id;
        $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
        $attributes['rows'] = $lines;
        $attributes['cols'] = 60;

        $input = html_writer::tag('textarea', s($step->get_qt_var($name)), $attributes);
        $input .= html_writer::empty_tag('input', array('type' => 'hidden',
                'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));
        // Convert textarea to codemirror editor.
        qtype_proforma\lib\as_codemirror($id, $mode, null, false, false);
        // Remember Codemirror id.
        self::$codemirrorid = $id;
        return $input;
    }


    /**
     * returns the html identfier for the textarea
     * @param $qa
     * @return string
     */
    protected function get_textarea_id($qa) {
        return 'id_' . $qa->get_qt_field_name(ANSWER);
    }

    /** @return string returns the class name */
    protected function class_name() {
        return 'qtype_proforma_editor';
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_read_only($qa, $step, $options) {
        $name = ANSWER;
        $question = $qa->get_question();
        $mode = $question->programminglanguage;
        $id = $this->get_textarea_id($qa);

        $attributes = array();
        $attributes['id'] = $id;
        $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
        $attributes['rows'] = $question->responsefieldlines;
        $attributes['cols'] = 60;
        $attributes['readonly'] = 'readonly';

        $input = html_writer::tag('textarea', s($step->get_qt_var($name)), $attributes);

        // Convert textarea to codemirror editor.
        qtype_proforma\lib\as_codemirror($id, $mode, null, true, false);
        // Remember Codemirror id.
        self::$codemirrorid = $id;
        return $input;
    }

    /**
     * @return string: returns the name of the answer step field
     */
    public function answerfieldname() {
        return ANSWER;
    }

}



/**
 * A renderer for questions where the student uses a version control system
 */
class qtype_proforma_format_versioncontrol_renderer extends qtype_proforma_format_renderer_base {

    private $name = VCSINPUT;

    /**
     * checks if question contains input field in URI template
     * @param $question
     * @return bool
     * @throws coding_exception
     */
    private function has_input_field($question) {
        if ($question->responseformat != qtype_proforma::RESPONSE_VERSION_CONTROL) {
            throw new coding_exception('unexpected responseformat in qtype_proforma_format_versioncontrol_renderer');
        }

        return (strpos($question->vcsuritemplate, PHINPUT) !== false);
    }

    /**
     * checks if question contains group field in URI template
     * @param $question
     * @return bool
     * @throws coding_exception
     */
    private function has_group_field($question) {
        if ($question->responseformat != qtype_proforma::RESPONSE_VERSION_CONTROL) {
            throw new coding_exception('unexpected responseformat in qtype_proforma_format_versioncontrol_renderer');
        }

        return ((strpos($question->vcsuritemplate, PHGROUP) !== false) or
            (strpos($question->vcsuritemplate, PHGROUPL) !== false));
    }

    /**
     * checks if question contains username field in URI template
     * @param $question
     * @return bool
     * @throws coding_exception
     */
    /*
    * USERFIELD is currently not supported
    private function has_user_field($question) {
        if ($question->responseformat != qtype_proforma::RESPONSE_VERSION_CONTROL) {
            throw new coding_exception('unexpected responseformat in qtype_proforma_format_versioncontrol_renderer');
        }

        return (strpos($question->vcsuritemplate, PHUSERNAME) !== FALSE);
    }
    */
    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_input($qa, $step, /*question_display_options*/ $options) {
        $input = '';
        $question = $qa->get_question();
        if ($this->has_input_field($question)) {
            $this->name = VCSINPUT;
            $inputname = $qa->get_qt_field_name($this->name);
            $id = 'id_' . $qa->get_qt_field_name($this->name);

            $attributes = array();
            $attributes['name'] = $inputname;
            $attributes['id'] = $id;
            $attributes['type'] = 'text';
            $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
            $attributes['size'] = 20;
            $attributes['value'] = s($step->get_qt_var($this->name));

            $input = html_writer::tag('label', $question->vcslabel, array('for' => $inputname));
            $input .= html_writer::tag('input', '' /*s($step->get_qt_var($name))*/, $attributes);
            $input .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));

        } else if ($this->has_group_field($question)) {
            global $COURSE;
            $this->name = VCSGROUP;
            $groupname = qtype_proforma\lib\get_groupname($options->context);
            $attributes = array();
            $id = 'id_' . $qa->get_qt_field_name($this->name);
            $inputname = $qa->get_qt_field_name($this->name);
            $attributes['name'] = $inputname;
            $attributes['id'] = $id;
            $attributes['class'] = $this->class_name() . ' qtype_proforma_response';
            $attributes['value'] = $groupname;
            if (qtype_proforma\lib\is_teacher()) {
                // $attributes['readonly'] = 'true';
                $attributes['type'] = 'text';
                $attributes['size'] = 10;
                $input = html_writer::tag('label', get_string('groupname', 'qtype_proforma') . ': ', array('for' => $inputname));
                $input .= html_writer::tag('input', '', $attributes);
                try {
                    // Get actual groupname if any.
                    $samplename = qtype_proforma\lib\get_groupname_sample();
                    if (!empty($samplename)) {
                        // Display URI with sample groupname in order to avoid problems
                        // with groups named 'group1' and URI template expecting '1' as
                        // group name.
                        $uri = str_replace('{group}', '<b>' . $samplename . '</b>', $question->vcsuritemplate);
                        $uri = str_replace('{groupl}', '<b>' . mb_strtolower($samplename) . '</b>', $uri);
                        $input .= '<br>' . html_writer::tag('small', get_string('sampleuri', 'qtype_proforma') . ': ' . $uri);
                    }
                } catch (Exception $ex) {
                    debugging('exception occured when getting groupname sample');
                }
            } else {
                $attributes['type'] = 'hidden';
                $input = get_string('groupname', 'qtype_proforma') . ': ' . $groupname;
                $input .= html_writer::tag('input', '', $attributes);
            }
            $input .= html_writer::empty_tag('input', array('type' => 'hidden',
                    'name' => $inputname . 'format', 'value' => FORMAT_PLAIN));

            return html_writer::tag('div', $input, array('class' => VCSGROUP));

        }

        return $input;
    }

    /** @return string returns the class name */
    protected function class_name() {
        return 'qtype_proforma_versioncontrol';
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $lines
     * @param $options
     * @return string
     */
    public function response_area_read_only($qa, $step, $options) {
        if (is_a ($step, 'question_attempt_step_read_only')) {
            return '';
        }

        if (null !== $step->get_qt_var(VCSINPUT)) {
            $question = $qa->get_question();
            return $question->vcslabel . ' '. s($step->get_qt_var(VCSINPUT));
        } else if (null !== $step->get_qt_var(VCSGROUP)) {
            return get_string('groupname', 'qtype_proforma') . ': '. s($step->get_qt_var(VCSGROUP));
        } else if (null !== $step->get_qt_var[VCSUSERNAME]) {
            return 'User '. ': '. s($step->get_qt_var(VCSUSERNAME));
        }

        return '???';
    }

    /**
     * @return string: returns the name of the answer step field
     */
    public function answerfieldname() {
        return $this->name;
    }
}


/**
 * A renderer for questions where the student needs to upload multiple files
 * that can be edited in a mixture of editor and explorer on client side.
 */
class qtype_proforma_format_explorer_renderer extends qtype_proforma_format_renderer_base {

    /**
     * returns the html fragment for the reponse area in readonly mode
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_read_only($qa, $step, $options) {
        // debugging('---');
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $itemid = null;
        $responsefiles = [];
        foreach ($files as $file) {
            // debugging('file: ' . $file->get_filepath() . $file->get_filename());
            // var_dump($file);
            $itemid = $file->get_itemid();
            $responsefiles[] = $file->get_filepath() . $file->get_filename();
        }

        // debugging('itemid = ' . $itemid);
        // debugging('context = ' . $options->context->id);
// DEBUGGING

        $clientid = uniqid();

        $params = new stdClass();
        $params->contextid = $options->context->id;
        $params->itemid = $itemid;
        $params->readonly = true;
        $params->subdirs = true;
        $params->usageid = $qa->get_usage_id();
        $params->slot = $qa->get_slot();
        $params->files = $responsefiles;
        $params->rootnode = get_string('rootsubmission', 'qtype_proforma');

        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_proforma/explorer', 'createExplorer',
            array('fileexplorer_' . $clientid, $params));

        return html_writer::tag('div', '', array('id' => 'fileexplorer_' . $clientid)) .
            html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $itemid));
    }

    /**
     * returns the html fragment for the reponse area in input mode
     *
     * @param $qa
     * @param $step
     * @param $options
     * @return string
     */
    public function response_area_input($qa, $step, /*question_display_options*/ $options) {
        $question = $qa->get_question();

        /////// $draftid = file_get_unused_draft_itemid();
/*
        $files = $qa->get_last_qt_files('attachments', $options->context->id);
        $itemid = null;
        $responsefiles = [];
        foreach ($files as $file) {
            debugging('file: ' . $file->get_itemid() . ' => ' . $file->get_filepath() . $file->get_filename());
            // var_dump($file);
            $itemid = $file->get_itemid();
            $responsefiles[] = $file->get_filepath() . $file->get_filename();
        }
        debugging('itemid = ' . $itemid);
        debugging('context = ' . $options->context->id);
*/

        $itemid = $qa->prepare_response_files_draft_itemid('attachments', $options->context->id);

        // $sql = 'select * from mdl_files where filearea="draft" and itemid=' . $itemid . ';';
        // debugging('---');
        // debugging($sql);
        $clientid = uniqid();
        $defaults = array(
            'readonly' => false,
            'areamaxbytes' => $question->maxbytes,
            'maxbytes' => $question->maxbytes,
            'maxfiles' => -1,
            'itemid' => $itemid,
            'subdirs' => 1,
            'client_id' => $clientid,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL,
            'rootnode' => get_string('rootsubmission', 'qtype_proforma'),
        );

        $context = $options->context;

        // debugging('itemid = ' . $itemid);
        // debugging('context = ' . $options->context->id);
        $repo = repository::get_instances(array('type' => 'upload', 'currentcontext' => $context));
        if (empty($repo)) {
            throw new moodle_exception('errornouploadrepo', 'moodle');
        }
        $repo = reset($repo); // Get the first (and only) upload repo.

        $fs = get_file_storage();
        // initialise options, getting files in root path
        $params = new stdClass();
        // $params = file_get_drafarea_files($defaults['itemid'], '/');
        $params->repo_id = $repo->id;
        foreach ($defaults as $name=>$value) {
            $params->$name = $value;
        }
        global $USER;
        $usercontext = context_user::instance($USER->id);
        $params->contextid = $usercontext->id;
        $params->explorerautosave = get_config('qtype_proforma', 'explorerautosave');
        // debugging('usercontext = ' . $params->contextid);

        // $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $options->itemid, 'id', true);
        // $options->filecount = count($files);
        // var_dump($options);
        global $PAGE;
        $PAGE->requires->js_call_amd('qtype_proforma/explorer', 'createExplorer',
            array('fileexplorer_' . $clientid, $params));

        return html_writer::tag('div', '', array('id' => 'fileexplorer_' . $clientid)) .
        html_writer::empty_tag(
                'input', array('type' => 'hidden', 'name' => $qa->get_qt_field_name('attachments'),
                'value' => $itemid));
    }

    /**
     * @return bool true: the student submission can have attachments
     */
    public function can_have_attachments() {
        return true;
    }

    /** @return string returns the class name */
    protected function class_name() {
        return 'qtype_proforma_explorer';
    }

    /**
     * @return string: returns the name of the answer step field
     */
    public function answerfieldname() {
        return ATTACHMENTS; // Attachments are not stored here.
    }
}
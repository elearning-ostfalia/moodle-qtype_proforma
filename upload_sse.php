<?php

require_once(__DIR__.'/../../../config.php');
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->libdir . '/externallib.php');

// Create events.
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');



try {
    require_sesskey();
    if (!isloggedin()) {
        throw new Exception('user is not logged in');
    }

    $itemid    = optional_param('itemid', -1, PARAM_INT);
    if ($itemid > 0) {
        // Task comes from Javascript taskeditor and is only created in draft area
        // and may be without a question.
        global $USER;
        // Item id is set which means that there is a temporary task
        // in the draft area that shall be uploaded to the grader.
        $contextid = required_param('contextid', PARAM_INT);
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);
        if (!isset($context)) {
            throw new invalid_parameter_exception('invalid context');
        }
        external_api::validate_context($context);
        if ($context->contextlevel == CONTEXT_COURSE) {
            require_capability('moodle/question:editmine', $context);
        }

        $filename = required_param('filename', PARAM_FILE);
        $fs = get_file_storage();
        $task = $fs->get_file($contextid, 'user', 'draft', $itemid, '/', $filename);
        if ($task === null) {
            throw new invalid_parameter_exception('No task file available');
        }

        // Use default grader and set upload URI
        $protocolhost = trim(get_config('qtype_proforma', 'graderuri_host'));
        $path = trim(get_config('qtype_proforma', 'uploaduri_path'));
        $uri = $protocolhost . $path;

        $grader = new \qtype_proforma_grader_2($uri);
    } else {
        // Get and validate question id.
        $id = required_param('id', PARAM_INT);

        $question = question_bank::load_question($id);
        // Consistency checks.
        if ($question == null) {
            throw new invalid_parameter_exception('no question');
        }
        if (get_class($question) != 'qtype_proforma_question') {
            throw new invalid_parameter_exception('invalid question type');
        }

        // Security checks
        global $DB;
        $contextid = $DB->get_field('question_categories', 'contextid', array('id'=>$question->category));
        $context = \context::instance_by_id($contextid, IGNORE_MISSING);
        if (isset($context)) {
            external_api::validate_context($context);
            if ($context->contextlevel == CONTEXT_COURSE) {
                require_capability('moodle/question:editmine', $context);
            }
        }
        $task = $question->get_task_file();
        if (!$task instanceof stored_file) {
            throw new coding_exception("task variable has wrong class");
        }
        $path = trim(get_config('qtype_proforma', 'uploaduri_path'));
        $grader = new \qtype_proforma_grader_2($question->get_uri($path));
    }


    list($graderoutput, $httpcode) = $grader->upload_task_to_grader($task);
    if ($graderoutput === True) {
        $graderoutput = 'successfully started';
    }

} catch (Exception $ex) {
    // Send error message as event data
    echo "data: $ex\n\n";
}




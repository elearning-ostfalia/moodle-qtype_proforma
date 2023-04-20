<?php

require_once(__DIR__.'/../../../config.php');
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
        require_capability('moodle/question:editmine', $context);
    }

    $grader = new \qtype_proforma_grader_2($question->get_uri('/api/v2/upload'));

    list($graderoutput, $httpcode) = $grader->upload_task_to_grader($question);
    if ($graderoutput === True) {
        $graderoutput = 'successfully started';
    }

} catch (Exception $ex) {
    // Send error message as event data
    echo "data: $ex\n\n";
}





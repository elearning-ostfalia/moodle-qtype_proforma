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
 * class for creating feedback HTML
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Define a custom exception class
 */
class feedback_exception extends Exception
{
    // Redefine the exception so message isn't optional.
    /* public function __construct($message, $code = 0, Exception $previous = null) {
        // Call parent constructor.
        parent::__construct($message, $code, $previous);
    } */

    // Custom string representation of object.
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

/**
 * class for rendering the grader response (i.e. feedback of question)
 */
class feedback_renderer {
    /**
     * reference to qtype_proforma_renderer (main renderer)
     * @var qtype_proforma_renderer|null
     */
    private $mainrenderer = null;

    /** @var int sum of all weights */
    private $totalweight = 0;

    /**
     * @var null Grading hints of (Moodle not ProformA) question.
     */
    private $gradinghints = null;

    /**
     * @var null reference to question attempt
     */
    private $qa = null;
    /**
     * feedback_renderer constructor.
     *
     * @param qtype_proforma_renderer $renderer
     */
    public function __construct(qtype_proforma_renderer $renderer) {
        $this->mainrenderer = $renderer;
    }

    /**
     * creates the html fragmenbt for a single freedback element
     * @param $feedback
     * @param bool $teacher
     * @param bool $subtest
     * @param bool $printpassedinfo
     * @param bool $passed
     * @param bool $general
     * @return string
     */
    private function render_single_feedback($feedback, $teacher = false, $subtest = false,
            $printpassedinfo = false, $passed = false, $general = false) {

        if ($teacher && !qtype_proforma\lib\is_teacher()) {
            return '';
        }

        // Extract properties.
        $level = (string) $feedback['level'];
        $title = (string) $feedback->title;
        // Escaped character sequence is replaced by non-escaped character.
        $content = (string) $feedback->content;
        $format = (string) $feedback->content['format'];
        $result = '';

        if ($general) {
            // Feedback independend from tests.
            $csstitle = array();
            $csscontent = array('class' => 'proforma_general');
        } else if ($subtest) {
            // Detailed subtest results.
            $csstitle = array('class' => 'proforma_subtest_title');
            $csscontent = array('class' => 'proforma_subtest_testlog');
            if ($printpassedinfo) {
                $truefeedbackimg = $this->mainrenderer->feedback_image((int) 1);
                $falsefeedbackimg = $this->mainrenderer->feedback_image((int) 0);
                $title = ($passed ? $truefeedbackimg : $falsefeedbackimg) . $title;
            } else {
                // Adjust left space.
                $csstitle = array('class' => 'proforma_subtest_title_2');
            }
        } else {
            // Simple logs.
            $csstitle = array('class' => 'proforma_testlog_title');
            $csscontent = array('class' => 'proforma_testlog');
        }

        // Todo: show different level.
        switch ($level) {
            case 'error':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'debug':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'warn':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            case 'info':
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
            default:
                $result .= html_writer::tag('div', $title, $csstitle);
                break;
        }

        if (strlen($content) > 0) {
            switch ($format) {
                case 'plaintext':
                    // We have to escape the special html characters again.
                    $result .= html_writer::tag('pre', htmlspecialchars($content), $csscontent);
                    break;
                case 'html':
                    $csscontent['class'] .= ' proforma_html';
                    $result .= html_writer::tag('p', $content, $csscontent);
                    break;
                default:
                    debugging('missing or invalid format for feedback (student/teacher):' . $format);
                    break;
            }
        }

        return $result;
    }

    /**
     * creates the html fragment for a subtest result
     * @param $testresult
     * @return string
     */
    private function render_subtest_result($testresult) {
        $passed = (string) $testresult->result->score === '1.0';
        $result = '';
        foreach ($testresult->{'feedback-list'} as $feedbacklist) {
            $count = 0;
            foreach ($feedbacklist->{'student-feedback'} as $feedback) {
                $result .= $this->render_single_feedback($feedback, false, true,
                        $count === 0, $passed);
                $count++;
            }
            // Print further teacher feedback if any.
            if (qtype_proforma\lib\is_teacher() && count($feedbacklist->{'teacher-feedback'})) {
                foreach ($feedbacklist->{'teacher-feedback'} as $feedback) {
                    $result .= $this->render_single_feedback($feedback, true, true);
                }

            }
        }

        return $result;
    }

    /**
     * renders a test with its subtests
     *
     * @param $subtestsresponse
     * @param $result
     */
    private function render_subtest_response($subtestsresponse, &$result) {

        foreach ($subtestsresponse->{'subtest-response'} as $response) {
            $result .= $this->render_subtest_result($response->{'test-result'});
        }
    }

    /**
     * creates the html fragment for a test title
     * @param $test
     * @param $score
     * @param $internalerror
     * @param $result
     * @param $allcorrect
     * @throws feedback_exception
     */
    private function render_test_title($test, $score, $internalerror, &$result, &$allcorrect) {
        $id = (string) $test['id'];
        $ghtest = $this->gradinghints->xpath("//test-ref[@ref='" . $id . "']");
        if (count($ghtest) == 0) {
            throw new feedback_exception('cannot find appropriate grading hints for test "' . $id . '""');
        }

        $ghtest = $ghtest[0];
        $testtitle = $ghtest->title;
        if (!isset($testtitle)) {
            $testtitle = 'Test ' . $id;
        }

        // Create unique identifier for each region
        // since there can be multiple regions per page!
        $collid = $this->mainrenderer->create_collapsible_region_id($this->qa);
        $visiblescore = '';
        if ($this->question->aggregationstrategy == qtype_proforma::WEIGHTED_SUM) {
            $weight = floatval((string) $ghtest['weight']) / $this->totalweight;
            if ($weight > 0.0) {
                // Only display percentage if this test counts more than 0.
                if (isset($score)) {
                    $weightscore = number_format($score * $weight / 1 * 100, 0);
                    $visiblescore = ' (' . $weightscore . '/' . number_format($weight * 100, 0) . ' %)';
                } else {
                    $visiblescore = ' ( ? /' . number_format($weight * 100, 0) . ' %)';
                }
            }
        }

        if ($internalerror) {
            $exclamation = $this->mainrenderer->pix_icon('i/caution', 'info');
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    $exclamation . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        } else if ($score === 1.0) {
            $successimg = $this->mainrenderer->feedback_image((int) 1);
            $result .= print_collapsible_region_start('', $collid,
                    $successimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        } else if ($score === 0.0) {
            $failimg = $this->mainrenderer->feedback_image((int) 0);
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    $failimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        } else {
            $partimg = $this->mainrenderer->feedback_image(0.1);
            $allcorrect = false;
            $result .= print_collapsible_region_start('', $collid,
                    $partimg . ' ' . $testtitle . $visiblescore,
                    '', true, true);
        }

        if (isset($ghtest->description) and strlen($ghtest->description) > 0) {
            $result .= html_writer::tag('span', $ghtest->description, array('class' => 'proforma_testlog_description'));
        }

        if ($internalerror) {
            $result .= html_writer::tag('p', get_string('testinternalerror', 'qtype_proforma'),
                array('class' => 'proforma_testlog_description'));
        }
    }

    private function render_feedback_list(SimpleXMLElement $feedbacklist, &$result) {
        foreach ($feedbacklist->children() as $feedback) {
            $teacher = ($feedback->getName() != "student-feedback");
            if ($teacher) {
                if (qtype_proforma\lib\is_teacher()) {
                    $result .= $this->render_single_feedback($feedback, true);
                }
            } else {
                $result .= $this->render_single_feedback($feedback);
            }
        }
    }


    /**
     * @param $testresponse
     * @param $result
     * @return array
     */
    private function render_test_response($testresponse, &$result): array {
        $containsinternalerror = false;
        $allcorrect = true;
        if (count($testresponse->{'subtests-response'}) == 0) {
            // Handle test with score.
            try {
                $testresult = $testresponse->{'test-result'}->result;
                if (!isset($testresult)) {
                    // Format error: no test result found.
                    throw new feedback_exception('Response format error: no test result available');
                }
                $internalerror = ((string) $testresult['is-internal-error'] === 'true');
                if ($internalerror) {
                    $containsinternalerror = true;
                }
                $score = floatval((string) $testresult->score);
                if (!isset($score)) {
                    // Format error: no score found.
                    throw new feedback_exception('Response format error: no score available');
                }
                $this->render_test_title($testresponse, $score, $internalerror, $result, $allcorrect);
                $this->render_feedback_list($testresponse->{'test-result'}->{'feedback-list'}, $result);
            } catch (Exception $ex) {
                // Display format errors (as much information as possible in order to
                // fix the bug).
                $containsinternalerror = true;
                $allcorrect = false;
                $this->render_test_title($testresponse, null, true, $result, $allcorrect);
                $result .= html_writer::tag('pre', $ex->getMessage(), array('class' => 'proforma_testlog'));
            }
        } else {
            // Handle tests with subtest results.
            list($score, $internalerror) = qtype_proforma_grader_2::calc_score_for_test($testresponse);
            if ($internalerror) {
                $containsinternalerror = true;
            }
            $this->render_test_title($testresponse, $score, $internalerror, $result, $allcorrect);
            $this->render_subtest_response($testresponse->{'subtests-response'}, $result);
        }
        // Collapsible region is created inside render_proforma_test_title.
        $result .= print_collapsible_region_end(true);
        return array($containsinternalerror, $result, $allcorrect);
    }

    /**
     * converts the ProFormA response to html
     *
     * @param $message
     * @param question_attempt $qa
     * @return string
     */
    public function render_proforma2_message($message, $question) {
        $result = '';
        // Check type of response.
        try {
            $response = new SimpleXMLElement($message, LIBXML_PARSEHUGE);
            if (!isset($response->{'separate-test-feedback'})) {
                return $result . 'UNSUPPORTED FEEDBACK FORMAT: ' .
                        html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
            }
        } catch (Exception $e) {
            return $result . 'UNSUPPORTED FEEDBACK FORMAT: ' .
                    html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
        }

        // Preset member variables.
        $gh = new SimpleXMLElement($question->gradinghints);
        $this->gradinghints = $gh->root;
        $this->question = $question;

        // Calculate total weight.
        $this->totalweight = 0;
        foreach ($this->gradinghints->{'test-ref'} as $testresponse) {
            $this->totalweight += floatval((string) $testresponse['weight']);
        }

        // $xpath = new DOMXPath($xmldoc);
        // todo: check namespace!
        // $xpath->registerNamespace('dns','urn:proforma:v2.0');

        // Create general feedback for student and teacher.
        foreach ($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'student-feedback'} as $feedback) {
            $result .= html_writer::tag('p', $this->render_single_feedback($feedback, false,
                    false, false, false, true));
        }
        if (qtype_proforma\lib\is_teacher()) {
            foreach ($response->{'separate-test-feedback'}->{'submission-feedback-list'}->{'teacher-feedback'} as $feedback) {
                $result .= html_writer::tag('p', $this->render_single_feedback($feedback, true,
                        false, false, false, true));
            }
        }

        $allcorrect = true;
        $containsinternalerror = false;

        $tests = $response->{'separate-test-feedback'}->{'tests-response'};
        /*
         * we want to avoid using namespaces in the response!
        if (!$tests->registerXPathNamespace('dns', 'urn:proforma:v2.0')) {
            $containsinternalerror = true;
            $result .= '<p><b>INTERNAL ERROR</b>: unknown response namespace</p>';
        } else {
        */
        // Iterate through all tests in the grading hints instead of
        // iterating through all tests in the response.
        // This guarantees that we detect missing tests in the response and
        // we can reorder the result.
        foreach ($this->gradinghints->{'test-ref'} as $ghtest) {
            $testid = (string)$ghtest['ref'];
            // Look for test in message:
            // Using xpath is more elagant but requires registering the default namespace.
            // $lookup = 'dns::test-response[@id="'.$testid.'"]';
            // In order to be more flexible with the namespace version we just iterate over
            // all tests in the response and seach for the appropriate one.
            $testresponse = null;
            foreach ($tests->{'test-response'} as $resptest) {
                if ($testid == (string)$resptest['id']) {
                    $testresponse = $resptest;
                    break;
                }
            }

            if ($testresponse == null) {
                // Inconsitent response.
                $containsinternalerror = true;
                $result .= '<p><b>INTERNAL ERROR</b>: Result for Test "' . $testid . '" is missing</p>';
            } else {
                list($internalerror, $result, $correct) = $this->render_test_response($testresponse[0], $result);
                if ($internalerror) {
                    $containsinternalerror = true;
                }
                if (!$correct) {
                    $allcorrect = false;
                }
            }
        }

        // Render version control information.
        $result = $this->render_vcs_information($response, $result);
        // Render grading information.
        $result = $this->render_grader_info($message, $response, $result);

        return $result;
    }

    /**
     * generate info text about the grader
     * @param $message
     * @param SimpleXMLElement $response
     * @param string $result
     * @return string
     */
    private function render_grader_info($message, SimpleXMLElement $response, string $result): string {
        if (qtype_proforma\lib\is_admin()) {
            // Infos for admins are displayed as smaller text.
            $result .= html_writer::start_tag('small', null);
            // Show grader info.
            try {
                $graderinfo = $response->{'response-meta-data'}->{'grader-engine'};
                $gradertext = $graderinfo['name'] . ' ' . $graderinfo['version'];
            } catch (Exception $e) {
                // Ignore exception.
                $gradertext = "Grader ???";
            }
            $result .= '<p></p>' . '[' . $gradertext . ']';

            // Debugging: show raw response.
            $qaid = $this->mainrenderer->create_collapsible_region_id(null);
            $result .= print_collapsible_region_start('', $qaid,
                    'raw response', '', true, true);
            $result .= html_writer::tag('xmp', $message, array('class' => 'proforma_testlog'));
            $result .= print_collapsible_region_end(true);

            $result .= html_writer::end_tag('small');
        }
        return $result;
    }

    /**
     * @param SimpleXMLElement $response
     * @param string $result
     * @return string
     */
    protected function render_vcs_information(SimpleXMLElement $response, string $result): string {
        try {
            $praktomat = $response->{'response-meta-data'}->children('praktomat', true);
            $vcs = $praktomat->{'response-meta-data'}->{'version-control-system'};
            if (isset($vcs) && count($vcs) > 0) {
                $attrib = $vcs->attributes();
                $vcstext = $attrib['name'] . ': ' . $attrib['submission-uri'] . ' Revision ' . $attrib['submission-revision'];
            } else {
                $vcstext = null;
            }
        } catch (Exception $e) {
            // Ignore exception.
            $vcstext = null;
        }

        if (isset($vcstext)) {
            $result .= html_writer::tag('small', $vcstext);
        }
        return $result;
    }
}
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
 * Behat extensions for proforma
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../../vendor/php-webdriver/webdriver/lib/WebDriverExpectedCondition.php');


use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Gherkin\Node\PyStringNode as PyStringNode;
use Facebook\WebDriver\WebDriverExpectedCondition as WebDriverExpectedCondition;

class behat_proforma extends behat_base {

    private $downloadfile = '/tmp/behattest_download.txt';

    /**
     * Generic right click action. Click on the element of the specified type.
     *
     * @When /^I rightclick on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     */
    public function i_rightclick_on($element, $selectortype) {

        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);
        $node->rightClick();
    }


    /**
     * Generic doubleclick click action. Click on the element of the specified type.
     *
     * @When /^I doubleclick on "(?P<element_string>(?:[^"]|\\")*)" "(?P<selector_string>[^"]*)"$/
     * @param string $element Element we look for
     * @param string $selectortype The type of what we look for
     */
    public function i_doubleclick_on($element, $selectortype) {

        // Gets the node based on the requested selector type and locator.
        $node = $this->get_selected_node($selectortype, $element);
        $this->ensure_node_is_visible($node);
        $node->doubleClick();
    }

    public function click_on_contextmenue_item($menuelement) {
        $session = $this->getSession();
        $driver = $session->getDriver();
        $webDriver = $session->getDriver()->getWebDriver();

        // Gets the node based on the requested selector type and locator.
        // $node = $this->get_selected_node($selectortype, $element);
        // $this->ensure_node_is_visible($node);
        // $node->click();
        // $this->js_trigger_click($node);

        // $js = 'document.getElementById('elementID').click()';
        $element = "//*[text() = '" . $menuelement . "']";
        echo $element .PHP_EOL;
        // The popup (alert) is created as async process because of some
        // Ajax calls for string localization.
        // This seems to make problems...
        // Selenium hangs...
        echo 'click on menu item' . PHP_EOL;
        $js = "document.evaluate(\"".$element."\", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click()";
        $webDriver->executeAsyncScript($js);

        sleep(2000);

        echo 'switch to alert' . PHP_EOL;
        $alert = $webDriver->switchTo()->alert();

        echo 'get state' . PHP_EOL;
        $state = $webDriver->executeAsyncScript('return document.readyState');
        echo 'state async ' . $state . PHP_EOL;

//        $state = $this->evaluate_script('return document.readyState');
//        echo 'state ' . $state . PHP_EOL;


//        $state = $this->evaluate_script('return document.readyState');
//        echo 'state ' . $state . PHP_EOL;


//        $state = $webDriver->executeAsyncScript('return document.readyState');
//        echo 'state async ' . $state . PHP_EOL;
        return;

        $alert = $webDriver->switchTo()->alert();
        var_dump($alert);
        $alert->dismiss();
        return;

        echo 'Send to alert alert' . PHP_EOL;
        $alert->sendKeys('filename.java');
        // echo 'Accept alert' . PHP_EOL;
        // $alert->accept();

        return;


        echo 'Switch to alert' . PHP_EOL;
        sleep(2000);
        // $js = "browser.switchTo().activeElement().sendKeys('Test')";
        // browser.actions().sendKeys('Test').perform();
        $this->execute_script($js);
        // self::type_keys($session, 'filename.java');
        $webDriver->switchTo()->activeElement()->sendKeys('Test');

        $alert = $webDriver->switchTo()->alert();
        // $this->wait_for_pending_js();

        ob_flush();
        var_dump($alert);
        ob_flush();
        $text = $alert->getText();
        echo 'Text: ' . $text . PHP_EOL;
        echo 'Type into alert' . PHP_EOL;
        $alert->sendKeys('filename.java');
        echo 'Accept alert' . PHP_EOL;
        $alert->accept();
    }

    /**
     * @When /^I click on "([^"]*)" in "([^"]*)" contextmenu$/
     */
    public function i_click_on_menu($menuelement, $nodename) {
        $this->execute("behat_general::i_wait_seconds", [0.5]);
        $xpath = "//*[text() = '". $nodename . "']";
        $this->i_rightclick_on($xpath, 'xpath_element');
        $this->execute("behat_general::i_wait_seconds", [0.5]);

        $xpath = "//*[text() = '". $menuelement . "']";
        $this->execute("behat_general::i_click_on", [$xpath, 'xpath_element']);
        $this->execute("behat_general::i_wait_seconds", [1]);
    }


    /**
     * @When /^I select "([^"]*)" radio button$/
     */
    public function i_select_radio_button($name) {
        $page = $this->getSession()->getPage();
        $radiobutton = $page->find('named', ['radio', $name]);
        if ($radiobutton) {
            $locator = $radiobutton->getAttribute('name');
            $option = $radiobutton->getAttribute('value');
            $page->selectFieldOption($locator, $option);
            return;
        }

        throw new Exception("Radio button {$name} not found");
    }

    /**
     * Downloads the file from a link on the page AND NEW: STORES IT TO FILESYSTEM and checks the size is in a given range.
     *
     * Only works if the link has an href attribute. Javascript downloads are
     * not supported. Currently, the href must be an absolute URL.
     *
     * The range includes the endpoints. That is, a 10 byte file in considered to
     * be between "5" and "10" bytes, and between "10" and "20" bytes.
     *
     * @Then /^following "(?P<link_string>[^"]*)" should download file with between "(?P<min_bytes>\d+)" and "(?P<max_bytes>\d+)" bytes$/
     * @throws ExpectationException
     * @param string $link the text of the link.
     * @param number $minexpectedsize the minimum expected file size in bytes.
     * @param number $maxexpectedsize the maximum expected file size in bytes.
     */
    public function following_should_download_file_with_between_and_bytes($link, $minexpectedsize, $maxexpectedsize) {
        // If the minimum is greater than the maximum then swap the values.
        if ((int)$minexpectedsize > (int)$maxexpectedsize) {
            list($minexpectedsize, $maxexpectedsize) = array($maxexpectedsize, $minexpectedsize);
        }

        $exception = new ExpectationException('Error while downloading data from ' . $link, $this->getSession());

        // It will stop spinning once file is downloaded or time out.
        $result = $this->spin(
                function($context, $args) {
                    $link = $args['link'];

                    return $this->download_file_from_link_and_store($link);
                },
                array('link' => $link),
                behat_base::get_extended_timeout(),
                $exception
        );

        // Check download size.
        $actualsize = (int)strlen($result);
        if ($actualsize < $minexpectedsize || $actualsize > $maxexpectedsize) {
            throw new ExpectationException('Downloaded data was ' . $actualsize .
                    ' bytes, expecting between ' . $minexpectedsize . ' and ' .
                    $maxexpectedsize . ' (check ' .  $this->downloadfile.  ')',
                $this->getSession());
        }
    }

    /**
     * Given the text of a link, download the linked file and return the contents.
     *
     * This is a helper method used by {@link following_should_download_bytes()}
     * and {@link following_should_download_between_and_bytes()}
     *
     * @param string $link the text of the link.
     * @return string the content of the downloaded file.
     */
    public function download_file_from_link_and_store($link) {
        // Find the link.
        $linknode = $this->find_link($link);
        $this->ensure_node_is_visible($linknode);

        // Get the href and check it.
        $url = $linknode->getAttribute('href');
        if (!$url) {
            throw new ExpectationException('Download link does not have href attribute',
                    $this->getSession());
        }
        if (!preg_match('~^https?://~', $url)) {
            throw new ExpectationException('Download link not an absolute URL: ' . $url,
                    $this->getSession());
        }

        // Download the URL and check the size.
        $session = $this->getSession()->getCookie('MoodleSession');
        // Download twice.
        download_file_content($url, array('Cookie' => 'MoodleSession=' . $session),
                null, false, 300, 20, false, $this->downloadfile);
        return download_file_content($url, array('Cookie' => 'MoodleSession=' . $session));
    }

    /**
     * Checks the value of a field using xpath.
     *
     * @Then /^the field with name "(?P<name_string>(?:[^"]|\\")*)" matches value "(?P<value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_field_with_name_matches_value($label, $value) {
        // Get the field.
        $fieldxpath = "//input[@name='".$label."']";
        $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Checks if the provided value matches the current field value.
        if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                    'The field "' . $label . '"" value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }

    /**
     * Checks, the field matches a given regular expression value.
     *
     * @Then /^the field "(?P<field_string>(?:[^"]|\\")*)" matches regexp "(?P<field_value_string>(?:[^"]|\\")*)"$/
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @param string $field
     * @param string $value
     * @return void
     */
    /* WORKS BUT IS UNUSED SO FAR
    public function the_field_matches_regexp($field, $value) {
        // Get the field.
        $formfield = behat_field_manager::get_form_field_from_label($field, $this);

        // Checks if the provided regular expression matches the current field value.
        $fieldvalue = $formfield->get_value();
        $match = preg_match($value, $fieldvalue);
        if ($match == 0) {
            throw new ExpectationException(
                'The \'' . $field . '\' value is \'' . $fieldvalue . '\', regular expression \'' . $value . '\' expected' ,
                $this->getSession()
            );
        }
    }*/

    /**
     * Checks the value of a multiline field.
     *
     * @Then /^the field "(?P<label_string>(?:[^"]|\\")*)" matches multiline$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_field_matches_multiline($label, PyStringNode $value) {
        // Get the field.
        $formfield = behat_field_manager::get_form_field_from_label($label, $this);
        $fieldvalue = $formfield->get_value();
        // Checks if the provided value matches the current field value.
        $value = str_replace("\t", "    ", (string)$value);
        $fieldvalue = str_replace("\t", "    ", $fieldvalue);

        if ($fieldvalue != $value) {
            throw new ExpectationException(
                    'The field "' . $label . '"" value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }

    /**
     * Checks the beginning of the value of a multiline field.
     *
     * @Then /^the field "(?P<label_string>(?:[^"]|\\")*)" starts with "(?P<value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_field_starts_with($label, $value) {
        // Get the field.
        $formfield = behat_field_manager::get_form_field_from_label($label, $this);
        $fieldvalue = $formfield->get_value();
        // Checks if the provided value matches the current field value.
        $length = strlen($value);
        if (substr($fieldvalue, 0, $length) != $value) {
            throw new ExpectationException(
                    'The field "' . $label . '"" value is \'' .
                        substr($fieldvalue, 0, $length) . '\', \'' . $value . '\' expected' ,
                        $this->getSession()
            );
        }
    }

    /**
     * Checks that if a checkbox is checked or not.
     *
     * @Then /^the "(?P<name_string>(?:[^"]|\\")*)" checkbox is "(?P<value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_checkbox_is($name, $value) {
        // Get the field.
        $fieldxpath = "//input[@name='".$name."' and @type='checkbox']";
        $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Checks if the provided value matches the current field value.
        switch($value) {
            case 'checked':
                $value = 1;
                break;
            case 'unchecked':
            case 'not checked':
                $value = 0;
                break;
            default:
                break;
        }
        if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                    'The "'.$name.'"checkbox value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }

    /**
     * Check checkbox.
     *
     * @When /^I check the "([^"]*)" checkbox$/
     */
    public function i_checkc_checkbox($name) {
        $this->getSession()->getPage()->checkField($name);
    }

    /**
     * Uncheck checkbox.
     *
     * @When /^I uncheck the "([^"]*)" checkbox$/
     */
    public function i_uncheck_checkbox($name) {
        $this->getSession()->getPage()->uncheckField($name);
    }

    /**
     * Set codemirror text with javascript in *Javascript* testcases.
     *
     * @When /^I set the codemirror "(?P<name_string>(?:[^"]|\\")*)" to "(?P<value_string>(?:[^"]|\\")*)"$/
     */
    public function set_the_codemirror_to($name, $value) {
        $command = 'return (function() { $("#id_' . $name .
            '").next(".CodeMirror").get(0).CodeMirror.setValue("'. $value. '"); })();';
        // fwrite(STDOUT, $command);	    
        $this->getSession()->getDriver()->evaluateScript($command);
    }

    /**
     * Set editor text (in Codemirror) with javascript in explorer *Javascript* testcases.
     *
     * @When /^I set the explorer editor text to "(?P<value_string>(?:[^"]|\\")*)"$/
     */
    public function set_editor_text_to($value) {
        // Only one Codemirror element is visible.
        $command = 'return (function() { 
            const editor = document.querySelector(".editor");
            let cm = editor.parentNode.querySelector(".CodeMirror:not([style*=\"display: none\"])");
            cm.CodeMirror.setValue("'. $value. '"); 
        })();';
        // fwrite(STDOUT, $command);
        $output = $this->getSession()->getDriver()->evaluateScript($command);
        // fwrite(STDOUT, $output);
    }

    /**
     * Set editor text (in Codemirror) with javascript in explorer *Javascript* testcases.
     *
     * @When /^I set the explorer editor text to multiline:$/
     */
    public function set_editor_text_to_multiline(PyStringNode $value) {
        $search = array("\r", "\n");
        $value = str_replace($search, "\\n", $value);
        $value = str_replace("\"", "\\\"", $value);

        $command = 'return (function() { 
            const editor = document.querySelector(".editor");
            let cm = editor.parentNode.querySelector(".CodeMirror:not([style*=\"display: none\"])");
            cm.CodeMirror.setValue("'. $value. '"); 
        })();';
        // fwrite(STDOUT, $command);
        $output = $this->getSession()->getDriver()->evaluateScript($command);
        // fwrite(STDOUT, $output);
    }

    /**
     * Set codemirror text with javascript in *Javascript* testcases.
     *
     * @When /^I set the codemirror "(?P<name_string>(?:[^"]|\\")*)" to multiline:$/
     */
    public function set_the_codemirror_to_multiline($name, PyStringNode $value) {
        $search = array("\r", "\n");
        $value = str_replace($search, "\\n", $value);
        $value = str_replace("\"", "\\\"", $value);
        // fwrite(STDOUT, $value);
        $command = 'return (function() { $("#id_' . $name .
                '").next(".CodeMirror").get(0).CodeMirror.setValue("'. $value. '"); })();';
        // fwrite(STDOUT, $command);
        $this->getSession()->getDriver()->evaluateScript($command);
    }

    /**
     * Set codemirror answer with javascript in *Javascript* testcases.
     *
     * @When /^I set the response to$/
     */
    public function set_the_response_to(PyStringNode $value) {
        // Remove newline and carriage return.
        $search = array("\r", "\n");
        $value = str_replace($search, "\\n", $value);
        $value = str_replace("\"", "\\\"", $value);
        // Do not use inline comments in response.
        // $value = html_e::escape($value);

        $command = 'return (function() { $(".qtype_proforma_response").next(".CodeMirror").get(0).CodeMirror.setValue("'. $value. '"); })();';
        // fwrite(STDOUT, $command);
        $this->getSession()->getDriver()->evaluateScript($command);
    }

    /**
     * Check codemirror text with javascript in *Javascript* testcases.
     *
     * @Then /^the codemirror "(?P<name_string>(?:[^"]|\\")*)" matches value "(?P<value_string>(?:[^"]|\\")*)"$/
     */
    public function the_codemirror_matches_value($name, $value) {
        $command = 'return (function() { return $("#id_'. $name . '").next(".CodeMirror").get(0).CodeMirror.getValue(); })();';
        // fwrite(STDOUT, $command);
        $output = $this->getSession()->getDriver()->evaluateScript($command);
        if ($output != $value) {
            throw new ExpectationException(
                    'The codemirror  "' . $name . '"" value is \'' . $output . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }


    /**
     * Create new question
     *
     * @When /^I create a new "(?P<proglang_string>(?:[^"]|\\")*)" question$/
     */
    public function create_new_question($proglang) {
        $this->execute("behat_forms::press_button", 'Create a new question ...');
        $this->execute("behat_forms::i_set_the_field_to", ['item_qtype_proforma', '1']);
        $this->execute("behat_general::i_click_on_in_the", ["Add", "button", "Choose a question type to add", "dialogue"]);
        $this->execute("behat_forms::i_set_the_field_to", ['item_' . $proglang, '1']);
        $this->execute("behat_general::i_click_on_in_the", ["Ok", "button", "Select programming language", "dialogue"]);
    }


    /**
     * Opens preview window on a particular question in the question bank UI.
     * (Background: In Moodle 3 a new Window is opened for preview.
     * In Moodle 4 the main window is used. So in Moodle 3 the user has to switch the window,
     * whereas in Moodle 4 he/she does not need to switch window.
     * In order to use the test scripts in both Moodle versions this step is added.)
     *
     *
     * @When I open preview for :questionname in the question bank
     * @param string $questionname the question name.
     */
    public function i_open_preview_for_the_question($questionname) {
/*        global $CFG;
        $moodleversion = $CFG->version;
        if ($moodleversion > 2022112800) {
            // Moodle 4.1
            // echo 'Moodle 4.1 ';
        } elseif ($moodleversion > 2022041900) { // 4.0.0
            // Moodle 4.0
            // echo 'Moodle 4 ';
        } else {
            // Moodle 3
            // echo 'Moodle 3 ';
        }
*/
        // Open Question bank context
        // echo '0';
        try {
            $this->i_navigate_to_in_current_page_administration("Question bank");
        } catch(Exception $err) {
            // Moodle 3: Ignore
        }
        // echo '1';
        // Open the menu.
//        $this->execute("behat_general::i_click_on_in_the",
//                    [get_string('edit'), 'link', $questionname, 'table_row']);
        $this->execute("behat_general::i_click_on_in_the",
            [get_string('edit'), 'button', $questionname, 'table_row']);
        // echo '2';

        // return;
        // Click the action from the menu.
        $this->execute("behat_general::i_click_on_in_the",
            ['Preview', 'link', $questionname, 'table_row']);
        // echo '3';

        // Switch to window if it exists
        try {
            $this->getSession()->switchToWindow('questionpreview');
        } catch(Exception $err) {
            // Moodle 4: Ignore
        }
    }

    /**
     * Closes preview window (compatible for Moodle 3 and Moodle 4).
     *
     * @When I close preview
     */
    public function i_close_preview() {
        // Moodle 3: Switch to main window
        try {
            $this->getSession()->switchToWindow(behat_general::MAIN_WINDOW_NAME);
            return;
        } catch(Exception $err) {
            // Moodle 4: Ignore
        }

        // Moodle 4
        try {
            $this->execute('behat_general::i_click_on', ["Close preview", 'button']);
            return;
        } catch(Exception $err) {
            // Moodle 3: Ignore
        }
    }

    /**
     * maximize window
     *
     * @Then /^I maximize window$/
     * @return void
     */
    public function I_maximize_window() {
        $this->getSession()->getDriver()->maximizeWindow();
    }

}

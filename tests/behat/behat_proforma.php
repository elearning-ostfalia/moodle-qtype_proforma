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
// along with ProFormA Question Type for Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat extensions for proforma
 *
 * @package    qtype_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// require_once(__DIR__ . '/../../questiontype.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;
use Behat\Gherkin\Node\PyStringNode as PyStringNode;

class behat_proforma extends behat_base {
    /**
     * @When /^I select "([^"]*)" radio button$/
     */
    public function iSelectRadioButton($name) {
        $page = $this->getSession()->getPage();
        $radioButton = $page->find('named', ['radio', $name]);
        if ($radioButton) {
            $locator = $radioButton->getAttribute('name');
            $option = $radioButton->getAttribute('value');
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
                    $maxexpectedsize, $this->getSession());
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
        // download twice
        download_file_content($url, array('Cookie' => 'MoodleSession=' . $session),
                null, false, 300, 20, false, '/tmp/behattest_download.txt');
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
        $value = str_replace("\t","    ", (string)$value);
        $fieldvalue = str_replace("\t","    ", $fieldvalue);
        //$value = str_replace(" ","*", (string)$value);
        //$fieldvalue = str_replace(" ","*", $fieldvalue);

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
        //$value = str_replace("\t","    ", (string)$value);
        //$fieldvalue = str_replace("\t","    ", $fieldvalue);

        $length = strlen($value);
        if (substr($fieldvalue, 0, $length) != $value) {
            throw new ExpectationException(
                    'The field "' . $label . '"" value is \'' . substr($fieldvalue, 0, $length) . '\', \'' . $value . '\' expected' ,
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
    public function iCheckCheckbox($name) {
        $this->getSession()->getPage()->checkField($name);
    }

    /**
     * Uncheck checkbox.
     *
     * @When /^I uncheck the "([^"]*)" checkbox$/
     */
    public function iUncheckCheckbox($name) {
        $this->getSession()->getPage()->uncheckField($name);
    }

    /**
     * Set codemirror text with javascript in *Javascript* testcases.
     *
     * @When /^I set the codemirror "(?P<name_string>(?:[^"]|\\")*)" to "(?P<value_string>(?:[^"]|\\")*)"$/
     */
    public function set_the_codemirror_to($name, $value) {
        $command = 'return (function() { $("#id_' . $name . '").next(".CodeMirror").get(0).CodeMirror.setValue("'. $value. '"); })();';
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

}

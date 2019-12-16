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

class behat_proforma extends behat_base {

    /**
     * @Given I set testmode
     */
    public function i_set_testmode() {
        // ??? how do we do that?
    }

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
     * check text in field with the label (expecting more than one field existing with the same label)
     * @Given /^the field "(?P<label_string>(?:[^"]|\\")*)" number "(?P<number_string>(?:[^"]|\\")*)" matches value "(?P<value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @param $label field label
     * @param $number one-based index of field
     * @param $value expected value
     */
    public function theFieldNumberMatchesValue($label, $number, $value) {
        // find all fields with label $label
        $fieldnodes = $this->find_all('named_partial', array('field', $label));
        if (count($fieldnodes) < $number)
            throw new ExpectationException('Not enough fields found with label "' . $label . '": ' . count($fieldnodes), $this->getSession());
        $fieldnode = $fieldnodes[$number-1];

        // Get the actual field.
        $field = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Checks if the provided value matches the current field value.
        if (!$field->matches($value)) {
            $fieldvalue = $field->get_value();
            throw new ExpectationException(
                    'The \'' . $label . '\' value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }


    /**
     * sets the text in field with the label (expecting more than one field existing with the same label)
     * @Given /^I set the field "(?P<label_string>(?:[^"]|\\")*)" number "(?P<number_string>(?:[^"]|\\")*)" to "(?P<value_string>(?:[^"]|\\")*)"$/
     * @throws ExpectationException
     * @param $label field label
     * @param $number one-based index of field
     * @param $value new value
     */
    public function ISetTheFieldNumberTo($label, $number, $value) {
        // find all fields with label $label
        $fieldnodes = $this->find_all('named_partial', array('field', $label));

        if (count($fieldnodes) < $number)
            throw new ExpectationException('Not enough fields found with label "' . $label . '": ' . count($fieldnodes), $this->getSession());
        $fieldnode = $fieldnodes[$number-1];

        // Get the actual field.
        $field = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Set value
        $field->set_value($value);

        // file_put_contents('/tmp/test.png', $this->getSession()->getScreenshot());
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
                null, false, 300, 20, false, '/var/www/html/moodle/behat_test_download.txt');
        return download_file_content($url, array('Cookie' => 'MoodleSession=' . $session));
    }


    /**
     * Checks that the checkstyle checkbox is checked.
     *
     * @Then /^the checkstyle checkbox is checked$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_checkstyle_checkbox_is_checked() {
        // Get the field.
        $fieldxpath = "//input[@name='checkstyle' and @type='checkbox']";
        $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Checks if the provided value matches the current field value.
        $value = 1;
        if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                    'The checkstyle checkbox value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }

    /**
     * Checks that the checkstyle checkbox is not checked.
     *
     * @Then /^the checkstyle checkbox is not checked$/
     * @throws ExpectationException
     * @throws ElementNotFoundException Thrown by behat_base::find
     * @return void
     */
    public function the_checkstyle_checkbox_is_not_checked() {
        // Get the field.
        $fieldxpath = "//input[@name='checkstyle' and @type='checkbox']";
        $fieldnode = $this->find('xpath', $fieldxpath);
        $formfield = behat_field_manager::get_form_field($fieldnode, $this->getSession());

        // Checks if the provided value matches the current field value.
        $value = 0;
        if (!$formfield->matches($value)) {
            $fieldvalue = $formfield->get_value();
            throw new ExpectationException(
                    'The checkstyle checkbox value is \'' . $fieldvalue . '\', \'' . $value . '\' expected' ,
                    $this->getSession()
            );
        }
    }
}

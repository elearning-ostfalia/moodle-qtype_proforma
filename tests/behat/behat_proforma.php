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

}

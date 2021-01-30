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

require_once(__DIR__ . '/../../../../../repository/upload/tests/behat/behat_repository_upload.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

class behat_proforma_upload extends behat_repository_upload {

    /** indicator for changing filepicker node evaluation */
    protected $defaultfilepicker = true;
    /**
     * Try to get the filemanager node specified by the element
     *
     * @param string $filepickerelement
     * @return \Behat\Mink\Element\NodeElement
     * @throws ExpectationException
     */
    protected function get_filepicker_node($filepickerelement) {
        if ($this->defaultfilepicker) {
            // call default function.
            return parent::get_filepicker_node($filepickerelement);
        }

        // More info about the problem (in case there is a problem).
        $exception = new ExpectationException('"' . $filepickerelement . '" filepicker can not be found', $this->getSession());

        // Gets the filemanager node specified by the locator which contains the filepicker container
        // either for filepickers created by mform or by admin config.
        $filepickerelement = behat_context_helper::escape($filepickerelement);
        $filepickercontainer = $this->find(
                'xpath',
                "//input[./@name = $filepickerelement]"
                . "//ancestor::*[@data-fieldtype = 'filemanager' or @data-fieldtype = 'filepicker']",
                $exception
        );

        return $filepickercontainer;
    }

    /**
     * Uploads a file to the specified filemanager leaving other fields in upload form default.
     * The paths should be relative to moodle codebase.
     * This version is a modification of i_upload_to_filemanager
     * by using the filemanager name instead of the label.
     *
     * @When /^I upload "(?P<filepath_string>(?:[^"]|\\")*)" to "(?P<filemanager_field_string>(?:[^"]|\\")*)" filemanager by name$/
     * @throws DriverException
     * @throws ExpectationException Thrown by behat_base::find
     * @param string $filepath
     * @param string $filemanagerelement
     */
    public function i_upload_to_filemanager_by_name($filepath, $filemanagerelement) {
        // Change behaviour when filepicker node must be found..
        $this->defaultfilepicker = false;
        try {
            $this->i_upload_file_to_filemanager($filepath, $filemanagerelement);
        } finally {
            $this->defaultfilepicker = true;
        }
    }
}
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
 * This file contains base functions for testing task files
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/question/type/proforma/questiontype.php');


class task_testcase extends advanced_testcase {

    
    // TODO: also used in question_test.php!
    public function assert_same_xml($expectedxml, $xml) {
        
        // Only for expectedxml: 
        // Delete newlines remaing when a child node is deleted.        
        $expectedxml = preg_replace("#\>\r\n[\s]*\r\n#", ">\r\n", $expectedxml); // Windows.        
        $expectedxml = preg_replace("#\>\n[\s]*\n#", ">\n", $expectedxml); // Unix.        
        
        // remove comments
        $xml = preg_replace('/<!--(.|\s)*?-->/', '', $xml);
        $expectedxml = preg_replace('/<!--(.|\s)*?-->/', '', $expectedxml);
        // remove uuid
        $xml = preg_replace('/uuid="(.|\s)*?"/', 'uuid="removed"', $xml);
        $expectedxml = preg_replace('/uuid="(.|\s)*?"/', 'uuid="removed"', $expectedxml);
        
        // escaped 
        $xmldoc = new DOMDocument();
        $xmldoc->loadXML($expectedxml);
        $expectedxml = $xmldoc->saveXML();

        $xmldoc->loadXML($xml);
        $xml = $xmldoc->saveXML();
        
        $this->assertEquals(str_replace("\r\n", "\n", $expectedxml),
                str_replace("\r\n", "\n", $xml));
    }


}
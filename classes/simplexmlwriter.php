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
 * Helper class for creating XML
 *
 * @package    qformat_proforma
 * @copyright  2019 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * class is used to write XML with less lines of code
 */
class SimpleXmlWriter extends XMLWriter {

    /** Create attribute for current element.
     *
     * @param string $name attribute name
     * @param string $value attribute value
     */
    public function create_attribute($name, $value) {
        $this->startAttribute($name);
        if ($value) {
            $this->text($value);
        } else {
            $this->text('');
        }
        $this->endAttribute();
    }

    /** Create child element with just text (no attributes and no further children)
     *
     * @param string $name element name
     * @param string $text element text (default: null)
     */
    public function create_childelement_with_text($name, $text = null) {
        $this->startElement($name);
        if ($text) {
            $this->text($text);
        }
        $this->endElement();
    }

}

// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software:
// you can redistribute it and/or modify
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
// along with ProFormA Question Type for Moodle.
// If not, see <http://www.gnu.org/licenses/>.

/**
 * Helper functions for forms.
 *
 * @package
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

/**
 * Hides or shows a form field depending on the content of another field.
 *
 * @param {string} id id of field to hide/show
 * @param {string} dependend id of field with URI
 * @param {string} needle text to search for in the URI field
 * @param {string} optionfield id of response format field
 * @param {string} option version control option
 */
function showOrHide(id, dependend, needle, optionfield, option) {
    try {
        // Get value in depended input.
        let text = document.getElementById(dependend).value;
        let remove = true;
        if (text.search(needle) >= 0) {
            // It contains the needle text =>
            // Get selected option in option field (response format)
            text = document.getElementById(optionfield).value;
            if (text == option) {
                // It contains version control => show.
                remove = false;
            }
        }
        document.getElementById('fitem_' + id).style.display = remove ? 'none' : null;
    } catch (e) {
        console.error('error ' + e);
    }
}


/**
 * Hide/show input field for label of version control input value
 *
 * @param {string} id id of field to hide/show
 * @param {string} dependend id of field with URI
 * @param {string} needle '{input}'
 * @param {string} optionfield id of response format field
 * @param {string} option version control option
 * @returns {undefined}
 */
export const showif = (id, dependend, needle, optionfield, option) => {
    // Initialise.
    showOrHide(id, dependend, needle, optionfield, option);
    // Check if key was pressed in URI text field.
    if (document.getElementById(dependend)) {
        document.getElementById(dependend).addEventListener('keyup', function() {
            showOrHide(id, dependend, needle, optionfield, option);
        });
    }
    // Check if response option is changed.
    if (document.getElementById(optionfield)) {
        document.getElementById(optionfield).addEventListener('change', function() {
            showOrHide(id, dependend, needle, optionfield, option);
        });
    }
};


/**
 * Removes required check
 * @param {string} id id of the form element
 * @param {string} dependend id of the select element it depends on
 */
export const requiredif = (id, dependend) => {
    let element = document.getElementById(id);
    if (element) {
        let selectelem = document.getElementById(dependend);
        if (!selectelem) {
            console.error('cannot find element identified by ' + dependend);
        }
    } else {
        console.error('cannot find element identified by ' + id);
    }
};



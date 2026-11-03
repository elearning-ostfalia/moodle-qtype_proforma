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
 * Debug output for ProFormA question type which is switched off by default.
 *
 * The switch is stored in the browser (localStorage), so no rebuild is needed.
 * Enable it in the browser console with
 *     localStorage.setItem('qtype_proforma_debug', '1');
 * and reload the page. Disable it with
 *     localStorage.removeItem('qtype_proforma_debug');
 *
 * This module has no Moodle dependencies so that it can also be used
 * in the standalone test environment (tests/javascript).
 *
 * @module     qtype_proforma/debug
 * @copyright  2026 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

/** Name of the localStorage item used as debug switch. */
const STORAGE_KEY = 'qtype_proforma_debug';

/**
 * Reads the debug switch from localStorage.
 *
 * @returns {boolean} true if debug output is enabled
 */
const readSwitch = () => {
    try {
        return window.localStorage.getItem(STORAGE_KEY) === '1';
    } catch (e) {
        // LocalStorage may be unavailable (privacy mode, blocked storage).
        return false;
    }
};

let enabled = readSwitch();

/**
 * Returns whether debug output is enabled.
 *
 * @returns {boolean}
 */
export const isDebugEnabled = () => enabled;

/**
 * Switches debug output on or off (for the current page and, if possible, permanently).
 *
 * @param {boolean} value true to enable debug output
 */
export const setDebugEnabled = (value) => {
    enabled = !!value;
    try {
        if (enabled) {
            window.localStorage.setItem(STORAGE_KEY, '1');
        } else {
            window.localStorage.removeItem(STORAGE_KEY);
        }
    } catch (e) {
        // Ignore: switch is only valid for the current page.
    }
};

/**
 * Writes the arguments to the console if debug output is enabled (replacement for console.log).
 *
 * @param {...*} args values to be logged
 */
export const debugLog = (...args) => {
    if (enabled) {
        // eslint-disable-next-line no-console
        console.log(...args);
    }
};

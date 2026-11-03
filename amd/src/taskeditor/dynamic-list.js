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
 * Class for extendable list
 *
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

// todo: replace table solution with something without table!


import {debugLog} from '../debug';
import $ from 'jquery';
import Templates from 'core/templates';

// Abstract class for a filename reference input
export class DynamicList {

    constructor(classFilename, cssClassname, jsClassName, label, help, mandatory) {
        this.classFilename = 'xml_fileref_filename';
        this.classAddItem = 'add_fileref';
        this.classRemoveItem = 'remove_item';
        this.help = help;
        this.createTableString(jsClassName, mandatory);
        this.rowTemplateHtml = null;
    }

    getClassFilename() {
        return this.classFilename;
    }

    createRowFromTemplate() {
        if (this.rowTemplateHtml) {
            return Promise.resolve(this.rowTemplateHtml);
        }

        let context = {
            'filenamelabel': '???'
        };
        return Templates.renderForPromise('qtype_proforma/taskeditor_fileref_table', context)
            .then(({html}) => {
                // The template contains a complete table => only use the content of its row.
                this.rowTemplateHtml = $('<div>').html(html).find('tr').first().html();
                return this.rowTemplateHtml;
            });
    }

    createTableString(className, mandatory) {
        this.className = className;
        this.mandatory = mandatory;
    }

    addItem(element) {
        // Add new line at the end
        // let td = element.parent();
        // let tr = td.parent();
        let tableBody = element.closest('tbody'); // Tr.parent();
        let label = tableBody.find("label").first().html();
        let newRow = document.createElement('tr');
        tableBody.append(newRow);
        element.hide(); // Hide current +-button
        return this.createRowFromTemplate()
            .then(html => {
                newRow.innerHTML = html;
                $(newRow).find("label").first().hide();
                $(newRow).find("label").first().html(label);
                tableBody.find("." + this.classRemoveItem).show(); // Show all remove file buttons
                tableBody.find("." + this.classAddItem).hide(); // Show all remove file buttons
                $(newRow).find("." + this.classAddItem).show();
                return $(newRow);
            });
    }

    // Virtual
    getPreviousItem(tr) {
        return tr.prev("tr");
    }

    // Virtual
    getItemCount(tableBody) {
        return tableBody.find("tr").length;
    }

    removeItem(element) {
        let td = element.parent();
        let tr = td.parent();

        // Remove line in file table for test
        let tableBody = tr.closest('tbody');


        let previousRow = this.getPreviousItem(tr); // Tr.prev("tr");
        let hasNextTr = tr.nextAll("tr");
        let hasPrevTr = tr.prevAll("tr");

        tr.remove(); // Remove row

        if (hasNextTr.length === 0) {
            // If row to be deleted is last row then add +-button to last row
            previousRow.find("." + this.classAddItem).show();
        }
        if (hasPrevTr.length === 0) {
            // Row to be deleted is first row
            // => add filename label to first column
            tableBody.find("label").first().show();
        }

        if (this.getItemCount(tableBody) === 1) {
            // Table has exactly one row left
            // => hide all remove file buttons
            debugLog('table has exactly one row left => hide all remove file buttons');
            tableBody.find("." + this.classRemoveItem).hide();
        }
    }
}



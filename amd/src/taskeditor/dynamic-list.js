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
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

// todo: replace table solution with something without table!


import $ from 'jquery';
import Templates from 'core/templates';

// abstract class for a filename reference input
export class DynamicList {

    constructor(classFilename, css_classname, jsClassName, label, help, mandatory, extra_css_class) {
        this.classFilename = 'xml_fileref_filename';
        this.classAddItem = 'add_fileref';
        this.classRemoveItem = 'remove_item';
        this.help = help;
        this.createTableString(jsClassName, mandatory);
        this.rowTemplateHtml = null;
    }

    getClassFilename() { return this.classFilename; }

    createRowFromTemplate() {
        if (this.rowTemplateHtml) {
            return Promise.resolve(this.rowTemplateHtml);
        }

        let context = {
            'filenamelabel' : '???'
        };
        return Templates.renderForPromise('qtype_proforma/taskeditor_fileref_row', context)
            .then(({html, js}) => {
                this.rowTemplateHtml = html;
                return html;
            });
    }

    createTableString(className, mandatory) {
        this.className = className;
        this.mandatory = mandatory;
    }

    addItem(element) {
        // add new line at the end
        // let td = element.parent();
        // let tr = td.parent();
        let table_body = element.closest('tbody'); // tr.parent();
        let label = table_body.find("label").first().html();
        let newRow = document.createElement('tr');
        table_body.append(newRow);
        element.hide(); // hide current +-button
        return this.createRowFromTemplate()
            .then(html => {
                newRow.innerHTML = html;
                $(newRow).find("label").first().hide();
                $(newRow).find("label").first().html(label);
                table_body.find("." + this.classRemoveItem).show(); // show all remove file buttons
                table_body.find("." + this.classAddItem).hide(); // show all remove file buttons
                $(newRow).find("." + this.classAddItem).show();
                return $(newRow);
            });
    }

    // virtual
    getPreviousItem(tr) {
        return tr.prev("tr");
    }

    // virtual
    getItemCount(table_body) {
        return table_body.find("tr").length;
    }

    removeItem(element) {
        let td = element.parent();
        let tr = td.parent();

        // remove line in file table for test
        let table_body = tr.closest('tbody');


        let previousRow = this.getPreviousItem(tr); // tr.prev("tr");
        let hasNextTr = tr.nextAll("tr");
        let hasPrevTr = tr.prevAll("tr");

        tr.remove(); // remove row

        if (hasNextTr.length === 0) {
            // if row to be deleted is last row then add +-button to last row
            //let tds = previousRow.find("td");
            //let td = tds.last();
            // $(this.tdAddButton).insertBefore(previousRow.find("td").last());
            console.log('row to be deleted is last row then add +-button to last row');

            previousRow.find("." + this.classAddItem).show();
        }
        if (hasPrevTr.length === 0) {
            // row to be deleted is first row
            // => add filename label to first column
            console.log('row to be deleted is first row => add filename label to first column');
            table_body.find("label").first().show();
            // let firstCell = table_body.find("td").first();
            // firstCell.append(this.label); // without td
        }

        // check if previousRow is first row
        let firstRow = table_body.find("tr")[0];
        if (this.getItemCount(table_body) === 1) {
            // table has exactly one row left
            // => hide all remove file buttons
            console.log('table has exactly one row left => hide all remove file buttons');
            table_body.find("." + this.classRemoveItem).hide();
        }
    }
}




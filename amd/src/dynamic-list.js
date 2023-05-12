/*
 * This proformaEditor was created by the eCULT-Team of Ostfalia University
 * http://ostfalia.de/cms/de/ecult/
 * The software is distributed under a CC BY-SA 3.0 Creative Commons license
 * https://creativecommons.org/licenses/by-sa/3.0/
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @copyright 2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @author   Karin Borm <k.borm@ostfalia.de>
 */

// todo: replace table solution with something without table!

import $ from 'jquery';
import Templates from 'core/templates';

// abstract class for a filename reference input
export class DynamicList {

    constructor(classFilename, css_classname, jsClassName, label, help, mandatory, extra_css_class) {
        this.classFilename = 'xml_fileref_filename'; // classFilename;
        this.classAddItem = 'add_fileref'; // css_classname.replace('xml_', 'add_'); // classAddItem;
        this.classRemoveItem = 'remove_item'; // css_classname.replace('xml_', 'remove_'); // classRemoveItem;
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




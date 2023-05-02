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


// abstract class for a filename reference input
export class DynamicList {
    static classRemoveItem = 'remove_item'; // css_classname.replace('xml_', 'remove_'); // classRemoveItem;

    constructor(classFilename, css_classname, jsClassName, label, help, mandatory, extra_css_class) {
        this.classFilename = classFilename;
        this.classAddItem = css_classname.replace('xml_', 'add_'); // classAddItem;
        this.help = help;

        this.configure(jsClassName, label, mandatory, extra_css_class);
    }

    getClassFilename() { return this.classFilename; }

    // virtual
    createRowContent() { return '';}

    createRow(node, first) {
        let td = document.createElement('td');
        let removeButton = td.createElement('button');
        removeButton.classList.add(this.classRemoveItem);
        // removeButton.onclick = TODO
        removeButton.style.display = 'none';
        removeButton.innerHTML = 'x';
        // node.append(td);

        let row = document.createElement('tr');
        row.createElement('');

        // hide first remove file button
        const tdFirstRemoveButton = "<td><button class='" + this.classRemoveItem +
            "' onclick='" + this.className + ".getInstance().removeItem($(this))' style='display: none;'>x</button></td>";

        return "<tr>" +
            "<td>" + (first?this.label:'') + "</td>" + // label

            this.createRowContent() +

            tdFirstRemoveButton + // x-button
            this.tdAddButton +
            '<td></td>' +
        "</tr>";
    }

    getLabelString(label) {
        if (label.length > 0) {
            // label = label + '(s)';
            return "<label for='" + this.classFilename +
                "'>" + label + (this.mandatory?"<span class='red'>*</span>":"") + ": </label>";
        } else {
            return "<label></label>";
        }
    }

    configure(className, label, mandatory, extra_css_class) {
        this.className = className;
        this.mandatory = mandatory;
        this.label = this.getLabelString(label);
        this.extra_css_class = extra_css_class;

        this.tdAddButton = "<td><button class='" + this.classAddItem +
            "' title='add another filename' onclick='" + className + ".getInstance().addItem($(this))'>+</button><br></td>";
    }

    createTable(node) {
        // node.innerHTML = this.table;
            let table = document.createElement('table');
            table.classList.add('dynamic_table');
            if (this.extra_css_class) {
                table.classList.add(this.extra_css_class);
                // table.style.padding('0');
            }
            // this.createRow(table, true);
            node.append(table);
/*            node.innerHTML = "<table class='dynamic_table " + this.extra_css_class + "' cellpadding='0'>" + // cellspacing='0' >" +
                this.createRow(true) +
                "</table>";
        } else {
            node.innerHTML = "<table class='dynamic_table' cellpadding='0'>" + // cellspacing='0' >" +
                this.createRow(true) +
                "</table>";
        }*/
    }

    addItem(element) {
        // add new line for selecting a file for a test
        let td = element.parent();
        let tr = td.parent();
        let table_body = tr.parent();
        let newRow = table_body.append(this.createRow(false));
        element.remove(); // remove current +-button
        $(table_body).find("." + this.classRemoveItem).show(); // show all remove file buttons
        return newRow;
    }

    // virtual
    getPreviousItem(tr) {
        return tr.prev("tr");
    }

    // virtual
    getItemCount(table_body) {
        return $(table_body).find("tr").length;
    }

    removeItem(element) {
        let td = element.parent();
        let tr = td.parent();

        // remove line in file table for test
        let table_body = tr.parent();

        let previousRow = this.getPreviousItem(tr); // tr.prev("tr");
        let hasNextTr = tr.nextAll("tr");
        let hasPrevTr = tr.prevAll("tr");

        tr.remove(); // remove row

        if (hasNextTr.length === 0) {
            // if row to be deleted is last row then add +-button to last row
            //let tds = previousRow.find("td");
            //let td = tds.last();
            $(this.tdAddButton).insertBefore(previousRow.find("td").last());
        }
        if (hasPrevTr.length === 0) {
            // row to be deleted is first row
            // => add filename label to first column
            let firstCell = $(table_body).find("td").first();
            firstCell.append(this.label); // without td
        }

        // check if previousRow is first row
        let firstRow = $(table_body).find("tr")[0];
        if (this.getItemCount(table_body) === 1) {
            // table has exactly one row left
            // => hide all remove file buttons
            $(table_body).find("." + this.classRemoveItem).hide();
        }
    }
}




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

import {DynamicList} from "./dynamic-list";
import $ from 'jquery';
import {readAndCreateFileData} from "./taskeditorhelper";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';


const loadFileOption = "<open...>";
const emptyFileOption = " "; // must not be empty!!

const showEditorText = 'View';
const hideEditorText = 'Hide';

let filenameClassList = [];
let filerefClassList = [];

const DEBUG_MODE = true;

// abstract class for a filename reference input
export class FileReferenceList extends DynamicList {

    constructor(classFilename, classFileref, jsClassName, label, help, mandatory) {
        super(classFilename, classFileref, jsClassName, label, help, mandatory, 'xml_fileref_table');

        //this.table = this.table +
        //    "<span class='drop_zone_text drop_zone'>Drop Your File(s) Here!</span>";

        filenameClassList.push('.' + this.classFilename);
        filerefClassList.push('.' + classFileref);
    }


    // override
    createTable(node) {
        super.createTable(node);
        node.innerHTML += "<span class='drop_zone_text drop_zone'>Drop Your File(s) Here!</span>";
    }

    createExtraContent() { return ''; }

    createRowContent() {
        const tdFilename = "<td><select class='mediuminput fileref_filename " + this.classFilename + "' " +
            "onchange = 'console.log(abcxxx); " +
            this.className + ".getInstance().onFileSelectionChanged($(this))' title='" + this.help + "'></select></td>"+
            "<td><label for='fileref_fileref'>Fileref: </label>"+ // fileref
            "<input class='tinyinput fileref_fileref' readonly/></td>";


        const tdExpandButton = "<td><button class='collapse' title='show content' onclick='" +
            this.className + ".getInstance().toggleEditor($(this))'>"+showEditorText+"</button><br></td>";

        return tdFilename + tdExpandButton + this.createExtraContent();
    }


    doOnAll(callback, root) {
        if (root)
            console.log('doOnAllIds ios deprecated, use static version instead');
        let theRoot = root?root:this.root;
        $.each(theRoot.find(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            return callback(filerefId);
        });
    }

    doOnNonEmpty(callback) {
        $.each(this.root.find(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            if (filerefId)
                return callback(filerefId);
        });
    }

    static doOnAllIds(root, callback) {
        $.each(root.find(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            return callback(filerefId);
        });
    }

    static doOnAllElements(root, callback) {
        $.each(root.find(".fileref_fileref"), function(index, item) {
            return callback(item);
        });
    }

    // init table
    init(root, DEBUG_MODE) {
        if (!this.root)
            this.root = root;
        FileReferenceList.updateFilenameList($(root).find("." + this.classFilename).last());
        FileReferenceList.rowEnableEditorButton(root, false);
        if (!DEBUG_MODE) {
            $(root).find(".fileref_fileref").hide();
            $(root).find("label[for='fileref_fileref']").hide();
        }

        // register dragenter, dragover.
        $(root).on({
            dragover: function(e) {
                e.preventDefault();
                e.stopPropagation();
                //e.dataTransfer.dropEffect = 'copy';
            },
            dragenter: function(e) {
                e.preventDefault();
                e.stopPropagation();
            },
/*
            drop: function(e){
                if(e.originalEvent.dataTransfer){
                    if(e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        //UPLOAD FILES HERE
                        this.JsClassname.uploadFiles(e.originalEvent.dataTransfer.files, e.currentTarget);
                    }
                }
            } */
        });
    }

    // for creation by reading xml
    setFilenameOnCreation(box, index, filename) { // index is 0-based
        // set filename
        if (index > 0) {
            // create new fileref if index > 0
            this.addItem(box.find("." + this.classAddItem).first());
        }
        let element = box.find("." + this.classFilename);
        FileReferenceList.updateFilenameList(element.eq(index));
        element.eq(index).val(filename).change();
    }

    getCountFilerefs(root) {
        let counter = 0;
        this.doOnAll(function () {
            counter++;
        }, root);
        return counter;
    }

    getNumberOfExtraColumns() { return 0;}

    toggleEditor(element) {
        let td = element.parent();
        let tr = td.parent();
        const fileid = tr.find('.fileref_fileref')[0].value;
        let ui_file = FileWrapper.constructFromId(fileid);

        if (element.html() === hideEditorText) {
            element.html(showEditorText);
            tr.next().remove();
        }
        else {
            const numberOfColumns = 7 + this.getNumberOfExtraColumns();
            if (ui_file && !ui_file.isBinary) {
                element.html(hideEditorText);
                $( "<tr><td colspan='"+ numberOfColumns + "'><textarea disabled cols='80' rows='10' class='fileref_viewer'>"+
                    ui_file.text
                    +"</textarea></td></tr>" ).insertAfter(tr);
            }
        }
    }

    static rowGetFileId(row) {
        return row.find('.fileref_fileref')[0].value;
    }

    static rowEnableEditorButton(row, enabled) {
        if (enabled) {
            // check if file is binary and cannot be viewed
            const fileid = FileReferenceList.rowGetFileId(row);
            if (!fileid)
                return;
            let ui_file = FileWrapper.constructFromId(fileid);
            if (ui_file.isBinary)
                enabled = false;
            //console.log('enable view button in fileref for ' + ui_file.filename + ', enabled = ' + enabled);
        }

        $(row).find(".collapse").last().prop('disabled', !enabled);
    }

    addItem(element) {
        let td = element.parent();
        let tr = td.parent();
        let table_body = tr.parent();

        let newRow = super.addItem(element);
        FileReferenceList.rowEnableEditorButton(newRow, false);
/*
        // add new line for selecting a file for a test
        let td = element.parent();
        let tr = td.parent();
        let table_body = tr.parent();
        table_body.append(this.createRow(false));
        td.remove(); // remove current +-button
        table_body.find("." + this.classRemoveFileref).show(); // show all remove file buttons
*/
        // add filelist to new file option
        FileReferenceList.updateFilenameList(table_body.find("." + this.classFilename).last());

        if (!DEBUG_MODE) {
            // hide new fileref fields
            $(table_body).find(".fileref_fileref").hide();
            $(table_body).find("label[for='fileref_fileref']").hide();
        }
    }


    // override
    getItemCount(table_body) {
        let count = 0;
        $.each(table_body.find("tr"), function(index, item) {
            if ($(item).find("td").length > 2)
                count++;
        });
        return count;
    }

    // override
    getPreviousItem(tr) {
        let previousRow = tr.prev("tr");

        if (previousRow.find('td').length === 1) {
            // only one column => editor visible go to previous row
            previousRow = previousRow.prev("tr");
        }
        return previousRow;
    }

    removeItem(element) {
        let td = element.parent();
        let tr = td.parent();

        // save associated fileid
        const fileid = FileReferenceList.rowGetFileId(tr); // tr.find('.fileref_fileref')[0].value;

        // remove editor
        const buttonText = td.prev().find('button').html();
        if (buttonText === hideEditorText) {
            // remove editor
            tr.next().remove();
        }

        super.removeItem(element);

        if (fileid) {
            FileReferenceList.deleteFile(fileid);
        }
    }

    // TODO move to file??
    static deleteFile(fileid) {
        // check if there any references
        let ui_file = FileWrapper.constructFromId(fileid);
        if (FileReferenceList.getCountFileIdReferenced(fileid) === 0) {
            // no reference at all => delete file
            //if (confirm(ui_file.filename + " is no longer referenced.\n" +
            //    "Shall it be removed from task?")) {
                ui_file.delete();
            //}
        }
    }

    static removeContent(filenameItem, removeItemIfPossible) {
        $(filenameItem).val(emptyFileOption); // do not call change!
        let tr = $(filenameItem).closest('tr');
        tr.find('.fileref_fileref').first().val('');
        let button = tr.find('.collapse');
        if (button.html() === hideEditorText) {
            // remove editor
            button.html(showEditorText);
            tr.next().remove();
        }
        // row has a
        if (removeItemIfPossible) {
            let removeButton = tr.find('.remove_item').first();
            const isHidden = removeButton.css("display") === "none";
            if (!isHidden)
                removeButton.click();
        }
    }

    // checks if a given file id is used somewhere
    // (needed when file shall be deleted)
    static getCountFileIdReferenced(fileId) {
        let count = 0;
        $.each($(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            if (filerefId === fileId) {
                count++;
            }
        });

        return count;
    }

    checkForExclusiveUse(ui_file, fileid, otherFileRefList, listname) {
        // iterate through all file reference objects to find an 'old' one
        $.each(otherFileRefList.root.find(".fileref_fileref"), function(index, item) {
            if (item.value === fileid) {
                alert("file class for file '" + ui_file.filename + "' will be no longer a " + listname + " file");

                //const filenameobject = $(item).closest('tr').find('.fileref_filename');

                // file id matches
                // remove old fileref object

                //filenameobject.val(emptyFileOption).change();
                // FileReferenceList.updateAllFilenameLists();
                // remove actual numeric fileref value
                FileReferenceList.removeContent(item, true);
/*
                //item.value = '';
                let tr = $(item).closest('tr');
                //tr.find('.fileref_fileref').first().val('');

                // check if complete row can be deleted
                const table_body = tr.parent();
                if (table_body.find('tr').length > 1) {
                    // more than one row => delete row
                    // (object does not matter)
                    otherFileRefList.removeItem($(item));
                }
*/
            }
        });
    }
    onFileUpload(filename, uploadBox) {
        // select new filename in first empty filename
        //console.log("uploadFiles: select " + filename + " in option list");
        let done = false;
        $.each($(uploadBox).find("." + this.classFilename), function(index, element) {
            if (done)
                return false;
            const currentFilename = $(element).val();
            if (!currentFilename || 0 === currentFilename.length) {
                $(element).val(filename).change();
                // FileReferenceList.rowEnableEditorButton($(element).parent(), true);
                done = true;
            }
        });

        if (!done) { // no empty select option is found
            // append filename
            let newRow = this.addItem($(uploadBox).find('.' + this.classAddItem).last());
            // select filename
            $(uploadBox).find("." + this.classFilename).last().val(filename).change();
            // FileReferenceList.rowEnableEditorButton(newRow, true);
        }
    }

    onFilerefChanged(ui_file, fileid) {}

    static onFileSelectionChanged (tempSelElem) {              // changing a filename in the drop-down
        // console.log(tempSelElem);
        // console.log('Value is ' + tempSelElem.value);
        function isDuplicateId(fileid) {
            const filerefs = $(tempSelElem).closest('table').find(".fileref_fileref");
            let found = false;
            $.each(filerefs, function(index, item) {
                if (item.value === fileid) {
                    // fileref already in list!
                    alert('file is already in this list!');
                    found = true;
                    return false;
                }
            });
            return found;
        }



        // var found = false;
        const selectedFilename = tempSelElem.value;
        // const selectedFilename = $(tempSelElem).val();
        console.log('selectedFilename ' + selectedFilename);
        // get old file id
        const nextTd = $(tempSelElem).parent().next('td');
        const row = $(tempSelElem).closest('tr');
        const oldFileId = nextTd.find('.fileref_fileref')[0].value;

        FileReferenceList.rowEnableEditorButton(row, false);

        switch (selectedFilename) {
            case loadFileOption:
                // read new file
                console.log('read new file ');
                // reset selection in case choosing a file fails
                //$(tempSelElem).val(emptyFileOption); // do not call change!
                FileReferenceList.removeContent(tempSelElem, false);
                // change callback
                let dummybutton = $("#dummy_file_upload_button").first();
                dummybutton.unbind("change");
                dummybutton.change(function () {
                    let inputbutton = $("#dummy_file_upload_button")[0];
                    let filenew = inputbutton.files[0];
                    if (!filenew) {
                        console.log("no file selected -> cancel");
                        return;
                    }

                    readAndCreateFileData(filenew, undefined /*-1*/,
                        function (newFilename, fileId) {
                            if ($(tempSelElem)) {
                                $(tempSelElem).val(newFilename).change();
                                FileReferenceList.rowEnableEditorButton(row, true);
                            }
                            // set classname if file belongs to JUNIT
                            //setJavaClassname(newFilename);
                            //setJUnitDefaultTitle(newFilename);
                        });
                });
                // perform dummy click
                dummybutton.click();
                return;
            case emptyFileOption:
            case emptyFileOption.trim():
                // delete fileref id
                nextTd.find('.fileref_fileref')[0].value = '';
                break;
            default:
                // find file id belonging to the filename
                if (selectedFilename && selectedFilename.trim().length) {
                    let ui_file = FileWrapper.constructFromFilename(selectedFilename);
                    if (ui_file) { // can be undefined when no filename is selected
                        const fileid = ui_file.id;
                        if (isDuplicateId(fileid)) {
                            // clean input field
                            //$(tempSelElem).val(emptyFileOption).change();
                            FileReferenceList.removeContent(tempSelElem, false);
                            return;
                        }

                        // set new file id
                        nextTd.find('.fileref_fileref')[0].value = fileid;
                        FileReferenceList.rowEnableEditorButton(row, true);
                        if ($(tempSelElem).hasClass('xml_test_filename')) {   // is it a test or a model-solution
                            // call test specific configured handler
                            if (fileid) {
                                // setJavaClassname(selectedFilename);
                                // setJUnitDefaultTitle(selectedFilename);
                                config.handleFilenameChangeInTest(selectedFilename, tempSelElem);
                            }
                        } else {
                            TestFileReference.onFilerefChanged(ui_file, fileid);
                        }
                    }
                }
        }

        if (oldFileId !== '') {
            // delete old file
            FileReferenceList.deleteFile(oldFileId);
        }
    };

    static updateAllViews() {
        $.each($(".fileref_viewer"), function(index, item) {
            let tr = $(item).closest('tr'); // parent().parent();
            let trPrev = tr.prev(); // parent().parent();
            const fileid = trPrev.find('.fileref_fileref')[0].value;
            let ui_file = FileWrapper.constructFromId(fileid);
            $(item).val(ui_file.text);
       });
    }

    static updateAllEditorButtons() {
        $.each($(".fileref_fileref"), function(index, item) {
            let row = $(item).parent().parent();
            FileReferenceList.rowEnableEditorButton(row, true);
        });
    }

    // - update all filename lists
    // - update selection index
    // - update selection text
    static updateAllFilenameLists(changedId, newFilename) {
        //console.log('updateAllFilenameLists for ' + changedId + ', new filename: ' + newFilename);

        $.each($(filenameClassList.join(',')), function(index, item) {
            //console.log("update filelist in test ");
            // store name of currently selected filename
            const text = $("option:selected", item).text();
            //console.log("selected is " + text);
            FileReferenceList.updateFilenameList(item); // update filename list in item

            // is selected filename in item changed?
            const refid = $(item).closest('tr').find('.fileref_fileref').first().val();
            if (refid === changedId && changedId !== undefined) {
                // yes =>
                if (newFilename !== undefined) {
                    // update selected filename if not deleted
                    console.log('change selection value');
                    $(item).val(newFilename);
                } else {
                    // file is deleted => remove content
                    FileReferenceList.removeContent(item, true);
                }
            } else {
                // no =>
                $(item).val(text);

                // let indexFound = -1;
                // if (text !== emptyFileOption) {
                //
                //     // check if previously selected filename is still in list
                //     $.each($(".xml_file_filename"), function (indexOpt, item) {
                //         if (item.value.length > 0 && item.value === text) {
                //             indexFound = indexOpt;
                //             return false;
                //         }
                //     });
                // }
                //
                // if (indexFound >= 0) {
                //     //console.log("selektiere " + indexFound);
                //     item.selectedIndex = indexFound + 1; // +1:weil am Anfang noch ein Leerstring ist
                // } else {
                //     // previously seelected text is not in the list
                //     // => expect filename to be deleted
                //     if (newFilename === undefined && text !== emptyFileOption) {
                //         console.error('could not find filename: <' + text + '>');
                //     }
                //     /*
                //                     // das ist kein guter Ort für so was!!
                //                     // remove actual numeric fileref value
                //                     let td = $(item).parent();
                //                     let tr = td.parent();
                //                     tr.find('.fileref_fileref').first().val('');
                //                     // check if complete row can be deleted
                //                     const table_body = tr.parent();
                //                     if (table_body.find('tr').length > 1) {
                //                         // more than one row => delete row
                //                         modelSolutionFileRefSingleton.removeFileRef($(item));
                //                     }
                //     */
                // }

            }
        });
    }

    // create the drop-down with all possible filenames
    static updateFilenameList(tempSelElem) {
        $(tempSelElem).empty();
        let tempOption = $("<option>" + emptyFileOption + "</option>");
        $(tempSelElem).append(tempOption); // empty string
        $.each($(".xml_file_filename"), function(index, item) {
            if (item.value.length > 0) {
                tempOption = $("<option></option>");
                tempOption[0].textContent = item.value;
                $(tempSelElem).append(tempOption);
            }
        });
        //tempSelElem.val(""); // preset no filename
        tempOption = $("<option></option>");
        tempOption[0].textContent = loadFileOption;
        $(tempSelElem).append(tempOption);
    }


    static uploadFiles(files, box, instance) {
        /*if (files.length > 1) {
            alert('You have dragged more than one file. You must drop exactly one file!');
            return;
        }
        */
        $.each(files, function(index, file) {
            readAndCreateFileData(file, undefined/*-1*/, function(filename) {
                instance.onFileUpload(filename, box);
            });
        });
    }

    static init(dropzoneSelector, sectionSelector, classname, dropZoneObject) {
        let root = dropZoneObject;
        if (dropzoneSelector)
            root = $(dropzoneSelector); // find approach that fits all classes

        if (sectionSelector) {
            $(sectionSelector)[0].textContent = "";
            $(sectionSelector).append(classname.getInstance().getTableString());
        }

        classname.getInstance().init(root, DEBUG_MODE);
        $(root).on({
            drop: function(e){
                if(e.originalEvent.dataTransfer){
                    if(e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        //UPLOAD FILES HERE
                        FileReferenceList.uploadFiles(e.originalEvent.dataTransfer.files, e.currentTarget,
                            classname.getInstance());
                    }
                }
            }
        });
    }
}





export class TestFileReference extends FileReferenceList {

    constructor(tablenode) {
        super('xml_test_filename', 'xml_test_fileref', 'TestFileReference', 'File',
            'file containing test cases, test configuration, libraries etc.', true);
        this.tablenode = tablenode;
        // Add callbacks:
        TestFileReference.addFileCallback(tablenode.querySelector('.add_test_fileref'));
        tablenode.querySelector('.fileref_filename').onchange = function(changeevent) {
            TestFileReference.onFileSelectionChanged(changeevent.target);
        }
    }

    static getInstance() {return testFileRefSingleton;}

    static removeRowCallback(removeevent) {
        removeevent.preventDefault();
        let element = removeevent.target;

        let tr = element.closest('tr');
        let tbody = element.closest('.xml_fileref_table').querySelector('tbody');

        let hasNextTr = (tr.nextElementSibling !== null);
        let hasPrevTr = (tr.previousElementSibling !== null);

        if (!hasNextTr) { //.length === 0) {
            console.log('no successor => readd add button');
            // if row to be deleted is last row then add +-button to last row
            tr.previousElementSibling.querySelector('.add_test_fileref').style.display = '';
        }

        tr.remove(); // remove complete row
        if (!hasPrevTr) { // .length === 0) {
            console.log('no predecessor => readd label');
            // row to be deleted is first row
            // => add filename label to first column
            tbody.querySelector('label').style.display = '';
        }

        // check if previousRow is first row
        if (tbody.querySelectorAll('tr').length === 1) {
            console.log('just one row left => hide remove buttons');
            // table has exactly one row left
            // => hide all remove file buttons
            tbody.querySelectorAll("." + DynamicList.classRemoveItem).forEach(
                e => {
                    e.style.display = 'None';
                }
            );
        }
    }

    static addFileCallback(addButton) {
        addButton.onclick = function (addevent) {
            addevent.preventDefault();
            let element = addevent.target;

            const context = {};
            Templates.renderForPromise('qtype_proforma/taskeditor_testfile_row', context)
                .then(({html, js}) => {
                    // Here eventually I have my compiled template, and any javascript that it generated.
                    // The templates object has append, prepend and replace functions.
                    let table = element.closest('.xml_fileref_table');
                    let tbody = table.querySelector('tbody');
                    let newrow = Templates.appendNodeContents(tbody, html, js)[0];
                    // Remove current +-button
                    element.style.display = 'None'; // element.remove();
                    // Hide label.
                    newrow.querySelector('label').style.display = 'None';
                    // Add callback to remove button(s).
                    tbody.querySelectorAll("." + DynamicList.classRemoveItem).forEach(e => {
                        // show all remove file buttons
                        e.style.display = '';
                        // Add callback for remove
                        e.onclick = function (removeevent) {
                            TestFileReference.removeRowCallback(removeevent);
                        }
                    });
                    // Add callbacks
                    TestFileReference.addFileCallback(newrow.querySelector('.add_test_fileref'));
                    newrow.querySelector('.fileref_filename').onchange = function(changeevent) {
                        TestFileReference.onFileSelectionChanged(changeevent.target);
                    }
                })
                // Deal with this exception (Using core/notify exception function is recommended).
                .catch((error) => {
                    console.error('Error occured');
                    console.error(error);
                    displayException(error);
                });
        }
    }

    /*
        onFileUpload(filename, uploadBox) {
            super.onFileUpload(filename, uploadBox);
            // set classname if exactly one file is assigned
            // todo: this should be part of the configuration
            // const ui_classname = $(uploadBox).find(".xml_ju_mainclass");
            // if (ui_classname.length === 1) {
            //     $.each(ui_classname, function(index, element) {
            //         const currentFilename = $(element).val();
            //         if (currentFilename === "" && !readXmlActive) {
            //             $(element).val(javaParser.getFullClassnameFromFilename(filename)).change();
            //         }
            //     });
            // }
        }*/
}
// let testFileRefSingleton = new TestFileReference();














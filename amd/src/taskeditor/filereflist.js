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
 * Helper functions for zipping and unzipping task
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     // This file is part of ProFormA Question Type for Moodle
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
 * Class for dealing with file references in tests or model solution
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

// todo: replace table solution with something without table!

import {DynamicList} from "./dynamic-list";
import $ from 'jquery';
import {readAndCreateFileData} from "./helper";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import {FileWrapper} from "./file";
import {taskeditorconfig} from "./config";
import {DEBUG_MODE,} from "./util";
import * as Str from 'core/str';
import {TestWrapper} from "./test";



const loadFileOption = "<open...>";
const newFileOption = "<new file>";
const emptyFileOption = " "; // must not be empty!!

var showEditorText = 'View'; // Str.get_string('taskeditorview', 'qtype_proforma'); // 'View';
var hideEditorText = 'Hide'; // Str.get_string('taskeditorhide', 'qtype_proforma'); // 'Hide';

let filenameClassList = [];
let filerefClassList = [];

// abstract class for a filename reference input
export class FileReferenceList extends DynamicList {

    static getLocalisedStrings() {
        let strings = [
            { key: 'taskeditorview', component: 'qtype_proforma' },
            { key: 'taskeditorhide', component: 'qtype_proforma' }
        ];
        return Str.get_strings(strings)
            .then(results => {
                showEditorText = results[0];
                hideEditorText = results[1];
            });
    }

    constructor(classFilename, classFileref, jsClassName, label, help, mandatory) {
        super(classFilename, classFileref, jsClassName, label, help, mandatory, 'xml_fileref_table');

        //this.table = this.table +
        //    "<span class='drop_zone_text drop_zone'>Drop Your File(s) Here!</span>";

        filenameClassList.push('.' + this.classFilename);
        filerefClassList.push('.' + classFileref);
    }


    // override
    /*
    getTableString() {
        return super.getTableString()  +
            "<span class='drop_zone_text drop_zone'>Drop Your File(s) Here!</span>";
    }*/

    // createExtraContent() { return ''; }

/*    createRowContent() {
        const tdFilename = "<td><select class='mediuminput fileref_filename " + this.classFilename + "' " +
            "title='" + this.help + "'></select></td>"+
            "<td><label for='fileref_fileref'>Fileref: </label>"+ // fileref
            "<input class='tinyinput fileref_fileref' readonly/></td>";


        const tdExpandButton = "<td><button class='taskeditor-collapse' title='show content'" +
            ">"+showEditorText+"</button><br></td>";

        return tdFilename + tdExpandButton + this.createExtraContent();
    }
*/

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
        FileReferenceList.updateFilenameList(root.find("." + this.classFilename).last());
        FileReferenceList.rowEnableEditorButton(root, false);
        if (!DEBUG_MODE) {
            root.find(".fileref_fileref").hide();
            root.find("label[for='fileref_fileref']").hide();
        }

        // register dragenter, dragover.
        root.on({
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
            return this.addItem(box.find("." + this.classAddItem).first())
                .then(() => {
                    let element = box.find("." + this.classFilename);
                    FileReferenceList.updateFilenameList(element.eq(index));
                    element.eq(index).val(filename).change();
            });
        } else {
            let element = box.find("." + this.classFilename);
            FileReferenceList.updateFilenameList(element.eq(index));
            element.eq(index).val(filename).change();
        }
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
                $( "<tr>" +
                    "   <td></td>" +
                    "   <td colspan='"+ numberOfColumns + "'><textarea disabled cols='80' rows='10' class='fileref_viewer'>"+
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

        row.find(".taskeditor-collapse").last().prop('disabled', !enabled);
    }

    addItem(element) {
        let td = element.parent();
        let tr = td.parent();
        let table_body = tr.parent();
        return super.addItem(element)
            .then(newRow => {
                // console.log('returned new row is');
                // console.log(newRow);
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
                    table_body.find(".fileref_fileref").hide();
                    table_body.find("label[for='fileref_fileref']").hide();
                }
                FileReferenceList.addCallbacks(newRow[0]);
            });
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
            // if (window.confirm(ui_file.filename + " is no longer referenced.\n" +
            //     "Shall it be removed from task?")) {
                ui_file.delete();
            // }
        }
    }

    static removeContent(filenameItem, removeItemIfPossible) {
        $(filenameItem).val(emptyFileOption); // do not call change!
        let tr = $(filenameItem).closest('tr');
        tr.find('.fileref_fileref').first().val('');
        let button = tr.find('.taskeditor-collapse');
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
            this.addItem($(uploadBox).find('.' + this.classAddItem).last())
                .then(newRow => {
                    // select filename
                    $(uploadBox).find("." + this.classFilename).last().val(filename).change();
                    // FileReferenceList.rowEnableEditorButton(newRow, true);
                });
        }
    }

    onFilerefChanged(ui_file, fileid) {}

    onFileSelectionChanged (tempSelElem) {              // changing a filename in the drop-down

        function isDuplicateId(fileid) {
            const filerefs = $(tempSelElem).closest('table').find(".fileref_fileref");
            let found = false;
            $.each(filerefs, function(index, item) {
                if (item.value === fileid) {
                    // fileref already in list!
                    // alert('file ' + item.value + ' is already in this list!');
                    found = true;
                    return false;
                }
            });
            return found;
        }

        // var found = false;
        const selectedFilename = $(tempSelElem).val();
        // get old file id
        const nextTd = $(tempSelElem).parent().next('td');
        const row = $(tempSelElem).closest('tr');
        const oldFileId = nextTd.find('.fileref_fileref')[0].value;

        FileReferenceList.rowEnableEditorButton(row, false);

        switch (selectedFilename) {
            case newFileOption:
                FileWrapper.createFromTemplate();
                const hash = '#proforma-files-section';
                const tab = document.querySelector('.nav-link[href="' + hash + '"]');
                if (tab) {
                    tab.click();
                }
                break;
            case loadFileOption:
                // read new file
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
                            alert('file ' + ui_file.filename + ' is already in this list!');
                            // clean input field
                            //$(tempSelElem).val(emptyFileOption).change();
                            FileReferenceList.removeContent(tempSelElem, false);
                            return;
                        }

                        // set new file id
                        nextTd.find('.fileref_fileref')[0].value = fileid;
                        FileReferenceList.rowEnableEditorButton(row, true);
                        if ($(tempSelElem).hasClass('xml_fileref_filename')) {   // is it a test or a model-solution
                            // call test specific configured handler
                            if (fileid) {
                                // setJavaClassname(selectedFilename);
                                // setJUnitDefaultTitle(selectedFilename);
                                taskeditorconfig.handleFilenameChangeInTest(selectedFilename, tempSelElem);
                            }
                        } else {
                            this.onFilerefChanged(ui_file, fileid);
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

        let tempOptionNew = $("<option></option>");
        tempOptionNew[0].textContent = newFileOption;
        $(tempSelElem).append(tempOptionNew);
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
/*        if (dropzoneSelector)
            root = $(dropzoneSelector); // find approach that fits all classes

        if (sectionSelector) {
            $(sectionSelector)[0].textContent = "";
            $(sectionSelector).append(classname.getInstance().getTableString());
        }
*/
        classname.getInstance().init(root, DEBUG_MODE);
        root.on({
            drop: function(e){
                console.log('ondrop ');
                console.log(e);
                let data = e.originalEvent.dataTransfer.getData("text");
                console.log(data);
                if (data.startsWith('move_test ')) {
                    // Move test
                    let result = data.substring('move_test '.length);
                    console.log('move ' + result);
                    let test = TestWrapper.constructFromId(result);
                    let thiselement = e.target.closest('.xml_test');
                    console.log(thiselement.id);
                    let otherelement = document.getElementById('test_' + result);
                    let nextSibling = thiselement.nextElementSibling;
                    if (nextSibling === otherelement) {
                        thiselement.before(otherelement);
                    } else {
                        thiselement.after(otherelement);
                    }
                    return;
                }

                // drop file.
                if (e.originalEvent.dataTransfer){
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

    static addCallbacks(rootnode) {
        // Add callback for onclick of '+' button.
        // console.log('Add callbacks for');
        // console.log(rootnode);
        // console.log('callback for + button');
        let subnode = rootnode.querySelector('.add_fileref');
        if (!subnode)
            console.error('could not find subnode .add_fileref');
        else {
            subnode.onclick = function (addevent) {
                addevent.preventDefault();
                // TODO: use static or global function!
                TestFileReference.getInstance().addItem($(addevent.target));
            }
        }
        // Add callback for onclick of 'x' button.
    //        rootnode.querySelector(".remove_item").onclick = function (removeevent) {
        // console.log('callback for x button');
//        rootnode.querySelector("." + TestFileReference.getInstance().classRemoveItem).onclick = function (removeevent) {
        subnode = rootnode.querySelector('.remove_item');
        if (!subnode)
            console.error('could not find subnode .remove_item');
        else {
            subnode.onclick = function (removeevent) {
                removeevent.preventDefault();
                TestFileReference.getInstance().removeItem($(removeevent.target));
            }
        }

        // console.log('callback for change selection');
        subnode = rootnode.querySelector('.fileref_filename');
        if (!subnode)
            console.error('could not find subnode .fileref_filename');
        else {
            subnode.onchange = function (changeevent) {
                changeevent.preventDefault();
                TestFileReference.getInstance().onFileSelectionChanged($(changeevent.target));
            }
        }

        // console.log('callback for toggle editor');
        subnode = rootnode.querySelector('.taskeditor-collapse');
        // console.error('TODO taskeditor-collapse');
        if (!subnode)
            console.error('could not find subnode .taskeditor-collapse');
        else {
            rootnode.querySelector(".taskeditor-collapse").onclick = function (toggleevent) {
                toggleevent.preventDefault();
                TestFileReference.getInstance().toggleEditor($(toggleevent.target));
            }
        }
    }
}


export class TestFileReference extends FileReferenceList {

    constructor() {
        super('xml_fileref_filename', 'xml_test_fileref', 'TestFileReference', 'File',
            'file containing test cases, test configuration, libraries etc.', true);
    }

    static getInstance() {return testFileRefSingleton;}
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
let testFileRefSingleton = new TestFileReference();


export class ModelSolutionFileReference extends FileReferenceList {

    constructor() {
        super('xml_model-solution_filename', 'xml_model-solution_fileref',
            'ModelSolutionFileReference', 'File',
            'file belonging to a model solution', true);
    }
    static getInstance() {return modelSolutionFileRefSingleton;}
}
let modelSolutionFileRefSingleton = new ModelSolutionFileReference();







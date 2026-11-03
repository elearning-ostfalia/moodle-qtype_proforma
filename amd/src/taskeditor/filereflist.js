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
 * Class for dealing with file references in tests or model solution
 *
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

// todo: replace table solution with something without table!

import {debugLog} from '../debug';
import {DynamicList} from "./dynamic-list";
import $ from 'jquery';
import {readAndCreateFileData} from "./helper";
import {FileWrapper} from "./file";
import {DEBUG_MODE, handleFilenameChangeInTest} from "./helper";
import * as Str from 'core/str';
import Notification from 'core/notification';


let loadFileOption = "<open...>";
let newFileOption = "<new file>";
const emptyFileOption = " "; // Must not be empty!!

let showEditorText = 'View'; // Str.get_string('taskeditorview', 'qtype_proforma'); // 'View';
let hideEditorText = 'Hide'; // Str.get_string('taskeditorhide', 'qtype_proforma'); // 'Hide';

let filenameClassList = [];
let filerefClassList = [];

// Abstract class for a filename reference input
export class FileReferenceList extends DynamicList {

    static getLocalisedStrings() {
        let strings = [
            {key: 'taskeditorview', component: 'qtype_proforma'},
            {key: 'taskeditorhide', component: 'qtype_proforma'},
            {key: 'openfile', component: 'qtype_proforma'},
            {key: 'newfile', component: 'qtype_proforma'}
        ];
        return Str.get_strings(strings)
            .then(results => {
                showEditorText = results[0];
                hideEditorText = results[1];
                loadFileOption = results[2];
                newFileOption = results[3];
                return results;
            });
    }

    constructor(classFilename, classFileref, jsClassName, label, help, mandatory) {
        super(classFilename, classFileref, jsClassName, label, help, mandatory, 'xml_fileref_table');

        // This.table = this.table +
        //    "<span class='drop_zone_text drop_zone'>Drop Your File(s) Here!</span>";

        filenameClassList.push('.' + this.classFilename);
        filerefClassList.push('.' + classFileref);
    }


    doOnAll(callback, root) {
        if (root) {
            debugLog('doOnAllIds ios deprecated, use static version instead');
        }
        let theRoot = root ? root : this.root;
        $.each(theRoot.find(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            return callback(filerefId);
        });
    }

    doOnNonEmpty(callback) {
        $.each(this.root.find(".fileref_fileref"), function(index, item) {
            const filerefId = item.value;
            if (filerefId) {
                return callback(filerefId);
            }
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

    // Init table
    init(root, DEBUG_MODE) {
        if (!this.root) {
            this.root = root;
        }
        FileReferenceList.updateFilenameList(root.find("." + this.classFilename).last());
        FileReferenceList.rowEnableEditorButton(root, false);
        if (!DEBUG_MODE) {
            root.find(".fileref_fileref").hide();
            root.find("label.fileref_fileref_label").hide();
        }

        // Register dragenter, dragover.
        root.on({
            dragover: function(e) {
                e.preventDefault();
                e.stopPropagation();
                // E.dataTransfer.dropEffect = 'copy';
            },
            dragenter: function(e) {
                e.preventDefault();
                e.stopPropagation();
            },
/*
            Drop: function(e){
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

    // For creation by reading xml
    setFilenameOnCreation(box, index, filename) { // Index is 0-based
        // set filename
        if (index > 0) {
            // Create new fileref if index > 0
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
        this.doOnAll(function() {
            counter++;
        }, root);
        return counter;
    }

    getNumberOfExtraColumns() {
        return 0;
    }

    toggleEditor(element) {
        let td = element.parent();
        let tr = td.parent();
        const fileid = tr.find('.fileref_fileref')[0].value;
        let uiFile = FileWrapper.constructFromId(fileid);

        if (element.html() === hideEditorText) {
            element.html(showEditorText);
            tr.next().remove();
        } else {
            const numberOfColumns = 7 + this.getNumberOfExtraColumns();
            if (uiFile && !uiFile.isBinary) {
                element.html(hideEditorText);
                $("<tr>" +
                    "   <td></td>" +
                    "   <td colspan='" + numberOfColumns + "'><textarea disabled cols='80' rows='10' class='fileref_viewer'>" +
                    uiFile.text
                    + "</textarea></td></tr>").insertAfter(tr);
            }
        }
    }

    static rowGetFileId(row) {
        return row.find('.fileref_fileref')[0].value;
    }

    static rowEnableEditorButton(row, enabled) {
        if (enabled) {
            // Check if file is binary and cannot be viewed
            const fileid = FileReferenceList.rowGetFileId(row);
            if (!fileid) {
                return;
            }
            let uiFile = FileWrapper.constructFromId(fileid);
            if (uiFile.isBinary) {
                enabled = false;
            }
        }

        row.find(".taskeditor-collapse").last().prop('disabled', !enabled);
    }

    addItem(element) {
        let td = element.parent();
        let tr = td.parent();
        let tableBody = tr.parent();
        return super.addItem(element)
            .then(newRow => {
                FileReferenceList.rowEnableEditorButton(newRow, false);
                // Add filelist to new file option
                FileReferenceList.updateFilenameList(tableBody.find("." + this.classFilename).last());

                if (!DEBUG_MODE) {
                    // Hide new fileref fields
                    tableBody.find(".fileref_fileref").hide();
                    tableBody.find("label.fileref_fileref_label").hide();
                }
                FileReferenceList.addCallbacks(newRow[0]);
            });
    }


    // Override
    getItemCount(tableBody) {
        let count = 0;
        $.each(tableBody.find("tr"), function(index, item) {
            if ($(item).find("td").length > 2) {
                count++;
            }
        });
        return count;
    }

    // Override
    getPreviousItem(tr) {
        let previousRow = tr.prev("tr");

        if (previousRow.find('td').length === 1) {
            // Only one column => editor visible go to previous row
            previousRow = previousRow.prev("tr");
        }
        return previousRow;
    }

    removeItem(element) {
        let td = element.parent();
        let tr = td.parent();

        // Save associated fileid
        const fileid = FileReferenceList.rowGetFileId(tr); // Tr.find('.fileref_fileref')[0].value;

        // remove editor
        const buttonText = td.prev().find('button').html();
        if (buttonText === hideEditorText) {
            // Remove editor
            tr.next().remove();
        }

        super.removeItem(element);

        if (fileid) {
            FileReferenceList.deleteFile(fileid);
        }
    }

    // TODO move to file??
    static deleteFile(fileid) {
        // Check if there any references
        let uiFile = FileWrapper.constructFromId(fileid);
        if (FileReferenceList.getCountFileIdReferenced(fileid) === 0) {
            // No reference at all => delete file
            // if (window.confirm(uiFile.filename + " is no longer referenced.\n" +
            //     "Shall it be removed from task?")) {
                uiFile.delete();
            // }
        }
    }

    static removeContent(filenameItem, removeItemIfPossible) {
        $(filenameItem).val(emptyFileOption); // Do not call change!
        let tr = $(filenameItem).closest('tr');
        tr.find('.fileref_fileref').first().val('');
        let button = tr.find('.taskeditor-collapse');
        if (button.html() === hideEditorText) {
            // Remove editor
            button.html(showEditorText);
            tr.next().remove();
        }
        // Row has a
        if (removeItemIfPossible) {
            let removeButton = tr.find('.remove_item').first();
            const isHidden = removeButton.css("display") === "none";
            if (!isHidden) {
                removeButton.click();
            }
        }
    }

    // Checks if a given file id is used somewhere
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

    checkForExclusiveUse(uiFile, fileid, otherFileRefList, listname) {
        // Iterate through all file reference objects to find an 'old' one
        $.each(otherFileRefList.root.find(".fileref_fileref"), function(index, item) {
            if (item.value === fileid) {
                Notification.alert('', "file class for file '" + uiFile.filename + "' will be no longer a " + listname + " file");
                // File id matches
                // remove old fileref object
                // remove actual numeric fileref value
                FileReferenceList.removeContent(item, true);
            }
        });
    }
    onFileUpload(filename, uploadBox) {
        // Select new filename in first empty filename
        let done = false;
        $.each($(uploadBox).find("." + this.classFilename), function(index, element) {
            if (done) {
                return false;
            }
            const currentFilename = $(element).val();
            if (!currentFilename || 0 === currentFilename.length) {
                $(element).val(filename).change();
                // FileReferenceList.rowEnableEditorButton($(element).parent(), true);
                done = true;
            }
        });

        if (!done) { // No empty select option is found
            // append filename
            this.addItem($(uploadBox).find('.' + this.classAddItem).last())
                .then(() => {
                    // Select filename
                    $(uploadBox).find("." + this.classFilename).last().val(filename).change();
                    // FileReferenceList.rowEnableEditorButton(newRow, true);
                });
        }
    }

    // eslint-disable-next-line no-unused-vars
    onFilerefChanged(uiFile, fileid) {}

    onFileSelectionChanged(tempSelElem) { // Changing a filename in the drop-down

        /**
         * Checks whether the file is already referenced in the same list.
         *
         * @param {string} fileid
         * @returns {boolean} true if the file is already in the list
         */
        function isDuplicateId(fileid) {
            const filerefs = $(tempSelElem).closest('table').find(".fileref_fileref");
            let found = false;
            $.each(filerefs, function(index, item) {
                if (item.value === fileid) {
                    // Fileref already in list!
                    // alert('file ' + item.value + ' is already in this list!');
                    found = true;
                    return false;
                }
            });
            return found;
        }

        // Var found = false;
        const selectedFilename = $(tempSelElem).val();
        // Get old file id
        const nextTd = $(tempSelElem).parent().next('td');
        const row = $(tempSelElem).closest('tr');
        const oldFileId = nextTd.find('.fileref_fileref')[0].value;

        FileReferenceList.rowEnableEditorButton(row, false);

        switch (selectedFilename) {
            case newFileOption: {
                FileWrapper.createFromTemplate()
                    .then(uiFile => {
                        // Preset filename with a unique filename
                        const newFilename = 'new file ' + uiFile.id;
                        uiFile.filename = newFilename;
                        if ($(tempSelElem)) {
                            // Select file as referenced file.
                            $(tempSelElem).val(newFilename).change();
                            FileReferenceList.rowEnableEditorButton(row, true);
                        }
                    });
                // Open tab with files in order to create file input
                const hash = '#proforma-files-section';
                const tab = document.querySelector('.nav-link[href="' + hash + '"]');
                if (tab) {
                    tab.click();
                }
                break;
            }
            case loadFileOption: {
                // Read new file
                // reset selection in case choosing a file fails
                // $(tempSelElem).val(emptyFileOption); // do not call change!
                FileReferenceList.removeContent(tempSelElem, false);
                // Change callback
                let dummybutton = $("#dummy_file_upload_button").first();
                dummybutton.unbind("change");
                dummybutton.change(function() {
                    let inputbutton = $("#dummy_file_upload_button")[0];
                    let filenew = inputbutton.files[0];
                    if (!filenew) {
                        debugLog("no file selected -> cancel");
                        return;
                    }

                    readAndCreateFileData(filenew, undefined /* -1*/,
                        function(newFilename) {
                            if ($(tempSelElem)) {
                                $(tempSelElem).val(newFilename).change();
                                FileReferenceList.rowEnableEditorButton(row, true);
                            }
                            // Set classname if file belongs to JUNIT
                            // setJavaClassname(newFilename);
                            // setJUnitDefaultTitle(newFilename);
                        });
                });
                // Perform dummy click
                dummybutton.click();
                return;
            }
            case emptyFileOption:
            case emptyFileOption.trim():
                // Delete fileref id
                nextTd.find('.fileref_fileref')[0].value = '';
                break;
            default:
                // Find file id belonging to the filename
                if (selectedFilename && selectedFilename.trim().length) {
                    let uiFile = FileWrapper.constructFromFilename(selectedFilename);
                    if (uiFile) { // Can be undefined when no filename is selected
                        const fileid = uiFile.id;
                        if (isDuplicateId(fileid)) {
                            Str.get_string('fileexists', 'qtype_proforma', uiFile.filename)
                                .then(content => Notification.alert('', content))
                                .catch(Notification.exception);
                            // Alert('file ' + uiFile.filename + ' is already in this list!');
                            // clean input field
                            // $(tempSelElem).val(emptyFileOption).change();
                            FileReferenceList.removeContent(tempSelElem, false);
                            return;
                        }

                        // Set new file id
                        nextTd.find('.fileref_fileref')[0].value = fileid;
                        FileReferenceList.rowEnableEditorButton(row, true);
                        if ($(tempSelElem).hasClass('xml_fileref_filename')) { // Is it a test or a model-solution
                            // call test specific configured handler
                            if (fileid) {
                                // SetJavaClassname(selectedFilename);
                                // setJUnitDefaultTitle(selectedFilename);
                                handleFilenameChangeInTest(selectedFilename, tempSelElem);
                            }
                        } else {
                            this.onFilerefChanged(uiFile, fileid);
                        }
                    }
                }
        }

        if (oldFileId !== '') {
            // Delete old file
            FileReferenceList.deleteFile(oldFileId);
        }
    }

    static updateAllViews() {
        $.each($(".fileref_viewer"), function(index, item) {
            let tr = $(item).closest('tr'); // Parent().parent();
            let trPrev = tr.prev(); // Parent().parent();
            const fileid = trPrev.find('.fileref_fileref')[0].value;
            let uiFile = FileWrapper.constructFromId(fileid);
            $(item).val(uiFile.text);
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

        $.each($(filenameClassList.join(',')), function(index, item) {
            // Store name of currently selected filename
            const text = $("option:selected", item).text();
            FileReferenceList.updateFilenameList(item); // Update filename list in item

            // is selected filename in item changed?
            const refid = $(item).closest('tr').find('.fileref_fileref').first().val();
            if (refid === changedId && changedId !== undefined) {
                // Yes =>
                if (newFilename !== undefined) {
                    // Update selected filename if not deleted
                    $(item).val(newFilename);
                } else {
                    // File is deleted => remove content
                    FileReferenceList.removeContent(item, true);
                }
            } else {
                // No =>
                $(item).val(text);

                // Let indexFound = -1;
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
                //                     const tableBody = tr.parent();
                //                     if (tableBody.find('tr').length > 1) {
                //                         // more than one row => delete row
                //                         modelSolutionFileRefSingleton.removeFileRef($(item));
                //                     }
                //     */
                // }

            }
        });
    }

    // Create the drop-down with all possible filenames
    static updateFilenameList(tempSelElem) {
        $(tempSelElem).empty();
        let tempOption = $("<option>" + emptyFileOption + "</option>");
        $(tempSelElem).append(tempOption); // Empty string
        $.each($(".xml_file_filename"), function(index, item) {
            if (item.value.length > 0) {
                tempOption = $("<option></option>");
                tempOption[0].textContent = item.value;
                $(tempSelElem).append(tempOption);
            }
        });
        // TempSelElem.val(""); // preset no filename
        tempOption = $("<option></option>");
        tempOption[0].textContent = loadFileOption;
        $(tempSelElem).append(tempOption);

        let tempOptionNew = $("<option></option>");
        tempOptionNew[0].textContent = newFileOption;
        $(tempSelElem).append(tempOptionNew);
    }


    static uploadFiles(files, box, instance) {
        /* If (files.length > 1) {
            alert('You have dragged more than one file. You must drop exactly one file!');
            return;
        }
        */
        $.each(files, function(index, file) {
            readAndCreateFileData(file, undefined/* -1*/, function(filename) {
                instance.onFileUpload(filename, box);
            });
        });
    }

    static init(dropzoneSelector, sectionSelector, classname, dropZoneObject) {
        let root = dropZoneObject;
        /*        If (dropzoneSelector)
            root = $(dropzoneSelector); // find approach that fits all classes

        if (sectionSelector) {
            $(sectionSelector)[0].textContent = "";
            $(sectionSelector).append(classname.getInstance().getTableString());
        }
*/
        classname.getInstance().init(root, DEBUG_MODE);
        root.on({
            drop: function(e) {
                debugLog('ondrop ');
                debugLog(e);
                let data = e.originalEvent.dataTransfer.getData("text");
                if (data.startsWith('move_test ')) {
                    // Move test
                    let result = data.substring('move_test '.length);
                    let thiselement = e.target.closest('.xml_test');
                    let otherelement = document.getElementById('test_' + result);
                    let nextSibling = thiselement.nextElementSibling;
                    if (nextSibling === otherelement) {
                        thiselement.before(otherelement);
                    } else {
                        thiselement.after(otherelement);
                    }
                    return;
                }

                // Drop file.
                if (e.originalEvent.dataTransfer) {
                    if (e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        // UPLOAD FILES HERE
                        FileReferenceList.uploadFiles(e.originalEvent.dataTransfer.files, e.currentTarget,
                            classname.getInstance());
                    }
                }
            }
        });
    }

    static addCallbacks(rootnode) {
        // Add callback for onclick of '+' button.
        let subnode = rootnode.querySelector('.add_fileref');
        if (!subnode) {
            console.error('could not find subnode .add_fileref');
        } else {
            subnode.onclick = function(addevent) {
                addevent.preventDefault();
                // TODO: use static or global function!
                TestFileReference.getInstance().addItem($(addevent.target));
            };
        }
        // Add callback for onclick of 'x' button.
    //        rootnode.querySelector(".remove_item").onclick = function (removeevent) {
//        rootnode.querySelector("." + TestFileReference.getInstance().classRemoveItem).onclick = function (removeevent) {
        subnode = rootnode.querySelector('.remove_item');
        if (!subnode) {
            console.error('could not find subnode .remove_item');
        } else {
            subnode.onclick = function(removeevent) {
                removeevent.preventDefault();
                TestFileReference.getInstance().removeItem($(removeevent.target));
            };
        }

        subnode = rootnode.querySelector('.fileref_filename');
        if (!subnode) {
            console.error('could not find subnode .fileref_filename');
        } else {
            subnode.onchange = function(changeevent) {
                changeevent.preventDefault();
                TestFileReference.getInstance().onFileSelectionChanged($(changeevent.target));
            };
        }

        subnode = rootnode.querySelector('.taskeditor-collapse');
        // Console.error('TODO taskeditor-collapse');
        if (!subnode) {
            console.error('could not find subnode .taskeditor-collapse');
        } else {
            rootnode.querySelector(".taskeditor-collapse").onclick = function(toggleevent) {
                toggleevent.preventDefault();
                TestFileReference.getInstance().toggleEditor($(toggleevent.target));
            };
        }
    }
}


export class TestFileReference extends FileReferenceList {

    constructor() {
        super('xml_fileref_filename', 'xml_test_fileref', 'TestFileReference', 'File',
            'file containing test cases, test configuration, libraries etc.', true);
    }

    static getInstance() {
        return testFileRefSingleton;
    }
/*
    OnFileUpload(filename, uploadBox) {
        super.onFileUpload(filename, uploadBox);
        // set classname if exactly one file is assigned
        // todo: this should be part of the configuration
        // const uiClassname = $(uploadBox).find(".xml_entry_point");
        // if (uiClassname.length === 1) {
        //     $.each(uiClassname, function(index, element) {
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
    static getInstance() {
        return modelSolutionFileRefSingleton;
    }
}
let modelSolutionFileRefSingleton = new ModelSolutionFileReference();



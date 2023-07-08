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
 * Classes for dealing with files
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm, Dr.U.Priss
 */

import $ from 'jquery';
import {setcounter, DEBUG_MODE, getExtension} from "./helper";
import * as taskeditorconfig from "./config";
import {javaParser} from "./java";
import {FileReferenceList} from "./filereflist";
import Templates from 'core/templates';
import * as CodeMirror from '../codemirror';
import Notification, {exception as displayException} from 'core/notification';
import '../clike';
import '../python';
import '../xml';
import * as Str from 'core/str';

export var fileStorages = [];
export var fileIDs = {};
export var codemirror = {};



// todo: merge with FileWrapper
export class FileStorage {

    constructor(isBinary, mimetype, content, filename) {
        this.isBinary = isBinary;
        this.mimetype = mimetype;
        this.content = content;
        this.originalFilename = this.filename = filename;
        this.storeAsFile = isBinary;
        this.byZipper=false;
    }

    setSize(size) {
        this.size = size;
    }

    setZipperFlag() {
        this.byZipper=true;
    }
}


// class for simpler access to file members from user interface
// todo: store 'data' in variables not in html
// => store data in html only in setter
export class FileWrapper {

    static uploadFileWhenDropped(files, fileBox){
        if (files.length > 1) {
            alert('You have dragged more than one file. You must drop exactly one file!');
            return;
        }
        const fileId= $(fileBox).find(".xml_file_id").val();
        readAndCreateFileData(files[0], fileId);
    }

    static constructFromId(id) {
        // this._id = id;
        let file = new FileWrapper();
        file._root = $("#file_" + id);
        //file._root = $(".xml_file_id[value='" + id + "']").closest(".xml_file");
        if (file.root.length === 0)
            return undefined; // no element with id found
        return file;
    }

    static constructFromRoot(root) {
        let file = new FileWrapper();
        file._root = root;
        return file;
    }

    static constructFromFilename(filename) {
        let file = new FileWrapper();
        $.each($(".xml_file_filename"), function(index, item) {
            if (filename === item.value ) {
                file._root = $(item).first().parent();
            }
        });
        if (!file._root) {
            console.error('FileWrapper.constructFromFilename cannot find root for filename ' + filename);
            return undefined;
        }
        return file;
    }

    getValue(member, xmlClass) {
        if (!member) {
            member = this.root.find(xmlClass).first();
        }
        return member.val();

    }

    // getter
    get root() { return this._root; }
    get id() { return this.getValue(this._id,".xml_file_id" ); }
    get filename() { return this.getValue(this._filename,".xml_file_filename" ); }
//    get class() { return this.getValue(this._class,".xml_file_class" ); }
    get type() { return this.getValue(this._type,".xml_file_type" ); }
    get comment() { return this.getValue(this._comment,".xml_internal_description" ); }
    get mimetype() { return fileStorages[this.id].mimetype; }
    get isBinary() { return fileStorages[this.id].isBinary; }
    get storeAsFile() { return fileStorages[this.id].storeAsFile; }
    get content() { return fileStorages[this.id].content; }
    get size() {
        if (this.isBinary)
            return fileStorages[this.id].size;
        else {
            return this.text.length;
        }
    }
    get originalFilename() { return fileStorages[this.id].originalFilename; }
//    get isLibrary() { return this.root.find(".file_library")[0].checked;}


    get text() {
        if (taskeditorconfig.useCodemirror) {
            return codemirror[this.id].getValue();
        } else {
            return this._root.find(".xml_file_text").val();
        }
    }

    // setter
    set text(newText) {
        if (taskeditorconfig.useCodemirror) {
            codemirror[this.id].setValue(newText);
            const fileObject = fileStorages[this.id];
//            console.log('Codemirror modes and mimetypes');
//            console.log(CodeMirror.modes);
//            console.log(CodeMirror.mimeModes);

//            console.log('Set CodeMirror-Mode: mimetype ' + fileObject.mimetype + ' => ' + this.getCodemirrorMode());
            codemirror[this.id].setOption("mode", this.getCodemirrorMode());
//            let editor = codemirror[this.id];
//             editor.refresh();
        } else {
            this._root.find(".xml_file_text").val(newText);
        }
    }

    set filename(name) {
        if (!this._filename) {
            this._filename = this.root.find(".xml_file_filename").first();
        }
        this._filename.val(name);
        this.filenameHeader = name;
        // this._root.find(".xml_filename_header").first().text(name);
        fileStorages[this.id].filename = name;
        if (taskeditorconfig.useCodemirror) {
            // Change mode depending on filename
            console.log('change mode depending on new filename');
            codemirror[this.id].setOption("mode", this.getCodemirrorMode());
        }
        // FileWrapper.onFilenameChanged(); // this); // TODO check for endless recursion!!
        // update filenames in all file references
        FileReferenceList.updateAllFilenameLists(this.id, name);
    }

    set filenameHeader(name) {
        // this._root.find(".xml_filename_header").first().text(name);
    }

/*    set class(newClass) {
        this._root.find(".xml_file_class").val(newClass);
    }
*/

    set comment(newComment) {
        this._root.find(".xml_internal_description").val(newComment);
    }

    set type(newType) {
        const oldType = this.type;
        if (!this._type) {
            this._type = this.root.find(".xml_file_type").first();
        }

        this._type.val(newType);
        if (this.isBinary) {
            // type is set for the first type, then
            this._type.attr('disabled', newType === 'file');
        }

        switch (newType) {
            case 'file':
                this.root.find(".xml_file_binary").show(); // show binary text
                this.root.find(".xml_file_non_binary").hide(); // hide editor
                let xml_file_size = this.root.find(".xml_file_size");
                let filesize = this.size;
                if (filesize)
                    filesize = filesize.toLocaleString();
                else
                    filesize = '???';

                xml_file_size.first().text('File size: ' + filesize + ", " +
                    'File type: ' + this.mimetype);
//                xml_file_size.first().text('File size: ' + this.size.toLocaleString() + ", " +
//                    'File type: ' + this.mimetype);
                break;
            case 'embedded':
                this.root.find(".xml_file_binary").hide(); // hide binary text
                this.root.find(".xml_file_non_binary").show(); // show editor
                break;
        }
    }

    set content(binaryFileContent) { fileStorages[this.id].content = binaryFileContent; }
    set mimetype(mimetype) { fileStorages[this.id].mimetype = mimetype; }
    set storeAsFile(storeAsFile) { fileStorages[this.id].storeAsFile = storeAsFile; }
    set originalFilename(filename) { fileStorages[this.id].originalFilename = filename; }
    set size(size) { fileStorages[this.id].size = size; }
    set isBinary(isBinary) {
        fileStorages[this.id].isBinary = isBinary;
        if (isBinary) console.log('set binary=true for ' + this.filename);
    }
//    set isLibrary(isLib) {this.root.find(".file_library")[0].checked = isLib;}

/*    disableTypeChange() {
        if (!this._type) {
            this._type = this.root.find(".xml_file_type").first();
        }
        this._type.attr('disabled', true);
    }
*/
    // other functions
    delete() {
        this.root.remove();
        delete fileIDs[this.id];
        FileReferenceList.updateAllFilenameLists(this.id);
    }

    getCodemirrorMode() {
        switch(getExtension(this.filename)) {
            case "java":   return "text/x-java";
            case "py": return "text/x-python";
            case "setlx":  return "text/text";
            case "c":      return "text/x-csrc";
            case "h":      return "text/x-csrc";
            case "cpp":    return "text/x-c++src";
            case "hpp":    return "text/x-c++src";
            case "xml":    return "application/xml";
            case "html":    return "text/html";
            case "sql":    return "text/x-sql";
            case "php":    return "text/x-php";
            case "js":     return "text/javascript";
            case "kt":     return "text/java";
            case "css":     return "text/css";
        }
        return "";
    }

    static onFilenameChanged(ui_file) {
        let changedId;
        // after change of filename update all filelists
        // console.log('onFilenameChanged => ' + ui_file.filename);

        function updateFilename() {
            // console.log('update internal settings ' + ui_file.filename);
            ui_file.filename = ui_file.filename; // needed in order to update internal data
            ui_file.filenameHeader = ui_file.filename;
            changedId = ui_file.id;
            if (taskeditorconfig.useCodemirror) {
                // Change mode depending on filename
                // console.log('change mode depending on new filename');
                codemirror[ui_file.id].setOption("mode", ui_file.getCodemirrorMode());
            }
        }

        if (ui_file) {
            // (the user has changed the filename in the filename input field)
            //ui_file.filenameHeader = ui_file.filename;

            // if the user has changed the filename and the extension is .java
            // then the filename is recalculated on base of the source code (package class)
            // and checked against user filename
            if (getExtension(ui_file.filename) === 'java') {
                // let filebox = $(textbox).closest(".xml_file");
                let text = ui_file.text; // "";
                const actualFilename = ui_file.filename;
                const expectedFilename = javaParser.getFilenameWithPackage(text, actualFilename);
                if (expectedFilename !== actualFilename && expectedFilename !== ".java") {
                    // Ask user if filename shall really be changed
                    return Str.get_string('changejavafilename', 'qtype_proforma', expectedFilename)
                        .then(localtext => {
                            if (confirm(localtext)) {
                                ui_file.filename = expectedFilename;
                            }
                            updateFilename();
                            // update filenames in all file references
                            FileReferenceList.updateAllFilenameLists(changedId, ui_file.filename);
                        });
                }
            }
            updateFilename();
        }
        // update filenames in all file references
        FileReferenceList.updateAllFilenameLists(changedId, ui_file.filename);
    };


    static doesFilenameExist(filename) {
        let found = false;
        $.each($(".xml_file_filename"), function(index, item) {
            if (item.value === filename) {
                found = true;
                return false;
            }
        });

        return found;
    }

/*
    static onReadFile(inputbutton) {             // read a file and its filename into the HTML form
        let filenew = inputbutton.files[0];
        const fileId = $(inputbutton).closest('.xml_file').find(".xml_file_id").val();
        readAndCreateFileData(filenew, fileId);
    }
*/

    /*
    static onReadAndCreateFile(inputbutton) {             // read a file and create a new file item
        let filenew = inputbutton.files[0];
        let ui_file = FileWrapper.create();
        readAndCreateFileData(filenew, ui_file.id);
    }*/


    static removeFile(button) {                                       // ask before removing
        // console.log('remove file');
        // let root = button.parent().parent().parent(); // arrgh!
        let ui_file = FileWrapper.constructFromRoot(button.closest('.xml_file')/*root*/);

        let ok = false;
        const filedata = {
            'id': ui_file.id,
            'filename': ui_file.filename
        };
        if (FileReferenceList.getCountFileIdReferenced(ui_file.id)) {
            // if true: cancel or remove all filenames/filerefs from model solution and test
            Str.get_string('confirmdeletefile1', 'qtype_proforma', filedata)
                .then(localtext => {
                    if (window.confirm(localtext)){
                        ui_file.delete();
                    }
                });
        } else {
            Str.get_string('confirmdeletefile2', 'qtype_proforma', filedata)
                .then(localtext => {
                    if (window.confirm(localtext)){
                        ui_file.delete();
                    }
                });
        }
    };



    static showHideEditor(button, ui_file_no_button, show) {
        let ui_file = undefined;
        if (ui_file_no_button)
            ui_file = ui_file_no_button;
        else
            ui_file = FileWrapper.constructFromRoot(button.closest('.xml_file'));

        if (taskeditorconfig.useCodemirror) {
            let editor = codemirror[ui_file.id];
            if (show)
                $(editor.getWrapperElement()).show();
            else
                $(editor.getWrapperElement()).hide();
            editor.refresh();
        } else {
            if (show)
                ui_file.root.find('.xml_file_text').show();
            else
                ui_file.root.find('.xml_file_text').hide();
        }

        if (show) {
            ui_file.root.find('.xml_file_editor_close').show();
            ui_file.root.find('.xml_file_edit').hide();
        } else {
            ui_file.root.find('.xml_file_editor_close').hide();
            ui_file.root.find('.xml_file_edit').show();
        }
    }

    static showEditor(button, ui_file_no_button) {
        FileWrapper.showHideEditor(button, ui_file_no_button, true);
    };

    static hideEditor(button, ui_file_no_button) {
        FileWrapper.showHideEditor(button, ui_file_no_button, false);
    };


    static doOnAllFiles(callback) {
        // todo: iterate through all files in variable
        $.each($(".xml_file_id"), function (indexOpt, item) {
            // console.log('.xml_file_id');
            // console.log(item);
            let uifile = FileWrapper.constructFromId(item.value);
            callback(uifile);
        });
    }

/*
    static onFileclassChanged(selectfield) {
        const text = $("option:selected", selectfield).text(); // selected text
        alert('do not change if old value is template, instruction or library!!!');
    }
*/

    static onFilenameChangedCallback(filenamebox) {
        let ui_file = FileWrapper.constructFromRoot($(filenamebox).closest(".xml_file"));
        FileWrapper.onFilenameChanged(ui_file);
    }

    static onFiletypeChanged(selectfield) {
        // after change of filetype change binary

        if (selectfield) {

            // if the user has changed the filename and the extension is .java
            // then the filename is recalculated on base of the source code (package class)
            // and checked against user filename
            //let filetype = $(selectfield).val();
            //let fileroot = $(selectfield).closest(".xml_file");

            let ui_file = FileWrapper.constructFromRoot($(selectfield).closest(".xml_file"));
            const newtype = ui_file.type;
            switch (newtype) { // filetype ) {
                case 'file':
                    const fileId = ui_file.id;
                    const filename = ui_file.filename;
                    const text = ui_file.text;

                    if (!("TextEncoder" in window))
                        alert("Sorry, this browser does not support TextEncoder...");
                    let enc = new TextEncoder("utf-8");

                    // change filestore attributes
                    let fileobject = fileStorages[fileId];
                    if (!fileobject) {
                        // create fileobject
                        fileobject = new FileStorage(true, '', '', filename);
                        fileStorages[fileId] = fileobject;
                    }
                    if (getExtension(fileobject.filename) !== getExtension(filename)) {
                        fileobject.mimetype = ''; // delete mimetype if filename has changed
                        fileobject.filename = filename;
                    }
                    fileobject.storeAsFile = true;
                    fileobject.content =  enc.encode(text);
                    fileobject.setSize(text.length);
                    // showBinaryFile(file.root /*fileroot*/, fileobject);
                    break;
                case 'embedded':
                    // showTextFile(file.root);
                    break;
            }
            // force default handling for new file type
            // (ok, that's not so pretty...)
            ui_file.type = newtype;
        }
    };


///////////////////////////////////////////////////////// utility functions
    /* Codemirror is a library that provides more sophisticated editor support for textareas.
     * Once it is turned on for a textarea, this textarea can no longer be accessed
     * using normal DOM methods. Instead it must be accessed using codemirror methods.
     * Currently codemirror is only used for xml_file_text.
     * The global codemirror hash above uses the fileID to identify the codemirror element.
     */
    static addCodemirrorElement(cmID, langmode = "text/x-java") {                     // cmID is determined by setcounter(), starts at 1
        // let textareaElem = FileWrapper.constructFromId(cmID).root.find(".xml_file_text")[0];
        // console.log(textareaElem);
        codemirror[cmID] = CodeMirror.fromTextArea(
//            textareaElem, {
            FileWrapper.constructFromId(cmID).root.find(".xml_file_text")[0],{
                // todo: set mode depending on programming language resp. file extension
                mode : langmode, indentUnit: 4, lineNumbers: true, matchBrackets: true, tabMode : "shift",
                styleActiveLine: true, /*viewportMargin: Infinity, */autoCloseBrackets: true,
//                theme: "eclipse", // Note: when theme is not found the language modes do not work!
                dragDrop: false
            });

        let editor = codemirror[cmID];
        $(editor.getWrapperElement()).resizable({
            handles: 's', // only resize in north-south-direction
            resize: function() {
                editor.refresh();
            }
        });
        editor.on("drop",function(editor,e){
            //uploadFileWhenDropped(e.originalEvent.dataTransfer.files, e.currentTarget);
            console.log('codemirror drop: ' + e);
        });
    }

    static createFromTemplate(id) {
        let fileid = id;
        if (!fileid) {
            fileid = setcounter(fileIDs);    // adding a file for the test
        } else {
            // this means that it is created with a known id
            // (from reading task.xml). So we need to keep the fileIDs in sync!
            fileIDs[fileid] = 1;
        }
        let context = {
            'fileid': fileid,
            'filesize': '???'
        };

        return Templates.renderForPromise('qtype_proforma/taskeditor_file', context)
            .then(({html, js}) => {
                Templates.appendNodeContents('#proforma-files-section', html, js);
                let ui_file = FileWrapper.constructFromId(fileid);
                if (fileStorages[fileid] === undefined) {
                    fileStorages[fileid] = new FileStorage(false, '', '', '');
                }

                // hide fields that exist only for technical reasons
                ui_file.root.find(".xml_file_binary").hide(); // hide binary text
                if (!DEBUG_MODE) {
                    ui_file.root.find(".xml_file_id").hide();
                    ui_file.root.find("label[for='xml_file_id']").hide();
                    ui_file.root.find(".xml_file_class").hide();
                    ui_file.root.find("label[for='xml_file_class']").hide();
                }
                // console.log('add file callbacks');
                // add callbacks:
                ui_file.root.find('button').first().on("click",
                    function (event) {
                        event.preventDefault();
                        FileWrapper.removeFile($(this));
                    });
                ui_file.root.find('.xml_file_filename').on("change",
                    function (event) {
                        event.preventDefault();
                        FileWrapper.onFilenameChangedCallback(this);
                    });
                ui_file.root.find('.xml_file_type').on("change",
                    function (event) {
                        event.preventDefault();
                        FileWrapper.onFiletypeChanged(this);
                    });
                ui_file.root.find('.xml_file_edit').on("click",
                    function (event) {
                        event.preventDefault();
                        FileWrapper.showEditor($(this));
                    });
                ui_file.root.find('.xml_file_editor_close').on("click",
                    function (event) {
                        event.preventDefault();
                        FileWrapper.hideEditor($(this));
                    });

                // enable drag & drop
                ui_file.root.on({
                    dragover: function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        //e.dataTransfer.dropEffect = 'copy';
                    },
                    dragenter: function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    },
                    drop: function (e) {
                        if (e.originalEvent.dataTransfer) {
                            if (e.originalEvent.dataTransfer.files.length) {
                                e.preventDefault();
                                e.stopPropagation();
                                /*UPLOAD FILES HERE*/
                                FileWrapper.uploadFileWhenDropped(e.originalEvent.dataTransfer.files, e.currentTarget);
                            }
                        }
                    }
                });


                FileWrapper.addCodemirrorElement(fileid, ui_file.getCodemirrorMode());

                FileWrapper.hideEditor(undefined, ui_file);
                return ui_file;
            })
            .catch((error) => {
                displayException(error);
            });
    }
}
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
 * Author:
 * Karin Borm (Dr. Uta Priss)
 */

import $ from 'jquery';
import {setcounter, DEBUG_MODE, getExtension} from "./taskeditorutil";
import {config} from "./taskeditorconfig";
import {FileReferenceList} from "./filereflist";
import Templates from 'core/templates';
import Notification, {exception as displayException} from 'core/notification';

export var fileStorages = [];
export var fileIDs = {};
export var codemirror = {};


/**
 * TextEncoder is supported by Chrome and Firefox. For use in other browser the following
 * code is copied from https://developer.mozilla.org/en-US/docs/Web/API/TextEncoder
 */
if (typeof TextEncoder === "undefined") {
    TextEncoder=function TextEncoder(){};
    TextEncoder.prototype.encode = function encode(str) {
        //alert('TextEncode by function');
        "use strict";
        var Len = str.length, resPos = -1;
        var resArr = typeof Uint8Array === "undefined" ? new Array(Len * 2) : new Uint8Array(Len * 3);
        for (var point=0, nextcode=0, i = 0; i !== Len; ) {
            point = str.charCodeAt(i), i += 1;
            if (point >= 0xD800 && point <= 0xDBFF) {
                if (i === Len) {
                    resArr[resPos += 1] = 0xef/*0b11101111*/; resArr[resPos += 1] = 0xbf/*0b10111111*/;
                    resArr[resPos += 1] = 0xbd/*0b10111101*/; break;
                }
                // https://mathiasbynens.be/notes/javascript-encoding#surrogate-formulae
                nextcode = str.charCodeAt(i);
                if (nextcode >= 0xDC00 && nextcode <= 0xDFFF) {
                    point = (point - 0xD800) * 0x400 + nextcode - 0xDC00 + 0x10000;
                    i += 1;
                    if (point > 0xffff) {
                        resArr[resPos += 1] = (0x1e/*0b11110*/<<3) | (point>>>18);
                        resArr[resPos += 1] = (0x2/*0b10*/<<6) | ((point>>>12)&0x3f/*0b00111111*/);
                        resArr[resPos += 1] = (0x2/*0b10*/<<6) | ((point>>>6)&0x3f/*0b00111111*/);
                        resArr[resPos += 1] = (0x2/*0b10*/<<6) | (point&0x3f/*0b00111111*/);
                        continue;
                    }
                } else {
                    resArr[resPos += 1] = 0xef/*0b11101111*/; resArr[resPos += 1] = 0xbf/*0b10111111*/;
                    resArr[resPos += 1] = 0xbd/*0b10111101*/; continue;
                }
            }
            if (point <= 0x007f) {
                resArr[resPos += 1] = (0x0/*0b0*/<<7) | point;
            } else if (point <= 0x07ff) {
                resArr[resPos += 1] = (0x6/*0b110*/<<5) | (point>>>6);
                resArr[resPos += 1] = (0x2/*0b10*/<<6)  | (point&0x3f/*0b00111111*/);
            } else {
                resArr[resPos += 1] = (0xe/*0b1110*/<<4) | (point>>>12);
                resArr[resPos += 1] = (0x2/*0b10*/<<6)    | ((point>>>6)&0x3f/*0b00111111*/);
                resArr[resPos += 1] = (0x2/*0b10*/<<6)    | (point&0x3f/*0b00111111*/);
            }
        }
        if (typeof Uint8Array!=="undefined") return new Uint8Array(resArr.buffer.slice(0, resPos+1));
        else return resArr.length === resPos+1 ? resArr : resArr.slice(0, resPos+1); // IE 6-9
    };
    TextEncoder.prototype.toString = function(){return "[object TextEncoder]"};
    try { // Object.defineProperty only works on DOM prototypes in IE8
        Object.defineProperty(TextEncoder.prototype,"encoding",{
            get:function(){if(TextEncoder.prototype.isPrototypeOf(this)) return"utf-8";
            else throw TypeError("Illegal invocation");}
        });
    } catch(e) { /*IE6-8 fallback*/ TextEncoder.prototype.encoding = "utf-8"; }
    if(typeof Symbol!=="undefined")TextEncoder.prototype[Symbol.toStringTag]="TextEncoder";
}



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

/*
const INTERNAL     = 'internal';
const TEMPLATE     = 'template';
const INSTRUCTION  = 'instruction';
const INTERNAL_LIB = 'internal-library';
const LIBRARY      = 'library';
*/

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
        if (config.useCodemirror) {
            return codemirror[this.id].getValue();
        } else {
            return this._root.find(".xml_file_text").val();
        }
    }

    // setter
    set text(newText) {
        if (config.useCodemirror) {
            codemirror[this.id].setValue(newText);
            const fileObject = fileStorages[this.id];
            //alert('CodeMirror-Mode: ' + fileObject.mimetype);
            codemirror[this.id].setOption("mode", this.getCodemirrorMode());
        } else {
            this._root.find(".xml_file_text").val(newText);
        }
    }

    set filename(name) {
        if (!this._filename) {
            this._filename = this.root.find(".xml_file_filename").first();
        }
        this._filename.val(name);
        this._root.find(".xml_filename_header").first().text(name);
        fileStorages[this.id].filename = name;
        // FileWrapper.onFilenameChanged(); // this); // TODO check for endless recursion!!
        // update filenames in all file references
        FileReferenceList.updateAllFilenameLists(this.id, name);
    }

    set filenameHeader(name) {
        this._root.find(".xml_filename_header").first().text(name);
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
            case "python": return "text/x-python";
            case "setlx":  return "text/text";
            case "c":      return "text/x-csrc";
            case "cpp":    return "text/x-c++src";
            case "xml":    return "application/xml";
            case "html":    return "text/html";
            case "sql":    return "text/x-sql";
            case "js":    return "textjavascript";
        }
        return "";
    }

    static onFilenameChanged(ui_file) {
        console.log('onFilenameChanged');
        // after change of filename update all filelists

        let changedId;
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
                    if (confirm("Java filenames shall consist of the " +
                            "package name, if any, and the class name.\n" +
                            "So the expected filename is '" + expectedFilename + "'\n" +
                            "Do you want to change the filename to '" + expectedFilename + "'?")) {
                        ui_file.filename = expectedFilename;
                    }
                }
            }
            ui_file.filenameHeader = ui_file.filename;
            changedId = ui_file.id;
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
    static onReadAndCreateFile(inputbutton) {             // read a file and create a new file item
        let filenew = inputbutton.files[0];
        let ui_file = FileWrapper.create();
        readAndCreateFileData(filenew, ui_file.id);
    }


    static removeFile(button) {                                       // ask before removing
        console.log('remove file');
        // let root = button.parent().parent().parent(); // arrgh!
        let ui_file = FileWrapper.constructFromRoot(button.closest('.xml_file')/*root*/);

        let ok = false;
        if (FileReferenceList.getCountFileIdReferenced(ui_file.id)) {
            // if true: cancel or remove all filenames/filerefs from model solution and test
            ok = window.confirm("File " + ui_file.id + " '" + ui_file.filename + "' is still referenced!\n" +
                "Do you really want to delete it?");
        } else {
            ok = window.confirm("Do you really want to delete file\n'" + ui_file.filename + "'?");
        }
        if (ok) {
            ui_file.delete();
        }
    };


    static showEditor(button, ui_file_no_button) {
        let ui_file = undefined;
        if (ui_file_no_button)
            ui_file = ui_file_no_button;
        else
            ui_file = FileWrapper.constructFromRoot(button.closest('.xml_file'));

        if (config.useCodemirror) {
            let editor = codemirror[ui_file.id];
            $(editor.getWrapperElement()).show();
        } else {
            ui_file.root.find('.xml_file_text').show();
        }


        ui_file.root.find('.xml_file_editor_close').show();
        ui_file.root.find('.xml_file_edit').hide();
    };

    static hideEditor(button, ui_file_no_button) {
        let ui_file = undefined;
        if (ui_file_no_button)
            ui_file = ui_file_no_button;
        else
            ui_file = FileWrapper.constructFromRoot(button.closest('.xml_file'));

        if (config.useCodemirror) {
            const id = ui_file.id;
            let editor = codemirror[ui_file.id];
            $(editor.getWrapperElement()).hide();
        } else {
            ui_file.root.find('.xml_file_text').hide();
        }

        ui_file.root.find('.xml_file_editor_close').hide();
        ui_file.root.find('.xml_file_edit').show();
    };


    static doOnAllFiles(callback) {
        // todo: iterate through all files in variable
        $.each($(".xml_file_id"), function (indexOpt, item) {
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
    static addCodemirrorElement(cmID) {                     // cmID is determined by setcounter(), starts at 1
        console.error('TODO addCodemirrorElement');
        return;
        codemirror[cmID] = CodeMirror.fromTextArea(
//            $(".xml_file_id[value='"+ cmID +"']").parent().parent().find(".xml_file_text")[0],{
            FileWrapper.constructFromId(cmID).root.find(".xml_file_text")[0],{
                // todo: set mode depending on programming language resp. file extension
                mode : "text/x-java", indentUnit: 4, lineNumbers: true, matchBrackets: true, tabMode : "shift",
                styleActiveLine: true, /*viewportMargin: Infinity, */autoCloseBrackets: true, theme: "eclipse",
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

    // TODO: Problem: Hier wird das html async erzeugt!
    static createFromTemplate(id) {
        let fileid = id;
        if (!fileid) {
            fileid = setcounter(fileIDs);    // adding a file for the test
        } else {
            // this means that it is created with a known id
            // (from reading task.xml). So we nned to keep the fileIDs in sync!
            fileIDs[fileid] = 1;
        }
        let context = {
            'fileid': fileid
        };

        Templates.renderForPromise('qtype_proforma/taskeditor_file', context)
            .then(({html, js}) => {
                Templates.appendNodeContents('#proforma-files-section', html, js);
                let ui_file = FileWrapper.constructFromId(fileid);
                fileStorages[fileid] = new FileStorage(false, '', '', '');

                // hide fields that exist only for technical reasons
                ui_file.root.find(".xml_file_binary").hide(); // hide binary text
                if (!DEBUG_MODE) {
                    ui_file.root.find(".xml_file_id").hide();
                    ui_file.root.find("label[for='xml_file_id']").hide();
                    ui_file.root.find(".xml_file_class").hide();
                    ui_file.root.find("label[for='xml_file_class']").hide();
                }
                console.log('add file callbacks');
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


                FileWrapper.addCodemirrorElement(fileid);

                FileWrapper.hideEditor(undefined, ui_file);
                return ui_file;
            })
            .catch((error) => {
                displayException(error);
            });
    }


    static create(id) {
        let fileid = id;
        if (!fileid) {
            fileid = setcounter(fileIDs);    // adding a file for the test
        } else {
            // this means that it is created with a known id
            // (from reading task.xml). So we nned to keep the fileIDs in sync!
            fileIDs[fileid] = 1;
        }

        $("#proforma-files-section").append("<div "+
            "id='file_" + fileid + "'" +
            "class='ui-widget ui-widget-content ui-corner-all xml_file drop_zone'>"+
            "<h3 class='ui-widget-header'><span class ='xml_filename_header'></span> (File #"+fileid+")<span "+
            "class='rightButton'><button" /* onclick='FileWrapper.removeFile($(this));'*/ +">x</button></span></h3>"+

            "<p>" +
            " <label for='xml_file_filename'>Filename<span class='red'>*</span>: </label>"+
            "<input class='mediuminput xml_file_filename' title='with extension'> </input>"+

            "<label for='xml_file_id'>ID: </label>"+ // do not set at first position because of layout (css)
            "<input class='tinyinput xml_file_id' value='"+fileid+"'></input>"+

            " <label for='xml_file_class'>Usage: " +
            // "<span class='red'>*</span>: " +
            "</label>"+

            " <label for='xml_file_type'>Store </label>"+
            "<select class='xml_file_type'" + /* onchange='FileWrapper.onFiletypeChanged(this)'*/ ">"+
                "<option value = 'embedded' selected='selected'>embedded</option>"+
                "<option value = 'file'>attached</option></select>"+
            "<span class='drop_zone_text'>Drop Your File Here!</span>" +
            "</p>"+

            "<p><label for='xml_internal_description'>Internal Description:</label>"+
            "<textarea rows='1' class='xml_internal_description'></textarea></p>"+

            "<p>" +
//            "<label>File content<span class='red'>*</span>: </label>"+
            "<span class='xml_file_binary'>(Binary file) " +
            "<span class='xml_file_size'>File size: ???</span>" +
            "</span>" +

            "<span class='xml_file_non_binary'>" +
            "<button class='xml_file_edit'" + /* onclick='FileWrapper.showEditor($(this));'*/ ">Edit</button>" +
            "<label for='xml_file_editor_close'>" +
            "<button class='xml_file_editor_close'" + /* onclick='FileWrapper.hideEditor($(this));'*/ ">Hide</button>" +
            "</label>" +
            "<span>" +
            "<textarea rows='3' cols='80' class='xml_file_text' onfocus='this.rows=10;' onmouseout='this.rows=6;'></textarea>" +
            "</span>" +

            "</span></p>" +
            "</div>");

        // const fileroot = $("#file_" + fileid);
        //const fileroot = $(".xml_file_id[value='" + fileid + "']").closest(".xml_file");

        let ui_file = FileWrapper.constructFromId(fileid); // constructFromRoot(fileroot);
        fileStorages[fileid] = new FileStorage(false, '', '', '');

        // hide fields that exist only for technical reasons
        ui_file.root.find(".xml_file_binary").hide(); // hide binary text
        if (!DEBUG_MODE) {
            ui_file.root.find(".xml_file_id").hide();
            ui_file.root.find("label[for='xml_file_id']").hide();
            ui_file.root.find(".xml_file_class").hide();
            ui_file.root.find("label[for='xml_file_class']").hide();
        }
/*
        ui_file.root.find(".xml_dummy_upload_file").hide();
        ui_file.root.find(".xml_dummy_upload_file").click(function(){
            ui_file.root.find(".xml_upload_file").click();
        });
*/
        console.log('add file callbacks');
        // add callbacks:
        ui_file.root.find('button').first().on("click",
            function(event) {
                event.preventDefault();
                FileWrapper.removeFile($(this));
        });
        ui_file.root.find('.xml_file_filename').on("change",
            function(event) {
                event.preventDefault();
                FileWrapper.onFilenameChangedCallback(this);
        });
        ui_file.root.find('.xml_file_type').on("change",
            function(event) {
                event.preventDefault();
                FileWrapper.onFiletypeChanged(this);
        });
        ui_file.root.find('.xml_file_edit').on("click",
            function(event) {
                event.preventDefault();
                FileWrapper.showEditor($(this));
        });
        ui_file.root.find('.xml_file_editor_close').on("click",
            function(event) {
                event.preventDefault();
                FileWrapper.hideEditor($(this));
        });



        // enable drag & drop
        ui_file.root.on({
            dragover: function(e) {
                e.preventDefault();
                e.stopPropagation();
                //e.dataTransfer.dropEffect = 'copy';
            },
            dragenter: function(e) {
                e.preventDefault();
                e.stopPropagation();
            },
            drop: function(e){
                if(e.originalEvent.dataTransfer){
                    if(e.originalEvent.dataTransfer.files.length) {
                        e.preventDefault();
                        e.stopPropagation();
                        /*UPLOAD FILES HERE*/
                        FileWrapper.uploadFileWhenDropped(e.originalEvent.dataTransfer.files, e.currentTarget);
                    }
                }
            }
        });


        FileWrapper.addCodemirrorElement(fileid);

        FileWrapper.hideEditor(undefined, ui_file);
        return ui_file;
    }

    static deleteAllFiles() {
        codemirror = {};
        fileStorages = []; // empty array
        fileIDs = {};

        $("#proforma-files-section")[0].textContent = "";                     // delete previous content
    }
}
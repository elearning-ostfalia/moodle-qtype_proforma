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
 * @author     eCULT-Team of Ostfalia University, K.Borm, Dr. U.Priss
 */

import * as zip from "../zip/zip";
import {FileStorage, FileWrapper, fileStorages } from "./file";
import {FileReferenceList } from "./filereflist";

zip.workerScriptsPath = "./js/";

const debug_unzip = false;

let unzippedFiles = {};
let taskfile_read = false;

/**
 * link files to fileStorages array
 *
 * This must be done after reading all files.
 * Unfortunately file reading is performed asynchrously. So it is not clear
 * in which order the files are read. Because of this the relinkFiles function
 * is called after every processing of a single file in order to guarantee
 * that all files are handled.
 **/
export function relinkFiles() {
    if (!taskfile_read)
        return; // wait and retry later
    // if (debug_unzip) console.log("relinkFiles ");

    // store not-embedded files in correct location in fileStorages array
    FileWrapper.doOnAllFiles(function(ui_file) {
        // if (debug_unzip) console.log("relink " + ui_file.filename + " type: " + ui_file.type);

        if (ui_file.type === 'file') {
            const fileid = ui_file.id; // fileroot.find(".xml_file_id").val();
            const filename = ui_file.filename; //$(item).val();
            if (unzippedFiles[filename] && !fileStorages[fileid].byZipper) {//!fileStorages[fileid].filename.length) {
                // note that there is always a fileStorage object whenever there is a ui file object!
                // file is not yet relinked => link to fileStorage
                fileStorages[fileid] = unzippedFiles[filename];
                unzippedFiles[filename] = undefined;
                // if (debug_unzip) console.log("relinkFiles " + filename + " -> " + fileid + " " + ui_file.type + " size: " + ui_file.size);
                ui_file.type = ui_file.type; // needed...
            } else {
                if (unzippedFiles[filename] && fileStorages[fileid].byZipper) { // fileStorages[fileid].filename.length) {
                    // consistency check
                    console.error("internal error: file is already relinked! filename " + filename + " -> " + fileid + " " + ui_file.type);
                    alert('internal error: file ' + filename + ' is already relinked!');
                } else {
                    /*if (!unzippedFiles[filename])
                        console.error("unzippedFiles[ " + filename + "] is missing");
                    if (fileStorages[fileid].filename.length)
                        console.error("fileStorages[ " + fileid + "] already mapped");
                    */
                }
            }
        }
    });

    // needed because files are read asynchronously
    FileReferenceList.updateAllEditorButtons();
}

/**
 * unzips the {task}.zip file
 * - store files temporarily in unzippedFiles
 * - when everything is read then iterate through all fileIds and
 *   move stored files to fileStorages
 *
 * @param blob: zip file object
 * @param location: where to put the 'task.xml'
 * @param readyCallback: callback for 'task.xml' file
 * @returns {string}
 */
export function unzipme(blob, readyCallback) {
    var unzipped_text = "???";
    // dictionary with files (name -> FileStorage)
    let filesRead = 0;
    let filesToBeRead = undefined;

    function onFilesRead(zipReader) {
        relinkFiles();
        //zipReader.close();
    }

    function unzipBlob(blob, callbackForTaskXml, callbackForFile) {
          try {
              const zipFileReader = new zip.BlobReader(blob);
              let zipReader = new zip.ZipReader(zipFileReader);
              console.log('unzip');
              zipReader.getEntries()
                  .then(entries => {
                      filesToBeRead = entries.length;
                      console.log('filesToBeRead ' + filesToBeRead);
                      entries.forEach(function(entry) {
                          // console.log(entry);
                          console.log('filename: ' + entry.filename);
                          if (entry.filename === 'task.xml') {
                              // console.log('unzip task.xml');
                              const taskXmlWriter = new zip.TextWriter();
                              entry.getData(taskXmlWriter)
                                  .then(xmlContent => {
                                        // if (debug_unzip) console.log('call callback For task.xml');
                                        callbackForTaskXml(xmlContent);
                                    });
                          } else {
                              // handle attached files'
                              // console.log('unzip attached file ' + entry.filename);
                              // store file
                              const blobWriter = new zip.BlobWriter();
                              entry.getData(blobWriter)
                                  .then(data => data.arrayBuffer())
                                  .then(data => {
                                      // console.log(data);
                                      // if (debug_unzip)
                                      //     console.log('call callbackForFile ' + entry.filename);
                                      callbackForFile(data, entry);
                                  });
                          }
                      });
                  })
                  .catch( error => {
                      console.error('error:', error);
                      alert(error);
                  });
              zipReader.close();

          } catch(e) {
              console.error(e);
          }
    }

    unzipBlob(blob,
        // callback for task.xml
        function (taskXmlContent) {
            unzipped_text = taskXmlContent;
            if (readyCallback) {
                // if (debug_unzip)
                //     console.log('call readyCallback');
                readyCallback(taskXmlContent);
            }
            // if (debug_unzip)
            //     console.log('set taskfile_read = true');
            taskfile_read = true;
            filesRead++;
            if (filesRead === filesToBeRead) {
                onFilesRead();
            }
        },
        // callback for attached files
        function (unzippedBlob, entry) {
            // console.log('attached file ' + entry.filename);
            // console.log(unzippedBlob);
            // read file header and derive mime type
            var arr = (new Uint8Array(unzippedBlob)).subarray(0, 4);
            var header = "";
            for(var i = 0; i < arr.length; i++) {
                let number = arr[i].toString(16);
                if (number.length === 1) {
                    number = '0' + number;
                }
                header += number;
            }

            let type = unzippedBlob.type; // "unknown"; // Or you can use the blob.type as fallback
            switch (header.toLowerCase()) {
                case '504b0304': type = 'application/zip'; break;
                case "25504446": type = 'application/pdf'; break;
                case "89504e47": type = "image/png"; break;
                case "47494638": type = "image/gif"; break;
                case "ffd8ffe0":
                case "ffd8ffe1":
                case "ffd8ffe2":
                case "ffd8ffe3":
                case "ffd8ffe8":
                    type = "image/jpeg";
                    break;
            }

            // if (debug_unzip) console.log(header + " => " + type);

            // store file
            unzippedFiles[entry.filename] =
                new FileStorage(true, type, unzippedBlob, entry.filename);
            unzippedFiles[entry.filename].setZipperFlag();
            unzippedFiles[entry.filename].setSize(entry.uncompressedSize);
            filesRead++
            // if (debug_unzip) console.log('filesRead value: ' + filesRead + ' filesToBeRead=' + filesToBeRead);
            if (filesRead === filesToBeRead) {
                onFilesRead();
            }
    });

    // return unzipped_text;
}


export function taskTitleToFilename() {

    function camelize(str) {
        // code from https://stackoverflow.com/questions/2970525/converting-any-string-into-camel-case
        return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function(letter, index) {
            return index === 0 ? letter.toLowerCase() : letter.toUpperCase();
        }).replace(/\s+/g, '');
    }
    let title = $("#id_name").val();
    title = camelize(title);
    // only allow characters, numbers, '-' and '_'
    title = title.replace(/[^a-z0-9_\-]/gi, "_");
    title = title + '.zip';
    return title;

    //return title.replace(/[^a-z0-9]/gi, "");
}

/**
 * create zip file
 */
export function zipme(TEXT_CONTENT, startdownload, maxsize) {
    // get task.xml content from user interface
    // var TEXT_CONTENT = taskXml; // $("#output").val();
    if (!TEXT_CONTENT || TEXT_CONTENT.length === 0) {
        console.error("zipme called with empty output");
        return;
    }

    // console.log(TEXT_CONTENT);
    const FILENAME = "task.xml";
    var blob;
    const zipname = taskTitleToFilename(); // zipname.replace(/[^a-z0-9]/gi, "");

    // iterate through all files:
    // - if file type is 'file' the file must be added to zip file
    // - if file is non binary it is stored in the editor!
    FileWrapper.doOnAllFiles(function(ui_file) {
    //$.each($(".xml_file_id"), function(index, item) {
        //const ui_file = FileWrapper.constructFromRoot($(item).closest(".xml_file"));
        // let fileroot = $(item).closest(".xml_file");
        // const fileId = fileroot.find(".xml_file_id").val();
        if (!ui_file.type === 'embedded') {
            // copy editor content to file storage
            ui_file.storeAsFile = true; // fileStorages[ui_file.id].storeAsFile = true;
            if (!ui_file.isBinary) {
                // copy content from editor if file is non binary
                ui_file.content = ui_file.text;
                // fileStorages[ui_file.id].content = ui_file.text;
            }
        }
    });

    async function zipBlob(blob) {
        const zipFileWriter = new zip.BlobWriter("application/zip");
        const zipWriter = new zip.ZipWriter(zipFileWriter);

        console.log('fileStorages.length is ' + fileStorages.length);
        let f = 0;
        while (f < fileStorages.length) {
            const ui_file = FileWrapper.constructFromId(f);
            if (ui_file && ui_file.storeAsFile) {
                let fblob = new Blob([ui_file.content], {type: ui_file.mimetype});
                console.log('add ' + ui_file.filename + ' to zip file');
                await zipWriter.add(ui_file.filename, new zip.BlobReader(fblob));
            }
            f++;
        }
        console.log('add ' + FILENAME + ' to zip file');
        await zipWriter.add(FILENAME, new zip.BlobReader(blob));
        await zipWriter.close();
        return await zipFileWriter.getData();
    }

    blob = new Blob([ TEXT_CONTENT ], {
        type : zip.getMimeType(FILENAME)
    });
    return zipBlob(blob)
        .then(zippedBlob => {
            if (startdownload) {
                const url = window.URL.createObjectURL(zippedBlob);
                let a = document.createElement("a");
                document.body.appendChild(a);
                a.style = "display: none";
                a.download = zipname;
                a.href = url;
                a.click();
            } else {
                if (maxsize > 0 && zippedBlob.size > maxsize) {
                    return Promise.reject('Task size is ' + zippedBlob.size + ' and exceeds maximum size (' + maxsize + ' bytes).\n' +
                        'Uploading to the Moodle server is blocked!');
                } else {
                    return zippedBlob;
                }
            }
    });
}
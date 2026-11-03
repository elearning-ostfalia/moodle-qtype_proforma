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
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm, Dr. U.Priss
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

import {debugLog} from '../debug';
import $ from 'jquery';
import * as zip from "../zip/zip";
import {FileStorage, FileWrapper, fileStorages} from "./file";
import {FileReferenceList} from "./filereflist";
import {alert as displayAlert, exception as displayException} from 'core/notification';


let unzippedFiles = {};
let taskfileRead = false;

/**
 * Link files to fileStorages array
 *
 * This must be done after reading all files.
 * Unfortunately file reading is performed asynchrously. So it is not clear
 * in which order the files are read. Because of this the relinkFiles function
 * is called after every processing of a single file in order to guarantee
 * that all files are handled.
 **/
export function relinkFiles() {
    if (!taskfileRead) {
        return;
    } // Wait and retry later

    // store not-embedded files in correct location in fileStorages array
    FileWrapper.doOnAllFiles(function(uiFile) {

        if (uiFile.type === 'file') {
            const fileid = uiFile.id; // Fileroot.find(".xml_file_id").val();
            const filename = uiFile.filename; // $(item).val();
            if (unzippedFiles[filename] && !fileStorages[fileid].byZipper) { // !fileStorages[fileid].filename.length) {
                // note that there is always a fileStorage object whenever there is a ui file object!
                // file is not yet relinked => link to fileStorage
                fileStorages[fileid] = unzippedFiles[filename];
                unzippedFiles[filename] = undefined;
                // eslint-disable-next-line no-self-assign
                uiFile.type = uiFile.type; // Needed...
            } else {
                if (unzippedFiles[filename] && fileStorages[fileid].byZipper) { // FileStorages[fileid].filename.length) {
                    // consistency check
                    console.error("internal error: file is already relinked! filename " + filename + " -> " +
                        fileid + " " + uiFile.type);
                    displayAlert('Internal error', 'File ' + filename + ' is already relinked!');
                } else {
                    /* If (!unzippedFiles[filename])
                        console.error("unzippedFiles[ " + filename + "] is missing");
                    if (fileStorages[fileid].filename.length)
                        console.error("fileStorages[ " + fileid + "] already mapped");
                    */
                }
            }
        }
    });

    // Needed because files are read asynchronously
    FileReferenceList.updateAllEditorButtons();
}

/**
 * Unzips the {task}.zip file
 * - store files temporarily in unzippedFiles
 * - when everything is read then iterate through all fileIds and
 *   move stored files to fileStorages
 *
 * @param {Blob} blob zip file object
 * @param {Function} readyCallback callback for 'task.xml' file
 */
export function unzipme(blob, readyCallback) {
    // Dictionary with files (name -> FileStorage)
    let filesRead = 0;
    let filesToBeRead;

    /**
     * Called when all files are read: moves the files to the file storages.
     */
    function onFilesRead() {
        relinkFiles();
        // ZipReader.close();
    }

    /**
     * Unzips the blob and calls the callbacks for task.xml and for each other file.
     *
     * @param {Blob} blob zip file
     * @param {Function} callbackForTaskXml called with the content of task.xml
     * @param {Function} callbackForFile called with content and zip entry of each other file
     */
    function unzipBlob(blob, callbackForTaskXml, callbackForFile) {
        /**
         * Reads a single zip entry and calls the matching callback.
         *
         * @param {Object} entry zip entry
         * @returns {Promise}
         */
        function readEntry(entry) {
            debugLog('filename: ' + entry.filename);
            if (entry.filename === 'task.xml') {
                return entry.getData(new zip.TextWriter())
                    .then(xmlContent => {
                        callbackForTaskXml(xmlContent);
                        return true;
                    });
            }
            // Handle attached files.
            return entry.getData(new zip.BlobWriter())
                .then(data => data.arrayBuffer())
                .then(data => {
                    callbackForFile(data, entry);
                    return true;
                });
        }

        try {
            const zipFileReader = new zip.BlobReader(blob);
            let zipReader = new zip.ZipReader(zipFileReader);
            debugLog('unzip');
            zipReader.getEntries()
                .then(entries => {
                    filesToBeRead = entries.length;
                    debugLog('filesToBeRead ' + filesToBeRead);
                    return Promise.all(entries.map(readEntry));
                })
                .catch(error => {
                    console.error('error:', error);
                    displayException(error);
                })
                .finally(() => zipReader.close());
        } catch (e) {
            console.error(e);
        }
    }

    unzipBlob(blob,
        // Callback for task.xml
        function(taskXmlContent) {
            if (readyCallback) {
                readyCallback(taskXmlContent);
            }
            taskfileRead = true;
            filesRead++;
            if (filesRead === filesToBeRead) {
                onFilesRead();
            }
        },
        // Callback for attached files
        function(unzippedBlob, entry) {
            // Read file header and derive mime type
            var arr = (new Uint8Array(unzippedBlob)).subarray(0, 4);
            var header = "";
            for (var i = 0; i < arr.length; i++) {
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


            // Store file
            unzippedFiles[entry.filename] =
                new FileStorage(true, type, unzippedBlob, entry.filename);
            unzippedFiles[entry.filename].setZipperFlag();
            unzippedFiles[entry.filename].setSize(entry.uncompressedSize);
            filesRead++;
            if (filesRead === filesToBeRead) {
                onFilesRead();
            }
    });

    // Return unzippedText;
}


/**
 * Derives the zip filename from the question title.
 *
 * @returns {string} filename
 */
export function taskTitleToFilename() {

    /**
     * Converts a string to camel case.
     *
     * @param {string} str
     * @returns {string} camel case string
     */
    function camelize(str) {
        // Code from https://stackoverflow.com/questions/2970525/converting-any-string-into-camel-case
        return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function(letter, index) {
            return index === 0 ? letter.toLowerCase() : letter.toUpperCase();
        }).replace(/\s+/g, '');
    }
    let title = $("#id_name").val();
    title = camelize(title);
    // Only allow characters, numbers, '-' and '_'
    title = title.replace(/[^a-z0-9_-]/gi, "_");
    title = title + '.zip';
    return title;

    // Return title.replace(/[^a-z0-9]/gi, "");
}

/**
 * Create zip file
 *
 * @param {string} TEXT_CONTENT content of task.xml
 * @param {boolean} startdownload true if the zip file shall be downloaded
 * @param {number} maxsize maximum size of the zip file in bytes (0: no limit)
 * @returns {Promise<Blob|undefined>} zipped task (undefined if there is no content)
 */
export function zipme(TEXT_CONTENT, startdownload, maxsize) {
    // Get task.xml content from user interface
    // var TEXT_CONTENT = taskXml; // $("#output").val();
    if (!TEXT_CONTENT || TEXT_CONTENT.length === 0) {
        console.error("zipme called with empty output");
        return Promise.resolve();
    }

    const FILENAME = "task.xml";
    var blob;
    const zipname = taskTitleToFilename(); // Zipname.replace(/[^a-z0-9]/gi, "");

    // iterate through all files:
    // - if file type is 'file' the file must be added to zip file
    // - if file is non binary it is stored in the editor!
    FileWrapper.doOnAllFiles(function(uiFile) {
    // $.each($(".xml_file_id"), function(index, item) {
        // const uiFile = FileWrapper.constructFromRoot($(item).closest(".xml_file"));
        // let fileroot = $(item).closest(".xml_file");
        // const fileId = fileroot.find(".xml_file_id").val();
        if (!uiFile.type === 'embedded') {
            // Copy editor content to file storage
            uiFile.storeAsFile = true; // FileStorages[uiFile.id].storeAsFile = true;
            if (!uiFile.isBinary) {
                // Copy content from editor if file is non binary
                uiFile.content = uiFile.text;
                // FileStorages[uiFile.id].content = uiFile.text;
            }
        }
    });

    /**
     * Creates the zip file with task.xml and all attached files.
     *
     * @param {Blob} blob task.xml content
     * @returns {Promise<Blob>} zip file
     */
    async function zipBlob(blob) {
        const zipFileWriter = new zip.BlobWriter("application/zip");
        const zipWriter = new zip.ZipWriter(zipFileWriter);

        debugLog('fileStorages.length is ' + fileStorages.length);
        let f = 0;
        while (f < fileStorages.length) {
            const uiFile = FileWrapper.constructFromId(f);
            if (uiFile && uiFile.storeAsFile) {
                let fblob = new Blob([uiFile.content], {type: uiFile.mimetype});
                debugLog('add ' + uiFile.filename + ' to zip file');
                await zipWriter.add(uiFile.filename, new zip.BlobReader(fblob));
            }
            f++;
        }
        debugLog('add ' + FILENAME + ' to zip file');
        await zipWriter.add(FILENAME, new zip.BlobReader(blob));
        await zipWriter.close();
        return await zipFileWriter.getData();
    }

    blob = new Blob([TEXT_CONTENT], {
        type: zip.getMimeType(FILENAME)
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
            } else if (maxsize > 0 && zippedBlob.size > maxsize) {
                throw new Error('Task size is ' + zippedBlob.size + ' and exceeds maximum size (' +
                    maxsize + ' bytes).\n' +
                    'Uploading to the Moodle server is blocked!');
            }
            return zippedBlob;
        });
}
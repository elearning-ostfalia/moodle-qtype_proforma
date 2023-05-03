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
 * Karin Borm, Dr. Uta Priss
 */


const DEBUG_MODE       = false;
const TEST_MODE        = false;
const SUBMISSION_TEST  = false;
const USE_VISIBLES     = false;


const codeversion   = '3.1.2 [230313]';    // current version of this code


const version094    = 'xsd/taskxml0.9.4.xsd';                // name of schema files
const version101    = 'xsd/taskxml1.0.1.xsd';

import {config} from "./taskeditorconfig";

function getInternalDescriptionString(comment) {
    return "<p>" +
        "<label for='xml_internal_description'  class='leftlabel'>Internal Description:</label>" +
        "<textarea rows='2'  class='xml_internal_description' " +
        "title = 'Internal description (not visible for students)'>"+comment+"</textarea></p>";
}

function getDescriptionHtmlString(description, comment) {
    return "<p><label for='xml_description'>Description: </label>" +
    "<textarea rows='2'  class='xml_description' " +
    "title = 'Visible description'>"+description+"</textarea>" +
        //"<br>" +
        "</p>" +
        getInternalDescriptionString(comment);

/*        "<p>" +
        "<label for='xml_internal_description'>Internal Description:</label>" +
    "<textarea rows='2'  class='xml_internal_description' " +
    "title = 'Internal description (not visible)'>"+comment+"</textarea></p>";*/
}

// without . (MyString.Java = java)
// to lowercase
export function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
}

// convert to mimetype that can be directely handeled by codemirror
export function getMimeType(mimetype, filename) {
    const extension = getExtension(filename);
    switch (extension.toLowerCase()) {
        // case 'log':
        // case 'txt':
        case 'xml':  return 'application/xml';
        case 'html':  return 'text/html';
        default: return config.getMimetype(mimetype, extension);
    }
}

//////////////////////////////////////////////////////////////////////////////
/* Each newly exported task needs its own UUID.
 * This function generates and returns an UUID.
 */
export function generateUUID(){
    var date = new Date().getTime();
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c_value) {
        var rand = (date + Math.random()*16)%16 | 0;
        date = Math.floor(date/16);
        return (c_value == 'x' ? rand : (rand&0x3|0x8)).toString(16);
    });
    return uuid;
}


//////////////////////////////////////////////////////////////////////////////
/* setcounter and deletecounter are only used for fileIDs, modelSolIDs, testIDs
 * setcounter finds the first available ID and returns it
 * setcounter should be called when a new item is created
 * deletecounter deletes an ID from the hash, to be used when deleting an item
 */
export function setcounter(temphash) {
    let tempcnter = 1;
    while (temphash.hasOwnProperty(tempcnter)) {         // if the counter is already used, take next one
        tempcnter++;
    }
    temphash[tempcnter] = 1;
    return tempcnter;
}

//////////////////////////////////////////////////////////////////////////////
// configuration support
//////////////////////////////////////////////////////////////////////////////

// classes

export class CustomTest {

    constructor(title, testType, extraFields, proglang) {
        this.title = title; // title in html output
        this.testType = testType; // test type in XML
        this.htmlExtraFields = extraFields; // html extra input elements
        this.proglang = proglang; // array of programming languages that the test can be used with

        this.withFileRef = true; // default: with test script(s)

        this.gradingWeight = 1; // default weight

        this.fileRefLabel = 'File'; // default label
        this.manadatoryFile = true;
        this.alternativeTesttypes = [];

        // derived member variables
        const compactName = title.replace(/ /g, "");
        this.xmlTemplateName = compactName;
        this.buttonJQueryId = "add" + compactName;
    }

    // override
    onCreate(testId) {}
    onReadXml(test, xmlReader, testConfigNode, testroot) {}
    onWriteXml(test, uiElement, testConfigNode, xmlDoc, xmlWriter) {}
    getFramework() {return undefined;}
    getExtraHtmlField() { return this.htmlExtraFields; }
}


/**
 * information about programming language
 *
 * @param name
 * @param tests
 * @constructor
 */
class ProglangInfo{
    constructor(name, tests) {
        this.name  = name;
        this.tests = tests;
    }
}


// -------------------------------------------------------------


// helper function for custom test configuration
let createFileWithContent = function(filename, content) {
    let ui_file = FileWrapper.create();
    ui_file.filename = filename;
    ui_file.text = content;
    // onFilenameChanged(ui_file);
    return ui_file.id;
}

let addFileReferenceToTest = function(testId, filename) {
    let xml_test_root = $(".xml_test_id[value='"+testId+"']").parent().parent();
    let element = xml_test_root.find(".xml_test_filename").last();
    element.val(filename).change();
};

let getTestField = function(testId, fieldClass) {
    let xml_test_root = $(".xml_test_id[value='"+testId+"']").parent().parent();
    return xml_test_root.parent().find(fieldClass).first();
}

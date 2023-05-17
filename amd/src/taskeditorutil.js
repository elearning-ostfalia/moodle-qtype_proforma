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
 * Javascript editor for ProFormA tasks
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, Dr.U.Priss, K.Borm
 */



import {TestWrapper} from "./taskeditortest";

export const DEBUG_MODE       = false;
export const TEST_MODE        = false;
export const SUBMISSION_TEST  = false;
export const USE_VISIBLES     = false;


export const version094    = 'xsd/taskxml0.9.4.xsd';                // name of schema files
export const version101    = 'xsd/taskxml1.0.1.xsd';


export function setErrorMessage(errormess, exception) { // setting the error console
    console.log('setErrorMessage');
    console.log(errormess);
    console.log(exception);
    window.alert(errormess);
}

export function clearErrorMessage() {

}

// without . (MyString.Java = java)
// to lowercase
export function getExtension(filename) {
    return filename.split('.').pop().toLowerCase();
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

    constructor(title, testType, template, proglang) {
        this.defaultTitle = title;
        this.title = title; // title in html output
        this.testType = testType; // test type in XML
        if (template) {
            this.mustacheTemplate = template; // html extra input elements
        } else {
            this.mustacheTemplate = 'qtype_proforma/taskeditor_test';
        }
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
    onReadXml(test, xmlReader, testConfigNode, context) {}
    onWriteXml(test, uiElement, testConfigNode, xmlDoc, xmlWriter) {}
    getFramework() {return undefined;}
    getMustacheTemplate() { return this.mustacheTemplate; }

    getTemplateContext() {
        return {
            'testtitle' : this.title,
            'filenamelabel' : this.fileRefLabel,
            'testtype': this.testType,
            'testheader': this.defaultTitle,
            'filemandatory': this.manadatoryFile,
            'weight': this.gradingWeight
        };
    }
    createTestForm() {
        TestWrapper.createFromTemplate(null,
            this.mustacheTemplate, this.getTemplateContext(), this.withFileRef);
    }
}


/**
 * information about programming language
 *
 * @param name
 * @param tests
 * @constructor
 */
/*
class ProglangInfo{
    constructor(name, tests) {
        this.name  = name;
        this.tests = tests;
    }
}
*/

// -------------------------------------------------------------
/*

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
    let element = xml_test_root.find(".xml_fileref_filename").last();
    element.val(filename).change();
};

let getTestField = function(testId, fieldClass) {
    let xml_test_root = $(".xml_test_id[value='"+testId+"']").parent().parent();
    return xml_test_root.parent().find(fieldClass).first();
}
*/
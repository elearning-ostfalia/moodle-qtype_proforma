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



import {TestWrapper} from "./test";

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




let newUuid;

/**
 * generetae new UUID. Note that this function always returns the same UUID
 * whenever it is called later on.
 *
 * @returns {string|*}
 */
export function generateUUID(){
    if (newUuid !== undefined) {
        // console.log('newUuid is ' + newUuid + ' (do not change)');
        return newUuid;
    }
    let date = new Date().getTime();
    newUuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c_value) {
        let rand = (date + Math.random()*16)%16 | 0;
        date = Math.floor(date/16);
        return (c_value === 'x' ? rand : (rand&0x3|0x8)).toString(16);
    });
    console.log('newUuid is ' + newUuid);
    return newUuid;
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
        this.helptext = undefined; // help text for this test
        this.entrypointhelp = undefined;
        if (template) {
            this.mustacheTemplate = template; // html extra input elements
        } else {
            this.mustacheTemplate = 'qtype_proforma/taskeditor_test';
        }
        this.proglang = proglang; // array of programming languages that the test can be used with

        this.withFileRef = true; // default: with test script(s)

        this.gradingWeight = 1; // default weight

        // this.fileRefLabel = 'File'; // default label
        this.manadatoryFile = true;
        this.alternativeTesttypes = [];

        // derived member variables
        const compactName = title.replace(/ /g, "");
        this.xmlTemplateName = compactName;
        this.buttonJQueryId = "add" + compactName;
        this.frameworkRequired = false;
        this.framework = null;

    }

    matches(item, proglang) {
        if (this.testType !== item.testtype) {
            return false;
        }
        // Check if proglang is set in configured test. If true then compare
        if (this.proglang !== undefined) {
            if (!this.proglang.includes(proglang)) {
                // Language does not match
                return false;
            }
        }
        // Distinguish between CUnit and Google test with C++ by framework
        if (this.frameworks !== undefined && item.framework !== undefined) {
            if (!this.frameworks.includes(item.framework.toLowerCase())) {
                // console.log(item.framework + ' is not in ')
                // console.log(configItem.frameworks);
                // Framework does not match
                return false;
            }
        }
        return true;
    }
    // override
    onCreate(testId) {}
    onReadXml(test, xmlReader, testConfigNode, context) {}
    onWriteXml(test, testConfigNode, xmlDoc, xmlWriter, task) {}
    getFramework() {return undefined;}
    getMustacheTemplate() { return this.mustacheTemplate; }

    getTemplateContext() {
        let result = {
            'testtitle' : this.title,
            // 'filenamelabel' : this.fileRefLabel,
            'testtype': this.testType,
            'testheader': this.defaultTitle,
            'filemandatory': this.manadatoryFile,
            'weight': this.gradingWeight,
/*            'info': {
                "text": this.helptext
            }*/
        };
        if (this.helptext) {
            result['info'] = {
                "text": this.helptext
            };
        }
        if (this.framework) {
            result['framework'] = this.framework;
        }
        if (this.frameworkRequired) {
            result['framework_version'] = {
                "selected": true,
                "value": '',
                "name": ''
            };
        }
        if (this.entrypointhelp) {
            result['entrypointinfo'] = {
                "text": this.entrypointhelp
            };
        }
        return result;
    }
    createTestForm() {
        TestWrapper.createFromTemplate(null,
            this.mustacheTemplate, this.getTemplateContext(), this.withFileRef);
    }
}



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
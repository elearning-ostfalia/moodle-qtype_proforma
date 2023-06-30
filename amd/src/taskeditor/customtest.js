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
 * Base class for configuration classes
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, Dr.U.Priss, K.Borm
 */

//////////////////////////////////////////////////////////////////////////////
// configuration support
//////////////////////////////////////////////////////////////////////////////

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
        this.framework = undefined;
        this.frameworks = undefined;
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
}


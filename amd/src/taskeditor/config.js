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
 * Classes and functions for handling different test types
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm (Dr.U.Priss)
 */

import {CustomTest, getExtension} from "./util";
import {javaParser} from "./java";
import {readXmlActive} from "./helper";
import * as Str from 'core/str';

// const configXsdSchemaFile = version101;   // choose version for output
/*
    version094:
        namespace = 'xmlns:'+pfix_unit+'="urn:proforma:unittest" xmlns:'+pfix_prak+'="urn:proforma:praktomat:v0.1" ' +
            'xmlns="urn:proforma:task:v0.9.4" xmlns:'+pfix_jart+'="urn:proforma:tests:jartest:v1" ';
    version101:
        namespace = 'xmlns:'+pfix_unit+'="urn:proforma:tests:unittest:v1" xmlns:'+pfix_prak+'="urn:proforma:praktomat:v0.2" '
            + 'xmlns="urn:proforma:task:v1.0.1" xmlns:'+pfix_jart+'="urn:proforma:tests:jartest:v1" ';
*/


export const useCodemirror = true;         // setting this to false turns Codemirror off

const praktomatns     = "urn:proforma:praktomat:v0.2"; // for checkstyle in task 1.0.1
//const jartestns       = "urn:proforma:tests:jartest:v1"; // for reading 1.0.1
const unittestns_old  = "urn:proforma:tests:unittest:v1";
const unittestns_new  = "urn:proforma:tests:unittest:v1.1";
const checkstylens    = "urn:proforma:tests:java-checkstyle:v1.1";

// Localized strings
let gtest_help;
let cunittest_help;
let makerun_help;
let junit_help;
let junitentry_help;

// -------------------------
// TESTS
// -------------------------
// default grading weights
const weightCompilation = 0;
const weightStaticTest = 0.2;

export function initStrings() {
    let strings = [
        { key: 'gtest_help_short', component: 'qtype_proforma' },
        { key: 'cunit_help_short', component: 'qtype_proforma' },
        { key: 'makerun_help', component: 'qtype_proforma' },
        { key: 'junit_help_short', component: 'qtype_proforma' },
        { key: 'junitentry_help', component: 'qtype_proforma' },
    ];
    return Str.get_strings(strings)
        .then(results => {
            console.log('config strings are initialised');
            gtest_help = results[0];
            cunittest_help = results[1];
            makerun_help = results[2];
            junit_help = results[3];
            junitentry_help = results[4];
            testCUnit       = new CUnitTest();
            testGoogleTest  = new GoogleTest();
            testJavaJUnit  = new JUnitTest();
            infoGoogleTest = testGoogleTest;
            infoCUnit = testCUnit;
            infoJavaJUnit = testJavaJUnit;

            testInfos = [
                testJavaComp,
                testJavaJUnit,
                testGoogleTest,
                testCUnit,
                testPython,
                testPythonDoctest,
                /*        testSetlX, testSetlXSyntax,
                        testCComp,*/
                testCheckStyle
            ];
        });
}

// convert to mimetype that can be directely handeled by codemirror
export function getMimeType(mimetype, filename) {
    const extension = filename.split('.').pop().toLowerCase();
    switch (extension) {
        case 'h':    return 'text/x-chdr';
        case 'c':    return 'text/x-csrc';
        case 'cpp':  return 'text/x-c++src';
        case 'java': return 'text/x-java';
        case 'py':   return 'text/x-python';
        case 'stlx': return 'text/x-setlx'; // no actual mode availble
        case 'xml':  return 'application/xml';
        case 'html':  return 'text/html';
        default: return mimetype;
    }
}

export function isBinaryFile(file, mimetype) {
    if (file.name.toLowerCase() === 'makefile') {
        return false;
    }
    if (mimetype && mimetype.match(/(text\/)/i))  // mimetype is 'text/...'
        return false;

    const extension = file.name.split('.').pop();
    switch (extension.toLowerCase()) {
        case 'c' :
        case 'h' :
        case 'cpp' :
        case 'hpp' :
        case 'hxx' :
        case 'cxx' :
        case 'java' :
        case 'log' :
        case 'py' :
        case 'txt' :
        case 'xml' :
        case 'php' :
        case 'js' :
        case 'html' :
        case 'csv' :
            return false;
        default: break;
    }
    return true;
}

export function handleFilenameChangeInTest(newFilename, tempSelElem) {
    function setJavaClassname(newFilename) {
        // set classname if file belongs to JUNIT and if exactly one file is assigned
        let testBox = $(tempSelElem).closest(".xml_test");
        const ui_classname = $(testBox).find(".xml_ju_mainclass");
        if (ui_classname.length === 1 // JUNIT box
            && ui_classname.first().val().trim() === '') { // and entry point not set
            ui_classname.first().val(javaParser.getFullClassnameFromFilename(newFilename));

            // $.each(ui_classname, function(index, element) {
            //     //let currentFilename = $(element).val();
            //     if (!readXmlActive)
            //         $(element).val(javaParser.getFullClassnameFromFilename(newFilename)).change();
            // });
        }
    }

    function setJUnitDefaultTitle(newFilename) {
        // set decsription according to classname
        let testBox = $(tempSelElem).closest(".xml_test");
        const ui_title = $(testBox).find(".xml_test_title");
        if (ui_title.length === 1) {
            $.each(ui_title, function(index, element) {
                let currentTitle = $(element).val();
                if (!readXmlActive && currentTitle === JUnitTest.DefaultTitle)
                    $(element).val("Junit Test " + javaParser.getPureClassnameFromFilename(newFilename)).change();
            });
        }
    }

    setJavaClassname(newFilename);
    setJUnitDefaultTitle(newFilename);
}

export function resolveNamespace(prefix, defaultns) {
    // todo: find better solution to figure out if namespace is supported
    switch (defaultns) {
        case 'urn:proforma:task:v1.0.1':
            switch (prefix) {
                case 'unit':      return unittestns_old;
                //case 'jartest':   return jartestns;
                case 'praktomat': return praktomatns; // for checkstyle
            }
            return '';
        case 'urn:proforma:v2.0':
            switch (prefix) {
                case 'unit':
                    //unitNs = xmldoc.lookupNamespaceURI('unit');
                    //if (unitNs.toString() !== unittestns_new)
                    //    alert('unit namespace is not supported in ProFormA version 2.0: ' + xmldoc.lookupNamespaceURI('unit'));
                    return unittestns_new;
                case 'cs': return checkstylens;
            }
            return '';
        default:
            return 'unsupported namespace'
    }
}

// export const taskeditorconfig = (function(testConfigNode) {



//    function writeNamespaces(task) {
        //task.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:jartest', jartestns);
        //task.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:praktomat', praktomatns);
/*
        task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);
        task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:cs", checkstylens);
*/
//    }



    function writeXmlExtra(metaDataNode, xmlDoc, xmlWriter) {
        //xmlWriter.createTextElement(metaDataNode, 'praktomat:allowed-upload-filename-mimetypes', '(text/.*)', praktomatns);
    }
/*
    function onProglangChanged(newProglang) {
        if (newProglang === 'cpp') {
            // Add GoogleTest
            $(".xml_u_framew option[value='GoogleTest']").remove();
            $(".xml_u_framew").append(`<option value="GoogleTest">GoogleTest</option>`);
        }
    }

 */
/*
    readXml(xmlfile) {
        let xmlReader = new XmlReader(xmlfile);
        switch (xmlReader.defaultns) {
            case 'urn:proforma:task:v1.0.1': return this.readXmlVersion101(xmlfile);
            case 'urn:proforma:v2.0': return this.readXmlVersion2(xmlfile);
            default:
                setErrorMessage("Unsupported ProFormA version " + xmlReader.defaultns);
        }
    }
*/



    // Tests objects
/*    class CCompilerTest extends CustomTest {
        constructor() {
            super("C Compiler Test", "c-compilation");
            this.gradingWeight = weightCompilation;
            this.manadatoryFile = false;
        }
    }*/
    class JavaCompilerTest extends CustomTest {
        constructor() {
            super("Compiler Test", "java-compilation");
            this.gradingWeight = weightCompilation;
            this.manadatoryFile = false;
        }
    }

    class PythonUnittest extends CustomTest {
        constructor() {
            super("Python Unittest", "unittest", "qtype_proforma/taskeditor_test", ['python']);
        }
    }

    class JUnitTest extends CustomTest  {
        static DefaultTitle = "JUnit Test";

        constructor() {
            super(JUnitTest.DefaultTitle, "unittest",
                "qtype_proforma/taskeditor_junit", ['java']);
            this.helptext = junit_help;
            this.entrypointhelp = junitentry_help;
        }
        onReadXml(test, xmlReader, testConfigNode, context) {
            let unitNode = xmlReader.readSingleNode("unit:unittest", testConfigNode);
            if (!unitNode)
                throw new Error('element unit:unittest not found in unittest or unittest namespace invalid');

            switch (unitNode.namespaceURI) {
                case unittestns_old:
                    context['entrypoint'] = xmlReader.readSingleText("unit:main-class", unitNode);
                    break;
                case unittestns_new:
                    context['entrypoint'] = xmlReader.readSingleText("unit:entry-point", unitNode);
                    break;
                default:
                    throw new Error('unsupported namespace ' + xmlReader.defaultns + ' in JUnitTest');
            }

            context['junit_version'] = xmlReader.readSingleText("@version", unitNode);
            context['junit_framework'] = xmlReader.readSingleText("@framework", unitNode);
        }
        onWriteXml(test, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = test.uiElement.root;
            task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);

            let unittestNode = xmlDoc.createElementNS(unittestns_new, "unit:unittest");
            testConfigNode.appendChild(unittestNode);

            xmlWriter.createTextElement(unittestNode, 'unit:entry-point', $(root).find(".xml_ju_mainclass").val(), unittestns_new);
            unittestNode.setAttribute("framework", $(root).find(".xml_ju_framew").val());
            unittestNode.setAttribute("version", $(root).find(".xml_ju_version").val());
        }
    }

    class GeneralUnitTest extends CustomTest  {
        withRunCommand = true;
        constructor(title, template, proglang, framework, withRunCommand = true) {
            super(title, "unittest", template, proglang);
            // this.fileRefLabel = 'Testfile(s) and CMakeLists.txt/ Makefile';
            this.framework = framework;
            this.withRunCommand = withRunCommand;
        }
/*
        getExtraHtmlField() {
            let output = '';
            if (this.withRunCommand) {
                output += "<p><label for='xml_u_mainclass'>Run Command<span class='red'>*</span>: </label>"+
                "<input class='mediuminput xml_u_mainclass' " +
                "title='command for running the test, depends on Makefile (e.g. ./run_test)'/>";
            }

            return output +
                " <label for='xml_u_framew'>Framework<span class='red'>*</span>: </label>"+
                "<select class='xml_u_framew'>" +
                "   <option selected='selected' value='" + this.framework +
                    "'>" + this.framework + "</option>" +
                "</select>"+
                "</p>";
        }
*/
        onReadXml(test, xmlReader, testConfigNode, context) {
            let unitNode = xmlReader.readSingleNode("unit:unittest", testConfigNode);
            if (!unitNode)
                throw new Error('XML: Test "' + this.title + '": subelement unit:unittest not found in unittest or unittest namespace invalid');

            if (unitNode.namespaceURI !== unittestns_new) {
                throw new Error('XML: Test "' + this.title + '": unsupported namespace ' + xmlReader.defaultns + ' in unit test');
            }
            if (this.withRunCommand) {
                context['entrypoint'] = xmlReader.readSingleText("unit:entry-point", unitNode);
                if (context['entrypoint'].trim() === '') {
                    throw new Error('XML: Test "' + this.title + '": run command is missing');
                }
            }

            let framework = xmlReader.readSingleText("@framework", unitNode);
            switch(framework) {
                case 'GoogleTest':
                    this.framework = 'GoogleTest';
                    this.proglang = ['c', 'cpp'];
                    break;
                case 'PythonUnittest':
                    this.framework = 'PythonUnittest';
                    this.proglang = ['python'];
                    break;
                case 'CUnit':
                    this.framework = 'CUnit';
                    this.proglang = ['c'];
                    break;
                default:
                    // Undefined.
                    framework = this.framework;
            }
            context['framework_version'] = xmlReader.readSingleText("@version", unitNode);
            context['framework'] = framework;
        }

        onWriteXml(test, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = test.uiElement.root;
            task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);

            let unittestNode = xmlDoc.createElementNS(unittestns_new, "unit:unittest");
            testConfigNode.appendChild(unittestNode);

            if (this.withRunCommand) {
                xmlWriter.createTextElement(unittestNode, 'unit:entry-point', $(root).find(".xml_u_mainclass").val(), unittestns_new);
            }
            unittestNode.setAttribute("framework", this.framework); // $(root).find(".xml_u_framew").val());
            unittestNode.setAttribute("version", $(root).find(".xml_u_version").val());
            // console.log(testConfigNode);
            // console.log(unittestNode);
        }
    }

    class GoogleTest extends GeneralUnitTest {
        constructor() {
            super("Google Test", "qtype_proforma/taskeditor_unittest", ['c', 'cpp'], 'GoogleTest');
            // console.log('gtest_help ' + gtest_help);
            this.helptext = gtest_help;
            this.entrypointhelp = makerun_help;
        }
    }

    class CUnitTest extends GeneralUnitTest {
        constructor() {
            super("CUnit Test", "qtype_proforma/taskeditor_unittest", ['c'], 'CUnit');
            this.helptext = cunittest_help;
            this.entrypointhelp = makerun_help;
        }
    }



    class CheckstyleTest extends CustomTest {
        constructor() {
            super("CheckStyle Test", "java-checkstyle",
                "qtype_proforma/taskeditor_checkstyle");
            this.gradingWeight = weightStaticTest;
            // this.fileRefLabel = 'Configuration File';
        }

        onReadXml(test, xmlReader, testConfigNode, context) {
            let csNode = xmlReader.readSingleNode("cs:java-checkstyle", testConfigNode);
            if (!csNode) {
                // task version 1.0.1
                // todo: check version
                let praktomatNode = xmlReader.readSingleNode("dns:test-meta-data", testConfigNode);
                context['warnings'] = xmlReader.readSingleText("praktomat:max-checkstyle-warnings", praktomatNode);
                context['cs_version'] = xmlReader.readSingleText("praktomat:version", testConfigNode);
            } else {
                switch (csNode.namespaceURI) {
                    case checkstylens:
                        context['cs_version'] = xmlReader.readSingleText("@version", csNode);
                        context['warnings'] = xmlReader.readSingleText("cs:max-checkstyle-warnings", csNode);
                        break;
                    default:
                        throw new Error('unsupported namespace ' + xmlReader.defaultns + ' in JUnitTest');
                }
            }
        }

        onWriteXml(test, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = test.uiElement.root;
            task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:cs", checkstylens);

            let csNode = xmlDoc.createElementNS(checkstylens, "cs:java-checkstyle");
            testConfigNode.appendChild(csNode);

            xmlWriter.createTextElement(csNode, 'cs:max-checkstyle-warnings', $(root).find(".xml_pr_CS_warnings").val(), checkstylens);
            csNode.setAttribute("version", $(root).find(".xml_pr_CS_version").val());
        }
    }

    class PythonDocTest extends CustomTest {
        constructor() {
            super("Python DocTest", "python-doctest");
            // this.alternativeTesttypes = ['python'];
        }
    }
    /*
    class setlXTest extends CustomTest {
        constructor() {
            super("SetlX Test", "setlx", '' );
            this.alternativeTesttypes = ['jartest'];
        }
    }
    class setlXSyntaxTest extends CustomTest {
        constructor() {
            super("SetlX Syntax Test", "setlx-compilation", '');
            this.gradingWeight = weightCompilation;
            this.alternativeTesttypes = ['jartest'];
        }
        onCreate(testId) {
            //this.initPraktomatTest(testId);
            // add file for the test
            const filename = 'setlxsyntaxtest.stlx';
            createFileWithContent(filename, 'print("");');
            // add file reference
            addFileReferenceToTest(testId, filename);
            // set test title
            getTestField(testId, ".xml_test_title").val("SetlX-Syntax-Test");
        }
    }


    */




/*   const testSetlX       = new setlXTest(setlXTest);
    const testSetlXSyntax = new setlXSyntaxTest();

    // beachten, das bei gleichen XML-Testtypen derjenige zuerst eingetragen wird, der ein Einlesen einer Datei erzeugt werden soll.
*/

/*
    // list of XML schema files that shall be used for validation
    const xsds = [
        // "proforma-test.xsd",
//        "xsd/proforma-unittest.xsd",
//        "xsd/proforma-checkstyle.xsd"
    ];


    // -------------------------
    // overload functions for further activities
    // -------------------------
    function createFurtherUiElements() {
        // LON-Capa weill be no longer supported
        //insertLCformelements();
    }
*/






// const testCComp       = new CCompilerTest();
const testJavaComp    = new JavaCompilerTest();
let testJavaJUnit;
const testCheckStyle  = new CheckstyleTest();
let testCUnit;
let testGoogleTest;
const testPython      = new PythonUnittest();
const testPythonDoctest = new PythonDocTest();


export let testInfos;

export let infoGoogleTest;
export let infoCUnit;
export let infoJavaJUnit;

export let infoJavaComp = testJavaComp;
export let infoPython = testPython;
export let infoPythonDoctest = testPythonDoctest;
export let infoCheckStyle = testCheckStyle;
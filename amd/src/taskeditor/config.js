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
 * Classes and functions for handling different test types
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm (Dr.U.Priss)
 */

import {CustomTest} from "./customtest";
import * as Str from 'core/str';

// const configXsdSchemaFile = version101;   // choose version for output
/*
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
let pythondoc_help;

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
        { key: 'pythondoc_help', component: 'qtype_proforma' },
    ];
    return Str.get_strings(strings)
        .then(results => {
            // console.log('config strings are initialised');
            gtest_help = results[0];
            cunittest_help = results[1];
            makerun_help = results[2];
            junit_help = results[3];
            junitentry_help = results[4];
            pythondoc_help = results[5];

            infoGoogleTest = new GoogleTest();
            infoCUnit = new CUnitTest();
            infoJavaJUnit = new JUnitTest();
            infoPythonDoctest = new PythonDocTest();

            testInfos = [
                testJavaComp,
                infoJavaJUnit,
                infoGoogleTest,
                infoCUnit,
                testPython,
                infoPythonDoctest,
                /*        testSetlX, testSetlXSyntax,
                        testCComp,*/
                testCheckStyle
            ];
        });
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



    // Test classes
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

    class GeneralUnitTest extends CustomTest  {
        withRunCommand = true;
        constructor(title, proglang, framework,
                    template = "qtype_proforma/taskeditor_unittest",
                    withRunCommand = true) {
            super(title, "unittest", template, proglang);
            this.framework = framework;
            this.withRunCommand = withRunCommand;
        }

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
            if (this.framework) {
                // Override if subclass has defined it
                framework = this.framework;
            }
            const version = xmlReader.readSingleText("@version", unitNode);
            if (version && version !== 'undefined' && version.trim() !== '') {
                context['framework_version'] = {
                    "selected": true,
                    "value": version,
                    "name": version
                };
            }
            context['framework'] = framework;
        }

        onWriteXml(test, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = test.uiElement.root;
            task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);

            let unittestNode = xmlDoc.createElementNS(unittestns_new, "unit:unittest");
            testConfigNode.appendChild(unittestNode);

            if (this.withRunCommand) {
                xmlWriter.createTextElement(unittestNode, 'unit:entry-point',
                    $(root).find(".xml_entry_point").val(), unittestns_new);
            }
            unittestNode.setAttribute("framework", this.framework);
            const versionelem = ($(root).find(".xml_framework_version"));
            if (versionelem) {
                unittestNode.setAttribute("version", versionelem.val());
            } else {
                unittestNode.setAttribute("version", '');
            }
        }
    }

    class JUnitTest extends GeneralUnitTest  {
        static DefaultTitle = "JUnit Test";

        constructor() {
            super(JUnitTest.DefaultTitle, ['java'], "JUnit", "qtype_proforma/taskeditor_junit");
            this.helptext = junit_help;
            this.entrypointhelp = junitentry_help;
            this.frameworkRequired = true;
        }
        onReadXml(test, xmlReader, testConfigNode, context) {
            super.onReadXml(test, xmlReader, testConfigNode, context);
            let unitNode = xmlReader.readSingleNode("unit:unittest", testConfigNode);
            if (!unitNode)
                throw new Error('element unit:unittest not found in unittest or unittest namespace invalid');

            switch (unitNode.namespaceURI) {
                case unittestns_old:
                    context['entrypoint'] = xmlReader.readSingleText("unit:main-class", unitNode);
                    break;
                case unittestns_new:
                    // default
                    break;
                default:
                    throw new Error('unsupported namespace ' + xmlReader.defaultns + ' in JUnitTest');
            }
        }
    }

    class GoogleTest extends GeneralUnitTest {
        constructor() {
            super("Google Test", ['c', 'cpp'], 'GoogleTest');
            this.helptext = gtest_help;
            this.entrypointhelp = makerun_help;
            this.frameworks = ['googletest', 'google-test', 'google' , 'google test'];
        }
    }

    class CUnitTest extends GeneralUnitTest {
        constructor() {
            super("CUnit Test", ['c'], 'CUnit');
            this.helptext = cunittest_help;
            this.entrypointhelp = makerun_help;
            this.frameworks = ['cunit', 'cunittest', 'cunit-test', 'cunit test'];
        }
    }

    class CheckstyleTest extends CustomTest {
        constructor() {
            super("CheckStyle Test", "java-checkstyle",
                "qtype_proforma/taskeditor_checkstyle");
            this.gradingWeight = weightStaticTest;
            this.frameworkRequired = true;
        }

        onReadXml(test, xmlReader, testConfigNode, context) {
            let csNode = xmlReader.readSingleNode("cs:java-checkstyle", testConfigNode);
            if (!csNode) {
                // task version 1.0.1
                // todo: check version
                let praktomatNode = xmlReader.readSingleNode("dns:test-meta-data", testConfigNode);
                context['warnings'] = xmlReader.readSingleText("praktomat:max-checkstyle-warnings", praktomatNode);
                context['framework_version'] = xmlReader.readSingleText("praktomat:version", testConfigNode);
            } else {
                switch (csNode.namespaceURI) {
                    case checkstylens:
                        const version = xmlReader.readSingleText("@version", csNode);
                        context['framework_version'] = {
                            "selected": true,
                            "value": version,
                            "name": version
                        };
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
            csNode.setAttribute("version", $(root).find(".xml_framework_version").val());
        }
    }

    class PythonDocTest extends CustomTest {
        constructor() {
            super("Python DocTest", "python-doctest",
                undefined, ['python']);
            // this.alternativeTesttypes = ['python'];
            this.helptext = pythondoc_help;
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


/*   const testSetlX = new setlXTest(setlXTest);
    const testSetlXSyntax = new setlXSyntaxTest();

*/

/*
    // list of XML schema files that shall be used for validation
    const xsds = [
        // "proforma-test.xsd",
//        "xsd/proforma-unittest.xsd",
//        "xsd/proforma-checkstyle.xsd"
    ];

*/

const testJavaComp    = new JavaCompilerTest();
const testCheckStyle  = new CheckstyleTest();
const testPython      = new PythonUnittest();


export let testInfos;

export let infoGoogleTest;
export let infoCUnit;
export let infoJavaJUnit;

export let infoJavaComp = testJavaComp;
export let infoPythonUnittest = testPython;
export let infoPythonDoctest;
export let infoCheckStyle = testCheckStyle;
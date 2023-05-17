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

import {CustomTest, getExtension} from "./taskeditorutil";
import {javaParser} from "./taskeditorjava";
import {readXmlActive} from "./taskeditorhelper";

// const configXsdSchemaFile = version101;   // choose version for output
/*
    version094:
        namespace = 'xmlns:'+pfix_unit+'="urn:proforma:unittest" xmlns:'+pfix_prak+'="urn:proforma:praktomat:v0.1" ' +
            'xmlns="urn:proforma:task:v0.9.4" xmlns:'+pfix_jart+'="urn:proforma:tests:jartest:v1" ';
    version101:
        namespace = 'xmlns:'+pfix_unit+'="urn:proforma:tests:unittest:v1" xmlns:'+pfix_prak+'="urn:proforma:praktomat:v0.2" '
            + 'xmlns="urn:proforma:task:v1.0.1" xmlns:'+pfix_jart+'="urn:proforma:tests:jartest:v1" ';
*/


export const taskeditorconfig = (function(testConfigNode) {
    const praktomatns     = "urn:proforma:praktomat:v0.2"; // for checkstyle in task 1.0.1
    //const jartestns       = "urn:proforma:tests:jartest:v1"; // for reading 1.0.1
    const unittestns_old  = "urn:proforma:tests:unittest:v1";
    const unittestns_new  = "urn:proforma:tests:unittest:v1.1";
    const checkstylens    = "urn:proforma:tests:java-checkstyle:v1.1";

//    function writeNamespaces(task) {
        //task.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:jartest', jartestns);
        //task.setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:praktomat', praktomatns);
/*
        task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);
        task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:cs", checkstylens);
*/
//    }

    function resolveNamespace(prefix, defaultns) {
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
    // -------------------------
    // TESTS
    // -------------------------


    const CUnit_Default_Title = "CUnit Test";
    const GoogleTest_Default_Title = "Google Test";
    const PythonUnittest_Default_Title = "Python Unittest";

    // default grading weights
    const weightCompilation = 0;
    const weightStaticTest = 0.2;


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

    class JUnitTest extends CustomTest  {
        static DefaultTitle = "JUnit Test";

        constructor() {
            super(JUnitTest.DefaultTitle, "unittest",
                "qtype_proforma/taskeditor_junit", ['java']);
            this.fileRefLabel = 'Junit and other file(s)';
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
        onWriteXml(test, uiElement, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = uiElement.root;
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
        constructor(title, proglang, framework, withRunCommand = true) {
            super(title, "unittest", "", proglang);
            this.fileRefLabel = 'Testfile(s) and CMakeLists.txt/ Makefile';
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
                throw new Error('element unit:unittest not found in unittest or unittest namespace invalid');

            if (unitNode.namespaceURI !== unittestns_new) {
                throw new Error('unsupported namespace ' + xmlReader.defaultns + ' in CUnitTest');
            }
            if (this.withRunCommand) {
                context['entrypoint'] = xmlReader.readSingleText("unit:entry-point", unitNode);
            }

            this.framework = xmlReader.readSingleText("@framework", unitNode);
            switch(this.framework) {
                case 'GoogleTest':
                    this.framework = 'GoogleTest';
                    this.proglang = ['c', 'cpp'];
                    break;
                case 'PythonUnittest':
                    this.framework = 'PythonUnittest';
                    this.proglang = ['python'];
                    break;
                default:
                case undefined:
                case '':
                // Fall through
                case 'CUnit':
                    this.framework = 'CUnit';
                    this.proglang = ['c'];
                    break;
            }
            // $(testroot).find(".xml_u_version").val(xmlReader.readSingleText("@version", unitNode));
            context['framework_version'] = xmlReader.readSingleText("@version", unitNode);
            // Update framework value
            // $(testroot).find(".xml_u_framew").html("<option selected='selected' value='" + this.framework +
            //     "'>" + this.framework + "</option>");
            context['framework'] = this.framework;
        }

        onWriteXml(test, uiElement, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = uiElement.root;
            task.setAttributeNS('http://www.w3.org/2000/xmlns/', "xmlns:unit", unittestns_new);

            let unittestNode = xmlDoc.createElementNS(unittestns_new, "unit:unittest");
            testConfigNode.appendChild(unittestNode);

            if (this.withRunCommand) {
                xmlWriter.createTextElement(unittestNode, 'unit:entry-point', $(root).find(".xml_u_mainclass").val(), unittestns_new);
            }
            unittestNode.setAttribute("framework", $(root).find(".xml_u_framew").val());
            unittestNode.setAttribute("version", $(root).find(".xml_u_version").val());
        }
    }

    class GoogleTest extends GeneralUnitTest {
        constructor() {
            super(GoogleTest_Default_Title, ['c', 'cpp'], 'GoogleTest');
        }
    }

    class CUnitTest extends GeneralUnitTest {
        constructor() {
            super(CUnit_Default_Title, ['c'], 'CUnit');
        }
    }

    class PythonUnittest extends GeneralUnitTest {
        constructor() {
            super(PythonUnittest_Default_Title, ['python'], 'PythonUnittest', false);
            this.fileRefLabel = 'Python unittest file(s)';

        }
    }

    class CheckstyleTest extends CustomTest {
        constructor() {
            super("CheckStyle Test", "java-checkstyle",
                "qtype_proforma/taskeditor_checkstyle");
            this.gradingWeight = weightStaticTest;
            this.fileRefLabel = 'Configuration File';
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

        onWriteXml(test, uiElement, testConfigNode, xmlDoc, xmlWriter, task) {
            let root = uiElement.root;
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
    // const testCComp       = new CCompilerTest();
    const testJavaComp    = new JavaCompilerTest();
    const testJavaJUnit   = new JUnitTest();
    const testCheckStyle  = new CheckstyleTest();

    const testCUnit       = new CUnitTest();
    const testGoogleTest  = new GoogleTest();

    const testPython      = new PythonUnittest();
    const testPythonDoctest = new PythonDocTest();
/*   const testSetlX       = new setlXTest(setlXTest);
    const testSetlXSyntax = new setlXSyntaxTest();

    // -------------------------------
    // Test buttons
    // -------------------------------
    // Reihenfolge: in der Reihenfolge, in der die Test in testInfos angelegt werden, werden auch die Testbuttons erzeugt!
    // beachten, das bei gleichen XML-Testtypen derjenige zuerst eingetragen wird, der ein Einlesen einer Datei erzeugt werden soll.
*/
    const testInfos = [
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

    // convert to mimetype that can be directely handeled by codemirror
    function getMimeType(mimetype, filename) {
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

    function isBinaryFile(file, mimetype) {
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
            case 'csv' :
                return false;
            default: break;
        }
        return true;
    }

    function handleFilenameChangeInTest(newFilename, tempSelElem) {
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

    // -------------------------
    // expose to public (interface)
    // -------------------------
    return {
        // methods
//        createFurtherUiElements: createFurtherUiElements,
        getMimeType: getMimeType,
        isBinaryFile: isBinaryFile,
        handleFilenameChangeInTest: handleFilenameChangeInTest,
        testInfos: testInfos,
        infoJavaComp: testJavaComp,
        infoJavaJUnit: testJavaJUnit,
        infoGoogleTest: testGoogleTest,
        infoCUnit: testCUnit,
        infoPython: testPython,
        infoPythonDoctest: testPythonDoctest,
        infoCheckStyle: testCheckStyle,
        resolveNamespace: resolveNamespace,
/*        writeXmlExtra: writeXmlExtra,
        //writeNamespaces: writeNamespaces,
        // data
        xsds: xsds,
        // switches, constants...*/
        useCodemirror: true,         // setting this to false turns Codemirror off
/*        // xsdSchemaFile: configXsdSchemaFile,
        maxSizeForEditor: 100000, // maximum file size to enable editing
*/
    }
})();


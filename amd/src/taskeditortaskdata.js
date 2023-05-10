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
 * @copyright 2018 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @author   Karin Borm <k.borm@ostfalia.de>
 */


import {setErrorMessage} from "./taskeditorutil";
import {config} from "./taskeditorconfig";

const T_LMS_USAGE = {
    DISPLAY: 'display',
    DOWNLOAD: 'download',
    EDIT: 'edit'
};

const T_VISIBLE = {
    YES: 'yes',
    NO: 'no',
    DELAYED: 'delayed'
};

const T_FILERESTRICTION_FORMAT = {
    POSIX: 'posix-ere',
    NONE: 'none'
};



// helper class
class XmlReader {
    constructor(xmlText) {
        this.xmlDoc = new DOMParser().parseFromString(xmlText,'text/xml');
        if (!this.xmlDoc.evaluate) {
            alert('XPATH not supported');
            return;
        }

        /*
        var parser = new DOMParser();
        [
            '<task xmlns="urn:proforma:v2.0" lang="en"/>',
            '<task xmlns="urn:proforma:task:v1.0.1" lang="en" uuid="e7a50a36-e0b7-486f-be80-0f217e7bcb80" xmlns:jartest="urn:proforma:tests:jartest:v1" xmlns:praktomat="urn:proforma:praktomat:v0.2" xmlns:unit="urn:proforma:tests:unittest:v1"/>',
            '<ns:root xmlns:ns="example.com/ns2"/>'
        ].forEach(function(item) {
            var doc = parser.parseFromString(item, "application/xml");
            alert('result of doc.lookupNamespaceURI(null): |' + doc.lookupNamespaceURI(null) + '|');
        });

*/
        this.defaultns = this.xmlDoc.lookupNamespaceURI(null);
        //alert('result of doc.lookupNamespaceURI(null): |' + doc.lookupNamespaceURI(null) + '|');

        this.rootNode = this.xmlDoc;

        const defaultns = this.defaultns;
        this.nsResolver = function (prefix) {

            switch (prefix) {
                case 'dns': return defaultns; // 'urn:proforma:task:v1.0.1';
                default:    return config.resolveNamespace(prefix, defaultns);
            }
        };
    }

    setRootNode(node) {
        this.rootNode = node;
    }

    readSingleNode(xpath, node) {
        const nodes = this.xmlDoc.evaluate(xpath, node?node:this.rootNode, this.nsResolver,
            XPathResult.UNORDERED_NODE_ITERATOR_TYPE /*FIRST_ORDERED_NODE_TYPE*/, null);
        return nodes.iterateNext(); // .singleNodeValue;
    }


    readSingleText(xpath, node, defaultValue) {
        const nodes = this.xmlDoc.evaluate(xpath, node?node:this.rootNode, this.nsResolver, XPathResult.FIRST_ORDERED_NODE_TYPE, null);
        if (nodes.singleNodeValue)
            return nodes.singleNodeValue.textContent.trim();
        else {
            if (typeof defaultValue !== 'undefined')
                return defaultValue;
            return null;
        }
    }

    readNodes(xpath, node) {
        return this.xmlDoc.evaluate(xpath, node?node:this.rootNode, this.nsResolver, XPathResult.UNORDERED_NODE_ITERATOR_TYPE, null);
    }
}


class XmlWriter {
    constructor(xmlDoc, ns) {
        this.xmlDoc = xmlDoc;
        this.ns = ns;
    }

    createCDataElement(node, tag, value, ns = undefined) {
        let newTag = this.xmlDoc.createElementNS(ns?ns:this.ns, tag);
        newTag.appendChild(this.xmlDoc.createCDATASection(value));
        node.appendChild(newTag);
        return newTag;
    }

    createTextElement(node, tag, value, ns = undefined, cdata = false) {
        let newTag = this.xmlDoc.createElementNS(ns?ns:this.ns, tag);
        if (cdata) {
            newTag.appendChild(this.xmlDoc.createCDATASection(value));
            throw new SyntaxError('cdata not supported, use createCDataElement');
        }
        else
            newTag.appendChild(this.xmlDoc.createTextNode(value));
        node.appendChild(newTag);
        return newTag;
    }

    createOptionalTextElement(node, tag, value, ns = undefined, cdata = false) {
        if (cdata) {
            throw new SyntaxError('cdata not supported, use createOptionalTextElement');
        }
        if (value === '')
            return;
        return this.createTextElement(node, tag, value, ns, cdata);
    }

    createOptionalCDataElement(node, tag, value, ns = undefined) {
        if (value === '')
            return;
        return this.createCDataElement(node, tag, value, ns);
    }
}

// task data structures
class TaskFileRef {
    constructor(id) {
        this.refid = id;
    }
}

class TaskFile {
    constructor() {
        this.filename = '';
        this.usedByGrader = false;
        this.usageInLms = null;
        this.visible = T_VISIBLE.NO;
        this.id = null;
        this.filetype = null;
        this.comment = null;
        this.content = null;
        this.binary = null;
    }
}

class TaskFileRestriction {
    constructor(filename, required, format) {
        this.restriction = filename;
        this.required = required;
        this.format = format;
    }
}


class TaskModelSolution {
    constructor() {
        this.id = null;
        this.description = "";
        this.comment = "";
        this.filerefs = [];
    }
}


class TaskTest {
    constructor() {
        this.id = null;
        this.title = null;
        this.description = "";
        this.comment = "";
        this.testtype = null;
        this.filerefs = [];
        this.writeCallback = null;
        this.uiElement = null;
    }
}

export class TaskClass {

    constructor() {
        this.title = '';
        this.description = '';
        this.comment = '';
        this.proglang = '';
        this.proglangVersion = '';
        this.parentuuid = null;
        this.uuid = null;
        this.lang = 'de';
        this.sizeSubmission = 0;
        this.filenameRegExpSubmission = '';
        this.codeskeleton = '';

        this.fileRestrictions = [];
        this.files = [];
        // this.external-resources ;
        this.modelsolutions = [];
        this.tests = [];
        this.gradinghints = [];
    }


    findFilenameForId(id) {
        let filename = undefined;
        this.files.forEach(function(item) {
            if (item.id === id) {
                filename = item.filename;
            }
        });
        return filename;
    }

    readTestConfig(xmlfile, testid, configItem, context) {
        try {
            let xmlReader = new XmlReader(xmlfile);
            xmlReader.setRootNode(xmlReader.readSingleNode("/dns:task/dns:tests/dns:test[@id="+testid+"]"));
            let configNodeNode = xmlReader.readSingleNode("dns:test-configuration");
            configItem.onReadXml(this.tests[testid], xmlReader, configNodeNode, context);
        } catch (err){
            alert (err);
            setErrorMessage("Error while parsing test configuration in xml file", err);
        }
    }

    readXmlVersion101(xmlfile) {

        let template_id = null;
        let template_referenced = false; // is template referenced in test or somewhere else?


        function readFileRefs(xmlReader, element, thisNode, visibility, task) {
            let fileRefIterator = xmlReader.readNodes("dns:filerefs/dns:fileref", thisNode);
            let fileRefNode = fileRefIterator.iterateNext();
            let counter = 0;
            while (fileRefNode) {
                let fileRef = new TaskFileRef();
                fileRef.refid = xmlReader.readSingleText("@refid", fileRefNode);
                if (template_id && (template_id === fileRef.refid)) {
                    template_referenced = true;
                }
                element.filerefs[counter++] = fileRef;
                fileRefNode = fileRefIterator.iterateNext();
                if (visibility != null) {
                    // increase visibilty
                    switch (task.files[fileRef.refid].visible) {
                        case T_VISIBLE.NO:  task.files[fileRef.refid].visible = visibility; break;
                        case T_VISIBLE.YES:  break;
                        case T_VISIBLE.DELAYED:
                            if (visibility === T_VISIBLE.YES)
                                task.files[fileRef.refid].visible = visibility;
                            break;
                    }
                }
            }
        }

        try {
            let xmlReader = new XmlReader(xmlfile);
            xmlReader.setRootNode(xmlReader.readSingleNode("/dns:task")); // => shorter xpaths

            this.title = xmlReader.readSingleText("dns:meta-data/dns:title");
            this.description = xmlReader.readSingleText("dns:description");
            this.proglang = xmlReader.readSingleText("dns:proglang");
            this.proglangVersion = xmlReader.readSingleText("dns:proglang/@version");
            this.uuid = xmlReader.readSingleText("@uuid");
            this.lang = xmlReader.readSingleText("@lang");
            this.sizeSubmission = xmlReader.readSingleText("dns:submission-restrictions/dns:regexp-restriction/@max-size");
            if (this.sizeSubmission !== '')
                this.sizeSubmission = this.sizeSubmission * 1000; // convert to bytes (or *1024?)
            // mimetype is unsupported
            // this.mimeTypeRegExpSubmission = xmlReader.readSingleText("dns:submission-restrictions/dns:regexp-restriction");

            // read files
            let iterator = xmlReader.readNodes("dns:files/dns:file");
            let thisNode = iterator.iterateNext();

            while (thisNode) {
                let taskfile = new TaskFile();
                taskfile.id = xmlReader.readSingleText("@id", thisNode);
                const fileclass = xmlReader.readSingleText("@class", thisNode);
                switch(fileclass) {
                    case 'internal':
                    case 'internal-library':
                        taskfile.usedByGrader = true;
                        taskfile.usageInLms = null;
                        taskfile.visible = T_VISIBLE.NO;
                        break;
                    case 'template':
                        if (this.codeskeleton === '') {
                            template_id = taskfile.id;
                            this.codeskeleton = thisNode.textContent;
                            taskfile.usedByGrader = false;
                            taskfile.usageInLms = T_LMS_USAGE.EDIT;
                            taskfile.visible = T_VISIBLE.YES;
                        }
                        else {
                            taskfile.usedByGrader = false;
                            //taskfile.usageInLms = T_LMS_USAGE.EDIT;
                            taskfile.usageInLms = T_LMS_USAGE.DOWNLOAD;
                            taskfile.visible = T_VISIBLE.YES;
                        }
                        break;
                    case 'instruction':
                        taskfile.usedByGrader = false;
                        taskfile.usageInLms = T_LMS_USAGE.DOWNLOAD;
                        taskfile.visible = T_VISIBLE.YES;
                        break;
                    case 'library':
                        taskfile.usedByGrader = true;
                        taskfile.usageInLms = T_LMS_USAGE.DOWNLOAD;
                        taskfile.visible = T_VISIBLE.YES;
                        break;
                }
                taskfile.comment = xmlReader.readSingleText("@comment", thisNode);
                taskfile.filetype = xmlReader.readSingleText("@type", thisNode);
                taskfile.filename = xmlReader.readSingleText("@filename", thisNode);
                taskfile.content = thisNode.textContent;
                this.files[taskfile.id] = taskfile;
                thisNode = iterator.iterateNext();
            }

            // read model solutions(s)
            iterator = xmlReader.readNodes("dns:model-solutions/dns:model-solution");
            thisNode = iterator.iterateNext();
            while (thisNode) {
                let modelSolution = new TaskModelSolution();
                modelSolution.id = xmlReader.readSingleText("@id", thisNode);
                modelSolution.comment = xmlReader.readSingleText("@comment", thisNode);
                readFileRefs(xmlReader, modelSolution, thisNode, T_VISIBLE.DELAYED, this);
                this.modelsolutions[modelSolution.id] = modelSolution;
                thisNode = iterator.iterateNext();
            }

            // read test(s)
            iterator = xmlReader.readNodes("dns:tests/dns:test");
            thisNode = iterator.iterateNext();
            let counter = 0;
            while (thisNode) {
                let test = new TaskTest();
                test.id = xmlReader.readSingleText("@id", thisNode);
                test.title = xmlReader.readSingleText("dns:title", thisNode);
                test.testtype = xmlReader.readSingleText("dns:test-type", thisNode);

                let configIterator = xmlReader.readNodes("dns:test-configuration", thisNode);
                let configNode = configIterator.iterateNext();
                readFileRefs(xmlReader, test, configNode);

                this.tests[counter] = test;
                counter++;
                thisNode = iterator.iterateNext();
            }

            // check if template is referenced somewhere. If not then the file can be deleted!
            if (!template_referenced) {
                delete this.files[template_id];
            }

        } catch (err){
            //alert (err);
            setErrorMessage("Error while parsing the xml file. The file has not been imported.", err);
        }
    }


    readXmlVersion2(xmlfile) {

        function readFileRefs(xmlReader, element, thisNode) {
            let fileRefIterator = xmlReader.readNodes("dns:filerefs/dns:fileref", thisNode);
            let fileRefNode = fileRefIterator.iterateNext();
            let counter = 0;
            while (fileRefNode) {
                let fileRef = new TaskFileRef();
                fileRef.refid = xmlReader.readSingleText("@refid", fileRefNode);
                element.filerefs[counter++] = fileRef;
                fileRefNode = fileRefIterator.iterateNext();
            }
        }

        try {
            let xmlReader = new XmlReader(xmlfile);
            xmlReader.setRootNode(xmlReader.readSingleNode("/dns:task")); // => shorter xpaths

            this.title = xmlReader.readSingleText("dns:title");
            this.description = xmlReader.readSingleText("dns:description");
            this.comment = xmlReader.readSingleText("dns:internal-description");
            this.proglang = xmlReader.readSingleText("dns:proglang");
            this.proglangVersion = xmlReader.readSingleText("dns:proglang/@version");
            this.uuid = xmlReader.readSingleText("@uuid");
            this.lang = xmlReader.readSingleText("@lang");
            this.sizeSubmission = xmlReader.readSingleText("dns:submission-restrictions/@max-size");

            let iterator = xmlReader.readNodes("dns:submission-restrictions/dns:file-restriction");
            let thisNode = iterator.iterateNext();
            let editCounter = 0;
            while (thisNode) {
                const required =
                this.fileRestrictions[editCounter++] = new TaskFileRestriction(thisNode.textContent,
                    xmlReader.readSingleText("@required", thisNode, "true")==='true',
                    xmlReader.readSingleText("@pattern-format", thisNode));
                thisNode = iterator.iterateNext();
            }

            // read files
            iterator = xmlReader.readNodes("dns:files/dns:file");
            thisNode = iterator.iterateNext();
            editCounter = 0;
            while (thisNode) {

                let taskfile = new TaskFile();
                taskfile.id = xmlReader.readSingleText("@id", thisNode);
                //taskfile.fileclass = xmlReader.readSingleText("@class", thisNode);
                taskfile.usedByGrader = (xmlReader.readSingleText("@used-by-grader", thisNode)==='yes');
                taskfile.usageInLms = xmlReader.readSingleText("@usage-by-lms", thisNode);
                taskfile.visible = xmlReader.readSingleText("@visible", thisNode);
                // todo:
                taskfile.comment = xmlReader.readSingleText("dns:internal-description", thisNode);
                let content = xmlReader.readSingleNode('*', thisNode); // nodeValue
                if (content) {
                    switch (content.nodeName) {
                        case "embedded-txt-file":
                            taskfile.filetype = 'embedded';
                            taskfile.filename = xmlReader.readSingleText("@filename", content);
                            taskfile.content = content.textContent;
                            taskfile.binary = false;
                            break;
                        case "embedded-bin-file":
                            // taskfile.filetype = 'embedded';
                            taskfile.filetype = 'file';
                            taskfile.filename = xmlReader.readSingleText("@filename", content);
                            // taskfile.content = atob(content.textContent);
                            taskfile.binary = true;
                            const filecontent =  atob(content.textContent);
                            console.log(taskfile.id + ' is embedded-bin-file');
                            let fileObject = new FileStorage(true, '', filecontent, taskfile.filename);
                            fileObject.setSize(filecontent.length);
                            fileStorages[taskfile.id] = fileObject;
                            break;
                        case "attached-bin-file":
                            taskfile.filetype = 'file';
                            taskfile.filename = content.textContent;
                            taskfile.binary = true;
                            break;
                        default:
                            setErrorMessage("Unknown file type for file #". taskfile.id);
                    }
                } else {
                    setErrorMessage("No file content for file #". taskfile.id);
                }

                // post processing:
                // copy file content for editor in associated text area
                const displaymode = xmlReader.readSingleText("@usage-by-lms", thisNode);
                if (taskfile.usageInLms === T_LMS_USAGE.EDIT) {
                    if (editCounter === 0) {
                        // do not store as file
                        this.codeskeleton = taskfile.content;
                    } else {
                        this.files[taskfile.id] = taskfile;
                    }
                    editCounter++;
                } else {
                    this.files[taskfile.id] = taskfile;
                }


/*
                let embeddedTextFile = xmlReader.readSingleNode("embedded-txt-file");
                if (embeddedTextFile) {
                    taskfile.filetype = 'embedded';
                    taskfile.filename = xmlReader.readSingleText("@filename", embeddedTextFile);
                    taskfile.content = embeddedTextFile.textContent;
                } else {
                    let attachedBinFile = xmlReader.readSingleNode("attached-bin-file");
                    if (attachedBinFile) {
                        taskfile.filetype = 'file';
                        taskfile.filename = xmlReader.readSingleText("@filename", attachedBinFile);
                    } else {
                        setErrorMessage("Unknown file type for file #". taskfile.id);
                    }
                }
*/



                thisNode = iterator.iterateNext();
            }

            // read model solutions(s)
            iterator = xmlReader.readNodes("dns:model-solutions/dns:model-solution");
            thisNode = iterator.iterateNext();
            while (thisNode) {
                let modelSolution = new TaskModelSolution();
                modelSolution.id = xmlReader.readSingleText("@id", thisNode);
                modelSolution.description = xmlReader.readSingleText("dns:description", thisNode);
                modelSolution.comment = xmlReader.readSingleText("dns:internal-description", thisNode);
                readFileRefs(xmlReader, modelSolution, thisNode);
                this.modelsolutions[modelSolution.id] = modelSolution;
                thisNode = iterator.iterateNext();
            }

            // read test(s)
            iterator = xmlReader.readNodes("dns:tests/dns:test");
            thisNode = iterator.iterateNext();
            let counter = 0;
            while (thisNode) {
                let test = new TaskTest();
                test.id = xmlReader.readSingleText("@id", thisNode);
                test.title = xmlReader.readSingleText("dns:title", thisNode);
                test.description = xmlReader.readSingleText("dns:description", thisNode);
                test.comment = xmlReader.readSingleText("dns:internal-description", thisNode);
                test.testtype = xmlReader.readSingleText("dns:test-type", thisNode);

                let configIterator = xmlReader.readNodes("dns:test-configuration", thisNode);
                let configNode = configIterator.iterateNext();
                readFileRefs(xmlReader, test, configNode);

                this.tests[counter] = test;
                counter++;
                thisNode = iterator.iterateNext();
            }

            // read grading hints
            const gradingfunction = xmlReader.readSingleText("dns:grading-hints/dns:root/@function");
            if (gradingfunction && gradingfunction !== 'sum') {
                setErrorMessage("Grading hints function " + gradingfunction + " is not supported");
            }
            iterator = xmlReader.readNodes("dns:grading-hints/dns:root/dns:test-ref");
            thisNode = iterator.iterateNext();
            while (thisNode) {
                const id = xmlReader.readSingleText("@ref", thisNode);
                this.tests.forEach(function(test) {
                    if (test.id === id)
                        test.weight = xmlReader.readSingleText("@weight", thisNode);
                });
                thisNode = iterator.iterateNext();
            }

       } catch (err){
           //alert (err);
           setErrorMessage("Error while parsing the xml file. The file has not been imported.", err);
       }
    }

    readXml(xmlfile) {
        let xmlReader = new XmlReader(xmlfile);
        switch (xmlReader.defaultns) {
            case 'urn:proforma:task:v1.0.1': return this.readXmlVersion101(xmlfile);
            case 'urn:proforma:v2.0': return this.readXmlVersion2(xmlfile);
            default:
                setErrorMessage("Unsupported ProFormA version " + xmlReader.defaultns);
        }
    }


    // todo: read data direectly from user input instead of using TaskClass object
    writeXml(topLevelDoc, rootNode) {
        let xmlDoc = null;
        let files = null;
        let fileRestrictions = null;
        let modelsolutions = null;
        let tests = null;
        let gradingRoot = null;
        let xmlWriter = null;
        const xmlns = "urn:proforma:v2.0";
        let task = null;

        /* Version 1.0.1
        function writeFile(item, index) {
            let fileElem = xmlDoc.createElementNS(xmlns, "file");
            fileElem.setAttribute("class", item.fileclass);
            fileElem.setAttribute("comment", item.comment);
            fileElem.setAttribute("filename", item.filename);
            fileElem.setAttribute("id", item.id);
            fileElem.setAttribute("type", item.filetype);
            files.appendChild(fileElem);
            if (item.filetype === 'embedded')
                fileElem.appendChild(xmlDoc.createCDATASection(item.content));
        }
        */

        // version 2.0

        function writeCodeSkeleton(task, id) {
            if (task.codeskeleton) {
                let fileElem = xmlDoc.createElementNS(xmlns, "file");
                fileElem.setAttribute("id", id);
                fileElem.setAttribute("used-by-grader", 'false');
                fileElem.setAttribute("usage-by-lms", T_LMS_USAGE.EDIT);
                fileElem.setAttribute("visible", T_VISIBLE.YES);

                // fileElem.setAttribute("comment", item.comment);
                files.appendChild(fileElem);
                let fileContentElem = xmlDoc.createElementNS(xmlns, "embedded-txt-file");
                fileContentElem.setAttribute("filename", 'code.txt');
                fileContentElem.appendChild(xmlDoc.createCDATASection(task.codeskeleton));
                fileElem.appendChild(fileContentElem);
                xmlWriter.createOptionalTextElement(fileElem, 'internal-description', 'Code Skeleton for Editor');
            }
        }

        function writeFile(item, index) {
            let fileElem = xmlDoc.createElementNS(xmlns, "file");
            fileElem.setAttribute("id", item.id);
            //fileElem.setAttribute("class", item.fileclass);
            fileElem.setAttribute("used-by-grader", item.usedByGrader);
            if (item.usageInLms) // optional
                fileElem.setAttribute("usage-by-lms", item.usageInLms);
            fileElem.setAttribute("visible", item.visible);

            // fileElem.setAttribute("comment", item.comment);
            files.appendChild(fileElem);
            if (item.filetype === 'embedded') {
                let fileContentElem = xmlDoc.createElementNS(xmlns, "embedded-txt-file");
                fileContentElem.setAttribute("filename", item.filename);
                fileContentElem.appendChild(xmlDoc.createCDATASection(item.content));
                fileElem.appendChild(fileContentElem);
            } else {
                xmlWriter.createTextElement(fileElem, 'attached-bin-file', item.filename);
            }
            xmlWriter.createOptionalTextElement(fileElem, 'internal-description', item.comment);
        }

        function writeModelSolution(item, index) {
            function writeFileref(file, index) {
                if (file.refid) {
                    let fileref = xmlDoc.createElementNS(xmlns, "fileref");
                    fileref.setAttribute("refid", file.refid);
                    filerefs.appendChild(fileref);
                }
            }
            let msElem = xmlDoc.createElementNS(xmlns, "model-solution");
            // msElem.setAttribute("description", item.comment); // alt
            msElem.setAttribute("id", item.id);
            modelsolutions.appendChild(msElem);
            let filerefs = xmlDoc.createElementNS(xmlns, "filerefs");
            msElem.appendChild(filerefs);
            item.filerefs.forEach(writeFileref);
            // remove filerefs is no fileref available
            let childs = filerefs.getElementsByTagName('fileref');
            if (childs.length === 0) {
                msElem.removeChild(filerefs);
            }
            xmlWriter.createOptionalTextElement(msElem, 'description', item.description);
            xmlWriter.createOptionalTextElement(msElem, 'internal-description', item.comment);
        }

        function writeTest(item, index) {
            function writeFileref(file, index) {
                if (file.refid) {
                    let fileref = xmlDoc.createElementNS(xmlns, "fileref");
                    fileref.setAttribute("refid", file.refid);
                    filerefs.appendChild(fileref);
                }
            }
            //console.log('writeXml: create ' + item.title);

            let testElem = xmlDoc.createElementNS(xmlns, "test");
            testElem.setAttribute("id", item.id);
            xmlWriter.createTextElement(testElem, 'title', item.title);
            xmlWriter.createOptionalTextElement(testElem, 'description', item.description);
            xmlWriter.createOptionalTextElement(testElem, 'internal-description', item.comment);
            xmlWriter.createTextElement(testElem, 'test-type', item.testtype);
            let config = xmlDoc.createElementNS(xmlns, "test-configuration");
            testElem.appendChild(config);
            let filerefs = xmlDoc.createElementNS(xmlns, "filerefs");
            config.appendChild(filerefs);
            item.filerefs.forEach(writeFileref);
            // remove filerefs is no fileref available
            let childs = filerefs.getElementsByTagName('fileref');
            if (childs.length === 0) {
                config.removeChild(filerefs);
            }

            tests.appendChild(testElem);
            if (item.configItem) {
                // alert('config write xml');
                item.configItem.onWriteXml(item, item.uiElement, config, xmlDoc, xmlWriter, task);
            }
        }

        function writeGradingTest(item, index) {
            let testElem = xmlDoc.createElementNS(xmlns, "test-ref");
            testElem.setAttribute("weight", item.weight);
            testElem.setAttribute("ref", item.id);
            gradingRoot.appendChild(testElem);
        }

        function writeFileRestriction(item, index) {
            //let regexp = xmlWriter.createTextElement(submission, "regexp-restriction", this.filenameRegExpSubmission);
            //submission.appendChild(regexp);
            // regexp.setAttribute("mime-type-regexp", this.mimeTypeRegExpSubmission);


            let fileElem = //xmlDoc.createElementNS(xmlns, "file-restriction");
            xmlWriter.createOptionalTextElement(fileRestrictions, 'file-restriction', item.restriction, xmlns);
            if (!item.required) // optional, defaults to true
                fileElem.setAttribute("required", item.required);

            if (item.format) // optional, defaults to none
                fileElem.setAttribute("pattern-format", item.format);

            //fileRestrictions.appendChild(fileElem);
        }


        try {

            if (topLevelDoc) {
                xmlDoc = topLevelDoc;
                task = xmlDoc.createElementNS(xmlns, "task");
                rootNode.appendChild(task);
            }
            else {
                xmlDoc = document.implementation.createDocument(xmlns, "task", null);
                task = xmlDoc.documentElement;
            }

            task.setAttribute("lang", this.lang);
            task.setAttribute("uuid", generateUUID());// this.uuid);
            //config.writeNamespaces(task);

            xmlWriter = new XmlWriter(xmlDoc, xmlns);

            xmlWriter.createTextElement(task, 'title', this.title);
            xmlWriter.createCDataElement(task, 'description', this.description);
            xmlWriter.createOptionalCDataElement(task, 'internal-description', this.comment);
            let proglang = xmlWriter.createTextElement(task, 'proglang', this.proglang);
            proglang.setAttribute("version", this.proglangVersion);

            fileRestrictions = xmlDoc.createElementNS(xmlns, "submission-restrictions");
            if (this.sizeSubmission)
                fileRestrictions.setAttribute("max-size", this.sizeSubmission);
            task.appendChild(fileRestrictions);
            this.fileRestrictions.forEach(writeFileRestriction);

            files = xmlDoc.createElementNS(xmlns, "files");
            task.appendChild(files);
            writeCodeSkeleton(this, 'codeskeleton');
            this.files.forEach(writeFile);

            modelsolutions = xmlDoc.createElementNS(xmlns, "model-solutions");
            task.appendChild(modelsolutions);
            this.modelsolutions.forEach(writeModelSolution);

            tests = xmlDoc.createElementNS(xmlns, "tests");
            task.appendChild(tests);
            const length = this.tests.length;
            this.tests.forEach(writeTest);

            // grading-hints
            let gradinghints = xmlDoc.createElementNS(xmlns, "grading-hints");
            task.appendChild(gradinghints);
            gradingRoot = xmlDoc.createElementNS(xmlns, "root");
            gradingRoot.setAttribute("function", "sum");
            gradinghints.appendChild(gradingRoot);
            this.tests.forEach(writeGradingTest);

            let metadata = xmlDoc.createElementNS(xmlns, "meta-data");
            task.appendChild(metadata);
            config.writeXmlExtra(metadata, xmlDoc, xmlWriter);
            //xmlWriter.createTextElement(metadata, 'praktomat:allowed-upload-filename-mimetypes', '(text/.*)');

            let serializer = new XMLSerializer();
            let result = serializer.serializeToString (xmlDoc);

            if ((result.substring(0, 5) !== "<?xml")){
                result = '<?xml version="1.0"?>' + result;
                // result = "<?xml version='1.0' encoding='UTF-8'?>" + result;
            }

            let xsds = [ 'xsd/proforma.xsd' ];
            // do not add all xsds from configuration because not all of them may be used
            // resulting in an error message
            xsds = xsds.concat(config.xsds);


            if (!topLevelDoc) { // do not validate for XML part
                // validate output
                xsds.forEach(function (xsd_file, index) {
                    $.get(xsd_file, function (data, textStatus, jqXHR) {      // read XSD schema
                        const valid = xmllint.validateXML({xml: result /*xmlString*/, schema: jqXHR.responseText});
                        if (valid.errors !== null) {                                // does not conform to schema
                            //alert(xsd_file);
                            setErrorMessage("Errors in XSD-Validation " + xsd_file + ":");
                            valid.errors.some(function (error, index) {
                                setErrorMessage(error);
                                return index > 15;
                            })
                        }
                    }).fail(function (jqXHR, textStatus, errorThrown) {
                        setErrorMessage("XSD-Schema " + xsd_file + " not found.", errorThrown);
                    });
                });
            }

            return result;
        } catch (err){
            setErrorMessage("Error creating task xml file.", err);
            return '';
        }
    }
}
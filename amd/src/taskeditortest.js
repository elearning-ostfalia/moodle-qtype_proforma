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
 * functions for representing a test in taskeditor.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm
 * @author     Dr.U.Priss
 */

import {TestFileReference, FileReferenceList, ModelSolutionFileReference} from "./filereflist";
import {setcounter, DEBUG_MODE} from "./taskeditorutil";
import {TaskClass} from "./taskeditortaskdata";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';


export var testIDs = {};


export class TestWrapper {
    static constructFromRoot(root) {
        let test = new TestWrapper();
        test._root = root;
        return test;
    }


    static constructFromId(id) {
        // this._id = id;
        let test = new TestWrapper();
        test._root = $("#test_" + id);
        if (test.root.length === 0)
            return undefined; // no element with id found
        return test;
    }


    getValue(member, xmlClass) {
        if (!member) {
            member = this.root.find(xmlClass).first();
        }
        return member.val();
    }

    // getter
    get root() { return this._root; }
    get id() { return this.getValue(this._id,".xml_test_id" ); }
    get title() { return this.getValue(this._id,".xml_test_title" ); }
    get comment() { return this.getValue(this._comment,".xml_internal_description"); }
    get description() { return this.getValue(this._description,".xml_description" ); }
    get testtype() { return this.getValue(this._type,".xml_test_type" ); }
    get weight() { return this.getValue(this._type,".xml_test_weight" ); }

    // setter
    set comment(newComment) { this._root.find(".xml_internal_description").val(newComment); }
    set description(newDescription) { this._root.find(".xml_description").val(newDescription); }
    set weight(newWeight) { this._root.find(".xml_test_weight").val(newWeight); }

    static doOnAll(callback) {
        // todo: iterate through all tests in variable
        $.each($(".xml_test_id"), function (indexOpt, item) {
            let test = TestWrapper.constructFromId(item.value);
            callback(test, indexOpt);
        });
    }

    delete() {
        // iterate through all referenced files and remove the references
        // => checks whether the file can be removed
        FileReferenceList.doOnAllElements(this.root, function(fileref_element) {
            let row = $(fileref_element).closest('tr');
            row.find('.remove_item').first().click();
        });

        delete testIDs[this.id];
        this.root.remove();
    }

    static delete(button) {
        if (window.confirm("Shall the test and all its files be removed from task?")) {
            let instance = TestWrapper.constructFromRoot(button.closest('.xml_test'));
            // remove instance
            instance.delete();
        }
    }

    /**
     *
     * @param id test identfier
     * @param template mustache template name
     * @param context context for mustache template
     * @param withFileRef with file references
     * @param item object to create from
     */
    static createFromTemplate(id, template, context, withFileRef, item, task) {
        let testid = id;
        if (!testid)
            testid = setcounter(testIDs);
        context['testid'] = testid;
        console.log("context for rendering template " + template);
        console.log(context);
        let test = undefined;

        return Templates.renderForPromise(template, context)
            .then(({html, js}) => {
                // console.log(html);
                Templates.appendNodeContents('#proforma-tests-section', html, js);

                // hide fields that exist only for technical reasons
                const testroot = $("#test_" + testid);

                test = TestWrapper.constructFromRoot(testroot);

                let subnode = $(testroot)[0].querySelector('.test-header');
                if (!subnode)
                    console.error('could not find subnode .test-header');
                else {
                    subnode.ondragstart = function (event) {
                        // event.preventDefault();
                        console.log('On drag start ' + testid);
                        event.dataTransfer.setData("text", 'move_test '  + testid);
                    }
                }

                FileReferenceList.init(null, null, TestFileReference, testroot);
                testroot.find('.dynamic_table').show();
                FileReferenceList.addCallbacks($(testroot)[0]);
                console.log('callback delete test button');
                testroot.find('button').first().on("click",
                    function(event) {
                        event.preventDefault();
                        TestWrapper.delete($(this));
                    });

                // TestFileReference.getInstance().init(testroot, DEBUG_MODE);

/*                if (!DEBUG_MODE) {
                    console.log('hide debug fields');
                    testroot.find(".xml_test_type").hide();
                    testroot.find("label[for='xml_test_type']").hide();
                    testroot.find(".xml_test_id").hide();
                    testroot.find("label[for='xml_test_id']").hide();
                }*/
                if (!withFileRef) {
                    console.log('hide fileref fields');
                    testroot.find("table").hide();
                    testroot.find(".drop_zone").hide();
                }
                else
                {
                    // TODO: disable drag & drop!
                    /*
                            testroot.on({
                                drop: function(e){
                                    if(e.originalEvent.dataTransfer){
                                        if(e.originalEvent.dataTransfer.files.length) {
                                            e.preventDefault();
                                            e.stopPropagation();
                                            //UPLOAD FILES HERE
                                            FileReferenceList.uploadFiles(e.originalEvent.dataTransfer.files, e.currentTarget,
                                                TestFileReference.getInstance());
                                        }
                                    }
                                }
                            });
                    */
                }
                if (item) {
                    console.log('update filelist for test');
                    let counter = 0;
                    console.log(item.filerefs);

                    item.filerefs.forEach(function(itemFileref, indexFileref) {
                        console.log('id ' + itemFileref.refid);
                        let filename = task.findFilenameForId(itemFileref.refid);
                        console.log('filename ' + filename);
                        let promiseFactories = [TestFileReference.getInstance().setFilenameOnCreation(test.root, counter++, filename)];
                        Promise.all(promiseFactories)
                            .then(() => {
                                console.log("promise completed");
                            })
                    });
                }
            })
            .catch((error) => { displayException(error); });
        // return test;
    }
}


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
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm
 * @author     Dr.U.Priss
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */

import $ from 'jquery';
import {TestFileReference, FileReferenceList} from "./filereflist";
import {setcounter} from "./helper";
import {exception as displayException, deleteCancelPromise} from 'core/notification';
import Templates from 'core/templates';
import * as Str from 'core/str';

export var testIDs = {};


export class TestWrapper {
    static constructFromRoot(root) {
        let test = new TestWrapper();
        test._root = root;
        return test;
    }


    static constructFromId(id) {
        // This._id = id;
        let test = new TestWrapper();
        test._root = $("#test_" + id);
        if (test.root.length === 0) {
            return undefined;
        } // No element with id found
        return test;
    }

    getValue(member, xmlClass) {
        if (!member) {
            const elem = this.root.find(xmlClass);
            if (!elem) {
                return undefined;
            }
            member = elem.first();
        }
        return member.val();
    }

    // Getter
    get root() {
        return this._root;
    }
    get id() {
        return this.getValue(this._id, ".xml_test_id");
    }
    get title() {
        return this.getValue(this._id, ".xml_test_title");
    }
    get comment() {
        return this.getValue(this._comment, ".xml_internal_description");
    }
    get description() {
        return this.getValue(this._description, ".xml_description");
    }
    get testtype() {
        return this.getValue(this._type, ".xml_test_type");
    }
    get weight() {
        return this.getValue(this._type, ".xml_test_weight");
    }
    get framework() {
        return this.getValue(this._type, ".xml_framework");
    }

    // Setter
    set comment(newComment) {
        this._root.find(".xml_internal_description").val(newComment);
    }
    set description(newDescription) {
        this._root.find(".xml_description").val(newDescription);
    }
    set weight(newWeight) {
        this._root.find(".xml_test_weight").val(newWeight);
    }
    set title(newTitle) {
        this._root.find(".xml_test_title").val(newTitle);
    }

    static doOnAll(callback) {
        // Todo: iterate through all tests in variable
        $.each($(".xml_test_id"), function(indexOpt, item) {
            let test = TestWrapper.constructFromId(item.value);
            callback(test, indexOpt);
        });
    }

    delete() {
        // Iterate through all referenced files and remove the references
        // => checks whether the file can be removed
        FileReferenceList.doOnAllElements(this.root, function(filerefElement) {
            let row = $(filerefElement).closest('tr');
            row.find('.remove_item').first().click();
        });

        delete testIDs[this.id];
        this.root.remove();
    }

    static delete(button) {
        deleteCancelPromise(
            Str.get_string('confirm', 'core'),
            Str.get_string('confirmdeletetest', 'qtype_proforma'),
            Str.get_string('delete', 'core'),
            {triggerElement: button[0]}
        )
            .then(() => {
                let instance = TestWrapper.constructFromRoot(button.closest('.xml_test'));
                // Remove instance
                instance.delete();
                return true;
            })
            .catch((error) => {
                // Rejection without an Error means the user cancelled the dialog.
                if (error instanceof Error) {
                    displayException(error);
                }
            });
    }

    /**
     * Creates a test in the task editor from a mustache template.
     *
     * @param {string} id test identfier
     * @param {string} template mustache template name
     * @param {Object} context context for mustache template
     * @param {boolean} withFileRef with file references
     * @param {Object} item object to create from
     * @param {Object} task task object
     * @returns {Promise}
     */
    static createFromTemplate(id, template, context, withFileRef, item, task) {
        let testid = id;
        if (!testid) {
            testid = setcounter(testIDs);
        }
        context.testid = testid;
        let test;

        return Templates.renderForPromise(template, context)
            .then(({html, js}) => {
                Templates.appendNodeContents('#proforma-tests-section', html, js);

                // Hide fields that exist only for technical reasons
                const testroot = $("#test_" + testid);

                test = TestWrapper.constructFromRoot(testroot);

                let subnode = $(testroot)[0].querySelector('.test-header');
                if (!subnode) {
                    console.error('could not find subnode .test-header');
                } else {
                    subnode.ondragstart = function(event) {
                        // Event.preventDefault();
                        event.dataTransfer.setData("text", 'move_test ' + testid);
                    };
                }

                FileReferenceList.init(null, null, TestFileReference, testroot);
                testroot.find('.dynamic_table').show();
                FileReferenceList.addCallbacks($(testroot)[0]);
                // Add callback for delete button
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
                    testroot.find("table").hide();
                    testroot.find(".drop_zone").hide();
                } else {
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
                    let counter = 0;
                    const promises = item.filerefs.map(itemFileref => {
                        let filename = task.findFilenameForId(itemFileref.refid);
                        return TestFileReference.getInstance().setFilenameOnCreation(test.root, counter++, filename);
                    });
                    return Promise.all(promises);
                }
                return true;
            })
            .catch((error) => {
                displayException(error);
            });
        // Return test;
    }
}


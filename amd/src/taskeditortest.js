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
 * functions for uploading a task.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

import {TestFileReference, FileReferenceList } from "./filereflist";
import {CustomTest, setcounter, DEBUG_MODE, getDescriptionHtmlString} from "./taskeditorutil";
import {testTypes} from "./taskeditorhelper";
import Notification, {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import {config} from "./taskeditorconfig";


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
        let instance = TestWrapper.constructFromRoot(button.closest('.xml_test'));
        // remove instance
        instance.delete();
    }

    /**
     *
     * @param id test identfier
     * @param template mustache template name
     * @param context context for mustache template
     * @param withFileRef with file references
     */
    static createFromTemplate(id, template, context, withFileRef) {
        let testid = id;
        if (!testid)
            testid = setcounter(testIDs);
        context['testid'] = testid;
        console.log("context");
        console.log(context);
        let test = undefined;

        Templates.renderForPromise(template, context)
            .then(({html, js}) => {
                Templates.appendNodeContents('#proforma-tests-section', html, js);

                // hide fields that exist only for technical reasons
                var testroot = $("#test_" + testid);

                // testroot.find(".xml_test_type").val(config.testType);
                test = TestWrapper.constructFromRoot(testroot);

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

                if (!DEBUG_MODE) {
                    console.log('hide debug fields');
                    testroot.find(".xml_test_type").hide();
                    testroot.find("label[for='xml_test_type']").hide();
                    testroot.find(".xml_test_id").hide();
                    testroot.find("label[for='xml_test_id']").hide();
                }
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
            })
            .catch((error) => { displayException(error); });
        return test;
    }

//    static create(id, TestName, MoreText, TestType, WithFileRef) {
    // create a new test HTML form element
    /*
    static create(id, TestName, config, weight) {
        let theWeight =  config.gradingWeight;
        if (typeof weight !== 'undefined')
            theWeight = weight;

        let testid = id;
        if (!testid)
            testid = setcounter(testIDs);

        TestFileReference.getInstance().createTableString('TestFileReference', config.fileRefLabel,
            config.manadatoryFile, 'xml_fileref_table');
        $("#proforma-tests-section").append("<div "+
            "id='test_" + testid + "'" +
            "class='ui-widget ui-widget-content ui-corner-all xml_test'>"+
            "<h3 class='ui-widget-header'>" + TestName + " (Test #"+testid+")<span "+
            "class='rightButton'><button>x</button></span></h3>"+

            "<p><label for='xml_test_id'>ID<span class='red'>*</span>: </label>"+
            "<input class='tinyinput xml_test_id' value='" + testid + "' readonly/>"+
            //    " <label for='xml_test_validity'>Validity: </label>"+
            //    "<input class='shortinput xml_test_validity'/>"+
            "<p><label for='xml_test_type'>Type: </label>"+
            "<select class='xml_test_type'>"+ testTypes + "</select>"+

            "</p>" +

            "<p><label for='xml_test_title'>Title<span class='red'>*</span>: </label>"+
            "<input class='maxinput xml_test_title' value='"+ TestName +"'/>" +
            "</p>"+
            getDescriptionHtmlString('', '') +

            config.getExtraHtmlField() +
            "<p>" + TestFileReference.getInstance().getTableString() + "</p>" +

            "<p><label>Grading Weight<span class='red'>*</span>:</label>"+
            "<input class='tinyinput xml_test_weight' value='"+ theWeight +"'/>" +
            "</p>"+

            "</div>");

        // hide fields that exist only for technical reasons
        var testroot = $("#test_" + testid);
        // var testroot = $(".xml_test_id[value='" + testid + "']").parent().parent();
        testroot.find(".xml_test_type").val(config.testType);
        let test = TestWrapper.constructFromRoot(testroot);
        console.log(testroot);
        console.log(test);

        FileReferenceList.init(null, null, TestFileReference, testroot);
        FileReferenceList.addCallbacks($(testroot)[0]);
        testroot.find('button').first().on("click",
            function(event) {
                event.preventDefault();
                TestWrapper.delete($(this));
            });

        // TestFileReference.getInstance().init(testroot, DEBUG_MODE);

        if (!DEBUG_MODE) {
            testroot.find(".xml_test_type").hide();
            testroot.find("label[for='xml_test_type']").hide();
            testroot.find(".xml_test_id").hide();
            testroot.find("label[for='xml_test_id']").hide();
        }
        if (!config.withFileRef) {
            testroot.find("table").hide();
            testroot.find(".drop_zone").hide();
        }

        return test;
    };

     */

}


export const test = (testid, filereftableid) => {

    function init(testid, filereftableid) {
        /*
        let config = new CustomTest("JUnit_Default_Title", "unittest", "", ['java']);
        let ui_test = TestWrapper.create(testid, 'Dummy title', config, 7);

         */
    }

    // console.log('create test instance');
    init(testid, filereftableid);
};
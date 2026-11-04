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
 * Helper functions for reading and writen task
 *
 * @package
 * @subpackage proforma
 * @copyright  2023 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     eCULT-Team of Ostfalia University, K.Borm, Dr.U.Priss
 */

import $ from 'jquery';
import {ModelSolutionFileReference, FileReferenceList} from './filereflist';
import {DEBUG_MODE, setcounter} from "./helper";
import Templates from 'core/templates';
import {exception as displayException, alert as displayAlert} from 'core/notification';
import * as Str from 'core/str';

export var modelSolIDs = {};

// TODO : common base class with TestWrapper
export class ModelSolutionWrapper {

    static constructFromRoot(root) {
        let ms = new ModelSolutionWrapper();
        ms._root = root;
        return ms;
    }

    static constructFromId(id) {
        // This._id = id;
        let ms = new ModelSolutionWrapper();
        ms._root = $("#modelsolution_" + id);
        if (ms.root.length === 0) {
            return undefined;
        } // No element with id found
        return ms;
    }

    getValue(member, xmlClass) {
        if (!member) {
            member = this.root.find(xmlClass).first();
        }
        return member.val();
    }

    // Getter
    get root() {
        return this._root;
    }
    get id() {
        return this.getValue(this._id, ".xml_model-solution_id");
    }
    get comment() {
        return this.getValue(this._comment, ".xml_internal_description");
    }
    get description() {
        return this.getValue(this._description, ".xml_description");
    }

    // Setter
    set comment(newComment) {
        this._root.find(".xml_internal_description").val(newComment);
    }
    set description(newDescription) {
        this._root.find(".xml_description").val(newDescription);
    }


    static doOnAll(callback) {
        // Todo: iterate through all modelsolutions in variable
        $.each($(".xml_model-solution_id"), function(indexOpt, item) {
            let modelsolution = ModelSolutionWrapper.constructFromId(item.value);
            return callback(modelsolution);
        });
    }

    delete() {
        // Iterate through all referenced files and remove the references
        // => checks whether the file can be removed
        FileReferenceList.doOnAllElements(this.root, function(filerefElement) {
            let row = $(filerefElement).closest('tr');
            row.find('.remove_item').first().click();
        });

        delete modelSolIDs[this.id];
        this.root.remove();
    }

    static delete(button) {
        if (document.querySelectorAll('.xml_model-solution').length == 1) {
            displayAlert('', 'There must be at least one model solution');
            return;
        }
        let instance = ModelSolutionWrapper.constructFromRoot(button.closest('.xml_model-solution'));
        // Remove instance
        instance.delete();
    }

    static createFromTemplate(id, description, comment, item, task) {
        if (!comment) {
            comment = '';
        }
        if (!description) {
            description = '';
        }

        let modelsolid = id;
        if (!modelsolid) {
            modelsolid = setcounter(modelSolIDs); // Adding a file for the test
        } else {
            // This means that it is created with a known id
            // (from reading task.xml). So we nned to keep the modelSolIDs in sync!
            modelSolIDs[modelsolid] = 1;
        }

        let strings = [
            {key: 'taskeditorfiles', component: 'qtype_proforma'},
            {key: 'comment', component: 'qtype_proforma'}
        ];
        return FileReferenceList.getLocalisedStrings()
            .then(() => Str.get_strings(strings))
            .then(results => {
                let context = {
                    'msid': modelsolid,
                    'testtitle': 'TODO Model Solution titel',
                    'filenamelabel': results[0],
                    'description': description
                };
                return Templates.renderForPromise('qtype_proforma/taskeditor_modelsol', context);
            })
            .then(({html, js}) => {
                Templates.appendNodeContents('#proforma-model-solution-section', html, js);

                // Hide fields that exist only for technical reasons
                const msroot = $("#modelsolution_" + modelsolid);
                let ms = ModelSolutionWrapper.constructFromRoot(msroot);

                FileReferenceList.init(null, null, ModelSolutionFileReference, msroot);
                FileReferenceList.addCallbacks($(msroot)[0]);
                msroot.find('button').first().on("click",
                    function(event) {
                        event.preventDefault();
                        ModelSolutionWrapper.delete($(this));
                    });

                if (!DEBUG_MODE) {
                    // Hide fields that exist only for technical reasons
                    msroot.find(".xml_model-solution_id").hide();
                    msroot.find("label[for='xml_model-solution_id']").hide();
                }

                if (!item) {
                    return ms;
                }
                let counter = 0;
                const promises = item.filerefs.map(function(itemFileref) {
                    let filename = task.findFilenameForId(itemFileref.refid);
                    return ModelSolutionFileReference.getInstance().setFilenameOnCreation(ms.root, counter++, filename);
                });
                return Promise.all(promises);
            })
            .catch((error) => {
                displayException(error);
            });
    }
}
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

import {ModelSolutionFileReference, FileReferenceList} from './filereflist'
import {DEBUG_MODE} from "./taskeditorutil";
import Templates from 'core/templates';
import {exception as displayException} from 'core/notification';
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
        // this._id = id;
        let ms = new ModelSolutionWrapper();
        ms._root = $("#modelsolution_" + id);
        if (ms.root.length === 0)
            return undefined; // no element with id found
        return ms;
    }

    getValue(member, xmlClass) {
        if (!member) {
            member = this.root.find(xmlClass).first();
        }
        return member.val();
    }

    // getter
    get root() { return this._root; }
    get id() { return this.getValue(this._id,".xml_model-solution_id" ); }
    get comment() { return this.getValue(this._comment,".xml_internal_description"); }
    get description() { return this.getValue(this._description,".xml_description" ); }

    // setter
    set comment(newComment) {
        this._root.find(".xml_internal_description").val(newComment);
    }
    set description(newDescription) { this._root.find(".xml_description").val(newDescription); }


    static doOnAll(callback) {
        // todo: iterate through all modelsolutions in variable
        $.each($(".xml_model-solution_id"), function (indexOpt, item) {
            let modelsolution = ModelSolutionWrapper.constructFromId(item.value);
            return callback(modelsolution);
        });
    }

    delete() {
        // iterate through all referenced files and remove the references
        // => checks whether the file can be removed
        FileReferenceList.doOnAllElements(this.root, function(fileref_element) {
            let row = $(fileref_element).closest('tr');
            row.find('.remove_item').first().click();
        });

        delete modelSolIDs[this.id];
        this.root.remove();
    }

    static delete(button) {
        if (document.querySelectorAll('.xml_model-solution').length == 1) {
            window.alert('There must be at least one model solution');
            return;
        }
        let instance = ModelSolutionWrapper.constructFromRoot(button.closest('.xml_model-solution'));
        // remove instance
        instance.delete();
    }

    static createFromTemplate(id, description, comment, item, task) {
        if (!comment)
            comment = '';
        if (!description)
            description = '';

        let modelsolid = id;
        if (!modelsolid) {
            modelsolid = setcounter(modelSolIDs);    // adding a file for the test
        } else {
            // this means that it is created with a known id
            // (from reading task.xml). So we nned to keep the modelSolIDs in sync!
            modelSolIDs[modelsolid] = 1;
        }

        let strings = [
            { key: 'taskeditorfiles', component: 'qtype_proforma' },
            { key: 'comment', component: 'qtype_proforma' }
        ];
        return FileReferenceList.getLocalisedStrings()
            .then(() => Str.get_strings(strings))
            .then(results => {
                let context = {
                    'msid': modelsolid,
                    'testtitle' : 'TODO Model Solution titel',
                    'filenamelabel' : results[0]
                };
                return Templates.renderForPromise('qtype_proforma/taskeditor_modelsol', context);
            })
            .then(({html, js}) => {
                console.log('model sol template rendered');
                Templates.appendNodeContents('#proforma-model-solution-section', html, js);
                console.log('model sol template appended');

                // hide fields that exist only for technical reasons
                const msroot = $("#modelsolution_" + modelsolid);
                let ms = ModelSolutionWrapper.constructFromRoot(msroot);

                FileReferenceList.init(null, null, ModelSolutionFileReference, msroot);
                console.log('Add callbacks to fileref table in Modelsol ');
                FileReferenceList.addCallbacks($(msroot)[0]);
                console.log('callback delete ms button');
                msroot.find('button').first().on("click",
                    function(event) {
                        event.preventDefault();
                        ModelSolutionWrapper.delete($(this));
                    });

                if (!DEBUG_MODE) {
                    // hide fields that exist only for technical reasons
                    msroot.find(".xml_model-solution_id").hide();
                    msroot.find("label[for='xml_model-solution_id']").hide();
                }

                if (item) {
                    console.log('update filelist for model sol');
                    let counter = 0;
                    console.log(item.filerefs);
                    item.filerefs.forEach(function(itemFileref, indexFileref) {
                        let filename = task.findFilenameForId(itemFileref.refid);
                        let promiseFactories = [ModelSolutionFileReference.getInstance().setFilenameOnCreation(ms.root, counter++, filename)];
                        Promise.all(promiseFactories)
                            .then(() => {
                                console.log("promise completed");
                            })
                    });
                }
            })
            .catch((error) => { displayException(error); });
    }
}
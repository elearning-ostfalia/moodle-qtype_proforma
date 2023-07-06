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
 * Display explorer/multitab editor.
 *
 * @package    qtype_proforma
 * @copyright  2022 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint-disable no-unused-vars */

import { Framework } from "./FileViewer";
import { MoodleQuestionAttemptSyncer, MoodleSyncer } from "./MoodleSyncer";
// import * as FormChangeChecker from 'core_form/changechecker';

function _start(nodename, options) {
    console.log('start for ' + nodename);
    const explorer = document.getElementById(nodename);

    let framework = new Framework();
    framework.buildFramework(explorer);
    // let submission = new RootNode('Submission', framework);
    if (options['readonly']) {
        console.log('create readonly framework');
        let syncer = new MoodleQuestionAttemptSyncer(options);
        framework.init(explorer, syncer, true, options);
    } else {
        console.log('create readwrite framework');
        let syncer = new MoodleSyncer(options);
        framework.init(explorer, syncer, false, options);
    }

    // Change submit function: Save before submit!
    // There is only one form in case of several questions per page
    // as all of the buttons are secondary buttons.
    // A common parent has class
    /* let form = explorer.closest('form');
    form.onsubmit = (event) => {
        // alert('form submit');
        // console.log('save before submit');
        // framework.save(); // synchronous action!
    }; */

    console.log('change submit function');
    let parent = explorer.closest('.que');
    let submitbutton = parent.querySelector('input[type="submit"]');
    if (submitbutton !== null) {
        // In student review there will be no button!
        submitbutton.onclick = (event) => {
            console.log('save before submit');
            // alert('button submit');
            framework.save(); // synchronous action!
        };
    }

    if (!options['readonly']) {
        // Mark form as dirty as there is no other change detection...
        // This does not work as disableAllChecks() is called somewhere else
        // which ignores all checks
        // There is a quiz-automsave which might intercept the default form change detection

/*        FormChangeChecker.startWatching();
        FormChangeChecker.watchForm(explorer);
        FormChangeChecker.markFormAsDirty(explorer);
        console.log('responseform changechecker...');
        let responseForm = explorer.closest('form');
        console.log(responseForm.dataset);
        console.log(responseForm.dataset.formDirty);
        console.log(responseForm.dataset.formSubmitted);
*/
        // If there is an onbeforeunload it will ask
        // even for normal Praktomat checks!

        /*
        console.log('set onbeforeunload');
        window.onbeforeunload = function(event) {
            // window.onbeforeunload = function() {
            // Hook for saving intermediate changes on leaving the page...
            event.preventDefault();
            console.log('Save framework');
            // framework.save(); // synchronous action!
            framework.editorstack.saveCurrentEditor(false);
            console.log('Framework saved');
            // alert('Framework saved');
            // This function disables everything if confirmed and page is turned.
            // So this does not make sense this way.
            // return confirm('Are you sure you want to leave?');
            event.returnValue = null;
        };*/
    } else {
        // console.log('NO onbeforeunload function');
    }



/*
    Promise.all([
        import('./FileViewer.js')
//    import('/amd/src/FileViewer.js') // inside Moodle
    ])
        .then(([
            fileviewer]) => {
            const explorer = document.getElementById(nodename);
//    const explorer = document.getElementById('fileexplorer');
            fileviewer.ProjectNode.buildFramework(explorer);
            let submission = new fileviewer.ProjectNode('Submission');
            fileviewer.ProjectNode.init(explorer);
        });

 */
}

export const createExplorer = (nodename, options) => {
    console.log('createExplorer called');


    // We must wait for the document to be ready.
    // Otherwise Codemirror and other controls might not yet be available.
    // Note that Codemirror is created asynchronously after document ready.
    // So this is not enough when something has to be done with Codemirror.
    if( document.readyState !== 'loading' ) {
        _start(nodename, options);
    } else {
        document.addEventListener("DOMContentLoaded", function() {
            _start(nodename, options);
        });
    }
};



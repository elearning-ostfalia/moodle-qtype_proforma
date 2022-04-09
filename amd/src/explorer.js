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

function _start(nodename, options) {
    console.log('start for ' + nodename);

    const explorer = document.getElementById(nodename);

    let framework = new Framework();
    framework.buildFramework(explorer);
    // let submission = new RootNode('Submission', framework);
    if (options['readonly']) {
        console.log('create readonly framework');
        let syncer = new MoodleQuestionAttemptSyncer(options);
        framework.init(explorer, syncer, true);
    } else {
        console.log('create readwrite framework');
        let syncer = new MoodleSyncer(options);
        framework.init(explorer, syncer, false);
    }

    // Change submit function: Save before submit!
    // VORSICHT: DAS MUSS GETESTET WERDEN MIT 2 oder mehr Frameworks!!
    console.log('change submit function');
    let form = explorer.closest('form');
    console.log(form);
    let saveSubmit = (event) => {
        console.log('save before submit');
        // event.preventDefault();
        framework.save(); // synchronous action!
/*
            .then(() => {
                console.log('do submit');
                // alert('jetzt sollte die Bewertung erfolgen. Aber das klappt nicht.');
                let button = form.querySelector('input[type="submit"]');
                // see qengine.js!!!!
                // Da ist eine Sperre gegen mehrfaches submitten :-(
                button.disabled = false;
                form.submit();
            });*/
    };
    form.onsubmit = saveSubmit;

    /*
    let submitbutton = explorer.parentNode.parentNode.parentNode.querySelector('.submit');
    if (submitbutton === undefined) {
        alert('cannot find submit button');
    } else {
        // Add hook for saving files before submission
        //
        // let new_element = old_element.cloneNode(true);
        // submitbutton.parentNode.replaceChild(new_element, submitbutton);
        submitbutton.addEventListener('click', event => {
            // event.preventDefault();
            // event.stopPropagation();
            // alert('CLICKED');
            console.log('CLICKED');
            framework.save();
        });
    }*/


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



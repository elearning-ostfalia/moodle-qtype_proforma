

/* global Treeitem */

'use strict';

/**
 * ARIA Treeview example
 *
 * @function onload
 * @description  after page has loaded initialize all treeitems based on the role=treeitem
 */

// import {ProjectNode} from "./FileViewer";


// import {Framework, RootNode} from "./FileViewer";

// import { Syncer } from "./MoodleSyncer";

Promise.all([
//    import('/amd/src/Tree.js'),
    import('./FileViewer.js')
//    import('/amd/src/FileViewer.js') // inside Moodle
])
    .then(([
//        tree,
               fileviewer]) => {
        console.log("editor.js script started");

        function docReady(fn) {
            // see if DOM is already available
            if (document.readyState === "complete" || document.readyState === "interactive") {
                // call on next available tick
                setTimeout(fn, 1);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        function _open() {
            console.log('initialise elements');

            // # 1
            const options1 = [];

            const explorer1 = document.getElementById('fileexplorer1');
            let syncer1 = new fileviewer.Syncer(options1);
            let framework1 = new fileviewer.Framework();
            framework1.buildFramework(explorer1);
//            let submission1 = new fileviewer.RootNode('Submission', framework1);
            framework1.init(explorer1, syncer1, false);

            // # 2
            const options2 = [];
            const explorer2 = document.getElementById('fileexplorer2');
            let syncer2 = new fileviewer.Syncer(options2);
            let framework2 = new fileviewer.Framework();
            framework2.buildFramework(explorer2);
//            let submission2 = new fileviewer.RootNode('Submission', framework2);
            framework2.init(explorer2, syncer2, true);

            console.log('change submit function');
            let form = explorer1.closest('form');
            console.log(form);
            let originalSubmit = form.submit;
            console.log(form.submit);
            try {
                let wrappedSubmit = (e) => {
                    e.preventDefault();
                    // e.preventDefault();
                    // window.history.back();
                    // form.action = '/action_page3.php';
                    // console.log('wrappedSubmit');
                    alert('save form');
                    setTimeout(() => {
                        alert('timeout expired');
                        form.onsubmit = originalSubmit;
                        form.submit();
                        form.onsubmit = wrappedSubmit;
                    }, 2000);
                };
                form.onsubmit = wrappedSubmit;
                // form.addEventListener('submit', wrappedSubmit);
            } catch(e) {}
            console.log(form.submit);
/*
            let button = form.querySelector('input[type="submit"]');
            console.log(button);
            console.log(button.click);
            button.onclick = () => {
                // e.preventDefault();
                // e.stopPropagation();
                alert('save buuton');
                return false;
            };
            console.log(button.click);
*/


            // Common files
//            let common = new fileviewer.ProjectNode('Common');


            /*
            function _initSplitview() {
                // Split view
                let mousedown = false;
                let resizer = document.getElementById('resize');
                let fileviewerwin = document.getElementById('fileviewer');
                resizer.addEventListener('mousedown', onMouseDown)

                function onMouseDown(event) {
                    mousedown = true;
                    document.body.addEventListener('mousemove', onMouseMove)
                    document.body.addEventListener('mouseup', onMouseUp)
                }

                function onMouseMove(event) {
                    if (mousedown) {
                        fileviewerwin.style.flexBasis = event.clientX + "px"
                    } else {
                        onMouseUp()
                    }
                }

                const onMouseUp = (e) => {
                    mousedown = false;
                    document.body.removeEventListener('mouseup', onMouseUp)
                    resizer.removeEventListener('mousemove', onMouseMove)
                }
            }

            _initSplitview();

             */


        }

        docReady(function() {
            console.log("doc ready");
            _open();
        });
});
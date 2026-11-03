

import {debugLog} from './debug.js';


/**
 * ARIA Treeview example
 *
 * @function onload
 * @description  after page has loaded initialize all treeitems based on the role=treeitem
 */


// import {Framework, RootNode} from "./FileViewer";

// import {FakeSyncer} from "./FakeSyncer";

Promise.all([
    import('./FileViewer.js'),
    import('./FakeSyncer.js')
//    Import('/amd/src/FileViewer.js') // inside Moodle
])
    .then(([fileviewer, fakesyncer]) => {
        debugLog("editor.js script started");

        /**
         * Calls the function as soon as the DOM is ready.
         *
         * @param {Function} fn function to be called
         */
        function docReady(fn) {
            // See if DOM is already available
            if (document.readyState === "complete" || document.readyState === "interactive") {
                // Call on next available tick
                setTimeout(fn, 1);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        /**
         * Creates the file explorers of the test page.
         */
        function _open() {
            debugLog('initialise elements');

            // # 1
            const options1 = [];

            const explorer1 = document.getElementById('fileexplorer1');
            let framework1 = new fileviewer.Framework();
            framework1.buildFramework(explorer1);

            let syncer1 = new fakesyncer.FakeSyncer(options1);
            //            Let submission1 = new fileviewer.RootNode('Submission', framework1);
            framework1.init(explorer1, syncer1, false);

            // # 2
            const options2 = [];
            const explorer2 = document.getElementById('fileexplorer2');
            let framework2 = new fileviewer.Framework();
            framework2.buildFramework(explorer2);

            let syncer2 = new fakesyncer.FakeSyncer(options2);
            //            Let submission2 = new fileviewer.RootNode('Submission', framework2);
            framework2.init(explorer2, syncer2, true);

            debugLog('change submit function');
            let form = explorer1.closest('form');
            debugLog(form);
            let originalSubmit = form.submit;
            debugLog(form.submit);
            try {
                let wrappedSubmit = (e) => {
                    e.preventDefault();
                    // E.preventDefault();
                    // window.history.back();
                    // form.action = '/action_page3.php';
                    alert('save form');
                    setTimeout(() => {
                        alert('timeout expired');
                        form.onsubmit = originalSubmit;
                        form.submit();
                        form.onsubmit = wrappedSubmit;
                    }, 2000);
                };
                form.onsubmit = wrappedSubmit;
                // Form.addEventListener('submit', wrappedSubmit);
            } catch (e) {}
            debugLog(form.submit);
/*
            Let button = form.querySelector('input[type="submit"]');
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
            debugLog("doc ready");
            _open();
        });
});
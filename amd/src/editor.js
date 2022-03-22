

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
            let framework1 = new fileviewer.Framework();
            framework1.buildFramework(explorer1);
            let submission1 = new fileviewer.RootNode('Submission', framework1);
            framework1.init(explorer1, options1);

            // # 2
            const options2 = [];
            const explorer2 = document.getElementById('fileexplorer2');
            let framework2 = new fileviewer.Framework();
            framework2.buildFramework(explorer2);
            let submission2 = new fileviewer.RootNode('Submission', framework2);
            framework2.init(explorer2, options2);

            // Create model solution
//            let modelsolution = new fileviewer.ProjectNode('Model Solution');
//            modelsolution.appendFile(new fileviewer.FileNode('MyString.java'));
//            modelsolution.appendFile(new fileviewer.FileNode('Helper.java'));

            // Create test
//            let test1 = new fileviewer.ProjectNode('Test 1');
/*            test1.appendFile(new fileviewer.FileNode('MyStringTest.java'));
            test1.appendFile(new fileviewer.FileNode('MyStringTest1.java'));
            test1.appendFolder(new fileviewer.FolderNode('data'));
            test1.folders[0].appendFile(new fileviewer.FileNode('input.txt'));

            // Common files
            let common = new fileviewer.ProjectNode('Common');
*/

//            let submission = new fileviewer.ProjectNode('Submission');

//            fileviewer.ProjectNode.init(explorer);

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
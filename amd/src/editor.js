

/* global Treeitem */

'use strict';

/**
 * ARIA Treeview example
 *
 * @function onload
 * @description  after page has loaded initialize all treeitems based on the role=treeitem
 */

// import {ProjectNode} from "./FileViewer";


Promise.all([
    import('/amd/src/Tree.js'),
    import('/amd/src/FileViewer.js')
])
    .then(([tree, fileviewer]) => {
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

            // Create model solution

            let modelsolution = new fileviewer.ProjectNode('Model Solution');
            modelsolution.files.push(new fileviewer.FileNode('MyString.java'));
            modelsolution.files.push(new fileviewer.FileNode('Helper.java'));

            // Create test
            let test1 = new fileviewer.ProjectNode('Test 1');
            test1.files.push(new fileviewer.FileNode('MyStringTest.java'));
            test1.files.push(new fileviewer.FileNode('MyStringTest1.java'));
            test1.folders.push(new fileviewer.FolderNode('data'));
            test1.folders[0].files.push(new fileviewer.FileNode('input.txt'));

            // Common files
            let common = new fileviewer.ProjectNode('Common');

            let fv = document.getElementById("fileviewer");
            fileviewer.ProjectNode.displayInTreeview(fv);

            var trees = document.querySelectorAll('[role="tree"]');

            for (var i = 0; i < trees.length; i++) {
                var t = new tree.Tree(trees[i]);
                t.init();
            }



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
        }

        docReady(function() {
            console.log("doc ready");
            _open();
        });



        /*
            window.addEventListener('load', function () {
    var trees = document.querySelectorAll('[role="tree"]');

    for (var i = 0; i < trees.length; i++) {
        var t = new Tree(trees[i]);
        t.init();
    }
});
*/

});
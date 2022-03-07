/*
 *   This content is licensed according to the W3C Software License at
 *   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 *   File:   Tree.js
 *
 *   Desc:   Tree widget that implements ARIA Authoring Practices
 *           for a tree being used as a file viewer
 */

/* global Treeitem */

'use strict';

/**
 * ARIA Treeview example
 *
 * @function onload
 * @description  after page has loaded initialize all treeitems based on the role=treeitem
 */

import('/amd/src/FileViewer.js')
    .then((fileviewer) => {
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
            var trees = document.querySelectorAll('[role="tree"]');

            for (var i = 0; i < trees.length; i++) {
                var t = new fileviewer.Tree(trees[i]);
                t.init();
            }

            console.log('add event listener');
            var treeitems = document.querySelectorAll('[role="treeitem"]');
            for (var i = 0; i < treeitems.length; i++) {
                treeitems[i].addEventListener('click', function (event) {
                    var treeitem = event.currentTarget;
                    var label = treeitem.getAttribute('aria-label');
                    if (!label) {
                        var child = treeitem.firstElementChild;
                        label = child ? child.innerText : treeitem.innerText;
                    }

                    document.getElementById('last_action').value = label.trim();

                    event.stopPropagation();
                    event.preventDefault();
                });
            }
        }

        docReady(function() {
            console.log("doc ready");
            _open();
        });


// import {TreeItem} from "./FileViewer";



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
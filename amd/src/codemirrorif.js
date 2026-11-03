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
 * The ProFormA Question CodeMirror support functions
 *
 * @package
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

/* eslint no-console: ["error", {"allow": ["error"]}] */


define(['jquery', 'jqueryui', 'qtype_proforma/codemirror', 'qtype_proforma/debug',
        'qtype_proforma/closebrackets', 'qtype_proforma/matchbrackets', 'qtype_proforma/active-line'
    ],
    function($, jqui, CodeMirror, Debug) {

    // Maps the programming language value used in PHP to the CodeMirror mode and
        // the corresponding javascript file to load
        let mapProglangToCodemirrorMode = function(moodleMode) {
            switch (moodleMode) {
            case "java": return ["text/x-java", 'qtype_proforma/clike'];
                case "python": return ["text/x-python", 'qtype_proforma/python'];
            case "setlx": return ["text/text", null];
            case "c": return ["text/x-csrc", 'qtype_proforma/clike'];
            case "cpp": return ["text/x-c++src", 'qtype_proforma/clike'];
                // With the actual xml mode the behat tests fail (hang in some scenarios
                // with an error message in the console output of the browser.
                // That's why I do not use xml mode anymore :-(
            case "xml": return ["", null]; // Return ["application/xml", 'qtype_proforma/xml'];
            case "none": return ["", null];
                default:
                    console.error("unsupported mode " + moodleMode + " for mapProglangToCodemirrorMode");
                    return ["text/text", null];
            }
        };


        return {

        // Function is called in edit_proforma_form. It triggers the CodeMirror mode switch
            // whenever the programming language changes
            switchMode: function(selectId, textareaId) {

            let changeMode = function(selectId, textareaId) {
                    var progLang = $("#" + selectId).val();
                // Map programming language to CodeMirror mode
                var newMode = mapProglangToCodemirrorMode(progLang);
                // Change mode in CodeMirror
                    require([newMode[1]], function() {
                        $("#" + textareaId).next(".CodeMirror").get(0).CodeMirror.setOption("mode", newMode[0]);
                    });
                };
                try {
                    changeMode(selectId, textareaId);
                    $("#" + selectId).on("change", function() {
                        changeMode(selectId, textareaId);
                    });
            } catch (err) {
                    console.error("Exception caught in codemirrorif.js function switchMode\n " + err.toString());
                    return;
                }
            },

        // Textarea_id: identifier of textarea element which shall
            // be converted to CodeMirror
            // readonly: readonly (1) or not (0)
            // mode: programming language
            // headerId: optional. If codemirror is located under a 'header'
            // (means it is not visible if created) it must be refreshed
            // when the header is clicked to show the Codemirror window.
            // Otherwise the CodeMirror is not visible.
            initCodemirror: function(textareaId, readonly, mode, headerId, enlargeWidth) {
            // Alert("initCodemirror called for " + textareaId); //  + " Mimemodes " + cm.mimeModes);
                try {
                    const textarea = document.getElementById(textareaId);
                    if (textarea === null) {
                        // Ignore silently
                        console.error('Invalid textarea id for initCodemirror: ' + textareaId + '. Element not found.');
                        return;
                    }
                    let editor = CodeMirror.fromTextArea(textarea, {
                        tabMode: "indent",
                        indentUnit: 4,
                        matchBrackets: true,
                        autoCloseBrackets: true,
                        styleActiveLine: true,
                        readOnly: readonly,
                    extraKeys: {'Tab': function() {
                        editor.replaceSelection('    ', 'end');
                    }},
                        lineNumbers: true
                    // ViewportMargin: Infinity
                    });
                    Debug.debugLog("CodeMirror.fromTextArea called");

                // Mode is not set when fromTextArea is used (why???)
                    // So mode is set later
                const newMode = mapProglangToCodemirrorMode(mode);
                    require([newMode[1]], function() {
                        editor.setOption("mode", newMode[0]);
                    });
                    Debug.debugLog("CodeMirror.mapProglangToCodemirrorMode called");


                    if (enlargeWidth) {
                        // Resize to fit parent window.
                        // Height will not be set. Otherwise window will shrink to fit text.
                        editor.setSize("100%", null);
                    }

                    editor.refresh();
                // Refresh codemirror editors  -
                    // otherwise content is visible only after first click in window
                    if (headerId) {
                    $('#' + headerId).click(function() {
                        // Refresh codemirror editor  -
                            // otherwise content is visible only after first click in window
                        setTimeout(function() {
                                editor.refresh();
                            }, 5);
                        });
                    }

                    $(editor.getWrapperElement()).resizable({
                    handles: 's', // Only resize in north-south-direction
                    resize: function() {
                            editor.refresh();
                        }
                    });

                /*                    Editor.classList.add("form-control");
                    editor.classList.add("custom-select");
                    editor.parent.classList.add("form-inline");
                    editor.parent.classList.add("felement");
*/
/*
                    if (window.ResizeObserver) {
                        // if the browser supports ResizeObserver than we make the parent window resizable

                        // make Codemirror resizable which is unfortunately not a feature of Codemirror:
                        // resize code is from https://codepen.io/sakifargo/pen/KodNyR
                        // see https://github.com/codemirror/CodeMirror/issues/850
                        // (does not work with Edge since Edge does not support CSS-resize)
                        var wrapper = editor.getWrapperElement().parentNode; // get DIV parent
                        wrapper.style.resize = "vertical"; // add resize handle to parent
                        wrapper.style.overflow = "hidden"; // do not show scrollbars in parent
                        // A fixed initial height is required for the resize handle to appear and
                        // to not fall into a shrinking loop due to the neg. offset in cm_resize()! :-o
                        // (also needed when editor initially does not contain any text)
                        wrapper.style.height = "25em"; // editor.getWrapperElement().offsetHeight; // "25em";
                        //wrapper.classList.add("form-control");
                        // editor.getWrapperElement().classList.add("form-control");

                        function resizeObserver() {
                            function cm_resize() {
                                // And CM needs room for the resize handle...
                                editor.setSize(wrapper.clientWidth-10, wrapper.clientHeight-5);
                                editor.refresh();
                            }

                            cm_resize(); // adjust size
                            new ResizeObserver(cm_resize).observe(wrapper);
                        }

                        if( document.readyState !== 'loading' ) {
                            resizeObserver();
                        } else {
                            document.addEventListener("DOMContentLoaded",resizeObserver());
                        }
                    } else { // (MutationObserver does not work properly)

                    if (window.MutationObserver) {
                        var target = document.getElementById(textareaId);
                        var wrapper = editor.getWrapperElement().parentNode; // get DIV parent
                        wrapper.style.resize = "vertical"; // add resize handle to parent
                        wrapper.style.overflow = "hidden"; // do not show scrollbars in parent
                        // A fixed initial height is required for the resize handle to appear and
                        // to not fall into a shrinking loop due to the neg. offset in cm_resize()! :-o
                        // (also needed when editor initially does not contain any text)
                        wrapper.style.height = "25em"; // editor.getWrapperElement().offsetHeight; // "25em";

                        function cm_resize(mutations) {
                            console.log('cm_resize');
                            //editor.disabled = target.disabled;
                            //mutations.forEach(function(mutation) {
                            //});
                            editor.setSize(wrapper.clientWidth-10, wrapper.clientHeight-1);
                            editor.refresh();
                        }

                        // Konfiguration des Observers: alles melden - Änderungen an Daten, Kindelementen und Attributen
                        var config = { attributes: true, childList: true, characterData: true }; // , subtree:true };
                        var observer1 = new MutationObserver(cm_resize, config);
                        var observer2 = new MutationObserver(cm_resize, config);
                        observer1.observe(target, config);
                        observer2.observe(wrapper, config);
                    }
                    // }
*/
            } catch (err) {
                    console.error("Exception caught in codemirrorif.js function initCodemirror\n " + err.toString());
                }
            }
        };
    });

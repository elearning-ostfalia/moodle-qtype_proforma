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
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */


/*
require([
    "cm/lib/codemirror", "cm/mode/htmlmixed/htmlmixed"
], function(CodeMirror) {
    CodeMirror.fromTextArea(document.getElementById("code"), {
        lineNumbers: true,
        mode: "text/x-java"
    });
});
*/

define(['jquery',
        'qtype_proforma/codemirror', 'qtype_proforma/clike', 'qtype_proforma/python',
        'qtype_proforma/closebrackets', 'qtype_proforma/matchbrackets', 'qtype_proforma/active-line'],
    function($, CodeMirror, clike, python, closebrackets, matchbrackets, activeline) {

        // maps the programming language value used in PHP to the CodeMirror mode
        map_proglang_to_codemirror_mode = function(moodle_mode) {
            switch (moodle_mode) {
                case "java":   return "text/x-java";
                case "python": return "text/x-python";
                case "setlx":  return "text/text";
                case "c":      return "text/x-csrc";
                case "none":   return "";
                default:
                    alert("unsupported mode " + moodle_mode + " for map_proglang_to_codemirror_mode");
                    return "text/text";
            }
        };


        return {

            // function is called in edit_proforma_form. It triggers the CodeMirror mode switch
            // whenever the programming language changes
            switch_mode: function(select_id, textarea_id) {

                changeMode = function (select_id, textarea_id) {
                    var progLang = $("#" + select_id).val();
                    // map programming language to CodeMirror mode
                    var newMode =  map_proglang_to_codemirror_mode(progLang);
                    // change mode in CodeMirror
                    $("#" + textarea_id).next(".CodeMirror").get(0).CodeMirror.setOption("mode", newMode);
                };
                try {
                    changeMode(select_id, textarea_id);
                    $("#" + select_id).on("change", function(e) {
                        changeMode(select_id, textarea_id);
                    });
                } catch(err) {
                    alert("Exception caught in codemirrorif.js function switch_mode\n " + err.toString());
                    return;
                }
            },

            // textarea_id: identifier of textarea element which shall
            // be converted to CodeMirror
            // readonly: readonly (1) or not (0)
            // mode: programming language
            // header_id: optional. If codemirror is located under a 'header'
            // (means it is not visible if created) it must be refreshed
            // when the header is clicked to show the Codemirror window.
            // Otherwise the CodeMirror is not visible.
            init_codemirror: function(textarea_id, readonly, mode, header_id) {
                //alert("init_codemirror called for " + textarea_id); //  + " Mimemodes " + cm.mimeModes);
                // console.log("init_codemirror called for " + classname );
                try {
                    var editor = CodeMirror.fromTextArea(document.getElementById(textarea_id), {
                        tabMode: "indent",
                        indentUnit: 4,
                        matchBrackets: true,
                        autoCloseBrackets: true,
                        styleActiveLine: true,
                        readOnly: readonly ? true : false,
                        extraKeys: {'Tab': function(){editor.replaceSelection('    ' , 'end');}},
                        lineNumbers: true
                        //viewportMargin: Infinity
                    });

                    // mode is not set when fromTextArea is used (why???)
                    // So mode is set later
                    var newMode =  map_proglang_to_codemirror_mode(mode);
/*                    if (newMode == 'text/x-python') {
                        editor.setOption("mode", "{name:'" + mode + "',version: 2,singleLineStringErrors: false}");
                    } else {
*/
                        editor.setOption("mode", newMode);
//                    }
                    //editor.setOption("theme", "eclipse"); // needs additional css file
                    //$(".CodeMirror").addClass("form-control");
                    editor.refresh();
                    // refresh codemirror editors  -
                    // otherwise content is visible only after first click in window
                    // setTimeout(function () {
                    //     Object.keys(codemirror).forEach(function(item) {codemirror[item].refresh();});
                    // }, 5);
                    //setTimeout(function () {editor.refresh();}, 5);

                    if (header_id) {
                        $('#' + header_id).click(function (e) {
                            // refresh codemirror editor  -
                            // otherwise content is visible only after first click in window
                            setTimeout(function () {
                                editor.refresh();
                            }, 5);
                        });
                    }
                } catch(err) {
                    alert("Exception caught in codemirrorif.js function init_codemirror\n " + err.toString());
                    return;
                }
            }
        };
    });

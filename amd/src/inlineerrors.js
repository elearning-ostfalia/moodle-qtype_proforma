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
 * Display error messages inline in Codemirror editor.
 *
 * @package    qtype_proforma
 * @copyright  2021 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

// import './codemirror-global';
// Moodle import:
// import CodeMirror from "./codemirror";
// import any mode

/* We store the widget handles for each editor because there is no
interface for removing all widgets from an editor. 
Using the DOM tree does not work because the widgets appear twice when readded.
*/

var editorWidgets; //  = {};


/**
 * returns all widgets for an editor
 * @param {*} editor 
 */
function _getWidgets(editor) {
    if (editorWidgets === undefined) {
        console.log('editorWidgets undefined => create');   
        editorWidgets = {};
    }
    console.log('editorWidgets found: ' + Object.keys(editorWidgets).length);   
    let widgets = editorWidgets[editor];
    if (widgets === undefined) {
        console.log('widgets not found => add');        
        let widgets = [];
        editorWidgets[editor] = widgets;
        return widgets;
    }
    console.log('widgets found: ' + widgets.length);        
    return widgets;
}

/**
 * removes all widgets from an editor
 * @param {*} editor 
 */
function _hideWidgets(editor) {
    try {
        editor.operation(function() {
            let wigdets = _getWidgets(editor);
            for (var i = 0; i < wigdets.length; ++i) {
                editor.removeLineWidget(wigdets[i]);
            }
            wigdets.length = 0;
        });
    } catch(e) {
        console.error('error occured ' + e);
    }
}


function _showMessages(editor, errors) {
    try {
        editor.operation(function() {

            _hideWidgets(editor);
            let widgets = _getWidgets(editor);

            console.log('add new widgets');            
            for (var i = 0; i < errors.length; ++i) {
                var err = errors[i];
                if (!err) {
                    continue;
                }
                var msg = document.createElement("div");
                switch (err.msgtype.toLowerCase()) {
                    case 'error':
                        var icon = msg.appendChild(document.createElement("span"));
                        icon.innerHTML = "x";
                        icon.className = 'proforma-dot-icon proforma-error-icon';
                        msg.className = "inline-error";
                        break;
                    case 'warn':
                    case 'warning':
                        var icon = msg.appendChild(document.createElement("span"));
                        // icon.innerHTML = "";
                        icon.className = "proforma-warn-icon proforma-warning";
                        msg.className = "inline-warning";
                        break;
                    case 'info':
                        var icon = msg.appendChild(document.createElement("span"));
                        icon.innerHTML = "i";
                        icon.className = 'proforma-dot-icon proforma-info-icon';
                        msg.className = "inline-info";
                        break;
                    default:
                        console.error('do not know message type ' + err.msgtype);
                        break;
                }
                msg.appendChild(document.createTextNode(' ' + err.text));
                let widget = editor.addLineWidget(err.line - 1, msg, {coverGutter: true, noHScroll: true});
                widgets.push(widget);
            }
          });
          var info = editor.getScrollInfo();
          var after = editor.charCoords({line: editor.getCursor().line + 1, ch: 0}, "local").top;
          if (info.top + info.clientHeight < after) {
              editor.scrollTo(null, after - info.clientHeight + 3);
          }
    } catch(e) {
        console.error('error occured ' + e);
    }
}


// Retrieve a CodeMirror Instance.
function _getCodeMirror(target) {
    var _target = target;
    if (typeof _target === 'string') {
        _target = document.querySelector(_target);
    }
    if (_target === null || !_target.tagName === undefined) {
        throw new Error('Element ' + target + ' does not reference a CodeMirror instance.');
    }

    if (_target.tagName === 'TEXTAREA') {
        return _target.nextSibling.CodeMirror;
    }

    console.error('could not find Codemirror editor for ' + target);
    return null;
}


function _getErrorsFromLog(collapsregion, regexp) {
    let region = document.getElementById(collapsregion);
    let testlogs = region.querySelectorAll('.proforma_testlog');
    let innertext = '';
    for (let testlog of testlogs) {
        console.log('testlog ' + testlog);
        if (testlog.innerText.length == 0) {
            // HtmlPreElement
            innertext = innertext + '\n' + testlog.textContent;
        } else {
            innertext = innertext + '\n' + testlog.innerText;
        }
    }
    console.log('text: ' + innertext);
    // global match
    let re = new RegExp(regexp, "mg");
    let results = innertext.matchAll(re);
    var messages = [];

    for (let result of results) {
        let {msgtype, filename, line, text} = result.groups;
        var error = {
          line: line,
          text: text,
          msgtype: msgtype,
        };
        messages.push(error);
    }
    return messages;
}


function _countMessages(messages) {
    let errors = 0;
    let warnings = 0;
    let infos = 0;
    for (var i = 0; i < messages.length; ++i) {
        var msg = messages[i];
        if (!msg) {
            continue;
        }
        switch (msg.msgtype.toLowerCase()) {
            case 'error':
                errors++;
                break;
            case 'warn':
            case 'warning':
                warnings++;
                break;
            case 'info':
                infos++;
                break;
            default:
                console.error('do not know message type ' + err.msgtype);
                break;
        }
    }
    return [errors, warnings, infos];
}


/**
 * embeds error messages found in log area using regexp
 *
 * @param {type} cmid Codemirror identifier
 * @param {type} collapsregion collapsible region with error messages
 * @param {type} regexp regulare expression for finding messages
 * @returns {undefined}
 */
export const embedError = (cmid, collapsregion, regexp) => {
    if (!cmid) {
        console.error('cmid is invalid');
        return;
     }

    let region = document.getElementById(collapsregion);
    if (!region) {
       console.error('region ' + collapsregion + ' not found');
       return;
    }

    let messages = _getErrorsFromLog(collapsregion, regexp);
    if (messages.length == 0) {
        console.log('no messages found');
        return;
    }

    const [errors, warnings, infos] = _countMessages(messages);

    const errorLabel = errors + '<span class="proforma-dot-icon proforma-error-icon">x</span>';
    const warningLabel = warnings   + '<span class="proforma-warn-icon proforma-warning"/></span>';
    const infoLabel = infos + '<span class="proforma-dot-icon proforma-info-icon">i</span>';


    const label = errorLabel + ' ' + warningLabel + ' ' + infoLabel;

    const SHOW = label; // 'Show inline';
    const HIDE = 'Hide inline';
    // Create button.
    var button = document.createElement("button");
    button.type = "button";
    button.className = "proforma-feedback-msg-btn";
    button.innerHTML  = SHOW;

    let showMsg = false;


    let a_element = region.querySelector('a');
    a_element.insertAdjacentElement("afterend", button);
    cmid = CSS.escape(cmid);
    button.addEventListener('click',
        function () {
            var editor = _getCodeMirror('#' + cmid);
            if (!showMsg) {
                _showMessages(editor, messages);
                button.className ="proforma-feedback-msg-btn active";
                showMsg = true;
            } else {
                _hideWidgets(editor, messages);
                button.className ="proforma-feedback-msg-btn";
                showMsg = false;
            }
    });
};




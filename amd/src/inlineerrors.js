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
// import CodeMirror from "./codemirror.js";
// Moodle import:
// import CodeMirror from "./codemirror";
// import any mode

var widgets = [];

function _hideErrors(editor) {
    try {
        editor.operation(function() {
            console.log('remove old widgets');
            for (var i = 0; i < widgets.length; ++i) {
                editor.removeLineWidget(widgets[i]);
            }
            widgets.length = 0;
        });
    } catch(e) {
        console.error('error occured ' + e);
    }
}

function _showErrors(editor, errors) {
    try {
        editor.operation(function(){
            console.log('remove old widgets');
            for (var i = 0; i < widgets.length; ++i) {
                editor.removeLineWidget(widgets[i]);
            }
            widgets.length = 0;

            console.log('add ' + errors.length.toString() + ' new widgets');
            for (var i = 0; i < errors.length; ++i) {
                var err = errors[i];
                if (!err) {
                    continue;
                }
                console.log('create new widget');
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
                widgets.push(editor.addLineWidget(err.line - 1, msg, {coverGutter: false, noHScroll: true}));
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
    console.log('collapsregion ' + collapsregion);
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
    console.log('regexp is ' + regexp);
    // global match
    var re = new RegExp(regexp, "mg");
    console.log('re is ' + re);

    let results = innertext.matchAll(re);
    var messages = [];

    for (let result of results) {
        let {msgtype, filename, line, text} = result.groups;

        // alert(`${msgtype}.${filename}.${line}.${text}`);
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

    // const label = '<i class="fa fa-folder">TEXT</i>';
    /* awsome fonts
    const errorLabel = '<i class="icon fa fa-times text-danger fa-fw " title="error" aria-label="error">' + errors + '</i>';
    const warningLabel = '<i class="icon fa fa-exclamation text-warning fa-fw" title="warning" aria-label="warning">' + warnings + '</i>';
    const infoLabel = '<i class="icon fa fa-info fa-fw" title="info" aria-label="info">' + infos + '</i>';
*/
    const errorLabel = errors + '<span class="proforma-dot-icon proforma-error-icon">x</span>';
    const warningLabel = warnings   + '<span class="proforma-warn-icon proforma-warning"/></span>';
    const infoLabel = infos + '<span class="proforma-dot-icon proforma-info-icon">i</span>';


    const label = errorLabel + ' ' + warningLabel + ' ' + infoLabel;

    const SHOW = label; // 'Show inline';
    const HIDE = 'Hide inline';
    // Create button.
    console.log('create new button');
    var button = document.createElement("button");
    button.type = "button";
    button.className = "proforma-feedback-msg-btn"; // "btn btn-secondary proforma-feedback-msg-btn"; //
    button.innerHTML  = SHOW;

    let showMsg = false;


    let a_element = region.querySelector('a');
    a_element.insertAdjacentElement("afterend", button);
    cmid = CSS.escape(cmid);
    button.addEventListener('click',
        function () {
            var editor = _getCodeMirror('#' + cmid);
            if (!showMsg) {
                _showErrors(editor, messages);
                button.className ="proforma-feedback-msg-btn active";
                showMsg = true;
            } else {
                _hideErrors(editor, messages);
                button.className ="proforma-feedback-msg-btn";
                showMsg = false;
            }
    });
};




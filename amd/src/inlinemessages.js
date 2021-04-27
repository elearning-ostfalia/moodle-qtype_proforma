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
import CodeMirror from "./codemirror";
// import any mode


/**
 * removes all widgets
 * @param {*} editor
 */
function _hideWidgets(widgets) {
    for (let i = 0; i < widgets.length; ++i) {
        widgets[i].clear();
    }
    widgets.length = 0;
    return widgets;
}


function _showMessages(editor, errors, widgets) {
    widgets = _hideWidgets(widgets);
    for (let i = 0; i < errors.length; ++i) {
        let err = errors[i];
        if (!err) {
            continue;
        }
        var msg = document.createElement("div");
        var icon;
        if (typeof msg.msgtype !== 'undefined') {        
            switch (err.msgtype.toLowerCase()) {
                case 'error':
                    icon = msg.appendChild(document.createElement("span"));
                    icon.innerHTML = "x";
                    icon.className = 'proforma-dot-icon proforma-error-icon';
                    msg.className = "proforma-inline-error";
                    break;
                case 'warn':
                case 'warning':
                    icon = msg.appendChild(document.createElement("span"));
                    // icon.innerHTML = "";
                    icon.className = "proforma-warn-icon proforma-warning";
                    msg.className = "proforma-inline-warning";
                    break;
                case 'info':
                    icon = msg.appendChild(document.createElement("span"));
                    icon.innerHTML = "i";
                    icon.className = 'proforma-dot-icon proforma-info-icon';
                    msg.className = "proforma-inline-info";
                    break;
                default:
                    icon = msg.appendChild(document.createElement("span"));
                    icon.innerHTML = "?";
                    icon.className = 'proforma-dot-icon proforma-else-icon';
                    msg.className = "proforma-inline-info";
                    console.error('do not know message type ' + err.msgtype);
                    break;
            }
        } else {
            // No message types:
            icon = msg.appendChild(document.createElement("span"));
            icon.innerHTML = "x";
            icon.className = 'proforma-dot-icon proforma-error-icon';
            msg.className = "proforma-inline-error";
        }
        msg.appendChild(document.createTextNode(' ' + err.text));
        var widget = editor.addLineWidget(err.line - 1, msg, {coverGutter: true, noHScroll: true});
        widgets.push(widget);
    }
    let info = editor.getScrollInfo();
    let after = editor.charCoords({line: editor.getCursor().line + 1, ch: 0}, "local").top;
    if (info.top + info.clientHeight < after) {
        editor.scrollTo(null, after - info.clientHeight + 3);
    }

    return widgets;
}


// Retrieve a CodeMirror Instance.
function _getCodeMirror(target) {
    let _target = target;
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
    let messages = [];
    let region = document.getElementById(collapsregion);
    let testlogs = region.querySelectorAll('.proforma_testlog');
    let innertext = '';
    for (let testlog of testlogs) {
        if (testlog.innerText.length == 0) {
            // HtmlPreElement
            innertext = innertext + '\n' + testlog.textContent;
        } else {
            innertext = innertext + '\n' + testlog.innerText;
        }
    }
    // global match
    let re = new RegExp(regexp, "mg");
    let results = innertext.matchAll(re);
    // console.log('innertext: ' + innertext);
    // console.log('regexp: ' + regexp);

    for (let result of results) {
        let {msgtype, filename, line, text} = result.groups;
        let error = {
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
    let somethingelse = 0;

    if (messages) {
        for (let i = 0; i < messages.length; ++i) {
            let msg = messages[i];
            if (!msg) {
                continue;
            }
            if (typeof msg.msgtype !== 'undefined') {
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
                        console.error('do not know message type ' + msg.msgtype);
                        somethingelse++;
                        break;
                }
            } else {
                errors++;
            }
        }
    }
    return [errors, warnings, infos, somethingelse];
}


const waitForElementById = id =>
    new Promise(resolve => {
        const wait = () => {
            const element = document.getElementById(id);
            if (element) {
                resolve(element);
            }
            window.requestAnimationFrame(wait);
        };
        wait();
    });


const waitForElement = (node, selector) =>
    new Promise(resolve => {
        const wait = () => {
            const element = node.querySelector(selector);
            if (element) {
                resolve(element);
            }
            window.requestAnimationFrame(wait);
        };
        wait();
    });

function _embedErrorWithDocumentLoaded(cmid, collapsregion, regexp) {
    var widgets = [];
    // Codemirror id must be escaped!
    cmid = CSS.escape(cmid);

    waitForElementById(collapsregion)
        .then((region) => {
            // wait for collapsible region
            return waitForElement(region, 'a');
        })
        .then((a_element) => {
            // Get Messages
            let messages = _getErrorsFromLog(collapsregion, regexp);
            if (messages.length == 0) {
                // console.log('no messages found => ready');
                return;
            }

            // not translated correctly by Babel:
            // const [errors, warnings, infos, somethingelse] = _countMessages(messages);
            const values = _countMessages(messages);
            const errors = values[0];
            const warnings = values[1];
            const infos = values[2];
            const somethingelse = values[3];

            // Create button label
            const errorLabel = errors + '<span class="proforma-dot-icon proforma-error-icon">x</span> ';
            const warningLabel = warnings   + '<span class="proforma-warn-icon proforma-warning"/></span> ';
            const infoLabel = infos + '<span class="proforma-dot-icon proforma-info-icon">i</span> ';
            const elseLabel = somethingelse + '<span class="proforma-dot-icon proforma-else-icon">?</span> ';

            let label = ' ';
            if (errors > 0) {
                label += errorLabel;
            }
            if (warnings > 0) {
                label += warningLabel;
            }
            if (infos > 0) {
                label += infoLabel;
            }
            if (somethingelse > 0) {
                label += elseLabel;
            }

            // Create button.
            let button = document.createElement("button");
            button.type = "button";
            button.className = "proforma-feedback-msg-btn";
            button.innerHTML  = label;
            a_element.insertAdjacentElement("afterend", button);

            let showMsg = false;
            button.addEventListener('click',
                function () {
                    // The editor is evaluated here and not before in order to avoid
                    // racing situations.
                    let editor = _getCodeMirror('#' + cmid);
                    if (!showMsg) {
                        widgets = _showMessages(editor, messages, widgets);
                        button.className ="proforma-feedback-msg-btn active";
                        showMsg = true;
                    } else {
                        widgets = _hideWidgets(widgets);
                        button.className ="proforma-feedback-msg-btn";
                        showMsg = false;
                    }
                });
        })
        .catch((error) => {
            console.error(error);
        });
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

    // We must wait for the document to be ready.
    // Otherwise Codemirror and other controls might not yet be available.
    // Note that Codemirror is created asynchronously after document ready.
    // So this is not enough when something has to be done with Codemirror.
    if( document.readyState !== 'loading' ) {
        _embedErrorWithDocumentLoaded(cmid, collapsregion, regexp);
    } else {
        document.addEventListener("DOMContentLoaded", function() {
            _embedErrorWithDocumentLoaded(cmid, collapsregion, regexp);
      });
    }
};




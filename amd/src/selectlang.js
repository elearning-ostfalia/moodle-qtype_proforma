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
 * Modal dialog for selecting a programming language.
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

define(['core/modal_factory', 'core/modal_events'], function(ModalFactory, ModalEvents) {
    function create_body(proglangs) {
        let body = "<form>";
        body += '<fieldset>';
        // console.log(proglangs);
        proglangs.forEach(function(item, index) {
            // We add a human readable identifier for testing access.
            let id = item[1].toLowerCase().split(' ')[0];
            body += '<p><input id="item_' + id + '" type="radio" name="lang" value="' + item[0] + '"';
            if (index == 0) {
                // Check first element
                body +=  'checked';
            }
            body += '> ' +  item[1] +'</input></p>';
        });

        // Add extra item for selecting the task editor:
        // 1 means persistent proforma file
        // body += '<p><input id="item_proforma" type="radio" name="lang" value="1"';
        // body += '> Taskeditor (experimental)</input></p>';

        body += '<br>';

        body += '</fieldset>';
        body += '</form>';
        return body;
    }

    return {
        select_lang: function(title, proglangs, returnurl) {
            function doModal() {
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: title,
                    body: create_body(proglangs),
                    large: false
                })
                .then(function(modal) {
                    modal.setSaveButtonText('Ok');
                    modal.getRoot().on(ModalEvents.save, function() {
                        // Check which radio button is checked.
                        let radioButtons = modal.getRoot().find('input');
                        for (var i = 0; i < radioButtons.length; i++) {
                            if(radioButtons[i].checked === true) {
                                let language = radioButtons[i].value;
                                // Preset task storage.
                                // document.getElementById("id_taskstorage").setAttribute('value', language);
                                // Append language value to URI and
                                // reload page.
                                let uri = window.location.href;
                                uri += '&proglang=' + language;
                                window.location.assign(uri);
                                return;
                            }
                        }
                    });
                    modal.getRoot().on(ModalEvents.cancel, function() {
                        // Cancel was pressed => redirect to returnurl.
                        window.location.assign(returnurl);
                    });
                    modal.show();
                }).catch(Notification.exception);
            }
            try {
                doModal();
            } catch(err) {
                console.error("Exception caught in select-lang.js function select_lang\n " + err.toString());
            }
        }
    };
});

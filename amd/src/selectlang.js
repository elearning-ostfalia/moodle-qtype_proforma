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
 * @copyright  2020 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>
 */

define(['jquery'], function($) {
    return {
        select_lang: function(returnurl) {
            try {
                function show_popup() {
                    var txt;
                    var lang = prompt("Select Programming lanuage:", "java");
                    if (lang == null || lang == "") {
                        lang = "";
                    }

                    return lang;
                }
                
                language = show_popup();
                if (language != "") {
                    // programming language was selected:
                    switch (language) {
                        case 'java': language = 3; break;
                        case 'setlx': language = 4; break;
                        default: 
                            alert('unbekannt');
                            language = 0;
                            break;
                    }
                    document.getElementById("id_taskstorage").setAttribute('value', language);
                    let uri = window.location.href;
                    uri += '&proglang=' + language;
                    window.location.assign(uri);                    
                } else {
                    // Cancel was pressed.
                    // => redirect to returnurl
                    alert(returnurl);
                    window.location.assign(returnurl); 
                }
            } catch(err) {
                console.error("Exception caught in select-lang.js function select_lang\n " + err.toString());
            }
        }
    };
});

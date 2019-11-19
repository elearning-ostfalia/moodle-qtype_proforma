<?php
// This file is part of ProFormA Question Type for Moodle
//
// ProFormA Question Type for Moodle is free software: you can redistribute it and/or modify
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'qtype_proforma', language 'de''
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia Hochschule fuer angewandte Wissenschaften
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>*
 */

$string['pluginname'] = 'ProFormA-Aufgabe';
$string['pluginname_help'] = 'Programmieraufgabe basierend auf einer ProFormA-Aufgabendatei mit automatischer Bewertung. Der Studierende kann entweder seine Lösung als Datei hochladen oder sie direkt in einem Editor eingeben. Dazu kann eine Code-Vorlage vorgegeben werden. ';
$string['pluginnameadding'] = 'ProFormA-Aufgabe hinzufügen';
$string['pluginnameediting'] = 'ProFormA-Aufgabe ändern';
$string['pluginnamesummary'] = 'Automatisch bewertete Programmieraufgabe: <br>
ACHTUNG: Neue Aufgaben müssen in die Moodle-Fragedatenbank <b>importiert</b> werden und können nur darüber eingebunden werden.<br>
Einen Editor zum Erstellen von Aufgaben gibt es unter https://media.elan-ev.de/proforma/editor/releases.html.';
$string['allowattachments'] = 'Max. Anzahl Dateien';
$string['comment'] = 'Kommentar';
$string['commentheader'] = 'Kommentar';
$string['nlines'] = '{$a} Zeilen';
$string['responsefieldlines'] = 'Anzahl Zeilen';
$string['responseformat'] = 'Eingabeform';
$string['responseoptions'] = 'Einreichung';
$string['graderoptions_header'] = 'Grader';
$string['responsetemplateheader'] = 'Codevorlagen';
$string['responsetemplate'] = 'Codevorlage';
$string['responsetemplate_help'] = 'Text wird bei Beginn eines neuen Versuchs im Editor für den Studierenden angezeigt (bzw. als Download angeboten). Wenn mehr als eine Vorlage existiert, ist nur die erste hier änderbar.';
$string['templateeditor'] = '';

$string['modelsolution'] = 'Musterlösung';
$string['modelsolutionheader'] = 'Musterlösung';
$string['modelsolution_help'] = 'Beispiellösung für die Aufgabe.';
$string['taskpath'] = 'Verzeichnis';
$string['taskpath_hint'] = $string['taskpath'];
$string['taskpath_hint_help'] = 'Verzeichnis der ProFormA-Aufgabendatei im Repository';
$string['repository'] = 'Repository';
$string['repository_hint'] = $string['repository'];
$string['repository_hint_help'] = 'URI des ProFormA-Repository';

$string['repositoryhost'] = 'Host des Repository';
$string['repositoryhost_desc'] = 'Host des Repository, beginnend mit der Protokoll-Angabe (https://)';
$string['repositorypath'] = 'Verzeichnis im Repository';
$string['repositorypath_desc'] = 'Verzeichnis einer ProFormA-Aufgabe im Repository';


$string['filename'] = 'Dateiname';
$string['filename_hint'] = $string['filename'];
$string['filename_hint_help'] = 'Da bei Nutzung des Editors als Eingabeform kein Dateiname zum Kompilieren vorhanden ist, muss dieser fest vorgegeben werden.';

$string['taskfilename'] = 'ProFormA-Aufgabendatei';
$string['taskfilename_hint'] = $string['taskfilename'];
$string['taskfilename_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei';

$string['compile'] = 'Compilation';
$string['code'] = 'Quellcode';

$string['addjunit'] = 'JUnit Test hinzufügen';


$string['highlight'] = 'Syntax Highlighting';
$string['highlight_hint'] = $string['proglang'];
$string['highlight_hint_help'] = 'Programmiersprache, die für das Syntax Highlighting genutzt werden soll';

$string['proglang'] = 'Programmiersprache';
$string['proglang_hint'] = $string['proglang'];
$string['proglang_hint_help'] = 'Verwendete Programmiersprache der Aufgabe';
$string['other'] = 'andere';


$string['miscellaneousheader'] = 'Verschiedenes';
$string['defaultpenalty'] = 'Abzug bei Fehlversuchen';
$string['defaultpenalty_desc'] = 'Abzug bei Fehlversuchen, falls Frageverhalten auf "Mehrfachbeantwortung (mit Abzügen)") gestellt ist';

$string['grader_heading'] = 'Grader-Einstellungen';
$string['graderuri_host'] = 'URI: Protokoll und Server';
$string['graderuri_host_desc'] = 'Protokoll und Server der Grader URI';
$string['graderuri_path'] = 'URI: Pfad';
$string['graderuri_path_desc'] = 'Pfad der Grader URI';
$string['javafile_without_package'] = 'Entfernen des Java-Paket-Pfads beim Einreichen mit Editoreingabe';
$string['javafile_without_package_desc'] = 'Falls zur Eingabe der Einreichung der Editor verwendet wird, wird der an den Grader gesendete Dateiname aus der Musterlösung erzeugt.
Wenn diese einen Paket-Pfad enthält, kann es sein, dass der Grader damit nicht klarkommt. Mit Setzen dieses Schalters wird der Pfad nicht mitgesendet.';

$string['grading_timeout'] = 'Grading Timeout';
$string['grading_timeout_desc'] = 'Zeit, nach der angenommen wird, dass vom Grader keine Antwort mehr kommt (in Sekunden)';

$string['gradepassed'] = 'Die Antwort ist richtig.';
$string['gradefailed'] = 'Die Antwort ist nicht vollständig richtig.';
$string['gradepartialpassed'] = 'Die Antwort ist zum Teil richtig.';
$string['gradeinternalerror'] = 'Beim Bewerten ist ein Fehler aufgetreten. Die Antwort konnte nicht bewertet werden.';

$string['usecodemirror'] = 'CodeMirror als Quelltexteditor benutzen';
$string['usecodemirror_desc'] = 'CodeMirror kann im Editor (studentische Sicht) und für die Eingabe der Codevorlage (Dozentensicht) eingesetzt werden. ';
$string['uuid_hint_help'] = 'UUID (universal unique identifier) der ProFormA-Aufgabendatei';
$string['configmaxbytes'] = 'maximale Summe der Dateigrößen der studentischen Einreichung';
$string['maxbytes'] = 'Max. Größe aller Dateien';
$string['maximumsubmissionsize'] = $string['maxbytes'];
$string['maximumsubmissionsize_help'] = 'Die Summe der Dateigrößen aller hochgeladene Dateien darf diesen Wert nicht überschreiten.';

$string['acceptedfiletypes'] = 'Akzeptierte Dateitypen';
$string['acceptedfiletypes_help'] = 'Akzeptierte Dateien können durch Eingabe einer mit Semikolon getrennten Liste von Mimetypes eingeschränkt werden, z.B. \'text/plain; application/java-archive; application/zip; application/xml\'. Auch können Dateiendungen vorgegeben werden (inkl "."): z.B. \'.java; .jar\'. Wenn das Feld leer bleibt, dann werden alle Dateien erlaubt.';
$string['nonexistentfiletypes'] = 'Die folgenden Dateitypen konnten nicht erkannt werden: {$a}';
$string['templates'] = 'Weitere Codevorlagen';
$string['templates_hint'] = $string['templates'];
$string['templates_hint_help'] = 'Namen der zusätzlichen Codevorlagen (meist gibt es nur eine Codevorlage)';
$string['downloads'] = 'Dateianhänge zur Aufgabenstellung';
$string['downloads_hint'] = $string['downloads'];
$string['downloads_hint_help'] = 'Dateien, die zum Lösen der Aufgabe benötigt werden und von den Studierenden heruntergeladen werden können';
$string['modelsolfiles'] = 'Datei(en) der Musterlösung';
$string['modelsolfiles_hint'] = $string['modelsolfiles'];
$string['modelsolfiles_hint_help'] = 'Dateien, die zusammen die Musterlösung bilden';
$string['attachments'] = 'Downloads:';
$string['questiondefaults'] = 'Standardwerte bei neuen Aufgaben';
$string['none'] = 'ohne';

$string['tests'] = 'Bewertung';
$string['notests'] = 'Keine Tests gefunden => benutze "all or nothing"';
$string['testdescription'] = 'Beschreibung';
$string['testtype'] = 'Typ';
$string['testtitle'] = 'Titel';
$string['weight'] = 'Gewicht';
$string['filepickeroptions'] = 'Filepicker-Eintellungen';

$string['all_or_nothing'] = 'alles oder nichts';
$string['weighted_mean'] = 'gewichtete Summe';
$string['aggregationstrategy'] = 'Aggregationsstrategie';
$string['aggregationstrategy_help'] = 'Aggregationsstrategie, die zum Ermitteln der Bewertung genutzt werden soll';

$string['nocreate'] = 'Neue Fragen müssen extern erzeugt und anschließend in die Fragensammlung importiert werden.<br>
Einen Editor zum Erzeugen von ProFormA-Fragen gibt es hier: https://media.elan-ev.de/proforma/editor/releases.html.';

$string['internaltesterror'] = 'Während des Testlaufs wurde ein interner Fehler festgestellt.';

$string['privacy:metadata'] = 'Das ProFormA Frageformat Plugin speichert keine personenbezogenen Daten.';





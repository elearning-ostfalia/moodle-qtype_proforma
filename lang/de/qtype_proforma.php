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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'qtype_proforma', language 'de''
 *
 * @package    qtype
 * @subpackage proforma
 * @copyright  2017 Ostfalia University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>*
 */

$string['pluginname'] = 'ProFormA-Aufgabe';
$string['pluginname_help'] = 'Programmieraufgabe basierend auf einer ProFormA-Aufgabendatei mit automatischer Bewertung. Der Studierende kann entweder seine Lösung als Datei hochladen oder sie direkt in einem Editor eingeben. Dazu kann eine Code-Vorlage vorgegeben werden. ';
$string['pluginnameadding'] = 'ProFormA-Aufgabe hinzufügen';
$string['pluginnameediting'] = 'ProFormA-Aufgabe ändern';
$string['pluginnamesummary'] = 'Automatisch bewertete Programmieraufgabe.';

// Capability names.
$string['proforma:runbulktest'] = 'Starten des ProFormA-Bulktests';
$string['proforma:viewsysteminfo'] = 'ProFormA-Systeminformationen sehen';


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
$string['modelsolution_help'] = 'Beispiellösung für die Aufgabe, wie sie dem Studierenden angezeigt werden soll, falls die Option "Richtige Antwort" in den Überprüfungsoptionen des Tests angeschaltet ist.';
$string['msfilename'] = 'Datei ';


$string['taskpath'] = 'Verzeichnis';
$string['taskpath_hint'] = 'Verzeichnis';
$string['taskpath_hint_help'] = 'Verzeichnis der ProFormA-Aufgabendatei im Repository';
$string['repository'] = 'Repository';
$string['repository_hint'] = 'Repository';
$string['repository_hint_help'] = 'URI des ProFormA-Repository';

$string['repositoryhost'] = 'Host des Repository';
$string['repositoryhost_desc'] = 'Host des Repository, beginnend mit der Protokoll-Angabe (https://)';
$string['repositorypath'] = 'Verzeichnis im Repository';
$string['repositorypath_desc'] = 'Verzeichnis einer ProFormA-Aufgabe im Repository';


$string['filename'] = 'Dateiname';
$string['filename_hint'] = 'Dateiname';
$string['filename_hint_help'] = 'Da bei Nutzung des Editors als Eingabeform kein Dateiname zum Kompilieren vorhanden ist, muss dieser fest vorgegeben werden.
Bitte beachten, dass u.U. auch das Package im Namen enthalten sein muss.';

$string['taskfilename'] = 'ProFormA-Aufgabendatei';
$string['taskfilename_hint'] = 'ProFormA-Aufgabendatei';
$string['taskfilename_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei';
$string['createdtask_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei. Achtung: Aufgabe muss erst gespeichert werden, damit die entsprechende ProFormA-Datei erzeugt wird.';
$string['task_hint'] = 'ProFormA-Aufgabendatei';
$string['task_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei. '
. 'Wenn diese ersetzt werden soll, bitte beachten, dass die neue Datei kompatibel zur alten ist. '
. 'Das bedeutet: <br>'
. '- gleiche Programmiersprache<br>'
. '- gleiche Anzahl Tests<br>'
. '- gleiche Testtypen in gleicher Reihenfolge';

$string['compile'] = 'Compilation';
$string['syntaxcheck'] = 'Syntax Check';
$string['code'] = 'Quellcode';

$string['addtest'] = '{$a} hinzufügen';

$string['junit'] = 'JUnit Test';
$string['setlx'] = 'SetlX Test';

$string['codeempty'] = 'Code fehlt.';
$string['titleempty'] = 'Titel fehlt.';
$string['versionrequired'] = 'Version ist nicht ausgewählt.';
$string['filenameerror'] = 'Kann Klassennamen nicht ermitteln (Dateiname).';
$string['entrypointerror'] = 'Kann Klassennamen nicht ermitteln (Entrypoint).';
$string['sumweightzero'] = 'Die Summe aller Gewichte darf nicht 0 sein.';


$string['highlight'] = 'Syntax Highlighting';
$string['highlight_hint'] = 'Programmiersprache';
$string['highlight_hint_help'] = 'Programmiersprache, die für das Syntax Highlighting genutzt werden soll';

$string['proglang'] = 'Programmiersprache';
$string['proglang_hint'] = 'Programmiersprache';
$string['proglang_hint_help'] = 'Verwendete Programmiersprache der Aufgabe. Zur Zeit können nur Java-Aufgaben erstellt werden.';
$string['proglangversion'] = 'Version der Programmiersprache';
$string['proglangversion_hint'] = 'Version der Programmiersprache';
$string['proglangversion_hint_help'] = 'Version der Programmiersprache.';
$string['other'] = 'andere';

$string['version'] = 'Version';


$string['miscellaneousheader'] = 'Verschiedenes';
$string['defaultpenalty'] = 'Abzug bei Fehlversuchen';
$string['defaultpenalty_desc'] = 'Abzug bei Fehlversuchen, falls Frageverhalten auf "Mehrfachbeantwortung (mit Abzügen)" gestellt ist';

$string['grader_heading'] = 'Grader-Einstellungen';
$string['graderuri_host'] = 'URI: Protokoll und Server';
$string['graderuri_host_desc'] = 'Protokoll und Server der Grader URI';
$string['graderuri_path'] = 'URI: Pfad';
$string['graderuri_path_desc'] = 'Pfad der Grader URI';

$string['javasettings_header'] = 'Einstellungen für Java-Fragen, die mit Moodle erzeugt werden';
$string['checkstyleversion'] = 'Checkstyle Version';
$string['checkstyleversion_desc'] = 'Komma separierte Liste mit Checkstyle-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';
$string['javaversion'] = 'Java Version';
$string['javaversion_desc'] = 'Komma separierte Liste mit Java-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';
$string['junitversion'] = 'JUnit Version';
$string['junitversion_desc'] = 'Komma separierte Liste mit JUnit-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';

$string['grading_timeout'] = 'Grading Timeout';
$string['grading_timeout_desc'] = 'Zeit, nach der angenommen wird, dass vom Grader keine Antwort mehr kommt (in Sekunden)';

$string['gradepassed'] = 'Die Antwort ist richtig.';
$string['gradefailed'] = 'Die Antwort ist nicht vollständig richtig.';
$string['gradepartialpassed'] = 'Die Antwort ist zum Teil richtig.';
$string['gradeinternalerror'] = 'Die Antwort kann aufgrund eines internen Fehlers nicht bewertet werden.';
$string['testinternalerror'] = 'Beim Ausführen des Tests ist ein interner Fehler aufgetreten:';

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
$string['filepickeroptions'] = 'Filepicker-Einstellungen';


$string['all_or_nothing'] = 'alles oder nichts';
$string['weighted_sum'] = 'gewichtete Summe';
$string['aggregationstrategy'] = 'Aggregationsstrategie';
$string['aggregationstrategy_help'] = 'Aggregationsstrategie, die zum Ermitteln der Bewertung genutzt werden soll';

$string['internaltesterror'] = 'Während des Testlaufs wurde ein interner Fehler festgestellt.';

$string['privacy:metadata'] = 'Das ProFormA Frageformat Plugin speichert keine personenbezogenen Daten.';


// Version control system.

$string['versioncontrol'] = 'Version Control System';
$string['vcsuritemplate'] = 'URI des Repository (Template)';
$string['vcsuritemplate_help'] = 'Ort, von dem die studentische Einreichung abgeholt wird - muss genau einen Platzhalter enthalten:<br>
{group} = Gruppenname der Gruppe, zu der der Studierende gehört<br>
{input} = Eingabe des Studierenden';
// Maybe for future use: '{username} = student\'s login name in Moodle'.

$string['groupname'] = 'Gruppe';

$string['vcslabel'] = 'Name des Eingabefelds';
$string['vcslabel_help'] = 'Name des Eingabefelds {input} in der Anzeige';
$string['sampleuri'] = 'Beispiel-URI';

$string['defaultvcsuri'] = 'Standard-URI-Template';
$string['defaultvcsuri_desc'] = 'sollte Platzhalter enthalten';

$string['vcslabeldefault'] = 'Standard-Label für das Eingabefeld';
$string['vcslabeldefault_desc'] = 'wird benötigt für das Eingabefeld {input}';

$string['vcs_header'] = 'Version control system';
$string['vcs_info'] = 'Für Einreichungen, die sich in einem Versionskontrollsystem (z.B. SVN, git o.ä.) befinden.
Die tatsächliche URI einer studentischen Einreichung wird aus dem URI-Template durch Ersetzen des Platzhalters ermittelt:<br>
{group}: Name der Gruppe, zu der der Studierende gehört<br>
{input}: erzeugt ein Eingabefeld, in dem der Studierende einen Identifier eingeben muss';
// Maybe for future use: '{username}: takes the student\'s username in Moodle'.
// Maybe for future use: '{func}: return value of configured helper function'.

$string['proglang_hdr'] = 'Programmiersprachen';
$string['proglang_hdr_info'] = 'Java wird standardmäßig unterstützt. '
. 'Andere Programmiersprachen, die nicht benötigt werden, können hier abgeschaltet werden.';

$string['selectlangtitle'] = 'Markieren Sie die gewünschte Programmiersprache';

$string['infotaskupdate'] = 'Bitte Datei überarbeiten oder ProFormA-Dateiimport nutzen.';

$string['editorinput'] = 'Editor';
$string['fileinput'] = 'Dateien';


// Bulk test.
$string['bulktestnomodelsolution'] = 'Diese Frage besitzt keine Musterlösung.';
$string['bulktestnofile'] = 'Diese Frage besitzt keine ProFormA-Task-Datei.';
$string['replacedollarscount'] = 'Diese Kategorie enthält {$a} ProFormA-Frage(n).';
$string['testpassesandfails'] = '{$a->passes} Passes und {$a->fails} Fehler.';
$string['proformaInstall_testsuite_notests'] = 'Fragen ohne ProFormA-Task-Datei!';
$string['proformaInstall_testsuite_nomodelsolution'] = 'Fragen ohne Musterlösung: bitte eine Musterlösung hinzufügen!';
$string['proformaInstall_testsuite_failingtests'] = 'Fehlgeschlagene Tests';
$string['proformaInstall_testsuite_fail'] = 'Nicht alle Tests wurden bestanden!';
$string['proformaInstall_testsuite_pass'] = 'Alle Tests wurden bestanden!';
$string['bulktesttitle'] = 'Starten aller Fragen in {$a}';
$string['replacedollarsindex'] = 'Kurse und Kursbereiche mit ProFormA-Fragen';
$string['bulktestrun'] = 'Starten aller Tests aller Fragen im System (langsam, nur für Administratoren)';
$string['bulktestindextitle'] = 'Bulk - Test';
$string['overallresult'] = 'Gesamtergebnis';
$string['passed'] = 'OK';
$string['failed'] = 'Fehler';
$string['bulktestcontinuefromhere'] = 'Nochmal starten oder fortsetzen, beginnend ab hier';


$string['errinvalidtask'] = 'Datei ist keine ProFormA-Aufgabendatei.';
$string['errinvalidproglang'] = 'Programmiersprache ist nicht \'{$a}\'.';
$string['errcounttest'] = 'Anzahl der Tests wurde geändert: {$a}.';
$string['errtestsincompatible'] = 'Testtypen oder -reihenfolge stimmen nicht überein.';
$string['errtaskinvalid'] = 'ProFormA-Datei kann nicht gelesen werden.';
$string['errnotask'] = 'ProFormA-Task.xml nicht gefunden.';
$string['erroldtask'] = 'Original-ProFormA-Datei kann nicht geprüft werden.';
$string['errtasknotunique'] = 'ProFormA-task.xml ist nicht eindeutig.';
$string['errinvalidtaskxml'] = 'Task.xml in ProFormA-Datei ist ungültig.';
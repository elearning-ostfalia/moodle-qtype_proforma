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
 * @package    qtype_proforma
 * @copyright  2017 Ostfalia University of Applied Sciences
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     K.Borm <k.borm[at]ostfalia.de>*
 */

$string['acceptedfiletypes'] = 'Akzeptierte Dateitypen';
$string['acceptedfiletypes_help'] = 'Akzeptierte Dateien können durch Eingabe einer mit Semikolon getrennten Liste von Mimetypes eingeschränkt werden, z.B. \'text/plain; application/java-archive; application/zip; application/xml\'. Auch können Dateiendungen vorgegeben werden (inkl "."): z.B. \'.java; .jar\'. Wenn das Feld leer bleibt, dann werden alle Dateien erlaubt.';
$string['addtest'] = '{$a} hinzufügen';
$string['admincollapse'] = 'Feedback initial ein-/ausklappen';
$string['aggregationstrategy'] = 'Aggregationsstrategie';
$string['aggregationstrategy_help'] = 'Aggregationsstrategie, die zum Ermitteln der Bewertung genutzt werden soll';
$string['all_or_nothing'] = 'alles oder nichts';
$string['allowattachments'] = 'Max. Anzahl Dateien';
$string['alreadyexists'] = '{$a} existiert bereits';
$string['alternativegrader'] = 'Für (fast) alle Programmiersprachen kann ein alternativer Grader genutzt werden
Es ist nur das Protokoll und Server der Grader URI anzugeben, der Pfad wird übernommen.';
$string['always_collapse'] = 'einklappen';
$string['always_expand'] = 'ausklappen';
$string['attachments'] = 'Downloads:';
$string['bulktestcontinuefromhere'] = 'Nochmal starten oder fortsetzen, beginnend ab hier';
$string['bulktestindextitle'] = 'Bulk - Test';
$string['bulktestloaderror'] = 'Diese Frage kann nicht geladen werden (inkonsistente Daten):';
$string['bulktestnofile'] = 'Diese Frage besitzt keine ProFormA-Task-Datei.';
$string['bulktestnomodelsolution'] = 'Diese Frage besitzt keine Musterlösung.';
$string['bulktestrun'] = 'Starten aller Tests aller Fragen im System (langsam, nur für Administratoren)';
$string['bulktesttitle'] = 'Starten aller Fragen in {$a}';
$string['c_graderuri_host'] = 'Grader für c';
$string['c_graderuri_host_desc'] = '';
$string['changejavafilename'] = 'Java-Dateinamen sollten den Klassennamen und
- sofern vorhanden - den Paketnamen enthalten.

Daher ist der erwartete Dateiname "{$a}".
Soll der Dateiname zu "{$a}" geändert werden?';
$string['checkmodelsol'] = 'Prüfe Musterlösung';
$string['checkmodelsol_help'] = 'Starte Tests mit Musterlösung';
$string['checkmodelsollog'] = 'Prüfe Musterlösung (Log)';
$string['checkstyleversion'] = 'Checkstyle Version';
$string['checkstyleversion_desc'] = 'Komma separierte Liste mit Checkstyle-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';
$string['checkstylewarnings'] = 'Max. Anzahl Warnungen';
$string['clang'] = 'CUnit Test';
$string['code'] = 'Quellcode';
$string['codeempty'] = 'Testcode fehlt.';
$string['collapse'] = 'Initial Ein-/Ausklappen';
$string['collapse_help'] = 'Das Feedback einiger Tests wird als sog. "Collapsible Region" dargestellt.<br>
Diese Einstellung gibt an, ob die Region initial eingeklappt (collapsed) oder ausgeklappt (expanded) ist.';
$string['comment'] = 'Kommentar';
$string['commentheader'] = 'Kommentar';
$string['compile'] = 'Compilation';
$string['configmaxbytes'] = 'maximale Summe der Dateigrößen der studentischen Einreichung';
$string['confirmdeletefile1'] = 'Datei "{$a->id}" "{$a->filename}" wird immer noch referenziert!

Soll die Datei trotzdem gelöscht werden?';
$string['confirmdeletefile2'] = 'Soll Datei "{$a->filename}" wirklich gelöscht werden?';
$string['confirmdeletetest'] = "Soll der Test wirklich aus der Aufgabe entfernt werden?
Nicht mehr referenzierte Dateien werden ebenfalls entfernt.";
$string['cpp_graderuri_host'] = 'Grader für C++';
$string['cpp_graderuri_host_desc'] = '';
$string['createdtask_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei. Achtung: Aufgabe muss erst gespeichert werden, damit die entsprechende ProFormA-Datei erzeugt wird.';
$string['cunit_help'] = 'Die Testdateien müssen
    ein Makefile (auch <code>CMakeLists.txt</code> für <i>cmake</i> möglich) und Dateien mit Testcode enthalten.<br>
    <code>main()</code> muss bei fehlerfreiem Testdurchlauf 0 liefern, im Fehlerfall <> 0.<br>
    CUnit muss im <i>basic mode</i> ausgeführt werden.<br>
    Die Dateien können einzeln hochgeladen werden oder aber alle als genau eine zip-Archiv gepackt sein.<br>
    Wenn das Executable, was vom Makefile erzeugt wird, <code>test</code> heißt, dann ist der Befehl zum Ausführen des Tests <code>./test</code>.';
$string['cunit_help_short'] = 'Die Testdateien müssen
    ein Makefile (auch <code>CMakeLists.txt</code> für <i>cmake</i> möglich) und Dateien mit Testcode enthalten.<br>
    <code>main()</code> muss bei fehlerfreiem Testdurchlauf 0 liefern, im Fehlerfall <> 0.<br>
    CUnit muss im <i>basic mode</i> ausgeführt werden.<br>
    Die Dateien können einzeln hochgeladen werden oder aber alle als genau eine zip-Archiv gepackt sein.';
$string['defaultpenalty'] = 'Abzug bei Fehlversuchen';
$string['defaultpenalty_desc'] = 'Abzug bei Fehlversuchen, falls Frageverhalten auf "Mehrfachbeantwortung (mit Abzügen)" gestellt ist';
$string['defaultvcsuri'] = 'Standard-URI-Template';
$string['defaultvcsuri_desc'] = 'sollte Platzhalter enthalten';
$string['delete'] = 'Löschen';
$string['deletefile'] = 'Soll die Datei {$a} wirklich gelöscht werden?';
$string['deletefolder'] = 'Soll das Verzeichnis {$a} wirklich gelöscht werden?';
$string['downloadmodelsolution'] = 'Download Musterlösung';
$string['downloads'] = 'Dateianhänge zur Aufgabenstellung';
$string['downloads_hint'] = 'Dateianhänge zur Aufgabenstellung';
$string['downloads_hint_help'] = 'Dateien, die zum Lösen der Aufgabe benötigt werden und von den Studierenden heruntergeladen werden können';
$string['downloadtask'] = 'Download Aufgabendatei';
$string['editorinput'] = 'Editor';
$string['edittestdetails'] = 'Testdetails bearbeiten';
$string['enterfilename'] = 'Neuer Dateiname';
$string['enterfoldername'] = 'Neuer Verzeichnisname';
$string['entrypoint'] = 'Entrypoint';
$string['entrypointerror'] = 'Kann Klassennamen nicht ermitteln (Entrypoint).';
$string['entrypointrequired'] = 'JUnit Entrypoint muss angegeben werden.';
$string['errcounttest'] = 'Anzahl der Tests wurde geändert: {$a}.';
$string['errinvalidproglang'] = 'Programmiersprache ist nicht \'{$a}\'.';
$string['errinvalidtask'] = 'Datei ist keine ProFormA-Aufgabendatei.';
$string['errinvalidtaskxml'] = 'Task.xml in ProFormA-Datei ist ungültig.';
$string['errmissingquestioninput'] = 'Der Fragename muss angegeben werden.';
$string['errmissingtest'] = 'Es muss mindestens ein Test mit den zugehörigen Dateien angegeben werden.';
$string['errnotask'] = 'ProFormA-Task.xml nicht gefunden.';
$string['erroldtask'] = 'Original-ProFormA-Datei kann nicht geprüft werden.';
$string['errtaskinvalid'] = 'ProFormA-Datei kann nicht gelesen werden.';
$string['errtasknotunique'] = 'ProFormA-task.xml ist nicht eindeutig.';
$string['errtestconfigambiguous'] = 'Testkonfiguration konnte nicht eindeutig ermittelt werden für
"{$a->title}".
Es wird "{$a->config}" angenommen.';
$string['errtestsincompatible'] = 'Testtypen oder -reihenfolge stimmen nicht überein.';
$string['executable'] = 'Befehl zum Ausführen des Tests';
$string['executablerequired'] = 'Befehl zum Ausführen des Tests muss angegeben werden.';
$string['expand_small'] = 'für kleine Regionen ausklappen';
$string['expand_student'] = 'für Studierende ausklappen';
$string['expand_teacher'] = 'für Lehrende ausklappen';
$string['explorerautosave'] = 'Autosave-Intervall bei Explorer-Eingabe';
$string['explorerautosave_desc'] = 'In der Eingabeform "Explorer" werden die Eingaben im Editor regelmäßig zum
Server zurückgesichert. Dieser Wert gibt das Zeitintervall in Sekunden an.
Wenn kein Wert angegeben wird, dann werden keine Eingaben gesichert.';
$string['explorerdarkmode'] = 'Zum dunklen Modus wechseln';
$string['explorerlightmode'] = 'Zum hellen Modus wechseln';
$string['failed'] = 'Fehler';
$string['feedbackoptions_heading'] = 'Feedback';
$string['fileexists'] = 'Datei "{$a}" existiert bereits.';
$string['fileinput'] = 'Dateien';
$string['filename'] = 'Dateiname';
$string['filename_hint'] = 'Dateiname';
$string['filename_hint_help'] = 'Da bei Nutzung des Editors als Eingabeform kein Dateiname zum Kompilieren vorhanden ist, muss dieser fest vorgegeben werden.
Bitte beachten, dass u.U. auch das Package im Namen enthalten sein muss.';
$string['filenameerror'] = 'Kann Klassennamen nicht ermitteln (Dateiname).';
$string['filepickeroptions'] = 'Datei-Upload-Einstellungen';
$string['fullscreen_help'] = 'Vollbildmodus umschalten';
$string['gradefailed'] = 'Die Antwort ist nicht vollständig richtig.';
$string['gradeinternalerror'] = 'Die Antwort kann aufgrund eines internen Fehlers nicht bewertet werden.';
$string['gradepartialpassed'] = 'Die Antwort ist zum Teil richtig.';
$string['gradepassed'] = 'Die Antwort ist richtig.';
$string['grader_heading'] = 'Grader-Einstellungen';
$string['graderoptions_header'] = 'Grader';
$string['graderuri_host'] = 'URI: Protokoll und Server';
$string['graderuri_host_desc'] = 'Protokoll (Schema) und Server der Grader URI';
$string['graderuri_path'] = 'URI: Pfad';
$string['graderuri_path_desc'] = 'Pfad der Grader URI';
$string['grading_timeout'] = 'Grading Timeout';
$string['grading_timeout_desc'] = 'Zeit, nach der angenommen wird, dass vom Grader keine Antwort mehr kommt (in Sekunden)';
$string['groupname'] = 'Gruppe';
$string['gtest_help'] = 'Die Testdateien müssen
    ein Makefile (auch <code>CMakeLists.txt</code> für <i>cmake</i> möglich) und Dateien mit Testcode enthalten.<br>
    <code>main()</code> muss bei fehlerfreiem Testdurchlauf 0 liefern, im Fehlerfall <> 0.<br>
    Die Dateien können einzeln hochgeladen werden oder aber alle als genau eine zip-Archiv gepackt sein.<br>
    Wenn das Executable, was vom Makefile erzeugt wird, <code>test</code> heißt, dann ist der Befehl zum Ausführen des Tests <code>./test</code>.';
$string['gtest_help_short'] = 'Die Testdateien müssen
    ein Makefile (auch <code>CMakeLists.txt</code> für <i>cmake</i> möglich) und Dateien mit Testcode enthalten.<br>
    <code>main()</code> muss bei fehlerfreiem Testdurchlauf 0 liefern, im Fehlerfall <> 0.<br>
    Die Dateien können einzeln hochgeladen werden oder aber alle als genau eine zip-Archiv gepackt sein.';
$string['highlight'] = 'Syntax Highlighting';
$string['highlight_hint'] = 'Programmiersprache';
$string['highlight_hint_help'] = 'Programmiersprache, die für das Syntax Highlighting genutzt werden soll';
$string['infoexplorer'] = 'Aktuell unterstützt das Explorerformat keine Unterverzeichnisse.';
$string['infotaskupdate'] = 'Bitte Datei überarbeiten oder ProFormA-Dateiimport nutzen.';
$string['inlinemessages'] = 'Meldungen in Editor anzeigen';
$string['inlinemessages_desc'] = 'Meldungen von z.B. Compiler oder statischen Analysewerkzeugen können direkt in den Editor eingeblendet werden. Erfordert die Nutzung von CodeMirror.';
$string['inlinemessages_help'] = 'Meldungen von z.B. Compiler oder statischen Analysewerkzeugen können direkt in den Editor eingeblendet werden. Dies ist u.U. nicht für alle Werkzeuge verfügbar.';
$string['internaltesterror'] = 'Während des Testlaufs wurde ein interner Fehler festgestellt.';
$string['invalidproglang'] = 'Version der Programmiersprache ungültig:';
$string['javasettings_header'] = 'Einstellungen für Java-Fragen, die mit Moodle erzeugt werden';
$string['javaversion'] = 'Java Version';
$string['javaversion_desc'] = 'Komma separierte Liste mit Java-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';
$string['junit'] = 'JUnit Test';
$string['junit_help_short'] = 'Die Dateien können einzeln hochgeladen werden oder aber alle als genau eine zip-Archiv gepackt sein.';
$string['junitentry_help'] = 'Hauptklasse der Testfunktionen inkl. Package-Name-Präfix,<br>
z.B. <code>de.ostfalia.informatik.prog.test1</code>';
$string['junitversion'] = 'JUnit Version';
$string['junitversion_desc'] = 'Komma separierte Liste mit JUnit-Versionen, die vom Grader unterstützt werden. Die erste ist Standard.';
$string['loadfile'] = 'Datei laden';
$string['makerun_help'] = 'Beispiel: Wenn das Executable, was vom Makefile erzeugt wird, <code>test</code> heißt, dann ist der Befehl zum Ausführen des Tests <code>./test</code>.';
$string['maxbytes'] = 'Max. Größe aller Dateien';
$string['maximumsubmissionsize'] = 'Max. Größe aller Dateien';
$string['maximumsubmissionsize_help'] = 'Die Summe der Dateigrößen aller hochgeladene Dateien darf diesen Wert nicht überschreiten.';
$string['maximumtasksize_help'] = 'Maximale Größe einer ProFormA-Aufgabendatei';
$string['miscellaneousheader'] = 'Verschiedenes';
$string['modelsolfiles'] = 'Datei(en) der Musterlösung';
$string['modelsolfiles_hint'] = 'Datei(en) der Musterlösung';
$string['modelsolfiles_hint_help'] = 'Dateien, die zusammen die Musterlösung bilden';
$string['modelsolution'] = 'Musterlösung';
$string['modelsolution_help'] = 'Beispiellösung für die Aufgabe, wie sie dem Studierenden angezeigt werden soll, falls die Option "Richtige Antwort" in den Überprüfungsoptionen des Tests angeschaltet ist.';
$string['modelsolutionheader'] = 'Musterlösung';
$string['msfilename'] = 'Datei ';
$string['new'] = 'neu';
$string['newemptyfile'] = 'Neue Datei';
$string['newfile'] = '<Neue Datei>';
$string['newfolder'] = 'Neues Verzeichnis';
$string['nlines'] = '{$a} Zeilen';
$string['nocodemirror'] = 'CodeMirror ist deaktiviert. Daher kann das Anzeigen von Meldungen im Editor nicht genutzt werden.';
$string['none'] = 'ohne';
$string['nonexistentfiletypes'] = 'Die folgenden Dateitypen konnten nicht erkannt werden: {$a}';
$string['notests'] = 'Keine Tests gefunden => benutze "all or nothing"';
$string['old'] = 'alt';
$string['openfile'] = '<Öffne Datei...>';
$string['other'] = 'andere';
$string['overallresult'] = 'Gesamtergebnis';
$string['passed'] = 'OK';
$string['pluginname'] = 'ProFormA-Aufgabe';
$string['pluginname_help'] = 'Programmieraufgabe basierend auf einer ProFormA-Aufgabendatei mit automatischer Bewertung. Der Studierende kann entweder seine Lösung als Datei hochladen oder sie direkt in einem Editor eingeben. Dazu kann eine Code-Vorlage vorgegeben werden. ';
$string['pluginnameadding'] = 'ProFormA-Aufgabe hinzufügen';
$string['pluginnameediting'] = 'ProFormA-Aufgabe ändern';
$string['pluginnamesummary'] = 'Automatisch bewertete Programmieraufgabe.';
$string['privacy:metadata'] = 'Das ProFormA Frageformat Plugin speichert keine personenbezogenen Daten.';
$string['proforma:runbulktest'] = 'Starten des ProFormA-Bulktests';
$string['proforma:viewsysteminfo'] = 'ProFormA-Systeminformationen sehen';
$string['proformaInstall_testsuite_fail'] = 'Nicht alle Tests wurden bestanden!';
$string['proformaInstall_testsuite_loaderror'] = 'Fragen, die nicht geladen werden können (inkonsistente Daten)';
$string['proformaInstall_testsuite_failingtests'] = 'Fehlgeschlagene Tests';
$string['proformaInstall_testsuite_nomodelsolution'] = 'Fragen ohne Musterlösung: bitte eine Musterlösung hinzufügen!';
$string['proformaInstall_testsuite_notests'] = 'Fragen ohne ProFormA-Task-Datei!';
$string['proformaInstall_testsuite_pass'] = 'Alle Tests wurden bestanden!';
$string['proglang'] = 'Programmiersprache';
$string['proglang_hdr'] = 'Programmiersprachen';
$string['proglang_hdr_info'] = 'Java wird standardmäßig unterstützt.
 Andere Programmiersprachen, die nicht benötigt werden, können hier abgeschaltet werden.';
$string['proglang_hint'] = 'Programmiersprache';
$string['proglang_hint_help'] = 'Verwendete Programmiersprache der Aufgabe. Zur Zeit können nur Java-Aufgaben erstellt werden.';
$string['proglangversion'] = 'Version der Programmiersprache';
$string['proglangversion_hint'] = 'Version der Programmiersprache';
$string['proglangversion_hint_help'] = 'Version der Programmiersprache.';
$string['python_graderuri_host'] = 'Grader für Python';
$string['python_graderuri_host_desc'] = '';
$string['pythondoc_help'] = 'Der Testcode muss in genau einer Datei enthalten sein.';
$string['questiondefaults'] = 'Standardwerte bei neuen Aufgaben';
$string['regexpfromgrader'] = 'Regular Expressions des Praktomat nutzen';
$string['regexpfromgrader_desc'] = 'Um die Compilermeldungen anzuzeigen, wird der Compileroutput mittels Regular Expression geparst. Normalerweise sollte der Regular Expression vom Grader verwendet werden.
    Bei Problemen kann jedoch auf den Regular Expression des Plugins zurückgegriffen werden.';
$string['rename'] = 'Umbenennen';
$string['replacedollarscount'] = 'Diese Kategorie enthält {$a} ProFormA-Frage(n).';
$string['replacedollarsindex'] = 'Kurse und Kursbereiche mit ProFormA-Fragen';
$string['repository'] = 'Repository';
$string['repository_hint'] = 'Repository';
$string['repository_hint_help'] = 'URI des ProFormA-Repository';
$string['repositoryhost'] = 'Host des Repository';
$string['repositoryhost_desc'] = 'Host des Repository, beginnend mit der Protokoll-Angabe (https://)';
$string['repositorypath'] = 'Verzeichnis im Repository';
$string['repositorypath_desc'] = 'Verzeichnis einer ProFormA-Aufgabe im Repository';
$string['responsefieldlines'] = 'Anzahl Zeilen';
$string['responsefilename'] = 'Dateiname';
$string['responseformat'] = 'Eingabeform';
$string['responseoptions'] = 'Antwortformat';
$string['responsetemplate'] = 'Codevorlage';
$string['responsetemplate_help'] = 'Text wird bei Beginn eines neuen Versuchs im Editor für den Studierenden angezeigt (bzw. als Download angeboten). Wenn mehr als eine Vorlage existiert, ist nur die erste hier änderbar.';
$string['responsetemplateheader'] = 'Codevorlagen';
$string['rootsubmission'] = 'Lösung';
$string['runtesturi_path'] = 'URI: Pfad zum Laufenlassen der Tests (mit Fortschrittsinformationen)';
$string['runtesturi_path_desc'] = 'Pfad zum Laufenlassen der Tests mit Fortschrittsinformationen, ab Praktomat 4.17';
$string['sampleuri'] = 'Beispiel-URI';
$string['selectlangtitle'] = 'Markieren Sie die gewünschte Programmiersprache';
$string['setlx'] = 'SetlX Test';
$string['submissionproformaversion'] = 'ProFormA Version';
$string['submissionproformaversion_help'] = 'ProFormA-Version, die für die Kommunikation zwischen Moodle und Grader verwendet werden soll.
Version "2.1 neu" erfordert mindestens Praktomat 4.15.0. "2.1 neu" ist erforderlich, um Abgaben in git zu prüfen.';
$string['sumweightzero'] = 'Die Summe aller Gewichte muss größer als null sein.';
$string['syntaxcheck'] = 'Syntax Check';
$string['task_hint'] = 'ProFormA-Aufgabendatei';
$string['task_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei.<br>
Wenn diese ersetzt werden soll, bitte beachten, dass die neue Datei <it>kompatibel</it> zur alten ist.<br>
Das bedeutet: <br>
- gleiche Programmiersprache<br>
- gleiche Anzahl Tests<br>
- gleiche Testtypen in gleicher Reihenfolge';
$string['taskeditor'] = 'Aufgabe bearbeiten';
$string['taskeditorattached'] = 'Binärdatei';
$string['taskeditorbinary'] = 'Binärdatei';
$string['taskeditorbinaryfile'] = 'Binärdatei - kann hier nicht bearbeitet werden.';
$string['taskeditordropzone'] = 'Drop zone';
$string['taskeditoredit'] = 'Bearbeiten';
$string['taskeditorembedded'] = 'Textdatei';
$string['taskeditorentrypointhelp'] = 'Name der Klasse, die die Main-Methode enthält - inkl. vollem Package-Pfad (z.B. de.ostfalia.zell.editor)';
$string['taskeditorfile'] = 'Datei';
$string['taskeditorfiles'] = 'Dateien';
$string['taskeditorfilesize'] = 'Dateigröße';
$string['taskeditorhide'] = 'Ausblenden';
$string['taskeditornewcheckstyle'] = 'Neuer Checkstyle Test';
$string['taskeditornewcunit'] = 'Neuer CUnit Test';
$string['taskeditornewgoogletest'] = 'Neuer Google Test';
$string['taskeditornewjavacomp'] = 'Neuer Java Compilertest';
$string['taskeditornewjunit'] = 'Neuer JUnit Test';
$string['taskeditornewpythondoc'] = 'Neuer Python Doctest';
$string['taskeditornewpythonunit'] = 'Neuer Python Unittest';
$string['taskeditorstore'] = '';
$string['taskeditortests'] = 'Tests';
$string['taskeditorview'] = 'Ansehen';
$string['taskeditorviewlist'] = 'Liste';
$string['taskeditorviewtree'] = 'Baum';
$string['taskfilename'] = 'ProFormA-Aufgabendatei';
$string['taskfilename_hint'] = 'ProFormA-Aufgabendatei';
$string['taskfilename_hint_help'] = 'Zugehörige ProFormA-Aufgabendatei';
$string['taskmaxbytes'] = 'Maximale Größe Aufgabendatei';
$string['taskpath'] = 'Verzeichnis';
$string['taskpath_hint'] = 'Verzeichnis';
$string['taskpath_hint_help'] = 'Verzeichnis der ProFormA-Aufgabendatei im Repository';
$string['templateeditor'] = '';
$string['templates'] = 'Weitere Codevorlagen';
$string['templates_hint'] = 'Weitere Codevorlagen';
$string['templates_hint_help'] = 'Namen der zusätzlichen Codevorlagen (meist gibt es nur eine Codevorlage)';
$string['testcode'] = 'Testcode';
$string['testcodefiles'] = 'Testcode-Dateien';
$string['testdescription'] = 'Beschreibung';
$string['testinternalerror'] = 'Beim Ausführen des Tests ist ein interner Fehler aufgetreten:';
$string['testpassesandfails'] = '{$a->passes} Passes und {$a->fails} Fehler.';
$string['tests'] = 'Bewertung';
$string['testtitle'] = 'Titel';
$string['testtype'] = 'Typ';
$string['titleempty'] = 'Titel fehlt.';
$string['upload'] = 'Upload Aufgabendatei zum Grader';
$string['upload_help'] = 'vorheriges Hochladen kann erstmalige Testzeit verkürzen';
$string['uploadlog'] = 'Upload (Log)';
$string['usecodemirror'] = 'CodeMirror als Quelltexteditor benutzen';
$string['usecodemirror_desc'] = 'CodeMirror kann im Editor (studentische Sicht) und für die Eingabe der Codevorlage (Dozentensicht) eingesetzt werden. ';
$string['useembeddedmessages'] = 'verwenden';
$string['uuid_hint_help'] = 'UUID (universal unique identifier) der ProFormA-Aufgabendatei';
$string['vcs_header'] = 'Version control system';
$string['vcs_info'] = 'Für Einreichungen, die sich in einem Versionskontrollsystem (z.B. SVN, git o.ä.) befinden.
Die tatsächliche URI einer studentischen Einreichung wird aus dem URI-Template durch Ersetzen des Platzhalters ermittelt:<br>
{group}: Name der Gruppe, zu der der Studierende gehört<br>
{groupl}: Name der Gruppe, zu der der Studierende gehört (Kleinbuchstaben)<br>
{input}: erzeugt ein Eingabefeld, in dem der Studierende einen Identifier eingeben muss';
$string['vcslabel'] = 'Name des Eingabefelds';
$string['vcslabel_help'] = 'Name des Eingabefelds {input} in der Anzeige';
$string['vcslabeldefault'] = 'Standard-Label für das Eingabefeld';
$string['vcslabeldefault_desc'] = 'wird benötigt für das Eingabefeld {input}';
$string['vcssystem'] = 'Art des Systems';
$string['vcsuritemplate'] = 'URI des Repository (Template)';
$string['vcsuritemplate_help'] = 'Ort, von dem die studentische Einreichung abgeholt wird - muss genau einen Platzhalter enthalten:<br>
{group} = Gruppenname der Gruppe, zu der der Studierende gehört<br>
{groupl} = wie {group} in Kleinbuchstaben<br>
{input} = Eingabe des Studierenden';
$string['version'] = 'Version';
$string['versioncontrol'] = 'Version Control System';
$string['versionrequired'] = 'Version ist nicht ausgewählt.';
$string['weight'] = 'Gewicht';
$string['weighted_sum'] = 'gewichtete Summe';

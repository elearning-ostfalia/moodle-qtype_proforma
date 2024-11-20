## 3.1.4

* adapt to Moodle 4.4

## 3.1.3

* if there is no test result than handle as internal error (needs grading state) 

## 3.1.2

* make string resources compliant to AMOS requirements (fix coding errors)

## 3.1.1

* make string resources compliant to AMOS requirements (fix coding errors)
* improve compatibility with old moodle XML for import

## 3.1.0

* taskeditor: provide fullscreen mode 
* improve compatibility with old moodle XML for import

## 3.0.2

* explorer view: adapt to changes in Moodle UI (check and finish attempt)
* explorer: prevent racing on new file

## 3.0.1

* taskeditor: improve performance for startup
* show loading image while opening taskeditor 
* hide default button in logmontor while running

## 3.0.0

* new: task editor for editing imported ProFormA tasks and for creating new tasks
* bugfix: context menu in explorer now appears on first click
* bugfix: explorer tab was not renamed if file is renamed
* new: open file if new file is created in explorer

## 2.11.0

new: separate upload task function 

## 2.10.1

Bugfix: groupl parameter for version control system did not work


## v2.10.0

new: support for git for external submissions from source control system
implement new proforma 2.1 format for grader requests
proforma format 2.0 is no longer supported for grader requests

## v2.9.0

new: Explorer response format

## v2.8.1

* bugfix: SVN and groups: get group according to quiz restrictions 

## v2.8.0

* new: Python unittest

## v2.7.3

* display extra log for Googletest (Praktomat 4.12.2)

## v2.7.2

* new: C++ support 
* bulktestindex improvements
* avoid wrapping of test log in feedback
* bugfix inline messages in editor 
* default Java 17

## v2.7.1

* bugfix installation without grader

## v2.7.0

* new: create and grade c (CUnit) questions
* refactor code for creating questions

## v2.6.0

* support for Proforma version 2.1 in request and response
* read message regular expression from grader
* bugfix: do not embed compiler messages for test files
* add validation for missing tests
* do not add compilation test by default

## v2.5.3

* bugfix: VCS: getting groupname with groupings

## v2.5.2

* remove beginning and trailing spaces in responsefilename

## v2.5.1

* correction of release identifier

## v2.5.0

* editor 'inline' messages for Checkstyle and Java compiler 
* show grader settings for teacher (Java and Setlx)

## v2.4.0 

* bugfix: update UUID and ProFormA version after task file update in ProFormA editor
* new: support for file upload in Java unit tests in Java editor (requires Praktomat 4.8.0)
* settings: Java version 11 as default
* layout changes for unit tests
* new: display grader version in settings editor

## v2.3.1

* new: bulktest
* new: proforma:viewsysteminfo capability for viewing grader response and ProFormA task
* new: simple jenkins pipeline file

## v2.3.0

* new: editor for creating SetlX questions (not enabled by default)
* new: allow updating actual ProFormA task file after import
* new: connection test in settings
* new: show sample URI for use of version control in teachers' preview
* improved feedback for response format errors and internal test errors
* send editor submission as base 64 encoded to grader in order to avoid problems with illegal 
* xml characters in student input (requires Praktomat 4.7)

## v2.2.1

* format: print all titles in feedback list (fits Praktomat 4.6.0 output changes)

## v2.2.0

* #5: bugfix duplicating questions
* #6: return error message for missing files in summarise_response (instead of exception, merge from 2.1.2)
* new: 'download files' (for question description) can be edited
* new: Java: support for different versions of Checkstyle, Java and JUnit
* edit form: hide field 'Syntax highlighting' when responseformat <> editor
* update default port number for grader to fit Praktomat
* Java: parse Java Generics for evaluating classname/file
* disable xml mode because of incompatiblity with behat tests (bug?)
* improve javascript loading
* check for sum of weights = 0 (i.e. avoid division by zero in 'fraction')
* update language texts

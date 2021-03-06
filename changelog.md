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

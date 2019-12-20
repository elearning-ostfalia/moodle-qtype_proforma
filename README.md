The ProForma Moodle Question Type is a Moodle Plugin that is used for 
automatically grading programming questions in Moodle quizzes. Questions are 
stored in the ProFormA format (https://github.com/ProFormA/proformaxml) version 2.0. 

Standard test frameworks (e.g. JUNIT for Java) are used for specifying tests. So there is no 
need to learn a new test description language. The plugin comes with a built-in
Java question generator.

Tests are run on an back-end system (grader or test runner) that also conforms to the ProFormA standard 
(https://github.com/ProFormA/proformaxml) version 2.0.  
 
Simple Java questions can be created in Moodle with the ProFormA question editor. 
On the other hand questions with any programming language can be imported as long as 
the test runner supports the programming language.

Copyright note: The renderer code partly bases upon the renderer from essay question type (Moodle core).
Small code parts (in particular in qbehaviour_adaptiveexternalgrading) are copied from Coderunner 
(https://moodle.org/plugins/qtype_coderunner).
  
## Features

- Java code can be checked with JUnit and Checkstyle
- submission as file upload or as input into editor
- submission file upload with more than one file 
- syntax highlighting in editor
- code snippet as starting point for student
- immediate feedback for students (optional) 
- supported programming languages are only limited by the available grader back-ends
 

## Screenshots

Student view with editor input (code template and syntax highlighting): 
![editor with code template](doc/student_editor.png "student view with editor")

Alternative: File upload with filepicker for large files or if the response consists of more than one file:

![filepicker](doc/student_filepicker.png "student view with filepicker")

Student feedback for Java question:

![filepicker](doc/student_feedback_2.png "feedback")
 

## Installation

####  Prerequisites 

The ProFormA question type requires:

- the Moodle plugin "qbehaviour_adaptiveexternalgrading" 
(https://github.com/elearning-ostfalia/moodle-qbehaviour_adaptiveexternalgrading) for 
question engine adaptation and

- a ProFromA grading back-end to run the tests.  
ProFormA-Praktomat (https://github.com/elearning-ostfalia/Proforma-Praktomat) is recommended.

#### Import from external sourcses

For importing questions from an external source an import plugin is available (optional):   

- Moodle-Plugin qformat_proforma (import for ProFormA questions)

We have a separate Javascript editor for creating tasks (https://github.com/ProFormA/formatEditor).
 
An online version is available at 
https://media.elan-ev.de/proforma/editor/releases/3.0.2/proformaEditor.html
       
Of course ProFormA tasks can be created by different other external tools as well.


<!-- Import process:

![import](doc/import_en.png "import")
-->

## Settings


### Admin settings

At least the following settings must be made:  

* set grader URI to IP address and port number of your 'ProFormA-Praktomat server'

and for the built-in Java question generator: 

* set Java version
* set Checkstyle version
* set JUnit version

### Quiz settings

In order to display the grading feedback to the student you need to set the following 
options in your Moodle quiz:

* Question behaviour: 'Adaptive' (with our without penalties) for displaying immediate feedback
  or 'deferred feedback' if the student  shall not see any feedback 
* Review Options: 'Specific Feedback' set to 'on' for showing detailed grading results
* Review Options: opt out 'Right Answer' if you do not want to show the model solution



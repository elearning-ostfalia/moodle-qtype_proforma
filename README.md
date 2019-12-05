The ProForma Moodle Question Type is a Moodle Plugin that is used for 
automatically graded programming questions in Moodle quizzes.

Standard test frameworks (e.g. JUNIT for Java) are used for specifying tests. So there is no 
need to learn a new test description language.

Tests are run on an back-end system (grader) that conforms to the ProFormA standard 
(https://github.com/ProFormA/proformaxml). It is not actually a grader but is 
so called. 
 
Simple Java questions can be created in Moodle with the ProFormA question editor. 
Otherwise questions with any programming language can be imported as long as there is
an external possibility to create them and the test runner supports the programming language.

Copyright note: The renderer code partly bases upon the renderer from essay question type (Moodle core).
Small code parts (in particular in qbehaviour_adaptiveexternalgrading) are copied from Coderunner 
(https://moodle.org/plugins/qtype_coderunner).
  
## Features

- Java code can be checked with JUnit and Checkstyle
- Moodle quiz question type 
- submission as file upload or as input into editor
- Submission file upload with more than one file 
- Syntax highlighting in editor
- Code snippet as starting point for student
- immediate feedback for students (optional) 
- supported programming languages are only limited by the available grader back-ends
 

## Screenshots

Student view with editor input (code template and syntax highlighting): 
![editor with code template](doc/student_editor.png "student view with editor")

Alternative: File upload with filepicker for large files or if the response consists of more than one file:

![filepicker](doc/student_filepicker.png "student view with filepicker")

Feedback from grader:

![filepicker](doc/student_feedback_2.png "feedback")
 

## Installation


Running this Plugin also requires the following plugins to be installed:

- Moodle-Plugin qbehaviour_adaptiveexternalgrading (question engine changes)

If you want to import ProFormA tasks from another source: 

- Moodle-Plugin qformat_proforma (import for ProFormA questions)

We have a separate editor for creating tasks (https://github.com/ProFormA/formatEditor). 
There is an online version available at 
https://media.elan-ev.de/proforma/editor/releases/3.0.1/proformaEditor.html       
Of course ProFormA tasks can be created from different other external tools as well.

Besides:
 
- A ProFromA grading back-end is required to run the tests. We use 
ProFormA-Praktomat (https://github.com/elearning-ostfalia/Proforma-Praktomat) for testing 
Java code with JUnit and Checkstyle.


Import process:

![import](doc/import_en.png "import")

## Settings


### Admin settings

* set grader URI to IP address and port number of your 'ProFormA-Praktomat server'

* set Java version
* set Checkstyle version
* set JUnit version

### Quiz settings

In order to show the grading feedback to the student you need to set the following options in your Moodle quiz:

* Question behaviour: 'Adaptive' (with our without penalties) for showing immediate feedback
  or 'deferred feedback' if the student  shall not see any feedback 
* Review Options: 'Specific Feedback' set to 'on' for showing detailed grading results
* Review Options: opt out 'Right Answer' if you do not want to show the model solution



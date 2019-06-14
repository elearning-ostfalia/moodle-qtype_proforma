The ProForma Moodle Question Type is a Moodle Plugin that is used for automatically graded programming questions in Moodle quizzes.
Grading is performed by an external grading system that conforms to the ProFormA standard 
(https://github.com/ProFormA/proformaxml).
For writing tests standard test frameworks (e.g. JUNIT for Java) can be is used. 

  
The renderer code partly bases upon the renderer from essay question type (Moodle core).
Small code parts (in particular in qbehaviour_adaptiveexternalgrading) are copied from Coderunner 
(https://moodle.org/plugins/qtype_coderunner).  

## Overview

Running this Plugin also requires the following plugins to be installed:

- Moodle-Plugin qbehaviour_adaptiveexternalgrading (question engine changes)
- Moodle-Plugin qformat_proforma (import for ProFormA questions)

Ostfalia-Praktomat (https://github.com/elearning-ostfalia/Ostfalia-Praktomat) is used as 
grading back-end. 

Questions must be imported from ProFormA tasks. ProFormA tasks can be created from different sources.
We use a separate editor for creating those tasks (https://github.com/ProFormA/formatEditor).


## Settings


### Admin settings

* set grader URI to IP address and port number of your 'Ostfalia Praktomat server'

### Quiz settings

In order to show the grading feedback to the student you need to set the following options in your Moodle quiz:

* Question behaviour: Adaptive mode (with our without penalties) for showing immediate feedback
* Review Options: 'Specific Feedback' set to 'on' for showing detailed grading results
* Review Options: opt out 'Right Answer' if you do not want to show the model solution



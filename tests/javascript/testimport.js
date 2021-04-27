// let module = await import('./inlineerrors.js');




import('/amd/src/inlinemessages.js')
.then((module) => {
        console.log("hello");
        // alert("hello");
        function docReady(fn) {
            // see if DOM is already available
            if (document.readyState === "complete" || document.readyState === "interactive") {
                // call on next available tick
                setTimeout(fn, 1);
            } else {
                document.addEventListener("DOMContentLoaded", fn);
            }
        }

        docReady(function() {
                let  regexp = "\\[(?<msgtype>[A-Z]+)\\]\\s(?<filename>\\/?(.+\\/)*(.+)\\.([^\\s:]+)):(?<line>[0-9]+)(:(?<column>[0-9]+))?:\\s(?<text>.+\\.)\\s\\[(?<short>\\w+)\\]";

                let editor = CodeMirror.fromTextArea(
                    document.getElementById("editor"), {
                        lineNumbers: true,
                });
                module.embedError("editor", "m-id-test-proforma-2044604495-3", regexp, 1);


                let editor2 = CodeMirror.fromTextArea(
                    document.getElementById("editor2"), {
                        lineNumbers: true,
                });                                
                module.embedError("editor2", "m-id-test-proforma-2044604495-4", regexp);

                let  regexpSetlx = "line\\ (?<line>[0-9]+)(:(?<column>[0-9]+))?\\s(?<text>.+)";

                let editor3 = CodeMirror.fromTextArea(
                    document.getElementById("editor3"), {
                        lineNumbers: true,
                });                                
                module.embedError("editor3", "m-id-test-proforma-2044604495-5", regexpSetlx);
                
            });
  });






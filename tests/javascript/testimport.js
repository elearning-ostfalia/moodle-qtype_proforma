// let module = await import('./inlineerrors.js');


/*Promise.all([
    import('/amd/src/inlineerrors.js'),
    import('/amd/src/codemirror.js')
])
.then(([module, cm]) => {
 */  

import('/amd/src/inlineerrors.js')
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
                    /*mode: "text/x-java",
                    indentUnit: 4,
                    matchBrackets: true,
                    tabMode: "shift",
                    styleActiveLine: true, autoCloseBrackets: true,*/
                });
                module.embedError("editor", "m-id-test-proforma-2044604495-3", regexp); 


                let editor2 = CodeMirror.fromTextArea(
                    document.getElementById("editor2"), {
                        lineNumbers: true,
                    /*mode: "text/x-java",
                    indentUnit: 4,
                    matchBrackets: true,
                    tabMode: "shift",
                    styleActiveLine: true, autoCloseBrackets: true,*/
                });
                module.embedError("editor2", "m-id-test-proforma-2044604495-4", regexp);                 
            });   
  });




     

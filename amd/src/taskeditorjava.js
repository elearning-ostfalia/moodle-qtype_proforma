/*
 * This is part of the proformaEditor which was created by the eCULT-Team
 * of Ostfalia University
 * http://ostfalia.de/cms/de/ecult/
 * The software is distributed under a CC BY-SA 3.0 Creative Commons license
 * https://creativecommons.org/licenses/by-sa/3.0/
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Author:
 * Karin Borm
 */

/**
 * contains functions and data relevant for handling java tests
 */







export var javaParser = (function() {

    // expose to public
    return {
        codeWithoutComment: codeWithoutComment,
        getClassAndPackage: getClassAndPackage,
        getFilenameWithPackage: getFilenameWithPackage,
        getPureClassnameFromFilename: getPureClassnameFromFilename,
        getFullClassnameFromFilename: getFullClassnameFromFilename
    }

    // all private

    function codeWithoutComment(code) {
        let newCode = code.replace(/\/\*[\s\S]*?\*\//gm, ""); // comment with /* */
        return newCode.replace(/\/\/.*/g, ""); // comment with //
    }

    /*
      function searches given text string (source code) for the classname and
      a package name. At first all comment lines are removed in order to avoid
      delivering data written in comment.
     */
    function getClassAndPackage(code) {
        function getPackageName(code) {
            const javapackage = code.match(/package([\s\S]*?);/);
            if (!javapackage) return "";
            switch (javapackage.length) {
                case 0:  return ""; // no package found
                case 1:  return javapackage[0]; // unclear what it is, deliver everything
                default: return javapackage[1].trim(); // found, expect package name as 2nd
            }
        }

        function getClasseName(code) {
            //const className = code.match("\\s*(public|private)\\s+class\\((s+)*?)(\\w+)\\s+((extends\\s+\\w+)|(implements\\s+\\w+( ,\\w+)*))?\\s*\\{");

            //const className = code.match(/\s*(|public|private)\s+class([\s\S]*?)(\{|extends|implements)/);
            //const className = code.match(/\s*(public|private)\s+class([\s\S]*?)(\{|extends|implements)/);
            const className = code.match(/class\s+([\S]+?)\s*(\{|extends|implements)/); //interface??
            if (!className) return "";
            switch (className.length) {
                case 0:  return ""; // no className found???
                case 1:  return className[0]; // unclear what it is, deliver everything
                default: return className[1].trim(); // found, expect className name as 2nd
            }
        }

        // preset return value
        let out = { class:"?", package:"?" };
        const pureCode = codeWithoutComment(code);
        out.class = getClasseName(pureCode);
        out.package = getPackageName(pureCode);
        return out;
    }


    function getFilenameWithPackage(code, filename) {
        let out = getClassAndPackage(code);
        //const className = filename.match(/([\S]*?)(.java)/i);
        let newFilename = "";
        const javapackage = out.package.replace(/\./g, "/");

        if (out.class.length > 0) {
            classname = out.class.trim();
            if (classname.endsWith('>')) {
                classname = classname.substr(0, classname.indexOf('<'))
            }
            filename = classname + ".java";
        } else {
            // no class name found (e.g. enum, interface ...)
            // if filename starts with package then remove package
            if (javapackage.length > 0 && filename.startsWith(javapackage)) {
                filename = filename.substr(javapackage.length + 1);
            }
        }

        if (javapackage.length > 0)
            newFilename = javapackage + "/" + filename; // out.class + ".java";
        else
            newFilename = filename; // out.class + ".java";
        return newFilename;
    }

    function getPureClassnameFromFilename(filename) {
        let newFilename = filename.replace(/.java/i, "");
        const index =  newFilename.indexOf("/");
        if (index > 0) {
            newFilename = newFilename.substring(index+1);
        }
        return newFilename;
    }

    function getFullClassnameFromFilename(filename) {
        // replace / by .
        let newFilename = filename.replace(/\//g, ".");
        // remove .java
        newFilename = newFilename.replace(/.java/i, "");
        return newFilename;
    }
})();










# coding=utf-8

# This is part of the ProFormA Editor
#
# This proformaEditor was created by the eCULT-Team of Ostfalia University
# http://ostfalia.de/cms/de/ecult/
# The software is distributed under a CC BY-SA 3.0 Creative Commons license
# https://creativecommons.org/licenses/by-sa/3.0/
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
# INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
# PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
# HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
# SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# simple http server for running testsuite
#
# @copyright 2018 Ostfalia Hochschule fuer angewandte Wissenschaften
# @author   Karin Borm <k.borm@ostfalia.de>


####################################################################
# start HHTP server, 
# Browser: http://localhost:8000/tests/javascript/test.html
####################################################################
import http.server
import socketserver
import runpy
import os

PORT = 8000



def startHttpServer():

    Handler = http.server.SimpleHTTPRequestHandler
    Handler.extensions_map.update({
        ".js": "application/javascript",
    });

    httpd = socketserver.TCPServer(("", PORT), Handler)

    # change working directory so that
    # the editor files are served
    os.chdir("../..")
    
    # start the server
    print("serving at port", PORT)
    httpd.serve_forever()


startHttpServer()

# Alternative auf der Kommandozeile

# python -m SimpleHTTPServer
# oder
# python -m SimpleHTTPServer PORT

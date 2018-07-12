# Copyright (c) 2017 CICESE

# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at

# http://www.apache.org/licenses/LICENSE-2.0

# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# Flask dependencies
from flask import Flask
from flask import render_template
from flask import make_response 
from flask import request, Response

# Internal dependencies
from lib.NGSIData import NGSIData

# Format dependencies
import pprint

# Start listening through port 8001
app = Flask(__name__);

""" Null route (/)
This route might be used for testing purposes.
"""
@app.route("/")
def hello():
    return "Hello LocalServer";

""" queryContext route (/v1/queryContext)
This method allows to retrieve NGSI v1 entities by triggering a POST call from which a contextResponses structure.
For more reference, please visit NGSI v1 official documentation: http://telefonicaid.github.io/fiware-orion/api/v1/
"""
@app.route("/v1/queryContext", methods = ['POST'])
def getData():

	# Data handler.
	_contextResponses = NGSIData();

	# Remote access data.
	username 	= str(request.headers["Referer"]).split("/")[3];
	service 	= str(request.headers["Fiware-Service"]);
	servicePath = str(request.headers["Fiware-ServicePath"]);
	token 		= "";#str(request.headers["X-Auth-Token"]);

	# Header settled.
	response = Response(response = _contextResponses.post(username, service, servicePath, request.args, token));
	response.headers["Accept"] 				= "application/json";
	response.headers["Fiware-Service"] 		= service;
	response.headers["Fiware-ServicePath"] 	= servicePath;

	return response;

if __name__ == "__main__":
    app.run(host = "0.0.0.0", port=8001, debug = False);

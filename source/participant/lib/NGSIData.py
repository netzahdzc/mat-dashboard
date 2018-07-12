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


# System and format dependencies
import os
import json
import MySQLdb

# Flask dependencies to handle Response's header
from flask import request, Response

""" NGSIData
This class enable a simple mechanism to retrieve User data under NGSI v1 structure.
"""
class NGSIData:
	def __init__(self):
		print("NGSIData object created");

	def post(self, _username, _service, _servicePath, _args, _token):
		_data = Retrieve();
		return _data.fromMySQLtoJson(_username, _service, _servicePath, _args, _token);
	
class Retrieve:
	def __init__(self):
		print("Retrieve object created");

	def fromMySQLtoJson(self, _username, _service, _servicePath, _args, _token):
		# Catalogues source
		catalogues 			= Catalogues();

		# Check token
		# TODO build a mechanism to use keyrock as validator
		if _token is not None:
			_token = "password";

		# MySQL setup
		mysql_username 		= _username;
		mysql_password 		= _token;
		mysql_host 			= "localhost";
		mysql_database_name = _service;
		mysql_table			= _servicePath[1:];

		print("Retrieving directory list");

		try:
			mysql_conn = MySQLdb.connect(
										host 	= mysql_host, 
										user 	= mysql_username, 
										passwd 	= mysql_password, 
										db 		= mysql_database_name, 
										charset = 'utf8');
			mysql_cursor = mysql_conn.cursor();
			mysql_cursor.execute("""
								SELECT `id`, `gender`, DATE_FORMAT(`birthday`, '%%d/%%m/%%Y') 
								FROM `%s`""" % mysql_table);
			
			row 	= mysql_cursor.fetchall();
			count 	= len(row);

			print("Reading specific directory");

			# Root structure.
			contextResponses = [];

			for row in mysql_cursor:
				# Root atribute's element struture.
				attributesWrap 	= [];

				# Dynamic building process, starts here.
				# ===========================================
				# Get each single line to handle them as json objects.
				jsonResult 	= {};
			
				# Collection of basic/general data to fill entities.
				entityId 	= row[0];
				entityType 	= "Participant";

				# Atributes data gathering.
				attributes = {};
				attributes["name"] 	= "gender";
				attributes["value"] = catalogues.uncodeGender(row[1]);
				# Putting attributes together 
				attributesWrap.append(attributes);

				# Atributes data gathering.
				attributes = {};
				attributes["name"] 	= "birthday";
				attributes["type"] 	= "DateTime";
				attributes["value"] = row[2];
				# Putting attributes together 
				attributesWrap.append(attributes);

				contextElement = {};
				contextElement["type"] 		= entityType;
				contextElement["isPattern"] = "false";
				contextElement["id"] 		= entityId;
				contextElement["attributes"]= attributesWrap;
				# Dynamic building process, ends here.
				# ===========================================

				# Status response from contextElement.
				statusCode = {};
				statusCode["code"] 			= "200";
				statusCode["reasonPhrase"] 	= "OK";

				# Contest Elements.
				contextElementWrap = {};
				contextElementWrap["contextElement"]= contextElement;
				contextElementWrap["statusCode"] 	= statusCode;
				
				# Getting togheter context elements
				contextResponses.append(contextElementWrap);

			# Status response from all call.
			errorCode = {};
			errorCode["code"] 				= "200";
			errorCode["reasonPhrase"] 		= "OK";
			errorCode["details"] 			= "Count: "+str(count);

			# # Putting json pieces together.
			mainObject = {};
			mainObject['contextResponses'] 	= contextResponses;
			mainObject['errorCode'] 		= errorCode;

		except Exception as e:
			print ("</br>Error: %s" % e);
			if mysql_cursor:
				mysql_cursor.close();
			if mysql_conn:
				mysql_conn.close();

		print(json.dumps(mainObject));

		return json.dumps(mainObject);

class Catalogues:
	def uncodeGender(self, code):
		genderString = "Unknow";

		if code == 1:
			genderString = "male";
		if code == 2:
			genderString = "female";

		return genderString;

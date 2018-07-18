#!/usr/bin/env python
import sys
import shutil
import os.path
import time
import logging
import ConfigParser
import re
import hashlib

import os
from crate import client # FIWARE adaptation 
from subprocess import Popen, PIPE, STDOUT
import sqlite3
import MySQLdb
from datetime import datetime

config = ConfigParser.ConfigParser()
config.read("../../sqlite/configuration.cnf")
file_dir = config.get('sqlite', 'file_dir')

mysql_username = config.get('mysql', 'username')
mysql_password = config.get('mysql', 'password')
mysql_host = config.get("mysql", 'host')
mysql_database_name = config.get("mysql", "database")
mysql_fiware_database_name = config.get("mysql", "fiware_database") # FIWARE adaptation
table_list = ['sensor_linear_acceleration', 'sensor_orientation', 'controls', 'participants', 'sessions', 'technicals', 'tests', 'users']
table_fiware_list = ['etanswer', 'etmotorphysicaltest', 'etdeviceSM', 'etdeviceSE', 'etcontroltest', 'etparticipant', 'etuser'] # FIWARE adaptation

# FIWARE adaptation starts here
# Global variables
refTests = {}
refDevices = {}
refTestsSensors = {}
refSensors = {}
refStartedTest = {}
refEndedTest = {}
list_of_answers = {}

# etmotorphysicaltest
dbConfiguration = 0
dbEntityId_test = 3
dbRefDevice = 5
dbRefUser = 6
dbDateTestEnded = 1
dbDateTestStarted = 2
dbTestType = 7

# etdevice
dbCategory = 0
dbConsistOf = 2
dbControlledProperty = 3
dbEntityId_device = 5
dbValue = 14
dbMobileModel = 11
dbMobileBrand = 9
dbMobileAndroidApi = 10
dbAppVersion = 12
dbCreated_device = 4

# etcontroltest
dbRefUser_control = 8
dbWeight = 4
dbHeight = 3
dbWaist = 10
dbHeartRate = 6
dbSystolic = 7
dbDiastolic = 5
dbCreated = 0

# etparticipant
dbName = 4
dbSurname = 5
dbGender = 3
dbTrash = 7
dbBirthday = 0

# etuser
dbName_user = 5
dbSurname_user = 6
dbCode = 0
dbEmail = 2
dbTrash_user = 8
dbCodeStatus = 1

# etanswer
dbEntityId_answer = 1
dbRefQuestion_answer = 3
dbRefUser_answer = 4
dbAnswer = 0
# FIWARE adaptation ends here

def parse_gender(option):
	outcome = 0
	if(option.lower() == 'male'):
		outcome = 1
	if(option.lower() == 'female'):
		outcome = 2
	return outcome

def parse_trash(option):
	outcome = 0
	if(option.lower() == 'true'):
		outcome = 1
	if(option.lower() == 'false'):
		outcome = 2
	return outcome

def parse_test_type(option):
	switcher = {
        'Timed Up and Go': 1,
		'30 second sit to stand test': 2,
		'4-Stage Balance Test': 3
	}
	return switcher.get(option, None)

def parse_test_option(option):
	switcher = {
		'Feet together': 3,
		'Sem-tandem': 2,
		'Tandem': 1,
		'One leg': 4
	}
	return switcher.get(option, None)

def parse_code(option):
	outcome = 0
	if(option.lower() == 'activated'):
		outcome = 1
	if(option.lower() == 'unactivated'):
		outcome = 2
	return outcome

def checkTableExists(dbcur, tablename):
    dbcur.execute("""
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_name = '{0}'
        """.format(tablename.replace('\'', '\'\'')))
    if dbcur.fetchone()[0] == 1:
        # dbcur.close()
        return True

    # dbcur.close()
    return False

def fiware_to_mysql():
	"""
	Below two lines enable connection with CRATE.OI to retrieve historical data from Cosmos.
	"""
	connection = client.connect()
	cursor = connection.cursor()

	"""
	This function extracts data from Cosmos and store it respectively into the assigned server.
	"""
	try:
		mysql_conn = MySQLdb.connect(host=mysql_host,user=mysql_username,passwd=mysql_password,db=mysql_fiware_database_name,charset='utf8')
		mysql_cursor = mysql_conn.cursor()

		if(checkTableExists(cursor, 'etmotorphysicaltest')):
			for table in table_fiware_list:
				q = ''

				if table is 'etdeviceSM':
					table = 'etdevice'
					q = "WHERE category LIKE 'smartphone'"

				if table is 'etdeviceSE':
					table = 'etdevice'
					q = "WHERE category LIKE 'sensor'"

				cursor.execute("SELECT * FROM %s %s" % (table, q))
				result = cursor.fetchall()
				
				for row in result:
					if(table is 'etanswer'):
						# print('1')
						list_of_answers[row[dbEntityId_answer]] = row[dbAnswer]

					if(table is 'etmotorphysicaltest'):
						# print('2')
						# Get user id
						q1 = "false" 
						q2 = "false"  
						q3 = "false"  
						q4 = "false" 
						q5 = "false"  
						q6 = "false" 
						q7 = "false"  
						q8 = "false"  
						q9 = "false"
						q10 = "false"
						test_option = ""
						data_evaluation_score = ""
						data_evaluation_description = ""
						refUser = row[dbRefUser]
						entityId_test = row[dbEntityId_test]
						participant_id = [x.strip() for x in refUser.split('/')]
						beginning_sensor_collection_timestamp = row[dbDateTestStarted]
						finishing_sensor_collection_timestamp = row[dbDateTestEnded]
						type_test = parse_test_type(row[dbTestType])
						
						if('testType' in row[dbConfiguration]):
							test_option = row[dbConfiguration]["testType"]#parse_test_option("Feet together") # Incluir como metadato en estructura JSON
						
						if('testScore' in row[dbConfiguration]):
							data_evaluation_score = row[dbConfiguration]["testScore"]
						
						if('testComments' in row[dbConfiguration]):
							data_evaluation_description = row[dbConfiguration]["testComments"]
							
						status = row[dbConfiguration]["testStatus"]
						last_updated = row[dbDateTestEnded]

						# Retrieval of answers
						for answer in row[dbConfiguration]["refAnswers"]:

							if('1' in answer):
								q1 = list_of_answers.get(answer, "false")

							if('2' in answer):
								q2 = list_of_answers.get(answer, "false")

							if('3' in answer):
								q3 = list_of_answers.get(answer, "false")

							if('4' in answer):
								q4 = list_of_answers.get(answer, "false")

							if('5' in answer):
								q5 = list_of_answers.get(answer, "false")

							if('6' in answer):
								q6 = list_of_answers.get(answer, "false")

							if('7' in answer):
								q7 = list_of_answers.get(answer, "false")

							if('8' in answer):
								q8 = list_of_answers.get(answer, "false")

							if('9' in answer):
								q9 = list_of_answers.get(answer, "false")

							if('10' in answer):
								q10 = list_of_answers.get(answer, "false")


						# Keep a record of test and questionnaire
						"""
						refStartedTest[key]
							"ffffffffff9cbbf4465f0ef30033c587-questionnaire-7118" => 2018-01-01T09:15:09.663Z-0600

						refEndedTest[key]
							"ffffffffff9cbbf4465f0ef30033c587-questionnaire-7118" => 2018-01-01T09:15:09.663Z-0600
						"""
						if("refQuestionnaire" in row[dbConfiguration]):
							questionnaireId = row[dbConfiguration]["refQuestionnaire"]

						# Get devices from current test
						list_of_devices = row[dbRefDevice]
							
						# Associate data with device's ids into two variables
						"""
						refTests[key]
							"smartphone-9845A" => ffffffffff9cbbf4465f0ef30033c587-71186
							"smartphone-9845B" => ffffffffff9cbbf4465f0ef30033c587-71186
							"smartphone-9845C" => ffffffffff9cbbf4465f0ef30033c587-71186

						refDevices[key]
							"smartphone-9845A" => http://207.249.127.162:1234/users/1
							"smartphone-9845B" => http://207.249.127.162:1234/users/1
							"smartphone-9845C" => http://207.249.127.162:1234/users/1
						"""
						for deviceId in list_of_devices:
							refTests[deviceId] = entityId_test
							refDevices[deviceId] = refUser

						# Store into database
						new_row = (participant_id[len(participant_id)-1], type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated, entityId_test)
						mysql_cursor.execute("""
							INSERT INTO `tests` (`participant_id`, `type_test`, `test_option`, `beginning_sensor_collection_timestamp`, `finishing_sensor_collection_timestamp`, `q1`, `q2`, `q3`, `q4`, `q5`, `q6`, `q7`, `q8`, `q9`, `q10`, `data_evaluation_score`, `data_evaluation_description`, `status`, `last_updated`, `test_id`)
							VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""", new_row)
						# print('</br>participant_id: %s, type_test: %s, test_option: %s, beginning_sensor_collection_timestamp: %s, finishing_sensor_collection_timestamp: %s, q1: %s, q2: %s, q3: %s, q4: %s, q5: %s, q6: %s, q7: %s, q8: %s, q9: %s, q10: %s, data_evaluation_score: %s, data_evaluation_description: %s, status: %s, last_updated: %s, entityId_test: %s' % (participant_id[len(participant_id)-1], type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated, entityId_test))


					if(table is 'etdevice'):
						# print('3')
						entityId_device = row[dbEntityId_device]
						refUser = refDevices.get(entityId_device)

						# Get device data based on previous ids
						if(row[dbCategory] == 'smartphone'):
							# Retrieve technicall data
							if(refUser is not None):
								participant_id = [x.strip() for x in refUser.split('/')]
								test_id = refTests.get(entityId_device)
								mobile_model = row[dbMobileModel]
								mobile_brand = row[dbMobileBrand]
								mobile_android_api = row[dbMobileAndroidApi]
								app_version = row[dbAppVersion]
								created = row[dbCreated_device]

								# Store into database
								new_row = (participant_id[len(participant_id)-1], test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created)
								mysql_cursor.execute("""
									INSERT INTO `technicals` (`participant_id`, `test_id`, `mobile_model`, `mobile_brand`, `mobile_android_api`, `app_version`, `created`)
									VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)
								#print('</br>participant_id: %s, test_id: %s, mobile_model: %s, mobile_brand: %s, mobile_android_api: %s, app_version: %s, created: %s' % (participant_id[len(participant_id)-1], test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created))
								
							#print('3.1')
							# Retrieve general test data
							list_of_sensor = row[dbConsistOf]

							# Associate devices with user's id
							"""
							refTestsSensors[key]
								"sensor-9845A" => ffffffffff9cbbf4465f0ef30033c587-71186
								"sensor-9845B" => ffffffffff9cbbf4465f0ef30033c587-71186
								"sensor-9845C" => ffffffffff9cbbf4465f0ef30033c587-71186

							refSensors[key]
								"sensor-9845A" => http://207.249.127.162:1234/users/1
								"sensor-9845B" => http://207.249.127.162:1234/users/1
								"sensor-9845C" => http://207.249.127.162:1234/users/1
							"""
							for sensorId in list_of_sensor:
								refTestsSensors[sensorId] = refTests.get(entityId_device)
								refSensors[sensorId] = refDevices.get(entityId_device)

						# Get sensor data based on previous ids
						if(row[dbCategory] == 'sensor'):
							# print('4')
							#print(entityId_device)
							# Retrieve ids data from dictionaries
							test_id = refTestsSensors.get(entityId_device)
							patient_ref = refSensors.get(entityId_device)

							# Retrieve only if required data was collected
							if(patient_ref is not None):
								participant_id = [x.strip() for x in patient_ref.split('/')]

								if(row[dbControlledProperty] is not None):
									# Accelerometer data only
									if('accelerometer' in row[dbControlledProperty]):
										list_of_data = [x.strip() for x in row[dbValue].split(' ')]
										list_of_data = list_of_data[:-1]
										# Format axis data
										for data in list_of_data:
											axis = [x.strip() for x in data.split(',')]
											accuracy = 3 #"accuracy"
											created = axis[3]

											# Store into database
											new_row = (participant_id[len(participant_id)-1], test_id, accuracy, axis[0], axis[1], axis[2], created)
											mysql_cursor.execute("""
												INSERT INTO `sensor_linear_acceleration` (`participant_id`, `test_id`, `accuracy`, `x`, `y`, `z`, `created`) 
												VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)
											# print('</br>participant_id: %s, test_id: %s, accuracy: %s, x: %s, y: %s, z: %s ' % (participant_id[len(participant_id)-1], test_id, accuracy, axis[0], axis[1], axis[2]))
									
									# Orientation data only
									if('orientation' in row[dbControlledProperty]):
										list_of_data = [x.strip() for x in row[dbValue].split(' ')]
										list_of_data = list_of_data[:-1]
										# Format axis data
										for data in list_of_data:
											axis = [x.strip() for x in data.split(',')]
											created = axis[3]

											# Store into database
											new_row = (participant_id[len(participant_id)-1], test_id, axis[0], axis[1], axis[2], created)
											mysql_cursor.execute("""
												INSERT INTO `sensor_orientation` (`participant_id`, `test_id`, `azimuth`, `pitch`, `roll`, `created`)
												VALUES (%s, %s, %s, %s, %s, %s)""", new_row)
											# print('</br>participant_id: %s, test_id: %s, x: %s, y: %s, z: %s ' % (participant_id[len(participant_id)-1], test_id, axis[0], axis[1], axis[2]))

					if(table is 'etcontroltest'):
						# print('5')
						refUser = row[dbRefUser_control]
						participant_id = [x.strip() for x in refUser.split('/')]
						weight = row[dbWeight]
						height = row[dbHeight]
						waits = row[dbWaist]
						heartRate = row[dbHeartRate]
						systolic = row[dbSystolic]
						diastolic = row[dbDiastolic]
						created = row[dbCreated]

						# Store into database
						new_row = (participant_id[len(participant_id)-1], weight, height, waits, heartRate, systolic, diastolic, created)
						mysql_cursor.execute("""
							INSERT INTO `controls` (`participant_id`, `weight`, `height`, `waist_size`, `heart_rate`, `systolic_blood`, `diastolic_blood`, `created`)
							VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""", new_row)
						# print('</br>participant_id: %s, weight: %s, height: %s, waist_size: %s, heart_rate: %s, systolic_blood: %s, diastolic_blood: %s, created: %s' % (participant_id[len(participant_id)-1], weight, height, waits, heartRate, systolic, diastolic, created))

					if(table is 'etparticipant'):
						# print('6')
						name = row[dbName]
						surname = row[dbSurname]
						gender = parse_gender(row[dbGender])
						trash = parse_trash(row[dbTrash])
						birthday = row[dbBirthday]
						photo = "NULL"
						created = "2017-08-28T10:30:24.703Z-0700"
						last_updated = "2017-08-28T10:30:24.703Z-0700"

						# Store into database
						new_row = (name, surname, gender, birthday, photo, trash, created, last_updated)
						mysql_cursor.execute("""
							INSERT INTO `participants` (`name`, `surname`, `gender`, `birthday`, `photo`, `trash`, `created`, `last_updated`)
							VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""", new_row)
						# print('</br>name: %s, surname: %s, gender: %s, birthday: %s, photo: %s, trash: %s, created: %s, last_updated: %s' % (name, surname, gender, birthday, photo, trash, created, last_updated))

					if(table is 'etuser'):
						# print('7')
						name = row[dbName_user]
						surname = row[dbSurname_user]
						code = row[dbCode]
						email = row[dbEmail]
						trash = parse_trash(row[dbTrash_user])
						gender = parse_gender('Male')#"gender"
						password = "password"
						activation_status = parse_code(row[dbCodeStatus])
						created = "2017-08-27T21:15:02.436Z-0700"
						last_updated = "2017-08-27T21:15:02.436Z-0700"

						# Store into database
						new_row = (name, surname, gender, email, password, code, activation_status, trash, created, last_updated)
						mysql_cursor.execute("""
							INSERT INTO `users` (`name`, `surname`, `gender`, `email`, `password`, `activation_code`, `activation_status`, `trash`, `created`, `last_updated`)
							VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""", new_row)
						# print('</br>name: %s, surname: %s, gender: %s, email: %s, password: %s, activation_code: %s, activation_status: %s, trash: %s, created: %s, last_updated: %s' % (name, surname, gender, email, password, code, activation_status, trash, created, last_updated))

				mysql_conn.commit()
		else:
			print ("</br>IMPORTANT. ACTION NEEDED: There is no data available on QuantumLeap.")
	except Exception as e:
		print ("</br>Error catched: %s" % e)
		if str(e).find("doc.etcontroltest"):
			print('</br>*** Include control data information of patients before continue with this process.')
		else:
			"""
			If there happened to be a problem, data is rollbacked, connections are close and the physical file is renamed for further (manual) inspection.
			"""
			print ("</br>Error catched: %s" % e)
			
		mysql_conn.rollback()
		if mysql_cursor:
			mysql_cursor.close()
		if mysql_conn:
			mysql_conn.close()
		if cursor:
			cursor.close()
		if connection:
			connection.close()
		#sys.exit();

def sqlite_to_mysql(sqlite_file_path):
	"""
	This function extracts data from sqlite files and store it respectively into the assigned server.
	"""
	try:
		mysql_conn = MySQLdb.connect(host=mysql_host,user=mysql_username,passwd=mysql_password,db=mysql_database_name,charset='utf8')
		mysql_cursor = mysql_conn.cursor()
		statinfo = os.stat(sqlite_file_path)
		size = statinfo.st_size

		if(size == 0):
			"""
			If the file is empty, it is renamed respectively.
			"""
			os.rename(sqlite_file_path, "%s.empty" % sqlite_file_path)
		else:
			"""
			Otherwise, the file is inspected to retrieve data.
			"""
			conn = sqlite3.connect(sqlite_file_path)
			conn.row_factory = sqlite3.Row
			cursor_tables = conn.cursor()
			
			cursor_tables.execute("SELECT name FROM sqlite_master WHERE type='table';")
			#cursor.fetchall()
			
			for table in cursor_tables:
				table = table[0]
				if(table in table_list):
					cursor = conn.cursor()
					cursor.execute("SELECT * FROM %s" % table)
					
					for row in cursor:
						if(table == 'sensor_linear_acceleration'):
							#print("sensor_linear_acceleration")
							participant_id, test_id, accuracy, x, y, z, created = row
							new_row = (participant_id, test_id, accuracy, x, y, z, created)
							mysql_cursor.execute("""
								INSERT INTO `sensor_linear_acceleration` (`participant_id`, `test_id`, `accuracy`, `x`, `y`, `z`, `created`) 
								VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'sensor_orientation'):
							#print("sensor_orientation")
							participant_id, test_id, azimuth, pitch, roll, created = row
							new_row = (participant_id, test_id, azimuth, pitch, roll, created)
							mysql_cursor.execute("""
								INSERT INTO `sensor_orientation` (`participant_id`, `test_id`, `azimuth`, `pitch`, `roll`, `created`)
								VALUES (%s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'controls'):
							#print("controls")
							_id, participant_id, weight, height, waist_size, heart_rate, systolic_blood, diastolic_blood, created = row
							new_row = (participant_id, weight, height, waist_size, heart_rate, systolic_blood, diastolic_blood, created)
							mysql_cursor.execute("""
								INSERT INTO `controls` (`participant_id`, `weight`, `height`, `waist_size`, `heart_rate`, `systolic_blood`, `diastolic_blood`, `created`)
								VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'participants'):
							#print("participants")
							_id, name, surname, gender, birthday, photo, trash, created, last_updated = row
							new_row = (name, surname, gender, birthday, photo, trash, created, last_updated)
							mysql_cursor.execute("""
								INSERT INTO `participants` (`name`, `surname`, `gender`, `birthday`, `photo`, `trash`, `created`, `last_updated`)
								VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'sessions'):
							#print("sessions")
							_id, user_id, created = row
							new_row = (user_id, created)
							mysql_cursor.execute("""
								INSERT INTO `sessions` (`user_id`, `created`)
								VALUES (%s, %s)""", new_row)

						if(table == 'technicals'):
							#print("technicals")
							_id, participant_id, test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created = row
							new_row = (participant_id, test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created)
							mysql_cursor.execute("""
								INSERT INTO `technicals` (`participant_id`, `test_id`, `mobile_model`, `mobile_brand`, `mobile_android_api`, `app_version`, `created`)
								VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'tests'):
							#print("tests")
							_id, participant_id, type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated = row
							new_row = (participant_id, type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated)
							mysql_cursor.execute("""
								INSERT INTO `tests` (`participant_id`, `type_test`, `test_option`, `beginning_sensor_collection_timestamp`, `finishing_sensor_collection_timestamp`, `q1`, `q2`, `q3`, `q4`, `q5`, `q6`, `q7`, `q8`, `q9`, `q10`, `data_evaluation_score`, `data_evaluation_description`, `status`, `last_updated`)
								VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""", new_row)
						
						if(table == 'users'):
							#print("users")
							_id, name, surname, gender, email, password, activation_code, activation_status, trash, created, last_updated = row
							new_row = (name, surname, gender, email, password, activation_code, activation_status, trash, created, last_updated)
							mysql_cursor.execute("""
								INSERT INTO `users` (`name`, `surname`, `gender`, `email`, `password`, `activation_code`, `activation_status`, `trash`, `created`, `last_updated`)
								VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)""", new_row)

			mysql_conn.commit()
			#print ("</br>File processed: " + sqlite_file_path)
			os.rename(sqlite_file_path, "%s.processed" % sqlite_file_path)

	except Exception as e:
		"""
		If there happened to be a problem, data is rollback, connections are close and the physical file is renamed for further (manual) inspection.
		"""
		print ("</br>Error on this file: %s" % e)
		mysql_conn.rollback()
		if mysql_cursor:
			mysql_cursor.close()
		if mysql_conn:
			mysql_conn.close()
		if cursor:
			cursor.close()
		if conn:
			conn.close()
		os.rename(sqlite_file_path, "%s.corrupted" % sqlite_file_path)
		#sys.exit();

def file_based_process():
	"""
	This script, search for all db files (i.e., those files ended with extension .db) into a specific directory.
	"""
	print ("</br>Backup process starting...")
	onlyfiles = [f for f in os.listdir("../../sqlite/%s" % file_dir) if os.path.isfile(os.path.join("../../sqlite/%s" % file_dir, f))]
	for (f) in onlyfiles:
		if(f.endswith(".db")):
			#print (f);
			try:
				sqlite_to_mysql("../../sqlite/%s/%s" % (file_dir, f) )
			except Exception as e:
				#print("</br> Just starting: ")
				print(e)
	print ("</br>Backup rocess finished")

def fiware_based_process():
	"""
	This script, search for all historical data stored on QuantumLeap.
	"""
	print ("</br>Fiware process starting...")
	fiware_to_mysql() 
	print ("</br>Fiware process finished")

if __name__ == "__main__":
	print("</br>Sequence started")
	file_based_process()
	fiware_based_process()
	print ("</br>Process finished.</br></br>Continue pressing button 3.")


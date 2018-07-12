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
from subprocess import Popen, PIPE, STDOUT
import sqlite3
import MySQLdb
from datetime import datetime

config = ConfigParser.ConfigParser()
config.read("./configuration.cnf")
file_dir = config.get('sqlite', 'file_dir')

mysql_username = config.get('mysql', 'username')
mysql_password = config.get('mysql', 'password')
mysql_host = config.get("mysql", 'host')
mysql_database_name = config.get("mysql", "database")

table_list = ['sensor_linear_acceleration', 'sensor_orientation', 'controls', 'participants', 'sessions', 'technicals', 'tests', 'users']

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
							patient_id, test_id, accuracy, x, y, z, created = row
							new_row = (patient_id, test_id, accuracy, x, y, z, created)
							mysql_cursor.execute("""
								INSERT INTO `sensor_linear_acceleration` (`patient_id`, `test_id`, `accuracy`, `x`, `y`, `z`, `created`) 
								VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'sensor_orientation'):
							#print("sensor_orientation")
							patient_id, test_id, azimuth, pitch, roll, created = row
							new_row = (patient_id, test_id, azimuth, pitch, roll, created)
							mysql_cursor.execute("""
								INSERT INTO `sensor_orientation` (`patient_id`, `test_id`, `azimuth`, `pitch`, `roll`, `created`)
								VALUES (%s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'controls'):
							#print("controls")
							_id, patient_id, weight, height, waist_size, heart_rate, systolic_blood, diastolic_blood, created = row
							new_row = (patient_id, weight, height, waist_size, heart_rate, systolic_blood, diastolic_blood, created)
							mysql_cursor.execute("""
								INSERT INTO `controls` (`patient_id`, `weight`, `height`, `waist_size`, `heart_rate`, `systolic_blood`, `diastolic_blood`, `created`)
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
							_id, patient_id, test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created = row
							new_row = (patient_id, test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created)
							mysql_cursor.execute("""
								INSERT INTO `technicals` (`patient_id`, `test_id`, `mobile_model`, `mobile_brand`, `mobile_android_api`, `app_version`, `created`)
								VALUES (%s, %s, %s, %s, %s, %s, %s)""", new_row)

						if(table == 'tests'):
							#print("tests")
							_id, patient_id, type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated = row
							new_row = (patient_id, type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated)
							mysql_cursor.execute("""
								INSERT INTO `tests` (`patient_id`, `type_test`, `test_option`, `beginning_sensor_collection_timestamp`, `finishing_sensor_collection_timestamp`, `q1`, `q2`, `q3`, `q4`, `q5`, `q6`, `q7`, `q8`, `q9`, `q10`, `data_evaluation_score`, `data_evaluation_description`, `status`, `last_updated`)
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

if __name__ == "__main__":
	"""
	This script, search for all db files (i.e., those files ended with extension .db) into a specific directory.
	"""
	print ("</br>Process starting...")
	onlyfiles = [f for f in os.listdir("./%s" % file_dir) if os.path.isfile(os.path.join("./%s" % file_dir, f))]
	for (f) in onlyfiles:
		if(f.endswith(".db")):
			#print (f);
			try:
				sqlite_to_mysql("./%s/%s" % (file_dir, f) )
			except Exception as e:
				#print("</br> Just starting: ")
				print(e)
	print ("</br>Process finished.</br></br>Continue pressing button 3.")

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
from crate import client # FIWARE adaptation
import sqlite3
import MySQLdb
from datetime import datetime

i = 0
config = ConfigParser.ConfigParser()
config.read("../../sqlite/configuration.cnf")
# config.read("configuration.cnf")
file_dir = config.get('sqlite', 'file_dir')
parsed_file_dir = config.get('parsed', 'parsed_file_dir')

mysql_username = config.get('mysql', 'username')
mysql_password = config.get('mysql', 'password')
mysql_host = config.get("mysql", 'host')
mysql_database_name = config.get("mysql", "database")
mysql_fiware_database_name = config.get("mysql", "fiware_database") # FIWARE adaptation
table_list = ['sensor_linear_acceleration', 'sensor_orientation', 'controls', 'participants', 'sessions', 'technicals', 'tests', 'users']

def clean_data_fiware_crate():
	"""
	To take action on the CRATE's database controller
	"""
	connection = client.connect()
	cursor = connection.cursor()

	# etquestionnaire
	cursor.execute("SELECT entity_id, COUNT(*) AS n FROM etquestionnaire GROUP BY entity_id HAVING COUNT(*) > 1")

	results = cursor.fetchall()
	for row in results:
		cursor.execute("SELECT time_index FROM etquestionnaire WHERE entity_id LIKE '%s' ORDER BY time_index DESC LIMIT 1" % row[0])
		results_ = cursor.fetchall()
		cursor.execute("DELETE FROM etquestionnaire WHERE time_index = %s " % results_[0][0])

	# etquestion
	cursor.execute("SELECT entity_id, COUNT(*) AS n FROM etquestion GROUP BY entity_id HAVING COUNT(*) > 1")

	results = cursor.fetchall()
	for row in results:
		cursor.execute("SELECT time_index FROM etquestion WHERE entity_id LIKE '%s' ORDER BY time_index DESC LIMIT 1" % row[0])
		results_ = cursor.fetchall()
		cursor.execute("DELETE FROM etquestion WHERE time_index = %s " % results_[0][0])


	cursor.close()
	connection.close()

def clean_data_base(table, dbName):
	"""
	This function truncates and reset autoincrease counter for all tables.
	"""
	try:
		mysql_conn = MySQLdb.connect(host=mysql_host,user=mysql_username,passwd=mysql_password,db=dbName,charset='utf8')
		mysql_cursor = mysql_conn.cursor()
		mysql_cursor.execute("""
			TRUNCATE `"""+table+"""` 
			""")
		mysql_cursor.execute("""
			ALTER TABLE `"""+table+"""` AUTO_INCREMENT = 1
			""")

	except Exception as e:
		"""
		If there happened to be a problem, data is rollback, connections are close and the physical file is renamed for further (manual) inspection.
		"""
		print(e);
		mysql_conn.rollback()
		if mysql_cursor:
			mysql_cursor.close()
		if mysql_conn:
			mysql_conn.close()
		#if cursor:
		#	cursor.close()
		#if conn:
		#	conn.close()


if __name__ == "__main__":
	"""
	This script, trigger both cleanning functions.
	Below section clean tables used for the mysqlite-files based approach.
	"""
	for table in table_list: 
		clean_data_base(table, mysql_database_name)

	"""
	Below section clean tables used for the FIWARE approach.
	"""
	for table in table_list: 
		clean_data_base(table, mysql_fiware_database_name)

	"""
	This function, remove the questionnaire on QL, since at this point the information is coming from the client-side
	instead of the dashboard
	"""
	clean_data_fiware_crate()

	"""
	This section cleans main db files.
	"""
	lastone = ""
	onlyfiles = [f for f in os.listdir("../../sqlite/"+file_dir) if os.path.isfile(os.path.join("../../sqlite/"+file_dir, f))]
	onlyfiles.sort()

	for (f) in onlyfiles:
		if (f.endswith(".db.processed")):
			try:
				os.rename("../../sqlite/"+file_dir+"/"+f, "../../sqlite/"+file_dir+"/"+f.split(".")[0]+".db")
			except Exception as e:
				if('No such file or directory' not in e):
					print("Error catched: %s" % e)
					continue
				#print (e)

	for (f) in onlyfiles:
		if (f.endswith(".db")):
			if (not f.endswith(("orientatio", "accelerome"), 7, 17)):
				try:
					os.rename("../../sqlite/"+file_dir+"/"+f, "../../sqlite/"+file_dir+"/"+f+".deprecated")
					i = i + 1;
					lastone = "../../sqlite/"+file_dir+"/"+f+".deprecated"
				except Exception as e:
					if('No such file or directory' not in e):
						print("Error catched: %s" % e)
						continue
					#print (e)

	if (lastone!=""):
		os.rename(lastone, lastone.split(".db", 1)[0]+".db")

		"""
		This section cleans ACC's txt files that will be created on step 3, so redundance is avoided
		"""
		onlyfiles = [f for f in os.listdir("../../sqlite/"+parsed_file_dir+"/acc") if os.path.isfile(os.path.join("../../sqlite/"+parsed_file_dir+"/acc", f))]
		onlyfiles.sort()
		for (f) in onlyfiles:
			try:
				os.remove("../../sqlite/"+parsed_file_dir+"/acc/"+f)
			except Exception as e:
				if('No such file or directory' not in e):
					continue
				else:
					print("Error catched: %s" % e)


		"""
		This section cleans ORIENT's txt files that will be created on step 3, so redundance is avoided
		"""
		onlyfiles = [f for f in os.listdir("../../sqlite/"+parsed_file_dir+"/orient") if os.path.isfile(os.path.join("../../sqlite/"+parsed_file_dir+"/orient", f))]
		onlyfiles.sort()
		for (f) in onlyfiles:
			try:
				os.remove("../../sqlite/"+parsed_file_dir+"/orient/"+f)
			except Exception as e:
				if('No such file or directory' not in e):
					continue
				else:
					print("Error catched: %s" % e)

		if (i == 1):
			print ("Process finished.</br></br>Continue to step 2.")
		else:
			print ("Please, press this button again.")
	else:
		print ("Please confirm there are file uploaded to the server. If so, please, press this button again, otherwise, proceede to upload mobile files to continue.")


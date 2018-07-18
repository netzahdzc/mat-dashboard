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

config = ConfigParser.ConfigParser()
config.read("../../sqlite/configuration.cnf")
file_dir = config.get('sqlite', 'file_dir')
parsed_file_dir = config.get('parsed', 'parsed_file_dir')

mysql_username = config.get('mysql', 'username')
mysql_password = config.get('mysql', 'password')
mysql_host = config.get("mysql", 'host')
mysql_database_name = config.get("mysql", "database")
mysql_fiware_database_name = config.get("mysql", "fiware_database") # FIWARE adaptation
table_fiware_list = ['etanswer', 'etcontroltest', 'etdevice', 'etdevicemodel', 'etmotorphysicaltest', 'etparticipant', 'etquestion', 'etquestionnaire', 'etuser'] # FIWARE adaptation
table_list = ['sensor_linear_acceleration', 'sensor_orientation', 'controls', 'participants', 'sessions', 'technicals', 'tests', 'users']

def clean_data_fiware_crate(table):
	"""
	To take action on the CRATE's database controller
	"""
	connection = client.connect()
	cursor = connection.cursor()
	cursor.execute("DROP TABLE IF EXISTS %s " % table)
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
	Below section clean tables from CRATE (i.e., QuamtumLeap)
	"""
	for table in table_fiware_list: 
		clean_data_fiware_crate(table)

	"""
	This section cleans main db files.
	"""
	lastone = ""
	onlyfiles = [f for f in os.listdir("../../sqlite/"+file_dir) if os.path.isfile(os.path.join("../../sqlite/"+file_dir, f))]
	onlyfiles.sort()
	for (f) in onlyfiles:
		if(not f.endswith(".php")):
			try:
				os.remove("../../sqlite/"+file_dir+"/"+f)
			except Exception as e:
				if('No such file or directory' not in e):
					continue
				else:
					print("Error catched: %s" % e)

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

	print("Files, database, and QuantumLeam data wiped.")


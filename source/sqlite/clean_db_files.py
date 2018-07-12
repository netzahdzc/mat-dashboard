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

i = 0
config = ConfigParser.ConfigParser()
config.read("./configuration.cnf")
file_dir = config.get('sqlite', 'file_dir')
parsed_file_dir = config.get('parsed', 'parsed_file_dir')

mysql_username = config.get('mysql', 'username')
mysql_password = config.get('mysql', 'password')
mysql_host = config.get("mysql", 'host')
mysql_database_name = config.get("mysql", "database")

def clean_data_base(table):
	"""
	This function truncates and reset autoincrease counter for all tables.
	"""
	try:
		mysql_conn = MySQLdb.connect(host=mysql_host,user=mysql_username,passwd=mysql_password,db=mysql_database_name,charset='utf8')
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
	"""
	#print ("</br>Process starting...")
	clean_data_base("controls")
	clean_data_base("participants")
	clean_data_base("sensor_linear_acceleration")
	clean_data_base("sensor_orientation")
	clean_data_base("sessions")
	clean_data_base("technicals")
	clean_data_base("tests")
	clean_data_base("users")

	"""
	This section cleans main db files.
	"""
	lastone = ""
	onlyfiles = [f for f in os.listdir("./"+file_dir) if os.path.isfile(os.path.join("./"+file_dir, f))]
	onlyfiles.sort()

	for (f) in onlyfiles:
		if (f.endswith(".db.processed")):
			try:
				os.rename("./"+file_dir+"/"+f, "./"+file_dir+"/"+f.split(".")[0]+".db")
			except Exception as e:
				print (e)

	for (f) in onlyfiles:
		if (f.endswith(".db")):
			if (not f.endswith(("orientatio", "accelerome"), 7, 17)):
				try:
					os.rename("./"+file_dir+"/"+f, "./"+file_dir+"/"+f+".deprecated")
					i = i + 1;
					#print ("</br>Processed: " + f)
					lastone = "./"+file_dir+"/"+f+".deprecated"
				except Exception as e:
					print (e)

	if (lastone!=""):
		os.rename(lastone, lastone.split(".db", 1)[0]+".db")

		"""
		This section cleans ACC's txt files that will be created on step 3, so redundance is avoided
		"""
		#print ("</br>Cleaning acc txt files...")
		onlyfiles = [f for f in os.listdir("./"+parsed_file_dir+"/acc") if os.path.isfile(os.path.join("./"+parsed_file_dir+"/acc", f))]
		onlyfiles.sort()
		for (f) in onlyfiles:
			try:
				os.remove("./"+parsed_file_dir+"/acc/"+f)
				#print ("</br>Processed: " + f)
			except Exception as e:
				print (e)



		"""
		This section cleans ORIENT's txt files that will be created on step 3, so redundance is avoided
		"""
		#print ("</br>Cleaning orient txt files...")
		onlyfiles = [f for f in os.listdir("./"+parsed_file_dir+"/orient") if os.path.isfile(os.path.join("./"+parsed_file_dir+"/orient", f))]
		onlyfiles.sort()
		for (f) in onlyfiles:
			try:
				os.remove("./"+parsed_file_dir+"/orient/"+f)
				#print ("</br>Processed: " + f)
			except Exception as e:
				print (e)

		if (i == 1):
			print ("</br>Process finished.</br></br>Continue pressing button 2.")
		else:
			print ("</br>Please, press this button again.")
	else:
		print ("</br>Please confirm there are file uploaded to the server. If so, please, press this button again, otherwise, proceede to upload mobile files to continue.")

	#print ("</br>Process finished")



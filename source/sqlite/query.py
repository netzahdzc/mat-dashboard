#!/usr/bin/env python
import sys
import shutil
import os.path
import time
import logging
import re
import os

from subprocess import Popen, PIPE, STDOUT
from datetime import datetime
from crate import client

SERVER_IP = 'localhost:4200'
DATABASE_NAME = 'etdevicemodel'

connection = client.connect(SERVER_IP)
cursor = connection.cursor()

cursor.execute('SELECT * FROM mtmatest.etdevicemodel')

for row in cursor:
	print(row)





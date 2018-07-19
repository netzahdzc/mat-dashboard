from crate import client

tables = ['etmotorphysicaltest', 'etdeviceSM', 'etdeviceSE', 'etcontroltest', 'etparticipant', 'etuser', 'etanswer', 'etquestionnaire']
mysql_prefix = 'mtmatest';

connection = client.connect()
cursor = connection.cursor()

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
dbRefDevice = 6
dbRefUser = 7
dbDateTestEnded = 1
dbDateTestStarted = 2
dbTestType = 8

# etdevice
dbCategory = 0
dbConsistOf = 2
dbControlledProperty = 3
dbEntityId_device = 5
dbValue = 15
dbMobileModel = 13
dbMobileBrand = 10
dbMobileAndroidApi = 11
dbAppVersion = 13
dbCreated_device = 4

# etcontroltest
dbRefUser_control = 9
dbWeight = 5
dbHeight = 4
dbWaist = 11
dbHeartRate = 7
dbSystolic = 8
dbDiastolic = 6
dbCreated = 0

# etparticipant
dbName = 5
dbSurname = 6
dbGender = 4
dbTrash = 7
dbBirthday = 0

# etuser
dbName_user = 6
dbSurname_user = 7
dbCode = 0
dbEmail = 2
dbTrash_user = 9
dbCodeStatus = 1

# etanswer
dbEntityId_answer = 1
dbRefQuestion_answer = 4
dbRefUser_answer = 5
dbAnswer = 0
# FIWARE adaptation ends here

for table in tables:
	q = ''

	if table is 'etdeviceSM':
		table = 'etdevice'
		q = "WHERE category LIKE 'smartphone'"

	if table is 'etdeviceSE':
		table = 'etdevice'
		q = "WHERE category LIKE 'sensor'"

	cursor.execute("SELECT * FROM %s %s" % ( (mysql_prefix + table), q))
	result = cursor.fetchall()
	
	for row in result:
		if(table is 'etmotorphysicaltest'):
			#print('1')
			# Get user id
			refUser = row[dbRefUser]
			entityId_test = row[dbEntityId_test]

			# Keep a record of test and questionnaire
			"""
				refQuestionnaire[key]
					"ffffffffff9cbbf4465f0ef30033c587-71186_xy" => ffffffffff9cbbf4465f0ef30033c587-questionnaire-7118

				refQuestionnaireUsers[key]
					"ffffffffff9cbbf4465f0ef30033c587-questionnaire-7118" => http://207.249.127.162:1234/users/1
			"""
			if("refQuestionnaire" in row[dbConfiguration]):
				questionnaireId = row[dbConfiguration]["refQuestionnaire"]
				refQuestionnaire[entityId_test] = questionnaireId
				refQuestionnaireUsers[questionnaireId] = refUser

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

		if(table is 'etanswer'):
			list_of_answers[row[dbRefQuestion_answer]] = row[dbAnswer]

		if(table is 'etquestionnaire'):
			entityId_questionnaire = row[dbEntityId_questionnaire]
			if(entityId_questionnaire in refQuestionnaire.values()):
				list_of_questions_id = row[dbRefQuestion]

				refUser = refQuestionnaireUsers.get(entityId_questionnaire)
				participant_id = [x.strip() for x in refUser.split('/')]
				type_test = row[dbQuestionnaireType]
				test_option = "test_option"
				beginning_sensor_collection_timestamp = "beginning_sensor_collection_timestamp"
				finishing_sensor_collection_timestamp = "finishing_sensor_collection_timestamp"
				q1 = list_of_answers.get(list_of_questions_id[0], "none")
				q2 = list_of_answers.get(list_of_questions_id[1], "none")
				q3 = list_of_answers.get(list_of_questions_id[2], "none")
				q4 = list_of_answers.get(list_of_questions_id[3], "none")
				q5 = list_of_answers.get(list_of_questions_id[4], "none")
				q6 = list_of_answers.get(list_of_questions_id[5], "none")
				q7 = list_of_answers.get(list_of_questions_id[6], "none")
				q8 = list_of_answers.get(list_of_questions_id[7], "none")
				q9 = list_of_answers.get(list_of_questions_id[8], "none")
				q10 = list_of_answers.get(list_of_questions_id[9], "none")
				data_evaluation_score = "data_evaluation_score"
				data_evaluation_description = "data_evaluation_description"
				status = "status"
				last_updated = "last_updated"

				print('participant_id: %s, type_test: %s, test_option: %s, beginning_sensor_collection_timestamp: %s, finishing_sensor_collection_timestamp: %s, q1: %s, q2: %s, q3: %s, q4: %s, q5: %s, q6: %s, q7: %s, q8: %s, q9: %s, q10: %s, data_evaluation_score: %s, data_evaluation_description: %s, status: %s, last_updated: %s' % (participant_id[len(participant_id)-1], type_test, test_option, beginning_sensor_collection_timestamp, finishing_sensor_collection_timestamp, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, data_evaluation_score, data_evaluation_description, status, last_updated))

		if(table is 'etdevice'):
			#print('2')
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
					print('participant_id: %s, test_id: %s, mobile_model: %s, mobile_brand: %s, mobile_android_api: %s, app_version: %s, created: %s' % (participant_id[len(participant_id)-1], test_id, mobile_model, mobile_brand, mobile_android_api, app_version, created))

				#print('2.1')
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
				#print('3')
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

							# Format axis data
							for data in list_of_data:
								axis = [x.strip() for x in data.split(',')]
								accuracy = "accuracy"
								# Store into database
								print('participant_id: %s, test_id: %s, accuracy: %s, x: %s, y: %s, z: %s ' % (participant_id[len(participant_id)-1], test_id, accuracy, axis[0], axis[1], axis[2]))
						
						# Orientation data only
						if('orientation' in row[dbControlledProperty]):
							list_of_data = [x.strip() for x in row[dbValue].split(' ')]

							# Format axis data
							for data in list_of_data:
								axis = [x.strip() for x in data.split(',')]
								# Store into database
								print('participant_id: %s, test_id: %s, x: %s, y: %s, z: %s ' % (participant_id[len(participant_id)-1], test_id, axis[0], axis[1], axis[2]))

		if(table is 'etcontroltest'):
			refUser = row[dbRefUser_control]
			participant_id = [x.strip() for x in refUser.split('/')]
			weight = row[dbWeight]
			height = row[dbHeight]
			waits = row[dbWaist]
			heartRate = row[dbHeartRate]
			systolic = row[dbSystolic]
			diastolic = row[dbDiastolic]
			create = row[dbCreated]

			# Store into database
			print('participant_id: %s, weight: %s, height: %s, waist_size: %s, heart_rate: %s, systolic_blood: %s, diastolic_blood: %s, created: %s' % (participant_id[len(participant_id)-1], weight, height, waits, heartRate, systolic, diastolic, create))

		if(table is 'etparticipant'):
			name = row[dbName]
			surname = row[dbSurname]
			gender = row[dbGender]
			trash = row[dbTrash]
			birthday = row[dbBirthday]
			photo = "false"
			created = "created"
			last_updated = "last_updated"

			# Store into database
			print('name: %s, surname: %s, gender: %s, birthday: %s, photo: %s, trash: %s, created: %s, last_updated: %s' % (name, surname, gender, birthday, photo, trash, created, last_updated))

		if(table is 'etuser'):
			name = row[dbName_user]
			surname = row[dbSurname_user]
			code = row[dbCode]
			email = row[dbEmail]
			trash = row[dbTrash]
			gender = "gender"
			password = "password"
			activation_status = "activation_status"
			created = "created"
			last_updated = "last_updated"

			# Store into database
			print('name: %s, surname: %s, gender: %s, email: %s, password: %s, activation_code: %s, activation_status: %s, trash: %s, created: %s, last_updated: %s' % (name, surname, gender, email, password, code, activation_status, trash, created, last_updated))


	#print(result)





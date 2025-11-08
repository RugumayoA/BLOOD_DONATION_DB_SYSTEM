

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


-- Table structure for table `blood_inventory`
CREATE TABLE IF NOT EXISTS `blood_inventory` (
  `inventory_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `blood_type` enum('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
  `quantity_ml` int(10) unsigned NOT NULL,
  `collection_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `storage_location` varchar(100) DEFAULT NULL,
  `status` enum('Available','Reserved','Quarantined','Discarded','Transfused') DEFAULT 'Available',
  `donation_id` int(10) unsigned DEFAULT NULL,
  `test_results` text,
  `processing_date` date DEFAULT NULL,
  PRIMARY KEY (`inventory_id`),
  KEY `idx_bi_donation` (`donation_id`),
  KEY `idx_inventory_status_expiry` (`status`,`expiry_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `blood_request`
CREATE TABLE IF NOT EXISTS `blood_request` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipient_id` int(10) unsigned NOT NULL,
  `request_date` datetime NOT NULL,
  `blood_type` enum('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
  `quantity_ml` int(10) unsigned NOT NULL,
  `urgency_level` enum('Low','Medium','High','Critical') DEFAULT 'Medium',
  `hospital_name` varchar(255) DEFAULT NULL,
  `doctor_name` varchar(255) DEFAULT NULL,
  `diagnosis` text,
  `status` enum('Pending','Approved','Rejected','Fulfilled','Cancelled') DEFAULT 'Pending',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `fulfillment_date` datetime DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`request_id`),
  KEY `idx_br_recipient` (`recipient_id`),
  KEY `idx_br_approved_by` (`approved_by`),
  KEY `idx_request_status_date` (`status`,`request_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `blood_transfusion`
CREATE TABLE IF NOT EXISTS `blood_transfusion` (
  `transfusion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL,
  `inventory_id` int(10) unsigned NOT NULL,
  `transfusion_date` datetime NOT NULL,
  `quantity_ml` int(10) unsigned NOT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `hospital_name` varchar(255) DEFAULT NULL,
  `patient_room` varchar(50) DEFAULT NULL,
  `status` enum('Started','Completed','Complications','Cancelled') DEFAULT 'Started',
  `complications` text,
  `notes` text,
  PRIMARY KEY (`transfusion_id`),
  KEY `idx_bt_request` (`request_id`),
  KEY `idx_bt_inventory` (`inventory_id`),
  KEY `idx_bt_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `donation_event`
CREATE TABLE IF NOT EXISTS `donation_event` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `number_of_participants` int(10) unsigned DEFAULT NULL,
  `status` enum('Planned','Ongoing','Completed','Cancelled') DEFAULT 'Planned',
  `target_blood` int(10) unsigned DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`event_id`),
  KEY `idx_event_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `donation_record`
CREATE TABLE IF NOT EXISTS `donation_record` (
  `donation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donor_id` int(10) unsigned NOT NULL,
  `session_id` int(10) unsigned DEFAULT NULL,
  `donation_date` datetime NOT NULL,
  `blood_volume_ml` int(10) unsigned NOT NULL,
  `hemoglobin_level` decimal(4,1) DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `bag_code` varchar(100) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`donation_id`),
  UNIQUE KEY `uq_bag_code` (`bag_code`),
  KEY `idx_dr_donor` (`donor_id`),
  KEY `idx_dr_session` (`session_id`),
  KEY `idx_dr_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `donation_session`
CREATE TABLE IF NOT EXISTS `donation_session` (
  `session_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `status` enum('Scheduled','Ongoing','Completed','Cancelled') DEFAULT 'Scheduled',
  `max_donors` int(10) unsigned DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`session_id`),
  KEY `idx_session_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `donor`
CREATE TABLE IF NOT EXISTS `donor` (
  `donor_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `blood_type` enum('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `medical_conditions` text,
  `medications` text,
  `last_donation_date` date DEFAULT NULL,
  `registration_date` date NOT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed','Other') DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`donor_id`),
  UNIQUE KEY `uq_donor_email` (`email`),
  KEY `idx_donor_blood_type` (`blood_type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `notification`
CREATE TABLE IF NOT EXISTS `notification` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recipient_type` enum('donor','recipient','staff') NOT NULL,
  `recipient_id` int(10) unsigned NOT NULL,
  `notification_type` enum('SMS','Email','Push','Call') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text,
  `sent_date` date DEFAULT NULL,
  `sent_time` time DEFAULT NULL,
  `status` enum('Queued','Sent','Failed') DEFAULT 'Queued',
  `delivery_method` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `recipient`
CREATE TABLE IF NOT EXISTS `recipient` (
  `recipient_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `blood_type` enum('O-','O+','A-','A+','B-','B+','AB-','AB+') NOT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `medical_condition` text,
  `allergies` text,
  `registration_date` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  PRIMARY KEY (`recipient_id`),
  UNIQUE KEY `uq_recipient_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Table structure for table `staff`
CREATE TABLE IF NOT EXISTS `staff` (
  `staff_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('Active','Inactive','On Leave') DEFAULT 'Active',
  `qualifications` text,
  `license_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `uq_staff_employee` (`employee_id`),
  UNIQUE KEY `uq_staff_username` (`username`),
  UNIQUE KEY `uq_staff_email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



-- Table structure for table `testing_record`
CREATE TABLE IF NOT EXISTS `testing_record` (
  `test_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donation_id` int(10) unsigned NOT NULL,
  `test_date` datetime NOT NULL,
  `test_type` varchar(100) NOT NULL,
  `test_result` enum('Positive','Negative','Indeterminate') NOT NULL,
  `staff_id` int(10) unsigned DEFAULT NULL,
  `test_notes` text,
  `retest_required` enum('Yes','No') DEFAULT 'No',
  `retest_date` datetime DEFAULT NULL,
  PRIMARY KEY (`test_id`),
  KEY `idx_tr_donation` (`donation_id`),
  KEY `idx_tr_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


-- Junction table for many-to-many relationship between donation_event and staff
CREATE TABLE IF NOT EXISTS `event_staff` (
  `event_id` int(10) unsigned NOT NULL,
  `staff_id` int(10) unsigned NOT NULL,
  `role` varchar(100) DEFAULT 'Staff',
  `assigned_date` datetime DEFAULT NULL,
  PRIMARY KEY (`event_id`, `staff_id`),
  KEY `idx_es_event` (`event_id`),
  KEY `idx_es_staff` (`staff_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- Junction table for many-to-many relationship between donation_event and donor
CREATE TABLE IF NOT EXISTS `event_donor` (
  `event_id` int(10) unsigned NOT NULL,
  `donor_id` int(10) unsigned NOT NULL,
  `registration_date` datetime DEFAULT NULL,
  `attendance_status` enum('Registered','Present','Absent') DEFAULT 'Registered',
  PRIMARY KEY (`event_id`, `donor_id`),
  KEY `idx_ed_event` (`event_id`),
  KEY `idx_ed_donor` (`donor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- Insert the 5 staff members with usernames and passwords
INSERT IGNORE INTO staff (employee_id, username, first_name, last_name, position, department, phone_number, email, password, hire_date, status, qualifications) VALUES
('SG001', 'KAishaRugumayo', 'Karungi', 'Aisha', 'Blood Collection Specialist', 'Collection Department', '0755385092', 'kisha.rugus@gmail.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '2025-01-01', 'Active', 'Certified Phlebotomist, Blood Safety Training'),
('SG002', 'APMukasa', 'Peter', 'Arnold Mukasa', 'Laboratory Technician', 'Testing Department', '0783946101', 'apmukasa@gmail.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '2025-01-01', 'Active', 'Medical Laboratory Science, Blood Testing Certification'),
('SG003', 'MKJoshua', 'Menhya', 'Joshua Kibedi', 'Inventory Manager', 'Storage Department', '0709397670', 'kibedijoshua7@gmail.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '2025-01-01', 'Active', 'Supply Chain Management, Blood Storage Certification'),
('SG004', 'HNdawula', 'Ndawula', 'Habibah', 'Donor Coordinator', 'Outreach Department', '0760986179', 'habibahndawula@gmail.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '2025-01-01', 'Active', 'Public Health, Community Outreach Training'),
('SG005', 'Ainedativah', 'Ainebyoona', 'Dativah', 'System Administrator', 'IT Department', '0755039309', 'aniebyoonadativah@gmail.com', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', '2025-01-01', 'Active', 'Information Technology, Database Management');


-- Update donor last_donation_date to be recent (within last year) for active donor status
-- First insert donors, then update their last_donation_date based on their actual donations
-- Insert 70 Sample Donors with Realistic Information
INSERT IGNORE INTO donor (first_name, last_name, date_of_birth, gender, blood_type, phone_number, email, address, city, medical_conditions, medications, last_donation_date, registration_date, marital_status, weight, height) VALUES
('John', 'Mukasa', '1990-05-15', 'Male', 'O+', '0770123456', 'john.mukasa@gmail.com', 'Plot 45, Nakawa', 'Kampala', NULL, NULL, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '2023-06-10', 'Married', 75.5, 175.0),
('Sarah', 'Nakato', '1988-03-22', 'Female', 'A+', '0780234567', 'sarah.nakato@gmail.com', 'Block 12, Ntinda', 'Kampala', NULL, NULL, '2025-02-20', '2023-07-05', 'Single', 62.0, 165.0),
('David', 'Kato', '1992-11-08', 'Male', 'B+', '0770345678', 'david.kato@gmail.com', 'Road 8, Entebbe', 'Entebbe', NULL, NULL, '2025-01-10', '2023-05-20', 'Married', 80.0, 180.0),
('Mary', 'Nalubega', '1995-07-14', 'Female', 'O-', '0780456789', 'mary.nalubega@gmail.com', 'Street 23, Jinja', 'Jinja', NULL, NULL, '2025-03-05', '2023-08-15', 'Single', 58.5, 160.0),
('Peter', 'Ssemwogerere', '1987-09-30', 'Male', 'AB+', '0770567890', 'peter.ssemwogerere@gmail.com', 'Plot 67, Mukono', 'Mukono', NULL, NULL, '2025-02-15', '2023-09-01', 'Married', 72.0, 170.0),
('Grace', 'Namukasa', '1993-12-25', 'Female', 'A-', '0780678901', 'grace.namukasa@gmail.com', 'Block 5, Masaka', 'Masaka', NULL, NULL, '2025-01-25', '2023-10-10', 'Single', 60.0, 162.0),
('James', 'Kiggundu', '1989-04-18', 'Male', 'O+', '0770789012', 'james.kiggundu@gmail.com', 'Road 34, Mbale', 'Mbale', NULL, NULL, '2025-03-10', '2023-11-05', 'Married', 78.5, 178.0),
('Ruth', 'Nabukeera', '1991-06-12', 'Female', 'B-', '0780890123', 'ruth.nabukeera@gmail.com', 'Street 56, Gulu', 'Gulu', NULL, NULL, '2025-02-28', '2023-12-01', 'Married', 65.0, 168.0),
('Michael', 'Lubega', '1994-08-20', 'Male', 'A+', '0770901234', 'michael.lubega@gmail.com', 'Plot 89, Mbarara', 'Mbarara', NULL, NULL, '2025-01-18', '2025-01-05', 'Single', 70.0, 172.0),
('Esther', 'Nakiyemba', '1996-02-14', 'Female', 'O+', '0780012345', 'esther.nakiyemba@gmail.com', 'Block 12, Fort Portal', 'Fort Portal', NULL, NULL, '2025-03-20', '2025-01-15', 'Single', 55.0, 158.0),
('Robert', 'Mugisha', '1986-10-05', 'Male', 'B+', '0770123457', 'robert.mugisha@gmail.com', 'Road 45, Lira', 'Lira', NULL, NULL, '2025-02-10', '2023-07-20', 'Married', 82.0, 182.0),
('Patience', 'Nalubowa', '1990-01-28', 'Female', 'AB-', '0780234568', 'patience.nalubowa@gmail.com', 'Street 78, Arua', 'Arua', NULL, NULL, '2025-01-30', '2023-08-25', 'Married', 63.5, 166.0),
('Daniel', 'Ssebunya', '1992-05-16', 'Male', 'O-', '0770345679', 'daniel.ssebunya@gmail.com', 'Plot 23, Soroti', 'Soroti', NULL, NULL, '2025-03-15', '2023-09-10', 'Single', 76.0, 176.0),
('Joyce', 'Nabatanzi', '1988-07-24', 'Female', 'A+', '0780456790', 'joyce.nabatanzi@gmail.com', 'Block 34, Tororo', 'Tororo', NULL, NULL, '2025-02-22', '2023-10-20', 'Married', 61.0, 164.0),
('Simon', 'Kawuma', '1991-09-11', 'Male', 'B+', '0770567901', 'simon.kawuma@gmail.com', 'Road 67, Hoima', 'Hoima', NULL, NULL, '2025-01-12', '2023-11-15', 'Single', 79.0, 179.0),
('Florence', 'Nakigudde', '1993-11-03', 'Female', 'O+', '0780679012', 'florence.nakigudde@gmail.com', 'Street 90, Kabale', 'Kabale', NULL, NULL, '2025-03-08', '2023-12-10', 'Married', 59.5, 161.0),
('Andrew', 'Mwesigwa', '1989-03-19', 'Male', 'A-', '0770789123', 'andrew.mwesigwa@gmail.com', 'Plot 56, Masindi', 'Masindi', NULL, NULL, '2025-02-18', '2025-01-01', 'Married', 74.0, 174.0),
('Prossy', 'Nabukeera', '1995-12-07', 'Female', 'AB+', '0780891234', 'prossy.nabukeera@gmail.com', 'Block 78, Bushenyi', 'Bushenyi', NULL, NULL, '2025-01-22', '2025-01-20', 'Single', 57.0, 159.0),
('Joseph', 'Kigozi', '1987-04-26', 'Male', 'O+', '0770902345', 'joseph.kigozi@gmail.com', 'Road 12, Iganga', 'Iganga', NULL, NULL, '2025-03-12', '2023-06-25', 'Married', 81.5, 183.0),
('Rebecca', 'Nalubwama', '1992-08-09', 'Female', 'B-', '0780013456', 'rebecca.nalubwama@gmail.com', 'Street 45, Kamuli', 'Kamuli', NULL, NULL, '2025-02-25', '2023-07-30', 'Single', 64.0, 167.0),
('Paul', 'Ssemakula', '1994-06-13', 'Male', 'A+', '0770124567', 'paul.ssemakula@gmail.com', 'Plot 89, Luwero', 'Luwero', NULL, NULL, '2025-01-08', '2023-08-30', 'Single', 73.5, 175.0),
('Agnes', 'Nakabuye', '1990-10-21', 'Female', 'O-', '0780235678', 'agnes.nakabuye@gmail.com', 'Block 23, Mityana', 'Mityana', NULL, NULL, '2025-03-18', '2023-09-25', 'Married', 62.5, 165.0),
('Charles', 'Mugerwa', '1991-02-04', 'Male', 'B+', '0770346789', 'charles.mugerwa@gmail.com', 'Road 56, Mukono', 'Mukono', NULL, NULL, '2025-02-05', '2023-10-30', 'Married', 77.0, 177.0),
('Dorothy', 'Nalubega', '1993-04-17', 'Female', 'AB-', '0780457890', 'dorothy.nalubega@gmail.com', 'Street 34, Wakiso', 'Wakiso', NULL, NULL, '2025-01-28', '2023-11-20', 'Single', 60.5, 163.0),
('Mark', 'Kawesi', '1988-12-29', 'Male', 'O+', '0770568901', 'mark.kawesi@gmail.com', 'Plot 67, Mpigi', 'Mpigi', NULL, NULL, '2025-03-22', '2023-12-15', 'Married', 80.5, 181.0),
('Betty', 'Nakawunde', '1996-01-15', 'Female', 'A+', '0780679012', 'betty.nakawunde@gmail.com', 'Block 45, Butambala', 'Butambala', NULL, NULL, '2025-02-12', '2025-01-10', 'Single', 56.0, 157.0),
('Tom', 'Ssebowa', '1989-07-08', 'Male', 'B+', '0770789123', 'tom.ssebowa@gmail.com', 'Road 78, Kalungu', 'Kalungu', NULL, NULL, '2025-01-05', '2023-06-15', 'Married', 75.0, 176.0),
('Catherine', 'Nabukeera', '1992-09-25', 'Female', 'O-', '0780890234', 'catherine.nabukeera@gmail.com', 'Street 12, Rakai', 'Rakai', NULL, NULL, '2025-03-25', '2023-07-10', 'Married', 61.5, 164.0),
('Steven', 'Mugabi', '1994-03-11', 'Male', 'A-', '0770901345', 'steven.mugabi@gmail.com', 'Plot 34, Lyantonde', 'Lyantonde', NULL, NULL, '2025-02-20', '2023-08-05', 'Single', 72.5, 173.0),
('Hannah', 'Nalubowa', '1990-05-27', 'Female', 'AB+', '0780012456', 'hannah.nalubowa@gmail.com', 'Block 56, Sembabule', 'Sembabule', NULL, NULL, '2025-01-15', '2023-09-15', 'Married', 63.0, 166.0),
('Richard', 'Kawuma', '1991-11-19', 'Male', 'O+', '0770123567', 'richard.kawuma@gmail.com', 'Road 23, Gomba', 'Gomba', NULL, NULL, '2025-03-28', '2023-10-05', 'Married', 78.0, 178.0),
('Lydia', 'Nakigudde', '1993-08-02', 'Female', 'B-', '0780234678', 'lydia.nakigudde@gmail.com', 'Street 67, Kalangala', 'Kalangala', NULL, NULL, '2025-02-08', '2023-11-10', 'Single', 59.0, 160.0),
('Francis', 'Mwesigwa', '1987-10-16', 'Male', 'A+', '0770345789', 'francis.mwesigwa@gmail.com', 'Plot 90, Kyankwanzi', 'Kyankwanzi', NULL, NULL, '2025-01-20', '2023-12-20', 'Married', 79.5, 180.0),
('Rose', 'Nabukeera', '1995-02-23', 'Female', 'O+', '0780456890', 'rose.nabukeera@gmail.com', 'Block 12, Nakaseke', 'Nakaseke', NULL, NULL, '2025-03-30', '2025-01-25', 'Single', 58.0, 158.0),
('Patrick', 'Kigozi', '1992-06-30', 'Male', 'B+', '0770567901', 'patrick.kigozi@gmail.com', 'Road 45, Nakasongola', 'Nakasongola', NULL, NULL, '2025-02-15', '2023-07-05', 'Married', 76.5, 177.0),
('Mercy', 'Nalubwama', '1994-04-14', 'Female', 'AB-', '0780678012', 'mercy.nalubwama@gmail.com', 'Street 78, Buikwe', 'Buikwe', NULL, NULL, '2025-01-10', '2023-08-10', 'Single', 60.0, 162.0),
('Brian', 'Ssemakula', '1989-08-28', 'Male', 'O-', '0770789123', 'brian.ssemakula@gmail.com', 'Plot 23, Kayunga', 'Kayunga', NULL, NULL, '2025-03-05', '2023-09-20', 'Married', 81.0, 182.0),
('Faith', 'Nakabuye', '1991-12-06', 'Female', 'A+', '0780890234', 'faith.nakabuye@gmail.com', 'Block 56, Mukono', 'Mukono', NULL, NULL, '2025-02-22', '2023-10-15', 'Married', 62.0, 165.0),
('Edward', 'Mugerwa', '1993-01-20', 'Male', 'B+', '0770901345', 'edward.mugerwa@gmail.com', 'Road 34, Buikwe', 'Buikwe', NULL, NULL, '2025-01-25', '2023-11-25', 'Single', 74.5, 174.0),
('Peace', 'Nalubega', '1990-07-12', 'Female', 'O+', '0780012456', 'peace.nalubega@gmail.com', 'Street 67, Jinja', 'Jinja', NULL, NULL, '2025-03-15', '2023-12-05', 'Married', 61.5, 164.0),
('Henry', 'Kawesi', '1992-09-24', 'Male', 'A-', '0770123567', 'henry.kawesi@gmail.com', 'Plot 90, Kamuli', 'Kamuli', NULL, NULL, '2025-02-10', '2025-01-05', 'Married', 77.5, 178.0),
('Hope', 'Nakawunde', '1995-03-08', 'Female', 'AB+', '0780234678', 'hope.nakawunde@gmail.com', 'Block 12, Iganga', 'Iganga', NULL, NULL, '2025-01-18', '2025-01-30', 'Single', 57.5, 159.0),
('Geoffrey', 'Ssebowa', '1988-05-21', 'Male', 'O+', '0770345789', 'geoffrey.ssebowa@gmail.com', 'Road 45, Bugiri', 'Bugiri', NULL, NULL, '2025-03-20', '2023-06-30', 'Married', 80.0, 181.0),
('Priscilla', 'Nabukeera', '1991-11-03', 'Female', 'B-', '0780456890', 'priscilla.nabukeera@gmail.com', 'Street 78, Mayuge', 'Mayuge', NULL, NULL, '2025-02-28', '2023-07-25', 'Married', 64.5, 168.0),
('Moses', 'Mugabi', '1993-02-16', 'Male', 'A+', '0770567901', 'moses.mugabi@gmail.com', 'Plot 23, Namayingo', 'Namayingo', NULL, NULL, '2025-01-12', '2023-08-20', 'Single', 73.0, 175.0),
('Naomi', 'Nalubowa', '1994-08-29', 'Female', 'O-', '0780678012', 'naomi.nalubowa@gmail.com', 'Block 56, Busia', 'Busia', NULL, NULL, '2025-03-08', '2023-09-30', 'Single', 59.5, 161.0),
('Samson', 'Kawuma', '1990-04-11', 'Male', 'B+', '0770789123', 'samson.kawuma@gmail.com', 'Road 34, Tororo', 'Tororo', NULL, NULL, '2025-01-30', '2023-10-25', 'Married', 78.5, 179.0),
('Deborah', 'Nakigudde', '1992-06-25', 'Female', 'AB-', '0780890234', 'deborah.nakigudde@gmail.com', 'Street 67, Pallisa', 'Pallisa', NULL, NULL, '2025-02-18', '2023-11-15', 'Married', 61.0, 163.0),
('Timothy', 'Mwesigwa', '1989-10-09', 'Male', 'O+', '0770901345', 'timothy.mwesigwa@gmail.com', 'Plot 90, Budaka', 'Budaka', NULL, NULL, '2025-03-22', '2023-12-25', 'Married', 79.0, 180.0),
('Ruth', 'Nabukeera', '1995-01-22', 'Female', 'A+', '0780012456', 'ruth2.nabukeera@gmail.com', 'Block 12, Kibuku', 'Kibuku', NULL, NULL, '2025-02-05', '2025-01-10', 'Single', 58.5, 158.0),
('Isaac', 'Kigozi', '1991-07-05', 'Male', 'B+', '0770123567', 'isaac.kigozi@gmail.com', 'Road 45, Butaleja', 'Butaleja', NULL, NULL, '2025-01-22', '2023-06-20', 'Married', 76.0, 177.0),
('Sarah', 'Nalubwama', '1993-09-18', 'Female', 'O+', '0780234678', 'sarah2.nalubwama@gmail.com', 'Street 78, Kaliro', 'Kaliro', NULL, NULL, '2025-03-12', '2023-07-15', 'Single', 60.5, 162.0),
('Joshua', 'Ssemakula', '1994-03-01', 'Male', 'A-', '0770345789', 'joshua.ssemakula@gmail.com', 'Plot 23, Buyende', 'Buyende', NULL, NULL, '2025-02-25', '2023-08-10', 'Single', 72.0, 173.0),
('Esther', 'Nakabuye', '1990-11-14', 'Female', 'AB+', '0780456890', 'esther2.nakabuye@gmail.com', 'Block 56, Luuka', 'Luuka', NULL, NULL, '2025-01-08', '2023-09-05', 'Married', 63.5, 166.0),
('Solomon', 'Mugerwa', '1992-05-27', 'Male', 'O-', '0770567901', 'solomon.mugerwa@gmail.com', 'Road 34, Namutumba', 'Namutumba', NULL, NULL, '2025-03-18', '2023-10-20', 'Married', 81.5, 183.0),
('Rebecca', 'Nalubega', '1991-08-10', 'Female', 'B+', '0780678012', 'rebecca2.nalubega@gmail.com', 'Street 67, Butebo', 'Butebo', NULL, NULL, '2025-02-12', '2023-11-10', 'Married', 64.0, 167.0),
('Benjamin', 'Kawesi', '1993-12-23', 'Male', 'A+', '0770789123', 'benjamin.kawesi@gmail.com', 'Plot 90, Ngora', 'Ngora', NULL, NULL, '2025-01-28', '2023-12-20', 'Single', 75.5, 176.0),
('Rachel', 'Nakawunde', '1995-04-06', 'Female', 'O+', '0780890234', 'rachel.nakawunde@gmail.com', 'Block 12, Serere', 'Serere', NULL, NULL, '2025-03-25', '2025-01-15', 'Single', 57.0, 157.0),
('Jacob', 'Ssebowa', '1988-06-19', 'Male', 'B-', '0770901345', 'jacob.ssebowa@gmail.com', 'Road 45, Amuria', 'Amuria', NULL, NULL, '2025-02-20', '2023-06-25', 'Married', 80.5, 181.0),
('Leah', 'Nabukeera', '1990-02-02', 'Female', 'AB-', '0780012456', 'leah.nabukeera@gmail.com', 'Street 78, Katakwi', 'Katakwi', NULL, NULL, '2025-01-15', '2023-07-20', 'Married', 62.5, 165.0),
('Aaron', 'Mugabi', '1992-08-15', 'Male', 'O+', '0770123567', 'aaron.mugabi@gmail.com', 'Plot 23, Kapelebyong', 'Kapelebyong', NULL, NULL, '2025-03-30', '2023-08-15', 'Married', 77.0, 178.0),
('Miriam', 'Nalubowa', '1994-10-28', 'Female', 'A+', '0780234678', 'miriam.nalubowa@gmail.com', 'Block 56, Bukedea', 'Bukedea', NULL, NULL, '2025-02-08', '2023-09-25', 'Single', 59.0, 160.0),
('Caleb', 'Kawuma', '1991-01-11', 'Male', 'B+', '0770345789', 'caleb.kawuma@gmail.com', 'Road 34, Kumi', 'Kumi', NULL, NULL, '2025-01-25', '2023-10-30', 'Married', 78.0, 179.0),
('Abigail', 'Nakigudde', '1993-03-24', 'Female', 'O-', '0780456890', 'abigail.nakigudde@gmail.com', 'Street 67, Ngora', 'Ngora', NULL, NULL, '2025-03-15', '2023-11-20', 'Single', 61.5, 164.0),
('Ethan', 'Mwesigwa', '1995-07-07', 'Male', 'AB+', '0770567901', 'ethan.mwesigwa@gmail.com', 'Plot 90, Soroti', 'Soroti', NULL, NULL, '2025-02-22', '2023-12-10', 'Single', 74.0, 174.0),
('Hannah', 'Nabukeera', '1990-09-20', 'Female', 'A-', '0780678012', 'hannah2.nabukeera@gmail.com', 'Block 12, Kaberamaido', 'Kaberamaido', NULL, NULL, '2025-01-10', '2025-01-05', 'Married', 63.0, 166.0),
('Noah', 'Kigozi', '1992-11-03', 'Male', 'O+', '0770789123', 'noah.kigozi@gmail.com', 'Road 45, Dokolo', 'Dokolo', NULL, NULL, '2025-03-28', '2023-06-30', 'Married', 79.5, 180.0),
('Elizabeth', 'Nalubwama', '1994-05-16', 'Female', 'B+', '0780890234', 'elizabeth.nalubwama@gmail.com', 'Street 78, Lira', 'Lira', NULL, NULL, '2025-02-15', '2023-07-25', 'Single', 60.0, 162.0),
('Luke', 'Ssemakula', '1989-08-29', 'Male', 'A+', '0770901345', 'luke.ssemakula@gmail.com', 'Plot 23, Alebtong', 'Alebtong', NULL, NULL, '2025-01-20', '2023-08-20', 'Married', 76.5, 177.0),
('Mary', 'Nakabuye', '1991-12-12', 'Female', 'O-', '0780012456', 'mary2.nakabuye@gmail.com', 'Block 56, Otuke', 'Otuke', NULL, NULL, '2025-03-12', '2023-09-30', 'Married', 62.0, 165.0),
('Matthew', 'Mugerwa', '1993-02-25', 'Male', 'AB-', '0770123567', 'matthew.mugerwa@gmail.com', 'Road 34, Amolatar', 'Amolatar', NULL, NULL, '2025-02-25', '2023-10-25', 'Single', 75.0, 176.0),
('Anna', 'Nalubega', '1995-06-08', 'Female', 'B-', '0780234678', 'anna.nalubega@gmail.com', 'Street 67, Apac', 'Apac', NULL, NULL, '2025-01-18', '2023-11-15', 'Single', 58.0, 158.0),
('John', 'Kawesi', '1990-10-21', 'Male', 'O+', '0770345789', 'john2.kawesi@gmail.com', 'Plot 90, Oyam', 'Oyam', NULL, NULL, '2025-03-22', '2023-12-25', 'Married', 80.0, 181.0),
('Martha', 'Nakawunde', '1992-04-04', 'Female', 'A+', '0780456890', 'martha.nakawunde@gmail.com', 'Block 12, Kole', 'Kole', NULL, NULL, '2025-02-10', '2025-01-10', 'Married', 61.0, 163.0),
('Peter', 'Ssebowa', '1994-08-17', 'Male', 'B+', '0770567901', 'peter2.ssebowa@gmail.com', 'Road 45, Kwania', 'Kwania', NULL, NULL, '2025-01-05', '2023-06-15', 'Single', 73.5, 175.0);


-- Insert Sample Recipients (for blood requests)
INSERT IGNORE INTO recipient (first_name, last_name, date_of_birth, gender, blood_type, phone_number, email, address, city, medical_condition, allergies, registration_date, status) VALUES
('James', 'Ochieng', '1985-03-15', 'Male', 'O+', '0770111111', 'james.ochieng@gmail.com', 'Plot 10, Nakasero', 'Kampala', 'Surgery Recovery', 'Penicillin', '2025-01-10', 'Active'),
('Margaret', 'Akello', '1990-07-22', 'Female', 'A+', '0780222222', 'margaret.akello@gmail.com', 'Block 5, Kololo', 'Kampala', 'Anemia', 'None', '2025-01-15', 'Active'),
('Robert', 'Okello', '1978-11-08', 'Male', 'B+', '0770333333', 'robert.okello@gmail.com', 'Road 20, Entebbe', 'Entebbe', 'Accident Victim', 'None', '2025-01-20', 'Active'),
('Sarah', 'Nakato', '1992-05-14', 'Female', 'O-', '0780444444', 'sarah.nakato.recipient@gmail.com', 'Street 15, Jinja', 'Jinja', 'Childbirth Complications', 'None', '2025-02-01', 'Active'),
('David', 'Mukasa', '1987-09-30', 'Male', 'AB+', '0770555555', 'david.mukasa.recipient@gmail.com', 'Plot 30, Mukono', 'Mukono', 'Cancer Treatment', 'None', '2025-02-05', 'Active'),
('Grace', 'Namukasa', '1995-12-25', 'Female', 'A-', '0780666666', 'grace.namukasa.recipient@gmail.com', 'Block 8, Masaka', 'Masaka', 'Surgery', 'Aspirin', '2025-02-10', 'Active'),
('Peter', 'Kiggundu', '1983-04-18', 'Male', 'O+', '0770777777', 'peter.kiggundu.recipient@gmail.com', 'Road 25, Mbale', 'Mbale', 'Trauma', 'None', '2025-02-15', 'Active'),
('Ruth', 'Nabukeera', '1991-06-12', 'Female', 'B-', '0780888888', 'ruth.nabukeera.recipient@gmail.com', 'Street 40, Gulu', 'Gulu', 'Surgery', 'None', '2025-02-20', 'Active'),
('Michael', 'Lubega', '1989-08-20', 'Male', 'A+', '0770999999', 'michael.lubega.recipient@gmail.com', 'Plot 50, Mbarara', 'Mbarara', 'Accident', 'None', '2025-03-01', 'Active'),
('Esther', 'Nakiyemba', '1993-02-14', 'Female', 'O+', '0780101010', 'esther.nakiyemba.recipient@gmail.com', 'Block 12, Fort Portal', 'Fort Portal', 'Childbirth', 'None', '2025-03-05', 'Active'),
('John', 'Mugisha', '1980-10-05', 'Male', 'B+', '0770111112', 'john.mugisha.recipient@gmail.com', 'Road 35, Lira', 'Lira', 'Surgery', 'None', '2025-03-10', 'Active'),
('Patience', 'Nalubowa', '1994-01-28', 'Female', 'AB-', '0780121212', 'patience.nalubowa.recipient@gmail.com', 'Street 60, Arua', 'Arua', 'Anemia', 'None', '2025-03-15', 'Active');


-- Insert Donation Events (staff_id: 1=SG001, 2=SG002, 3=SG003, 4=SG004, 5=SG005)
INSERT IGNORE INTO donation_event (event_name, event_date, start_time, location, staff_id, number_of_participants, status, target_blood, notes) VALUES
('World Blood Donor Day 2025', '2025-01-15', '09:00:00', 'Main Blood Bank, Kampala', 1, 20, 'Completed', 50, 'Annual blood donation drive'),
('Community Outreach - Nakawa', '2025-01-20', '08:00:00', 'Nakawa Collection Center', 1, 15, 'Completed', 30, 'Community awareness campaign'),
('Hospital Partnership - Jinja', '2025-01-25', '10:00:00', 'Jinja Regional Hospital', 1, 25, 'Completed', 40, 'Hospital collaboration event'),
('Monthly Donation Drive', '2025-02-05', '09:00:00', 'Main Blood Bank, Kampala', 1, 20, 'Completed', 35, 'Regular monthly collection'),
('Mobile Unit - Entebbe', '2025-02-10', '08:00:00', 'Entebbe Collection Point', 1, 18, 'Completed', 30, 'Mobile collection unit'),
('Valentine Blood Drive', '2025-02-20', '09:00:00', 'Main Blood Bank, Kampala', 2, 22, 'Completed', 45, 'Valentine special event'),
('Community Health Fair', '2025-02-25', '10:00:00', 'Mukono Health Center', 2, 20, 'Completed', 40, 'Health fair participation'),
('Spring Donation Campaign', '2025-03-05', '09:00:00', 'Main Blood Bank, Kampala', 2, 20, 'Completed', 38, 'Spring season drive'),
('Regional Hospital Drive', '2025-03-10', '08:00:00', 'Masaka Regional Hospital', 2, 15, 'Completed', 30, 'Regional partnership'),
('Youth Donation Day', '2025-03-15', '09:00:00', 'Main Blood Bank, Kampala', 3, 20, 'Completed', 42, 'Youth engagement event'),
('Mobile Collection - Mbale', '2025-03-20', '10:00:00', 'Mbale Collection Center', 3, 18, 'Completed', 35, 'Mobile unit deployment'),
('Community Outreach - Gulu', '2025-01-22', '08:00:00', 'Gulu Regional Hospital', 4, 16, 'Completed', 32, 'Northern region outreach'),
('Monthly Collection', '2025-02-15', '09:00:00', 'Main Blood Bank, Kampala', 4, 20, 'Completed', 40, 'Regular monthly session'),
('Western Region Drive', '2025-02-28', '10:00:00', 'Mbarara Collection Point', 4, 19, 'Completed', 38, 'Western region campaign'),
('IT Department Drive', '2025-03-12', '09:00:00', 'Main Blood Bank, Kampala', 5, 20, 'Completed', 36, 'Department-specific drive'),
('Mobile Unit - Fort Portal', '2025-03-18', '08:00:00', 'Fort Portal Health Center', 5, 17, 'Completed', 34, 'Mobile collection'),
('End of Quarter Drive', '2025-03-25', '09:00:00', 'Main Blood Bank, Kampala', 5, 20, 'Completed', 40, 'Quarterly collection event');

-- Insert Donation Sessions (staff_id: 1=SG001, 2=SG002, 3=SG003, 4=SG004, 5=SG005)
INSERT IGNORE INTO donation_session (session_date, start_time, end_time, location, staff_id, status, max_donors, notes) VALUES
('2025-01-15', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 1, 'Completed', 20, 'Regular donation session'),
('2025-01-20', '08:00:00', '16:00:00', 'Nakawa Collection Center', 1, 'Completed', 15, 'Community outreach'),
('2025-01-25', '10:00:00', '18:00:00', 'Jinja Regional Hospital', 1, 'Completed', 25, 'Hospital partnership'),
('2025-02-05', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 1, 'Completed', 20, 'Regular session'),
('2025-02-10', '08:00:00', '16:00:00', 'Entebbe Collection Point', 1, 'Completed', 18, 'Mobile unit'),
('2025-02-20', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 2, 'Completed', 22, 'Regular session'),
('2025-02-25', '10:00:00', '18:00:00', 'Mukono Health Center', 2, 'Completed', 20, 'Community drive'),
('2025-03-05', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 2, 'Completed', 20, 'Regular session'),
('2025-03-10', '08:00:00', '16:00:00', 'Masaka Regional Hospital', 2, 'Completed', 15, 'Hospital partnership'),
('2025-03-15', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 3, 'Completed', 20, 'Regular session'),
('2025-03-20', '10:00:00', '18:00:00', 'Mbale Collection Center', 3, 'Completed', 18, 'Mobile unit'),
('2025-01-18', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 4, 'Completed', 20, 'Regular session'),
('2025-01-22', '08:00:00', '16:00:00', 'Gulu Regional Hospital', 4, 'Completed', 16, 'Hospital partnership'),
('2025-02-15', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 4, 'Completed', 20, 'Regular session'),
('2025-02-28', '10:00:00', '18:00:00', 'Mbarara Collection Point', 4, 'Completed', 19, 'Community outreach'),
('2025-03-12', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 5, 'Completed', 20, 'Regular session'),
('2025-03-18', '08:00:00', '16:00:00', 'Fort Portal Health Center', 5, 'Completed', 17, 'Mobile unit'),
('2025-03-25', '09:00:00', '17:00:00', 'Main Blood Bank, Kampala', 5, 'Completed', 20, 'Regular session');


-- Insert Donations (distributed across staff: SG001=18, SG002=16, SG003=14, SG004=12, SG005=10 = 70 total)
-- Staff SG001 (staff_id=1, 18 donations, ~8,300ml total)
INSERT IGNORE INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES
(1, 1, '2025-01-15 10:30:00', 450, 14.5, '120/80', 72, 36.5, 1, 'BAG-2025-001', 'Normal donation'),
(2, 1, '2025-01-15 11:00:00', 450, 13.8, '118/75', 68, 36.6, 1, 'BAG-2025-002', 'First time donor'),
(3, 1, '2025-01-15 11:30:00', 500, 15.2, '125/82', 70, 36.4, 1, 'BAG-2025-003', 'Regular donor'),
(4, 1, '2025-01-15 12:00:00', 450, 14.0, '115/78', 65, 36.7, 1, 'BAG-2025-004', 'Normal donation'),
(5, 1, '2025-01-15 13:00:00', 450, 14.3, '122/80', 73, 36.5, 1, 'BAG-2025-005', 'Normal donation'),
(6, 2, '2025-01-20 09:30:00', 450, 13.9, '119/77', 69, 36.6, 1, 'BAG-2025-006', 'Normal donation'),
(7, 2, '2025-01-20 10:00:00', 500, 15.0, '128/85', 75, 36.4, 1, 'BAG-2025-007', 'Regular donor'),
(8, 2, '2025-01-20 10:30:00', 450, 14.2, '120/79', 71, 36.5, 1, 'BAG-2025-008', 'Normal donation'),
(9, 2, '2025-01-20 11:00:00', 450, 13.7, '117/76', 67, 36.7, 1, 'BAG-2025-009', 'Normal donation'),
(10, 3, '2025-01-25 10:00:00', 450, 14.1, '121/78', 70, 36.6, 1, 'BAG-2025-010', 'Normal donation'),
(11, 3, '2025-01-25 11:00:00', 500, 15.1, '126/83', 74, 36.4, 1, 'BAG-2025-011', 'Regular donor'),
(12, 3, '2025-01-25 12:00:00', 450, 14.4, '123/81', 72, 36.5, 1, 'BAG-2025-012', 'Normal donation'),
(13, 4, '2025-02-05 10:30:00', 450, 13.6, '118/77', 68, 36.7, 1, 'BAG-2025-013', 'Normal donation'),
(14, 4, '2025-02-05 11:30:00', 450, 14.7, '124/82', 73, 36.5, 1, 'BAG-2025-014', 'Normal donation'),
(15, 5, '2025-02-10 09:00:00', 500, 15.3, '127/84', 76, 36.4, 1, 'BAG-2025-015', 'Regular donor'),
(16, 5, '2025-02-10 10:00:00', 450, 14.0, '119/78', 69, 36.6, 1, 'BAG-2025-016', 'Normal donation'),
(17, 5, '2025-02-10 11:00:00', 450, 13.9, '120/79', 71, 36.5, 1, 'BAG-2025-017', 'Normal donation'),
(18, 5, '2025-02-10 12:00:00', 450, 14.2, '122/80', 72, 36.6, 1, 'BAG-2025-018', 'Normal donation');

-- Staff SG002 (staff_id=2, 16 donations, ~7,200ml total)
INSERT IGNORE INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES
(19, 6, '2025-02-20 10:00:00', 450, 14.3, '121/79', 70, 36.5, 2, 'BAG-2025-019', 'Normal donation'),
(20, 6, '2025-02-20 10:30:00', 450, 13.8, '118/76', 68, 36.7, 2, 'BAG-2025-020', 'Normal donation'),
(21, 6, '2025-02-20 11:00:00', 500, 15.0, '125/82', 74, 36.4, 2, 'BAG-2025-021', 'Regular donor'),
(22, 6, '2025-02-20 11:30:00', 450, 14.1, '120/78', 71, 36.6, 2, 'BAG-2025-022', 'Normal donation'),
(23, 6, '2025-02-20 12:00:00', 450, 14.5, '123/81', 73, 36.5, 2, 'BAG-2025-023', 'Normal donation'),
(24, 7, '2025-02-25 10:00:00', 450, 13.9, '119/77', 69, 36.6, 2, 'BAG-2025-024', 'Normal donation'),
(25, 7, '2025-02-25 11:00:00', 500, 15.2, '126/83', 75, 36.4, 2, 'BAG-2025-025', 'Regular donor'),
(26, 7, '2025-02-25 12:00:00', 450, 14.0, '117/75', 67, 36.7, 2, 'BAG-2025-026', 'Normal donation'),
(27, 8, '2025-03-05 10:30:00', 450, 14.4, '122/80', 72, 36.5, 2, 'BAG-2025-027', 'Normal donation'),
(28, 8, '2025-03-05 11:30:00', 450, 13.7, '118/76', 68, 36.6, 2, 'BAG-2025-028', 'Normal donation'),
(29, 9, '2025-03-10 09:00:00', 500, 15.1, '128/85', 76, 36.4, 2, 'BAG-2025-029', 'Regular donor'),
(30, 9, '2025-03-10 10:00:00', 450, 14.2, '121/79', 71, 36.5, 2, 'BAG-2025-030', 'Normal donation'),
(31, 9, '2025-03-10 11:00:00', 450, 13.6, '119/78', 70, 36.7, 2, 'BAG-2025-031', 'Normal donation'),
(32, 9, '2025-03-10 12:00:00', 450, 14.6, '124/82', 74, 36.5, 2, 'BAG-2025-032', 'Normal donation'),
(33, 9, '2025-03-10 13:00:00', 450, 14.0, '120/79', 72, 36.6, 2, 'BAG-2025-033', 'Normal donation'),
(34, 9, '2025-03-10 14:00:00', 450, 13.8, '118/77', 69, 36.6, 2, 'BAG-2025-034', 'Normal donation');

-- Staff SG003 (staff_id=3, 14 donations, ~6,300ml total)
INSERT IGNORE INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES
(35, 10, '2025-03-15 10:00:00', 450, 14.3, '121/79', 70, 36.5, 3, 'BAG-2025-035', 'Normal donation'),
(36, 10, '2025-03-15 10:30:00', 500, 15.0, '125/82', 74, 36.4, 3, 'BAG-2025-036', 'Regular donor'),
(37, 10, '2025-03-15 11:00:00', 450, 13.9, '119/77', 69, 36.6, 3, 'BAG-2025-037', 'Normal donation'),
(38, 10, '2025-03-15 11:30:00', 450, 14.1, '120/78', 71, 36.5, 3, 'BAG-2025-038', 'Normal donation'),
(39, 10, '2025-03-15 12:00:00', 450, 14.5, '123/81', 73, 36.5, 3, 'BAG-2025-039', 'Normal donation'),
(40, 11, '2025-03-20 09:00:00', 450, 13.8, '118/76', 68, 36.7, 3, 'BAG-2025-040', 'Normal donation'),
(41, 11, '2025-03-20 10:00:00', 500, 15.2, '126/83', 75, 36.4, 3, 'BAG-2025-041', 'Regular donor'),
(42, 11, '2025-03-20 11:00:00', 450, 14.0, '117/75', 67, 36.7, 3, 'BAG-2025-042', 'Normal donation'),
(43, 11, '2025-03-20 12:00:00', 450, 14.4, '122/80', 72, 36.5, 3, 'BAG-2025-043', 'Normal donation'),
(44, 11, '2025-03-20 13:00:00', 450, 13.7, '118/76', 68, 36.6, 3, 'BAG-2025-044', 'Normal donation'),
(45, 11, '2025-03-20 14:00:00', 450, 14.2, '121/79', 71, 36.5, 3, 'BAG-2025-045', 'Normal donation'),
(46, 11, '2025-03-20 15:00:00', 450, 13.6, '119/78', 70, 36.7, 3, 'BAG-2025-046', 'Normal donation'),
(47, 11, '2025-03-20 16:00:00', 500, 15.1, '128/85', 76, 36.4, 3, 'BAG-2025-047', 'Regular donor'),
(48, 11, '2025-03-20 17:00:00', 450, 14.6, '124/82', 74, 36.5, 3, 'BAG-2025-048', 'Normal donation');

-- Staff SG004 (staff_id=4, 12 donations, ~5,400ml total)
INSERT IGNORE INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES
(49, 12, '2025-01-18 10:00:00', 450, 14.3, '121/79', 70, 36.5, 4, 'BAG-2025-049', 'Normal donation'),
(50, 12, '2025-01-18 10:30:00', 450, 13.9, '119/77', 69, 36.6, 4, 'BAG-2025-050', 'Normal donation'),
(51, 12, '2025-01-18 11:00:00', 500, 15.0, '125/82', 74, 36.4, 4, 'BAG-2025-051', 'Regular donor'),
(52, 13, '2025-01-22 09:00:00', 450, 14.1, '120/78', 71, 36.5, 4, 'BAG-2025-052', 'Normal donation'),
(53, 13, '2025-01-22 10:00:00', 450, 14.5, '123/81', 73, 36.5, 4, 'BAG-2025-053', 'Normal donation'),
(54, 13, '2025-01-22 11:00:00', 450, 13.8, '118/76', 68, 36.7, 4, 'BAG-2025-054', 'Normal donation'),
(55, 14, '2025-02-15 10:30:00', 500, 15.2, '126/83', 75, 36.4, 4, 'BAG-2025-055', 'Regular donor'),
(56, 14, '2025-02-15 11:30:00', 450, 14.0, '117/75', 67, 36.7, 4, 'BAG-2025-056', 'Normal donation'),
(57, 15, '2025-02-28 10:00:00', 450, 14.4, '122/80', 72, 36.5, 4, 'BAG-2025-057', 'Normal donation'),
(58, 15, '2025-02-28 11:00:00', 450, 13.7, '118/76', 68, 36.6, 4, 'BAG-2025-058', 'Normal donation'),
(59, 15, '2025-02-28 12:00:00', 450, 14.2, '121/79', 71, 36.5, 4, 'BAG-2025-059', 'Normal donation'),
(60, 15, '2025-02-28 13:00:00', 450, 13.6, '119/78', 70, 36.7, 4, 'BAG-2025-060', 'Normal donation');

-- Staff SG005 (staff_id=5, 10 donations, ~4,700ml total)
INSERT IGNORE INTO donation_record (donor_id, session_id, donation_date, blood_volume_ml, hemoglobin_level, blood_pressure, pulse_rate, temperature, staff_id, bag_code, notes) VALUES
(61, 16, '2025-03-12 10:00:00', 450, 14.3, '121/79', 70, 36.5, 5, 'BAG-2025-061', 'Normal donation'),
(62, 16, '2025-03-12 10:30:00', 500, 15.0, '125/82', 74, 36.4, 5, 'BAG-2025-062', 'Regular donor'),
(63, 16, '2025-03-12 11:00:00', 450, 13.9, '119/77', 69, 36.6, 5, 'BAG-2025-063', 'Normal donation'),
(64, 16, '2025-03-12 11:30:00', 450, 14.1, '120/78', 71, 36.5, 5, 'BAG-2025-064', 'Normal donation'),
(65, 17, '2025-03-18 09:00:00', 450, 14.5, '123/81', 73, 36.5, 5, 'BAG-2025-065', 'Normal donation'),
(66, 17, '2025-03-18 10:00:00', 450, 13.8, '118/76', 68, 36.7, 5, 'BAG-2025-066', 'Normal donation'),
(67, 17, '2025-03-18 11:00:00', 500, 15.2, '126/83', 75, 36.4, 5, 'BAG-2025-067', 'Regular donor'),
(68, 18, '2025-03-25 10:30:00', 450, 14.0, '117/75', 67, 36.7, 5, 'BAG-2025-068', 'Normal donation'),
(69, 18, '2025-03-25 11:30:00', 450, 14.4, '122/80', 72, 36.5, 5, 'BAG-2025-069', 'Normal donation'),
(70, 18, '2025-03-25 12:30:00', 450, 13.7, '118/76', 68, 36.6, 5, 'BAG-2025-070', 'Normal donation');


-- Insert Blood Inventory (linked to donations, expiry 42 days after collection)
INSERT IGNORE INTO blood_inventory (blood_type, quantity_ml, collection_date, expiry_date, storage_location, status, donation_id, test_results, processing_date) VALUES
('O+', 450, '2025-01-15', '2025-02-26', 'Cold Storage Unit A1', 'Available', 1, 'All tests negative', '2025-01-16'),
('A+', 450, '2025-01-15', '2025-02-26', 'Cold Storage Unit A2', 'Available', 2, 'All tests negative', '2025-01-16'),
('B+', 500, '2025-01-15', '2025-02-26', 'Cold Storage Unit B1', 'Available', 3, 'All tests negative', '2025-01-16'),
('O-', 450, '2025-01-15', '2025-02-26', 'Cold Storage Unit A3', 'Reserved', 4, 'All tests negative', '2025-01-16'),
('AB+', 450, '2025-01-15', '2025-02-26', 'Cold Storage Unit C1', 'Available', 5, 'All tests negative', '2025-01-16'),
('A-', 450, '2025-01-20', '2025-03-02', 'Cold Storage Unit A4', 'Available', 6, 'All tests negative', '2025-01-21'),
('O+', 500, '2025-01-20', '2025-03-02', 'Cold Storage Unit A5', 'Available', 7, 'All tests negative', '2025-01-21'),
('B-', 450, '2025-01-20', '2025-03-02', 'Cold Storage Unit B2', 'Available', 8, 'All tests negative', '2025-01-21'),
('A+', 450, '2025-01-20', '2025-03-02', 'Cold Storage Unit A6', 'Available', 9, 'All tests negative', '2025-01-21'),
('O+', 450, '2025-01-25', '2025-03-07', 'Cold Storage Unit A7', 'Available', 10, 'All tests negative', '2025-01-26'),
('B+', 500, '2025-01-25', '2025-03-07', 'Cold Storage Unit B3', 'Available', 11, 'All tests negative', '2025-01-26'),
('AB-', 450, '2025-01-25', '2025-03-07', 'Cold Storage Unit C2', 'Available', 12, 'All tests negative', '2025-01-26'),
('O-', 450, '2025-02-05', '2025-03-18', 'Cold Storage Unit A8', 'Available', 13, 'All tests negative', '2025-02-06'),
('A+', 450, '2025-02-05', '2025-03-18', 'Cold Storage Unit A9', 'Available', 14, 'All tests negative', '2025-02-06'),
('O+', 500, '2025-02-10', '2025-03-23', 'Cold Storage Unit A10', 'Available', 15, 'All tests negative', '2025-02-11'),
('A+', 450, '2025-02-10', '2025-03-23', 'Cold Storage Unit A11', 'Available', 16, 'All tests negative', '2025-02-11'),
('O+', 450, '2025-02-10', '2025-03-23', 'Cold Storage Unit A12', 'Available', 17, 'All tests negative', '2025-02-11'),
('O+', 450, '2025-02-10', '2025-03-23', 'Cold Storage Unit A13', 'Available', 18, 'All tests negative', '2025-02-11'),
('O+', 450, '2025-02-20', '2025-04-02', 'Cold Storage Unit A14', 'Available', 19, 'All tests negative', '2025-02-21'),
('O+', 450, '2025-02-20', '2025-04-02', 'Cold Storage Unit A15', 'Available', 20, 'All tests negative', '2025-02-21'),
('B+', 500, '2025-02-20', '2025-04-02', 'Cold Storage Unit B4', 'Available', 21, 'All tests negative', '2025-02-21'),
('A+', 450, '2025-02-20', '2025-04-02', 'Cold Storage Unit A16', 'Available', 22, 'All tests negative', '2025-02-21'),
('O+', 450, '2025-02-20', '2025-04-02', 'Cold Storage Unit A17', 'Available', 23, 'All tests negative', '2025-02-21'),
('A-', 450, '2025-02-25', '2025-04-07', 'Cold Storage Unit A18', 'Available', 24, 'All tests negative', '2025-02-26'),
('AB+', 500, '2025-02-25', '2025-04-07', 'Cold Storage Unit C3', 'Available', 25, 'All tests negative', '2025-02-26'),
('O-', 450, '2025-02-25', '2025-04-07', 'Cold Storage Unit A19', 'Available', 26, 'All tests negative', '2025-02-26'),
('A+', 450, '2025-03-05', '2025-04-16', 'Cold Storage Unit A20', 'Available', 27, 'All tests negative', '2025-03-06'),
('B-', 450, '2025-03-05', '2025-04-16', 'Cold Storage Unit B5', 'Available', 28, 'All tests negative', '2025-03-06'),
('B+', 500, '2025-03-10', '2025-04-21', 'Cold Storage Unit B6', 'Available', 29, 'All tests negative', '2025-03-11'),
('O-', 450, '2025-03-10', '2025-04-21', 'Cold Storage Unit A21', 'Available', 30, 'All tests negative', '2025-03-11'),
('A+', 450, '2025-03-10', '2025-04-21', 'Cold Storage Unit A22', 'Available', 31, 'All tests negative', '2025-03-11'),
('O+', 450, '2025-03-10', '2025-04-21', 'Cold Storage Unit A23', 'Available', 32, 'All tests negative', '2025-03-11'),
('O+', 450, '2025-03-10', '2025-04-21', 'Cold Storage Unit A24', 'Available', 33, 'All tests negative', '2025-03-11'),
('O+', 450, '2025-03-10', '2025-04-21', 'Cold Storage Unit A25', 'Available', 34, 'All tests negative', '2025-03-11'),
('A+', 450, '2025-03-15', '2025-04-26', 'Cold Storage Unit A26', 'Available', 35, 'All tests negative', '2025-03-16'),
('B+', 500, '2025-03-15', '2025-04-26', 'Cold Storage Unit B7', 'Available', 36, 'All tests negative', '2025-03-16'),
('O+', 450, '2025-03-15', '2025-04-26', 'Cold Storage Unit A27', 'Available', 37, 'All tests negative', '2025-03-16'),
('A+', 450, '2025-03-15', '2025-04-26', 'Cold Storage Unit A28', 'Available', 38, 'All tests negative', '2025-03-16'),
('O+', 450, '2025-03-15', '2025-04-26', 'Cold Storage Unit A29', 'Available', 39, 'All tests negative', '2025-03-16'),
('O-', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit A30', 'Available', 40, 'All tests negative', '2025-03-21'),
('A+', 500, '2025-03-20', '2025-05-01', 'Cold Storage Unit A31', 'Available', 41, 'All tests negative', '2025-03-21'),
('B+', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit B8', 'Available', 42, 'All tests negative', '2025-03-21'),
('A+', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit A32', 'Available', 43, 'All tests negative', '2025-03-21'),
('B-', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit B9', 'Available', 44, 'All tests negative', '2025-03-21'),
('O+', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit A33', 'Available', 45, 'All tests negative', '2025-03-21'),
('A+', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit A34', 'Available', 46, 'All tests negative', '2025-03-21'),
('B+', 500, '2025-03-20', '2025-05-01', 'Cold Storage Unit B10', 'Available', 47, 'All tests negative', '2025-03-21'),
('O+', 450, '2025-03-20', '2025-05-01', 'Cold Storage Unit A35', 'Available', 48, 'All tests negative', '2025-03-21'),
('O+', 450, '2025-01-18', '2025-02-29', 'Cold Storage Unit A36', 'Transfused', 49, 'All tests negative', '2025-01-19'),
('O+', 450, '2025-01-18', '2025-02-29', 'Cold Storage Unit A37', 'Available', 50, 'All tests negative', '2025-01-19'),
('B+', 500, '2025-01-18', '2025-02-29', 'Cold Storage Unit B11', 'Available', 51, 'All tests negative', '2025-01-19'),
('A+', 450, '2025-01-22', '2025-03-04', 'Cold Storage Unit A38', 'Available', 52, 'All tests negative', '2025-01-23'),
('O+', 450, '2025-01-22', '2025-03-04', 'Cold Storage Unit A39', 'Available', 53, 'All tests negative', '2025-01-23'),
('AB-', 450, '2025-01-22', '2025-03-04', 'Cold Storage Unit C4', 'Available', 54, 'All tests negative', '2025-01-23'),
('B+', 500, '2025-02-15', '2025-03-28', 'Cold Storage Unit B12', 'Available', 55, 'All tests negative', '2025-02-16'),
('O+', 450, '2025-02-15', '2025-03-28', 'Cold Storage Unit A40', 'Available', 56, 'All tests negative', '2025-02-16'),
('O+', 450, '2025-02-28', '2025-04-10', 'Cold Storage Unit A41', 'Available', 57, 'All tests negative', '2025-02-29'),
('AB-', 450, '2025-02-28', '2025-04-10', 'Cold Storage Unit C5', 'Available', 58, 'All tests negative', '2025-02-29'),
('O+', 450, '2025-02-28', '2025-04-10', 'Cold Storage Unit A42', 'Available', 59, 'All tests negative', '2025-02-29'),
('B+', 450, '2025-02-28', '2025-04-10', 'Cold Storage Unit B13', 'Available', 60, 'All tests negative', '2025-02-29'),
('O+', 450, '2025-03-12', '2025-04-23', 'Cold Storage Unit A43', 'Available', 61, 'All tests negative', '2025-03-13'),
('B+', 500, '2025-03-12', '2025-04-23', 'Cold Storage Unit B14', 'Available', 62, 'All tests negative', '2025-03-13'),
('A+', 450, '2025-03-12', '2025-04-23', 'Cold Storage Unit A44', 'Available', 63, 'All tests negative', '2025-03-13'),
('A+', 450, '2025-03-12', '2025-04-23', 'Cold Storage Unit A45', 'Available', 64, 'All tests negative', '2025-03-13'),
('O+', 450, '2025-03-18', '2025-04-29', 'Cold Storage Unit A46', 'Available', 65, 'All tests negative', '2025-03-19'),
('O+', 450, '2025-03-18', '2025-04-29', 'Cold Storage Unit A47', 'Available', 66, 'All tests negative', '2025-03-19'),
('AB+', 500, '2025-03-18', '2025-04-29', 'Cold Storage Unit C6', 'Available', 67, 'All tests negative', '2025-03-19'),
('O-', 450, '2025-03-25', '2025-05-06', 'Cold Storage Unit A48', 'Available', 68, 'All tests negative', '2025-03-26'),
('A+', 450, '2025-03-25', '2025-05-06', 'Cold Storage Unit A49', 'Available', 69, 'All tests negative', '2025-03-26'),
('B+', 450, '2025-03-25', '2025-05-06', 'Cold Storage Unit B15', 'Available', 70, 'All tests negative', '2025-03-26');


-- Insert Testing Records (for all donations)
INSERT IGNORE INTO testing_record (donation_id, test_date, test_type, test_result, staff_id, test_notes, retest_required, retest_date) VALUES
(1, '2025-01-16 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(1, '2025-01-16 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(1, '2025-01-16 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(1, '2025-01-16 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(2, '2025-01-16 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(2, '2025-01-16 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(2, '2025-01-16 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(2, '2025-01-16 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(3, '2025-01-16 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(3, '2025-01-16 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(3, '2025-01-16 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(3, '2025-01-16 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(4, '2025-01-16 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(4, '2025-01-16 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(4, '2025-01-16 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(4, '2025-01-16 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(5, '2025-01-16 17:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(5, '2025-01-16 17:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(5, '2025-01-16 18:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(5, '2025-01-16 18:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(6, '2025-01-21 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(6, '2025-01-21 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(6, '2025-01-21 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(6, '2025-01-21 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(7, '2025-01-21 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(7, '2025-01-21 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(7, '2025-01-21 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(7, '2025-01-21 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(8, '2025-01-21 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(8, '2025-01-21 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(8, '2025-01-21 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(8, '2025-01-21 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(9, '2025-01-21 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(9, '2025-01-21 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(9, '2025-01-21 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(9, '2025-01-21 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(10, '2025-01-26 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(10, '2025-01-26 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(10, '2025-01-26 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(10, '2025-01-26 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(11, '2025-01-26 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(11, '2025-01-26 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(11, '2025-01-26 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(11, '2025-01-26 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(12, '2025-01-26 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(12, '2025-01-26 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(12, '2025-01-26 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(12, '2025-01-26 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(13, '2025-02-06 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(13, '2025-02-06 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(13, '2025-02-06 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(13, '2025-02-06 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(14, '2025-02-06 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(14, '2025-02-06 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(14, '2025-02-06 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(14, '2025-02-06 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(15, '2025-02-11 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(15, '2025-02-11 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(15, '2025-02-11 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(15, '2025-02-11 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(16, '2025-02-11 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(16, '2025-02-11 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(16, '2025-02-11 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(16, '2025-02-11 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(17, '2025-02-11 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(17, '2025-02-11 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(17, '2025-02-11 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(17, '2025-02-11 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(18, '2025-02-11 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(18, '2025-02-11 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(18, '2025-02-11 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(18, '2025-02-11 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(19, '2025-02-21 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(19, '2025-02-21 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(19, '2025-02-21 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(19, '2025-02-21 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(20, '2025-02-21 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(20, '2025-02-21 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(20, '2025-02-21 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(20, '2025-02-21 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(21, '2025-02-21 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(21, '2025-02-21 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(21, '2025-02-21 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(21, '2025-02-21 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(22, '2025-02-21 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(22, '2025-02-21 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(22, '2025-02-21 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(22, '2025-02-21 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(23, '2025-02-21 17:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(23, '2025-02-21 17:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(23, '2025-02-21 18:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(23, '2025-02-21 18:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(24, '2025-02-26 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(24, '2025-02-26 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(24, '2025-02-26 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(24, '2025-02-26 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(25, '2025-02-26 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(25, '2025-02-26 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(25, '2025-02-26 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(25, '2025-02-26 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(26, '2025-02-26 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(26, '2025-02-26 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(26, '2025-02-26 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(26, '2025-02-26 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(27, '2025-03-06 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(27, '2025-03-06 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(27, '2025-03-06 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(27, '2025-03-06 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(28, '2025-03-06 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(28, '2025-03-06 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(28, '2025-03-06 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(28, '2025-03-06 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(29, '2025-03-11 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(29, '2025-03-11 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(29, '2025-03-11 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(29, '2025-03-11 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(30, '2025-03-11 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(30, '2025-03-11 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(30, '2025-03-11 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(30, '2025-03-11 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(31, '2025-03-11 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(31, '2025-03-11 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(31, '2025-03-11 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(31, '2025-03-11 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(32, '2025-03-11 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(32, '2025-03-11 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(32, '2025-03-11 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(32, '2025-03-11 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(33, '2025-03-11 17:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(33, '2025-03-11 17:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(33, '2025-03-11 18:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(33, '2025-03-11 18:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(34, '2025-03-11 19:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(34, '2025-03-11 19:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(34, '2025-03-11 20:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(34, '2025-03-11 20:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(35, '2025-03-16 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(35, '2025-03-16 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(35, '2025-03-16 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(35, '2025-03-16 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(36, '2025-03-16 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(36, '2025-03-16 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(36, '2025-03-16 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(36, '2025-03-16 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(37, '2025-03-16 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(37, '2025-03-16 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(37, '2025-03-16 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(37, '2025-03-16 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(38, '2025-03-16 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(38, '2025-03-16 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(38, '2025-03-16 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(38, '2025-03-16 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(39, '2025-03-16 17:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(39, '2025-03-16 17:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(39, '2025-03-16 18:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(39, '2025-03-16 18:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(40, '2025-03-21 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(40, '2025-03-21 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(40, '2025-03-21 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(40, '2025-03-21 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(41, '2025-03-21 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(41, '2025-03-21 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(41, '2025-03-21 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(41, '2025-03-21 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(42, '2025-03-21 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(42, '2025-03-21 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(42, '2025-03-21 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(42, '2025-03-21 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(43, '2025-03-21 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(43, '2025-03-21 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(43, '2025-03-21 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(43, '2025-03-21 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(44, '2025-03-21 17:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(44, '2025-03-21 17:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(44, '2025-03-21 18:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(44, '2025-03-21 18:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(45, '2025-03-21 19:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(45, '2025-03-21 19:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(45, '2025-03-21 20:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(45, '2025-03-21 20:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(46, '2025-03-21 21:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(46, '2025-03-21 21:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(46, '2025-03-21 22:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(46, '2025-03-21 22:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(47, '2025-03-21 23:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(47, '2025-03-21 23:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(47, '2025-03-22 00:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(47, '2025-03-22 00:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(48, '2025-03-22 01:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(48, '2025-03-22 01:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(48, '2025-03-22 02:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(48, '2025-03-22 02:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(49, '2025-01-19 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(49, '2025-01-19 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(49, '2025-01-19 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(49, '2025-01-19 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(50, '2025-01-19 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(50, '2025-01-19 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(50, '2025-01-19 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(50, '2025-01-19 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(51, '2025-01-19 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(51, '2025-01-19 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(51, '2025-01-19 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(51, '2025-01-19 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(52, '2025-01-23 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(52, '2025-01-23 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(52, '2025-01-23 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(52, '2025-01-23 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(53, '2025-01-23 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(53, '2025-01-23 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(53, '2025-01-23 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(53, '2025-01-23 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(54, '2025-01-23 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(54, '2025-01-23 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(54, '2025-01-23 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(54, '2025-01-23 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(55, '2025-02-16 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(55, '2025-02-16 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(55, '2025-02-16 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(55, '2025-02-16 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(56, '2025-02-16 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(56, '2025-02-16 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(56, '2025-02-16 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(56, '2025-02-16 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(57, '2025-02-29 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(57, '2025-02-29 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(57, '2025-02-29 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(57, '2025-02-29 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(58, '2025-02-29 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(58, '2025-02-29 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(58, '2025-02-29 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(58, '2025-02-29 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(59, '2025-02-29 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(59, '2025-02-29 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(59, '2025-02-29 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(59, '2025-02-29 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(60, '2025-02-29 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(60, '2025-02-29 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(60, '2025-02-29 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(60, '2025-02-29 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(61, '2025-03-13 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(61, '2025-03-13 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(61, '2025-03-13 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(61, '2025-03-13 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(62, '2025-03-13 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(62, '2025-03-13 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(62, '2025-03-13 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(62, '2025-03-13 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(63, '2025-03-13 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(63, '2025-03-13 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(63, '2025-03-13 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(63, '2025-03-13 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(64, '2025-03-13 15:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(64, '2025-03-13 15:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(64, '2025-03-13 16:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(64, '2025-03-13 16:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(65, '2025-03-19 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(65, '2025-03-19 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(65, '2025-03-19 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(65, '2025-03-19 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(66, '2025-03-19 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(66, '2025-03-19 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(66, '2025-03-19 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(66, '2025-03-19 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(67, '2025-03-19 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(67, '2025-03-19 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(67, '2025-03-19 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(67, '2025-03-19 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(68, '2025-03-26 09:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(68, '2025-03-26 09:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(68, '2025-03-26 10:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(68, '2025-03-26 10:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(69, '2025-03-26 11:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(69, '2025-03-26 11:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(69, '2025-03-26 12:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(69, '2025-03-26 12:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(70, '2025-03-26 13:00:00', 'HIV Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(70, '2025-03-26 13:30:00', 'Hepatitis B Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(70, '2025-03-26 14:00:00', 'Hepatitis C Test', 'Negative', 2, 'Standard screening', 'No', NULL),
(70, '2025-03-26 14:30:00', 'Syphilis Test', 'Negative', 2, 'Standard screening', 'No', NULL);


-- Insert Blood Requests (from recipients)
INSERT IGNORE INTO blood_request (recipient_id, request_date, blood_type, quantity_ml, urgency_level, hospital_name, doctor_name, diagnosis, status, approved_by, approved_date, fulfillment_date, notes) VALUES
(1, '2025-01-20 10:00:00', 'O+', 450, 'High', 'Mulago National Referral Hospital', 'Dr. Sarah Nakato', 'Emergency surgery - blood loss', 'Fulfilled', 3, '2025-01-20 10:30:00', '2025-01-20 14:00:00', 'Urgent request fulfilled'),
(2, '2025-01-25 09:00:00', 'A+', 450, 'Medium', 'Nakasero Hospital', 'Dr. James Mukasa', 'Anemia treatment', 'Fulfilled', 3, '2025-01-25 09:30:00', '2025-01-25 16:00:00', 'Standard request'),
(3, '2025-02-01 11:00:00', 'B+', 500, 'Critical', 'Entebbe Hospital', 'Dr. David Kato', 'Accident victim - massive blood loss', 'Fulfilled', 3, '2025-02-01 11:15:00', '2025-02-01 12:00:00', 'Critical emergency'),
(4, '2025-02-05 08:00:00', 'O-', 450, 'High', 'Jinja Regional Hospital', 'Dr. Mary Nalubega', 'Childbirth complications', 'Fulfilled', 3, '2025-02-05 08:30:00', '2025-02-05 15:00:00', 'Maternal emergency'),
(5, '2025-02-10 10:00:00', 'AB+', 450, 'Medium', 'Mukono Health Center', 'Dr. Peter Ssemwogerere', 'Cancer treatment', 'Fulfilled', 3, '2025-02-10 10:30:00', '2025-02-10 17:00:00', 'Oncology patient'),
(6, '2025-02-15 09:00:00', 'A-', 450, 'Low', 'Masaka Hospital', 'Dr. Grace Namukasa', 'Elective surgery', 'Approved', 3, '2025-02-15 09:30:00', NULL, 'Scheduled surgery'),
(7, '2025-02-20 14:00:00', 'O+', 500, 'High', 'Mbale Regional Hospital', 'Dr. James Kiggundu', 'Trauma case', 'Fulfilled', 3, '2025-02-20 14:30:00', '2025-02-20 18:00:00', 'Trauma patient'),
(8, '2025-02-25 10:00:00', 'B-', 450, 'Medium', 'Gulu Hospital', 'Dr. Ruth Nabukeera', 'Surgery preparation', 'Approved', 3, '2025-02-25 10:30:00', NULL, 'Pre-surgery'),
(9, '2025-03-05 08:00:00', 'A+', 450, 'High', 'Mbarara Hospital', 'Dr. Michael Lubega', 'Accident victim', 'Fulfilled', 3, '2025-03-05 08:30:00', '2025-03-05 13:00:00', 'Emergency case'),
(10, '2025-03-10 11:00:00', 'O+', 450, 'Medium', 'Fort Portal Hospital', 'Dr. Esther Nakiyemba', 'Childbirth', 'Pending', NULL, NULL, NULL, 'Awaiting approval'),
(11, '2025-03-15 09:00:00', 'B+', 500, 'High', 'Lira Hospital', 'Dr. John Mugisha', 'Surgery', 'Approved', 3, '2025-03-15 09:30:00', NULL, 'Surgical procedure'),
(12, '2025-03-20 10:00:00', 'AB-', 450, 'Low', 'Arua Hospital', 'Dr. Patience Nalubowa', 'Anemia', 'Pending', NULL, NULL, NULL, 'Routine treatment');


-- Insert Blood Transfusions (linked to requests and inventory)
INSERT IGNORE INTO blood_transfusion (request_id, inventory_id, transfusion_date, quantity_ml, staff_id, hospital_name, patient_room, status, complications, notes) VALUES
(1, 49, '2025-01-20 14:00:00', 450, 1, 'Mulago National Referral Hospital', 'Ward 3, Room 12', 'Completed', NULL, 'Successful transfusion, patient stable'),
(2, 2, '2025-01-25 16:00:00', 450, 1, 'Nakasero Hospital', 'Ward 1, Room 5', 'Completed', NULL, 'Transfusion completed successfully'),
(3, 3, '2025-02-01 12:00:00', 500, 1, 'Entebbe Hospital', 'ICU Room 2', 'Completed', NULL, 'Critical patient stabilized'),
(4, 4, '2025-02-05 15:00:00', 450, 1, 'Jinja Regional Hospital', 'Maternity Ward, Room 8', 'Completed', NULL, 'Maternal emergency resolved'),
(5, 5, '2025-02-10 17:00:00', 450, 2, 'Mukono Health Center', 'Oncology Ward, Room 3', 'Completed', NULL, 'Oncology patient transfusion'),
(7, 7, '2025-02-20 18:00:00', 500, 2, 'Mbale Regional Hospital', 'Emergency Ward, Room 1', 'Completed', NULL, 'Trauma patient treated'),
(9, 35, '2025-03-05 13:00:00', 450, 3, 'Mbarara Hospital', 'Ward 2, Room 7', 'Completed', NULL, 'Accident victim stabilized');


-- Insert Notifications
INSERT IGNORE INTO notification (recipient_type, recipient_id, notification_type, title, message, sent_date, sent_time, status, delivery_method) VALUES
('donor', 1, 'Email', 'Thank You for Your Donation', 'Dear John Mukasa, Thank you for your blood donation on 2025-01-15. Your contribution saves lives!', '2025-01-15', '18:00:00', 'Sent', 'Email'),
('donor', 2, 'SMS', 'Donation Reminder', 'Hi Sarah Nakato, You are eligible to donate again. Please visit us soon!', '2025-03-20', '10:00:00', 'Sent', 'SMS'),
('donor', 3, 'Email', 'Donation Confirmation', 'Dear David Kato, Your donation on 2025-01-15 has been processed successfully.', '2025-01-16', '09:00:00', 'Sent', 'Email'),
('recipient', 1, 'SMS', 'Blood Request Approved', 'Your blood request has been approved. Blood will be delivered to Mulago Hospital.', '2025-01-20', '10:35:00', 'Sent', 'SMS'),
('recipient', 2, 'Email', 'Blood Request Status', 'Your blood request has been fulfilled. Blood delivered to Nakasero Hospital.', '2025-01-25', '16:30:00', 'Sent', 'Email'),
('donor', 10, 'SMS', 'Donation Reminder', 'Hi Esther Nakiyemba, You can donate again. Your last donation was on 2025-01-25.', '2025-03-25', '09:00:00', 'Queued', 'SMS'),
('staff', 1, 'Email', 'Session Reminder', 'Reminder: You have a donation session scheduled for tomorrow at Main Blood Bank.', '2025-01-14', '17:00:00', 'Sent', 'Email'),
('staff', 2, 'SMS', 'Test Results Ready', 'Test results for donation batch are ready for review.', '2025-01-16', '15:00:00', 'Sent', 'SMS'),
('donor', 15, 'Email', 'Thank You', 'Thank you for your regular donations. You have helped save many lives!', '2025-02-11', '18:00:00', 'Sent', 'Email'),
('recipient', 3, 'Call', 'Urgent Blood Request', 'Your critical blood request has been approved. Blood is being prepared for delivery.', '2025-02-01', '11:20:00', 'Sent', 'Phone Call');


-- Post-insert updates to fix relationships and ensure data consistency
-- Update donor last_donation_date based on their actual donations (makes them active - within last year)
-- Using simpler syntax compatible with older MySQL versions
UPDATE donor d, (SELECT donor_id, MAX(donation_date) as max_date FROM donation_record GROUP BY donor_id) dr 
SET d.last_donation_date = DATE(dr.max_date) 
WHERE d.donor_id = dr.donor_id;

-- Fallback: Ensure all donors with donations have a recent last_donation_date (within last year)
-- This ensures active donors show up in statistics
-- Using a simpler approach compatible with older MySQL (no subquery in WHERE)
UPDATE donor d, (SELECT DISTINCT donor_id FROM donation_record) dr
SET d.last_donation_date = DATE_SUB(NOW(), INTERVAL 2 MONTH) 
WHERE d.donor_id = dr.donor_id 
AND (d.last_donation_date IS NULL OR d.last_donation_date < DATE_SUB(NOW(), INTERVAL 1 YEAR));

-- Update testing records staff_id to use staff_id=2 (SG002 - Laboratory Technician) for all tests
UPDATE testing_record 
SET staff_id = 2
WHERE staff_id IS NULL OR staff_id != 2;

-- Update blood_request approved_by to use staff_id=3 (SG003 - Inventory Manager)
UPDATE blood_request
SET approved_by = 3
WHERE approved_by IS NULL AND status IN ('Approved', 'Fulfilled');

-- Update blood_transfusion staff_id to use staff_id=1 (SG001)
UPDATE blood_transfusion
SET staff_id = 1
WHERE staff_id IS NULL;

-- Fix any donation_session records with invalid staff_id values
-- Ensure all sessions have valid staff_id that matches existing staff
UPDATE donation_session ds
LEFT JOIN staff s ON ds.staff_id = s.staff_id
SET ds.staff_id = 1
WHERE ds.staff_id IS NOT NULL AND s.staff_id IS NULL;

-- CRITICAL: Fix donation_record staff_id values to ensure staff performance tracking works
-- This updates donation records to use the ACTUAL staff_id values from the staff table
-- Uses employee_id to find the correct staff_id (works even if staff_id values are 6-10 instead of 1-5)
-- This ensures the JOIN in staff performance queries works correctly

-- Staff SG001: donations 1-18 (18 donations, ~8,300ml total)
-- Find staff_id for SG001 and update donation records
UPDATE donation_record dr
INNER JOIN staff s ON s.employee_id = 'SG001'
SET dr.staff_id = s.staff_id
WHERE dr.donation_id BETWEEN 1 AND 18;

-- Staff SG002: donations 19-34 (16 donations, ~7,200ml total)
UPDATE donation_record dr
INNER JOIN staff s ON s.employee_id = 'SG002'
SET dr.staff_id = s.staff_id
WHERE dr.donation_id BETWEEN 19 AND 34;

-- Staff SG003: donations 35-48 (14 donations, ~6,300ml total)
UPDATE donation_record dr
INNER JOIN staff s ON s.employee_id = 'SG003'
SET dr.staff_id = s.staff_id
WHERE dr.donation_id BETWEEN 35 AND 48;

-- Staff SG004: donations 49-60 (12 donations, ~5,400ml total)
UPDATE donation_record dr
INNER JOIN staff s ON s.employee_id = 'SG004'
SET dr.staff_id = s.staff_id
WHERE dr.donation_id BETWEEN 49 AND 60;

-- Staff SG005: donations 61-70 (10 donations, ~4,700ml total)
UPDATE donation_record dr
INNER JOIN staff s ON s.employee_id = 'SG005'
SET dr.staff_id = s.staff_id
WHERE dr.donation_id BETWEEN 61 AND 70;

-- Final safety check: any remaining donation_record with invalid staff_id gets assigned to SG001
-- This handles any edge cases where donation records might have invalid staff_id values
-- Note: If you get an error on this, you can skip it - the main fixes above should handle most cases
UPDATE donation_record dr
LEFT JOIN staff s ON dr.staff_id = s.staff_id
INNER JOIN staff s2 ON s2.employee_id = 'SG001'
SET dr.staff_id = s2.staff_id
WHERE dr.staff_id IS NOT NULL AND s.staff_id IS NULL;


-- Update all dates from 2024 to 2025 in existing database records
-- Run this section if you have existing 2024 data and want to update it to 2025
-- Update staff hire dates
UPDATE staff SET hire_date = DATE_ADD(hire_date, INTERVAL 1 YEAR) WHERE YEAR(hire_date) = 2024;

-- Update donor last_donation_date and registration_date
UPDATE donor SET last_donation_date = DATE_ADD(last_donation_date, INTERVAL 1 YEAR) WHERE YEAR(last_donation_date) = 2024;
UPDATE donor SET registration_date = DATE_ADD(registration_date, INTERVAL 1 YEAR) WHERE YEAR(registration_date) = 2024;

-- Update donation_record dates
UPDATE donation_record SET donation_date = DATE_ADD(donation_date, INTERVAL 1 YEAR) WHERE YEAR(donation_date) = 2024;

-- Update donation_session dates
UPDATE donation_session SET session_date = DATE_ADD(session_date, INTERVAL 1 YEAR) WHERE YEAR(session_date) = 2024;

-- Update donation_event dates
UPDATE donation_event SET event_date = DATE_ADD(event_date, INTERVAL 1 YEAR) WHERE YEAR(event_date) = 2024;

-- Update blood_inventory dates
UPDATE blood_inventory SET collection_date = DATE_ADD(collection_date, INTERVAL 1 YEAR) WHERE YEAR(collection_date) = 2024;
UPDATE blood_inventory SET expiry_date = DATE_ADD(expiry_date, INTERVAL 1 YEAR) WHERE YEAR(expiry_date) = 2024;
UPDATE blood_inventory SET processing_date = DATE_ADD(processing_date, INTERVAL 1 YEAR) WHERE YEAR(processing_date) = 2024;

-- Update testing_record dates
UPDATE testing_record SET test_date = DATE_ADD(test_date, INTERVAL 1 YEAR) WHERE YEAR(test_date) = 2024;
UPDATE testing_record SET retest_date = DATE_ADD(retest_date, INTERVAL 1 YEAR) WHERE YEAR(retest_date) = 2024;

-- Update blood_request dates
UPDATE blood_request SET request_date = DATE_ADD(request_date, INTERVAL 1 YEAR) WHERE YEAR(request_date) = 2024;
UPDATE blood_request SET approved_date = DATE_ADD(approved_date, INTERVAL 1 YEAR) WHERE YEAR(approved_date) = 2024;
UPDATE blood_request SET fulfillment_date = DATE_ADD(fulfillment_date, INTERVAL 1 YEAR) WHERE YEAR(fulfillment_date) = 2024;

-- Update blood_transfusion dates
UPDATE blood_transfusion SET transfusion_date = DATE_ADD(transfusion_date, INTERVAL 1 YEAR) WHERE YEAR(transfusion_date) = 2024;

-- Update recipient registration_date
UPDATE recipient SET registration_date = DATE_ADD(registration_date, INTERVAL 1 YEAR) WHERE YEAR(registration_date) = 2024;

-- Update notification dates
UPDATE notification SET sent_date = DATE_ADD(sent_date, INTERVAL 1 YEAR) WHERE YEAR(sent_date) = 2024;
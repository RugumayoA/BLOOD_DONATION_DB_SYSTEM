

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

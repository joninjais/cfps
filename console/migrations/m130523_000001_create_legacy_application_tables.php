<?php

use yii\db\Migration;

/**
 * Creates application tables that predate the tracked Yii migrations.
 * Existing tables are preserved; rollback removes only tables created here.
 */
class m130523_000001_create_legacy_application_tables extends Migration
{
    private const STATE_TABLE = '{{%m130523_legacy_table_state}}';

    private const TABLES = [
        'card' => <<<'SQL'
CREATE TABLE `card` (
  `card_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `price` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`card_id`) USING BTREE,
  KEY `idx_card_card_id` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'chospital2' => <<<'SQL'
CREATE TABLE `chospital2` (
  `hospcode` char(5) NOT NULL DEFAULT '',
  `hosname` varchar(255) DEFAULT NULL,
  `mu` varchar(2) DEFAULT NULL,
  `subdistcode` char(2) DEFAULT NULL,
  `ampcode` char(2) DEFAULT NULL,
  `provcode` char(2) DEFAULT NULL,
  `cupcode` char(5) DEFAULT NULL,
  PRIMARY KEY (`hospcode`) USING BTREE,
  KEY `idx_chospital2_hospcode` (`hospcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'diesease' => <<<'SQL'
CREATE TABLE `diesease` (
  `diesease_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `typehealth_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`diesease_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'fwfdatabase' => <<<'SQL'
CREATE TABLE `fwfdatabase` (
  `fwfid` int(11) NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(50) NOT NULL,
  `PeriodStartDate` date NOT NULL,
  `PeriodEndDate` date NOT NULL,
  `CID` varchar(255) NOT NULL,
  `FundHolderID` varchar(5) NOT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT '',
  `timeregis` varchar(255) NOT NULL,
  `regisdate` varchar(255) NOT NULL,
  `exsfile` varchar(255) DEFAULT '',
  `card_price` decimal(10,2) DEFAULT NULL,
  `pp` decimal(10,2) DEFAULT NULL,
  `man_ssj` decimal(10,2) DEFAULT NULL,
  `man_sp` decimal(10,2) DEFAULT NULL,
  `hc_sp` decimal(10,2) DEFAULT NULL,
  `claim` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`fwfid`) USING BTREE,
  KEY `fwfid` (`fwfid`) USING BTREE,
  KEY `PeriodID` (`PeriodID`) USING BTREE,
  KEY `PeriodStartDate` (`PeriodStartDate`) USING BTREE,
  KEY `PeriodEndDate` (`PeriodEndDate`) USING BTREE,
  KEY `CID` (`CID`) USING BTREE,
  KEY `FundHolderID` (`FundHolderID`) USING BTREE,
  KEY `regisdate` (`regisdate`) USING BTREE,
  KEY `idx_card_price_on_fwfdatabase` (`card_price`),
  KEY `idx_fwfdatabase_card_price` (`card_price`),
  KEY `idx_fwfdatabase_FundHolderID` (`FundHolderID`),
  KEY `idx_fwf_cid_period` (`CID`,`PeriodStartDate`,`PeriodEndDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'fwfhealth' => <<<'SQL'
CREATE TABLE `fwfhealth` (
  `fwfhealth_id` int(11) NOT NULL AUTO_INCREMENT,
  `datev` varchar(50) DEFAULT NULL,
  `hcode` varchar(5) DEFAULT NULL,
  `cid` varchar(13) DEFAULT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `birthday` varchar(255) DEFAULT NULL,
  `nation` varchar(255) DEFAULT NULL,
  `type_mg` varchar(255) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `bossname` varchar(255) DEFAULT NULL,
  `tel` varchar(255) DEFAULT NULL,
  `hn` varchar(255) DEFAULT NULL,
  `vn` varchar(255) DEFAULT NULL,
  `card_id` varchar(255) DEFAULT NULL COMMENT 'ราคาบัตร',
  `typehealth_id` varchar(255) DEFAULT NULL COMMENT 'ประเภทผลตรวจสุขภาพ',
  `diesease_id` varchar(255) DEFAULT NULL COMMENT 'โรคที่เป็น',
  `check_card` varchar(255) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`fwfhealth_id`) USING BTREE,
  KEY `fwfhealth_id` (`fwfhealth_id`) USING BTREE,
  KEY `datev` (`datev`) USING BTREE,
  KEY `hcode` (`hcode`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `hn` (`hn`) USING BTREE,
  KEY `vn` (`vn`) USING BTREE,
  KEY `card_id` (`card_id`) USING BTREE,
  KEY `check_card` (`check_card`) USING BTREE,
  KEY `exsfile` (`exsfile`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'health_office_stg' => <<<'SQL'
CREATE TABLE `health_office_stg` (
  `row_no` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ลำดับแถวที่นำเข้า (ไม่มีใน Excel)',
  `name` varchar(255) DEFAULT NULL COMMENT 'คอลัมน์ที่ 1: ชื่อ',
  `code9` varchar(20) DEFAULT NULL COMMENT 'คอลัมน์ที่ 2: รหัส 9 หลักใหม่',
  `code9_old` varchar(20) DEFAULT NULL COMMENT 'คอลัมน์ที่ 3: รหัส 9 หลัก',
  `hcode5` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 4: รหัส 5 หลัก',
  `license_no` varchar(50) DEFAULT NULL COMMENT 'คอลัมน์ที่ 5: เลขอนุญาตให้ประกอบสถานบริการสุขภาพ 11 หลัก',
  `org_type` varchar(50) DEFAULT NULL COMMENT 'คอลัมน์ที่ 6: ประเภทองค์กร',
  `facility_type` varchar(100) DEFAULT NULL COMMENT 'คอลัมน์ที่ 7: ประเภทหน่วยบริการสุขภาพ',
  `affiliation` varchar(100) DEFAULT NULL COMMENT 'คอลัมน์ที่ 8: สังกัด',
  `department` varchar(100) DEFAULT NULL COMMENT 'คอลัมน์ที่ 9: แผนก/กรม',
  `hospital_level` varchar(100) DEFAULT NULL COMMENT 'คอลัมน์ที่ 10: ระดับโรงพยาบาล',
  `bed_count` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 11: เตียงที่ใช้จริง',
  `status` varchar(30) DEFAULT NULL COMMENT 'คอลัมน์ที่ 12: สถานะการใช้งาน',
  `region` varchar(30) DEFAULT NULL COMMENT 'คอลัมน์ที่ 13: เขตบริการ',
  `address_line` varchar(255) DEFAULT NULL COMMENT 'คอลัมน์ที่ 14: ที่อยู่',
  `province_code` varchar(5) DEFAULT NULL COMMENT 'คอลัมน์ที่ 15: รหัสจังหวัด',
  `province_name` varchar(50) DEFAULT NULL COMMENT 'คอลัมน์ที่ 16: จังหวัด',
  `district_code` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 17: รหัสอำเภอ',
  `district_name` varchar(50) DEFAULT NULL COMMENT 'คอลัมน์ที่ 18: อำเภอ/เขต',
  `subdistrict_code` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 19: รหัสตำบล',
  `subdistrict_name` varchar(50) DEFAULT NULL COMMENT 'คอลัมน์ที่ 20: ตำบล/แขวง',
  `moo` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 21: หมู่',
  `postal_code` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 22: รหัสไปรษณีย์',
  `parent_name` varchar(100) DEFAULT NULL COMMENT 'คอลัมน์ที่ 23: แม่ข่าย',
  `established_date` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 24: วันที่ก่อตั้ง',
  `closed_date` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 25: วันที่ปิดบริการ',
  `source_updated_date` varchar(10) DEFAULT NULL COMMENT 'คอลัมน์ที่ 26: อัพเดตล่าสุด(เริ่ม 05/09/2566)',
  PRIMARY KEY (`row_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='ทะเบียนหน่วยบริการสุขภาพ (จาก health_office.xlsx)'
SQL,
        'insurance_policies' => <<<'SQL'
CREATE TABLE `insurance_policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_code` varchar(20) DEFAULT NULL COMMENT 'รหัสหน่วยงาน',
  `agency_name` varchar(255) DEFAULT NULL COMMENT 'หน่วยงาน',
  `province` varchar(100) DEFAULT NULL COMMENT 'จังหวัด',
  `f_code` varchar(50) DEFAULT NULL COMMENT 'FCode',
  `passport_no` varchar(50) DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง',
  `citizen_id` varchar(20) DEFAULT NULL COMMENT 'เลขบัตรประจำตัว',
  `alien_ref_id` varchar(20) DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว',
  `title_name` varchar(50) DEFAULT NULL COMMENT 'คำนำหน้า',
  `full_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล',
  `nationality` varchar(100) DEFAULT NULL COMMENT 'สัญชาติ',
  `ethnicity` varchar(100) DEFAULT NULL COMMENT 'เชื้อชาติ',
  `hn_code` varchar(50) DEFAULT NULL COMMENT 'HN',
  `purchase_date` date DEFAULT NULL COMMENT 'วันที่ซื้อกรมธรรม์',
  `effective_date` date DEFAULT NULL COMMENT 'วันที่เกิดสิทธิ',
  `expiry_date` date DEFAULT NULL COMMENT 'วันที่หมดอายุ',
  `policy_status` varchar(100) DEFAULT NULL COMMENT 'สถานะกรมธรรม์',
  `card_price` decimal(10,2) DEFAULT NULL COMMENT 'ราคาบัตร',
  `coverage_period` text DEFAULT NULL COMMENT 'ระยะเวลาคุ้มครอง',
  `father_passport_no` varchar(50) DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง บิดา',
  `father_citizen_id` varchar(20) DEFAULT NULL COMMENT 'เลขบัตรประจำตัว บิดา',
  `father_alien_ref_id` varchar(20) DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว บิดา',
  `father_full_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล บิดา',
  `mother_passport_no` varchar(50) DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง มารดา',
  `mother_citizen_id` varchar(20) DEFAULT NULL COMMENT 'เลขบัตรประจำตัว มารดา',
  `mother_alien_ref_id` varchar(20) DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว มารดา',
  `mother_full_name` varchar(255) DEFAULT NULL COMMENT 'ชื่อ-นามสกุล มารดา',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_card_price_on_insurance_policies` (`card_price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'ipdata' => <<<'SQL'
CREATE TABLE `ipdata` (
  `ipid` int(11) NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(5) NOT NULL,
  `admitdate` date NOT NULL,
  `dischartdate` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `fund` varchar(255) DEFAULT NULL,
  `cid` varchar(255) DEFAULT NULL,
  `hn` varchar(50) NOT NULL,
  `an` varchar(50) NOT NULL,
  `icd10` varchar(255) DEFAULT NULL,
  `icd9` varchar(255) DEFAULT NULL,
  `los` int(11) DEFAULT NULL,
  `cost` varchar(50) DEFAULT NULL,
  `adjrw` varchar(25) DEFAULT NULL,
  `costadjrw` varchar(50) DEFAULT NULL,
  `FundHolderID` varchar(5) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ipid`) USING BTREE,
  KEY `ipid` (`ipid`) USING BTREE,
  KEY `hospcode` (`hospcode`) USING BTREE,
  KEY `admitdate` (`admitdate`) USING BTREE,
  KEY `dischartdate` (`dischartdate`) USING BTREE,
  KEY `cid` (`cid`) USING BTREE,
  KEY `hn` (`hn`) USING BTREE,
  KEY `an` (`an`) USING BTREE,
  KEY `icd10` (`icd10`) USING BTREE,
  KEY `icd9` (`icd9`) USING BTREE,
  KEY `los` (`los`) USING BTREE,
  KEY `cost` (`cost`) USING BTREE,
  KEY `adjrw` (`adjrw`) USING BTREE,
  KEY `costadjrw` (`costadjrw`) USING BTREE,
  KEY `FundHolderID` (`FundHolderID`) USING BTREE,
  KEY `exsfile` (`exsfile`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'opdata' => <<<'SQL'
CREATE TABLE `opdata` (
  `opid` int(11) NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(5) NOT NULL,
  `dateserv` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `fund` varchar(255) DEFAULT NULL,
  `cid` varchar(255) DEFAULT NULL,
  `hn` varchar(50) NOT NULL,
  `seq` varchar(100) NOT NULL,
  `icd10` varchar(255) DEFAULT NULL,
  `op_pp_type` char(2) DEFAULT NULL COMMENT 'ประเภทย OP PP',
  `m1` varchar(50) DEFAULT NULL,
  `m2` varchar(50) DEFAULT NULL,
  `m3` varchar(50) DEFAULT NULL,
  `m4` varchar(50) DEFAULT NULL,
  `m5` varchar(50) DEFAULT NULL,
  `m6` varchar(50) DEFAULT NULL,
  `m7` varchar(50) DEFAULT NULL,
  `m8` varchar(50) DEFAULT NULL,
  `m9` varchar(50) DEFAULT NULL,
  `m10` varchar(50) DEFAULT NULL,
  `m11` varchar(50) DEFAULT NULL,
  `m12` varchar(50) DEFAULT NULL,
  `m13` varchar(50) DEFAULT NULL,
  `m14` varchar(50) DEFAULT NULL,
  `m15` varchar(50) DEFAULT NULL,
  `m16` varchar(50) DEFAULT NULL,
  `alltotal` varchar(100) DEFAULT NULL,
  `FundHolderID` varchar(5) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`opid`) USING BTREE,
  KEY `opid` (`opid`) USING BTREE,
  KEY `hospcode` (`hospcode`) USING BTREE,
  KEY `dateserv` (`dateserv`) USING BTREE,
  KEY `hn` (`hn`) USING BTREE,
  KEY `seq` (`seq`) USING BTREE,
  KEY `icd10` (`icd10`) USING BTREE,
  KEY `alltotal` (`alltotal`) USING BTREE,
  KEY `FundHolderID` (`FundHolderID`) USING BTREE,
  KEY `exsfile` (`exsfile`) USING BTREE,
  KEY `idx_opdata_fundholder_fund` (`FundHolderID`,`fund`),
  KEY `idx_opdata_cid_dateserv` (`cid`,`dateserv`),
  KEY `idx_opdata_icd10` (`icd10`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'periodtbl' => <<<'SQL'
CREATE TABLE `periodtbl` (
  `periodtbl_id` int(11) NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(255) DEFAULT NULL,
  `PeriodName` varchar(255) DEFAULT NULL,
  `PeriodStartDate` varchar(255) DEFAULT NULL,
  `PeriodEndDate` varchar(255) DEFAULT NULL,
  `ClaimEndDate` varchar(255) DEFAULT NULL,
  `PricePerHead` varchar(255) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`periodtbl_id`) USING BTREE,
  KEY `PeriodID` (`PeriodID`) USING BTREE,
  KEY `PeriodName` (`PeriodName`) USING BTREE,
  KEY `PeriodStartDate` (`PeriodStartDate`) USING BTREE,
  KEY `PeriodEndDate` (`PeriodEndDate`) USING BTREE,
  KEY `ClaimEndDate` (`ClaimEndDate`) USING BTREE,
  KEY `PricePerHead` (`PricePerHead`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'periodtbl_tmp' => <<<'SQL'
CREATE TABLE `periodtbl_tmp` (
  `periodtbl_id` int(11) NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(255) DEFAULT NULL,
  `PeriodName` varchar(255) DEFAULT NULL,
  `PeriodStartDate` varchar(255) DEFAULT NULL,
  `PeriodEndDate` varchar(255) DEFAULT NULL,
  `ClaimEndDate` varchar(255) DEFAULT NULL,
  `PricePerHead` varchar(255) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`periodtbl_id`) USING BTREE,
  KEY `PeriodID` (`PeriodID`) USING BTREE,
  KEY `PeriodName` (`PeriodName`) USING BTREE,
  KEY `PeriodStartDate` (`PeriodStartDate`) USING BTREE,
  KEY `PeriodEndDate` (`PeriodEndDate`) USING BTREE,
  KEY `ClaimEndDate` (`ClaimEndDate`) USING BTREE,
  KEY `PricePerHead` (`PricePerHead`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'refer_claim' => <<<'SQL'
CREATE TABLE `refer_claim` (
  `rcid` int(11) NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(5) NOT NULL COMMENT 'รหัส รพ.ต้นทาง (ผู้ส่งต่อ/ผู้จ่าย) เชื่อม opdata.hospcode',
  `serv_hospcode` varchar(5) NOT NULL COMMENT 'รหัสหน่วยบริการที่รักษา/เรียกเก็บ เชื่อม opdata.refertohospcode',
  `dateserv` date NOT NULL COMMENT 'วันที่รับบริการ (ค.ศ.)',
  `hn` varchar(50) NOT NULL COMMENT 'HN ของหน่วยบริการที่รักษา',
  `vn_an` varchar(50) DEFAULT NULL COMMENT 'เลขที่รับบริการ VN/AN',
  `cid` varchar(255) DEFAULT NULL COMMENT 'เลขที่บัตร',
  `name` varchar(255) NOT NULL COMMENT 'ชื่อ-สกุล',
  `refer_number` varchar(50) NOT NULL COMMENT 'เลขที่ refer',
  `sex` char(1) DEFAULT NULL COMMENT '1=ชาย, 2=หญิง',
  `p_age` smallint(5) unsigned DEFAULT NULL COMMENT 'อายุเป็นปี',
  `icd10` varchar(255) DEFAULT NULL COMMENT 'การวินิจฉัยโรค ICD-10',
  `icd9` varchar(255) DEFAULT NULL COMMENT 'การผ่าตัด/หัตถการ ICD-9-CM',
  `cost_lab` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ชันสูตร',
  `cost_xray` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'รังสี',
  `cost_radiother` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'รังสีรักษา',
  `cost_drug` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'ยาและเวชภัณฑ์',
  `cost_other` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'บำบัดรักษาอื่นๆ',
  `cost_total` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'รวมทั้งสิ้น',
  `central_pay` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'เบิกส่วนกลาง',
  `claim_amount` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'จำนวนเรียกเก็บ',
  `username` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้บันทึก',
  `username_edit` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้แก้ไข',
  `record_date` date DEFAULT NULL COMMENT 'วันที่บันทึก',
  `record_time` time DEFAULT NULL COMMENT 'เวลาบันทึก',
  `modified_date` date DEFAULT NULL COMMENT 'วันที่แก้ไข',
  `modified_time` time DEFAULT NULL COMMENT 'เวลาแก้ไข',
  PRIMARY KEY (`rcid`) USING BTREE,
  UNIQUE KEY `uk_claim` (`hospcode`,`refer_number`,`serv_hospcode`,`hn`,`dateserv`) USING BTREE,
  KEY `idx_refer` (`refer_number`,`hospcode`) USING BTREE,
  KEY `idx_refer_to` (`refer_number`,`serv_hospcode`) USING BTREE,
  KEY `idx_dateserv` (`dateserv`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ค่ารักษาผู้ป่วยนอกส่งต่อ ประกันสุขภาพแรงงานต่างด้าว (แบบ นค.1)'
SQL,
        'registrationtbl' => <<<'SQL'
CREATE TABLE `registrationtbl` (
  `registrationtbl_id` int(11) NOT NULL AUTO_INCREMENT,
  `CID` varchar(255) DEFAULT NULL,
  `PeriodID` varchar(255) DEFAULT NULL,
  `FundHolderID` varchar(255) DEFAULT NULL,
  `RegistrationDate` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `birthdate` varchar(255) DEFAULT NULL,
  `Gender` varchar(255) DEFAULT NULL,
  `HospitalID` varchar(255) DEFAULT NULL,
  `race` varchar(255) DEFAULT NULL,
  `Type` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `Office` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `employerTel` varchar(255) DEFAULT NULL,
  `SentDataDate` varchar(255) DEFAULT NULL,
  `status_sendm` varchar(255) DEFAULT NULL,
  `period_name` varchar(255) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`registrationtbl_id`) USING BTREE,
  KEY `CID` (`CID`) USING BTREE,
  KEY `PeriodID` (`PeriodID`) USING BTREE,
  KEY `FundHolderID` (`FundHolderID`) USING BTREE,
  KEY `RegistrationDate` (`RegistrationDate`) USING BTREE,
  KEY `fname` (`fname`) USING BTREE,
  KEY `lname` (`lname`) USING BTREE,
  KEY `period_name` (`period_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'registrationtbl_tmp' => <<<'SQL'
CREATE TABLE `registrationtbl_tmp` (
  `registrationtbl_id` int(11) NOT NULL AUTO_INCREMENT,
  `CID` varchar(255) DEFAULT NULL,
  `PeriodID` varchar(255) DEFAULT NULL,
  `FundHolderID` varchar(255) DEFAULT NULL,
  `RegistrationDate` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `birthdate` varchar(255) DEFAULT NULL,
  `Gender` varchar(255) DEFAULT NULL,
  `HospitalID` varchar(255) DEFAULT NULL,
  `race` varchar(255) DEFAULT NULL,
  `Type` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `Office` varchar(255) DEFAULT NULL,
  `employer` varchar(255) DEFAULT NULL,
  `employerTel` varchar(255) DEFAULT NULL,
  `SentDataDate` varchar(255) DEFAULT NULL,
  `status_sendm` varchar(255) DEFAULT NULL,
  `period_name` varchar(255) DEFAULT NULL,
  `exsfile` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`registrationtbl_id`) USING BTREE,
  KEY `CID` (`CID`) USING BTREE,
  KEY `PeriodID` (`PeriodID`) USING BTREE,
  KEY `FundHolderID` (`FundHolderID`) USING BTREE,
  KEY `RegistrationDate` (`RegistrationDate`) USING BTREE,
  KEY `fname` (`fname`) USING BTREE,
  KEY `lname` (`lname`) USING BTREE,
  KEY `period_name` (`period_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'typehealth' => <<<'SQL'
CREATE TABLE `typehealth` (
  `typehealth_id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`typehealth_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'typeprice' => <<<'SQL'
CREATE TABLE `typeprice` (
  `typeprice_id` int(11) NOT NULL AUTO_INCREMENT,
  `card_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `sentprice` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`typeprice_id`) USING BTREE,
  KEY `idx_typeprice_card_id` (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'user_tmp' => <<<'SQL'
CREATE TABLE `user_tmp` (
  `fname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `pass` varchar(255) DEFAULT NULL,
  `orgname` varchar(255) DEFAULT NULL,
  `hoscode` varchar(255) DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
        'xlsfileuploads' => <<<'SQL'
CREATE TABLE `xlsfileuploads` (
  `xlsid` int(11) NOT NULL AUTO_INCREMENT,
  `exsfile` varchar(255) NOT NULL,
  `hoscode` varchar(5) NOT NULL,
  `user` varchar(50) NOT NULL,
  `typefileupload` varchar(50) NOT NULL,
  `uploaddatetime` datetime NOT NULL,
  `importstatus` int(11) NOT NULL DEFAULT 0,
  `startdatetime` datetime DEFAULT NULL,
  `enddatetime` datetime DEFAULT NULL,
  PRIMARY KEY (`xlsid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
SQL,
    ];

    public function safeUp()
    {
        if ($this->db->getTableSchema(self::STATE_TABLE, true) === null) {
            $this->createTable(self::STATE_TABLE, [
                'table_name' => $this->string(64)->notNull()->primaryKey(),
            ]);
        }

        foreach (self::TABLES as $table => $definition) {
            if ($this->db->getTableSchema('{{%' . $table . '}}', true) !== null) {
                continue;
            }

            $sql = str_replace('`' . $table . '`', '{{%' . $table . '}}', $definition);
            $this->execute($sql);
            $this->insert(self::STATE_TABLE, ['table_name' => $table]);
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema(self::STATE_TABLE, true) === null) {
            return;
        }

        $createdTables = $this->db->createCommand(
            'SELECT [[table_name]] FROM ' . self::STATE_TABLE
        )->queryColumn();

        foreach (array_reverse($createdTables) as $table) {
            $this->dropTable('{{%' . $table . '}}');
        }

        $this->dropTable(self::STATE_TABLE);
    }
}

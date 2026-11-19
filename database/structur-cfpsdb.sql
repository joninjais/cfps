/*
 Navicat Premium Dump SQL

 Source Server         : 192.168.2.5-cfps
 Source Server Type    : MySQL
 Source Server Version : 110809 (11.8.9-MariaDB)
 Source Host           : 192.168.2.5:3306
 Source Schema         : cfpsdb

 Target Server Type    : MySQL
 Target Server Version : 110809 (11.8.9-MariaDB)
 File Encoding         : 65001

 Date: 14/11/2026 06:54:05
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for auth_assignment
-- ----------------------------
DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE `auth_assignment`  (
  `item_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`item_name`, `user_id`) USING BTREE,
  INDEX `idx-auth_assignment-user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_item
-- ----------------------------
DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE `auth_item`  (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` smallint NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL,
  `rule_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NULL DEFAULT NULL,
  `data` blob NULL,
  `created_at` int NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE,
  INDEX `rule_name`(`rule_name` ASC) USING BTREE,
  INDEX `idx-auth_item-type`(`type` ASC) USING BTREE,
  CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_item_child
-- ----------------------------
DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE `auth_item_child`  (
  `parent` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `child` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`, `child`) USING BTREE,
  INDEX `child`(`child` ASC) USING BTREE,
  CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE `auth_rule`  (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` blob NULL,
  `created_at` int NULL DEFAULT NULL,
  `updated_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb3 COLLATE = utf8mb3_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for card
-- ----------------------------
DROP TABLE IF EXISTS `card`;
CREATE TABLE `card`  (
  `card_id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `price` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`card_id`) USING BTREE,
  INDEX `idx_card_card_id`(`card_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for chospital2
-- ----------------------------
DROP TABLE IF EXISTS `chospital2`;
CREATE TABLE `chospital2`  (
  `hospcode` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `hosname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `mu` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `subdistcode` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ampcode` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `provcode` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `cupcode` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`hospcode`) USING BTREE,
  INDEX `idx_chospital2_hospcode`(`hospcode` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for diesease
-- ----------------------------
DROP TABLE IF EXISTS `diesease`;
CREATE TABLE `diesease`  (
  `diesease_id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `typehealth_id` int NULL DEFAULT NULL,
  PRIMARY KEY (`diesease_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for fwf_fy_claim
-- ----------------------------
DROP TABLE IF EXISTS `fwf_fy_claim`;
CREATE TABLE `fwf_fy_claim`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `FundHolderID` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'รหัสโรงพยาบาลเจ้าของกองทุน',
  `hosname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อโรงพยาบาล',
  `CID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'เลขประจำตัวผู้เอาประกัน',
  `PeriodStartDate` date NOT NULL COMMENT 'วันเริ่มต้นงวด (ต้นฉบับ)',
  `PeriodEndDate` date NOT NULL COMMENT 'วันสิ้นสุดงวด (ต้นฉบับ)',
  `timeregis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'งวดที่ลงทะเบียน',
  `FiscalYear` int NOT NULL COMMENT 'ปีงบประมาณ (พ.ศ.)',
  `FY_Start` date NOT NULL COMMENT 'วันเริ่มต้นปีงบประมาณ',
  `FY_End` date NOT NULL COMMENT 'วันสิ้นสุดปีงบประมาณ',
  `Period_in_FY_Start` date NULL DEFAULT NULL COMMENT 'วันเริ่มต้นที่ทับซ้อนในปีงบนั้น',
  `Period_in_FY_End` date NULL DEFAULT NULL COMMENT 'วันสิ้นสุดที่ทับซ้อนในปีงบนั้น',
  `Days_in_FY` int NULL DEFAULT NULL COMMENT 'จำนวนวันในปีงบนั้น',
  `DaysBetween` int NULL DEFAULT NULL COMMENT 'จำนวนวันทั้งหมดของ record',
  `card_price` decimal(10, 2) NULL DEFAULT NULL COMMENT 'ราคาบัตร',
  `claim` decimal(12, 2) NULL DEFAULT NULL COMMENT 'ค่า claim รวม',
  `claim_per_day` decimal(12, 4) NULL DEFAULT NULL COMMENT 'claim ต่อวัน',
  `claim_in_FY` decimal(12, 2) NULL DEFAULT NULL COMMENT 'claim เฉพาะปีงบนั้น',
  `manage_ssj_per_day` decimal(12, 4) NULL DEFAULT NULL COMMENT 'บริหารจัดการต่อวัน',
  `pp_per_day` decimal(12, 4) NULL DEFAULT NULL COMMENT 'PP ต่อวัน',
  `manage_ssj_in_FY` decimal(12, 2) NULL DEFAULT NULL COMMENT 'บริหารจัดการเฉพาะปีงบนั้น',
  `pp_in_FY` decimal(12, 2) NULL DEFAULT NULL COMMENT 'PP เฉพาะปีงบนั้น',
  `imported_at` timestamp NULL DEFAULT current_timestamp COMMENT 'เวลานำเข้า',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uq_fwf_fy_claim_key`(`FundHolderID`, `CID`, `PeriodStartDate`, `PeriodEndDate`, `FiscalYear`) USING HASH,
  INDEX `idx_fwf_fy_claim_fy`(`FiscalYear`) USING BTREE,
  INDEX `idx_fwf_fy_claim_holder`(`FundHolderID`) USING BTREE,
  INDEX `idx_fwf_fy_claim_cid`(`CID`(250)) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1391827 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for fwfdatabase
-- ----------------------------
DROP TABLE IF EXISTS `fwfdatabase`;
CREATE TABLE `fwfdatabase`  (
  `fwfid` int NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `PeriodStartDate` date NOT NULL,
  `PeriodEndDate` date NOT NULL,
  `CID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `FundHolderID` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `timeregis` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `regisdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `card_price` decimal(10, 2) NULL DEFAULT NULL,
  `pp` decimal(10, 2) NULL DEFAULT NULL,
  `man_ssj` decimal(10, 2) NULL DEFAULT NULL,
  `man_sp` decimal(10, 2) NULL DEFAULT NULL,
  `hc_sp` decimal(10, 2) NULL DEFAULT NULL,
  `claim` decimal(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`fwfid`) USING BTREE,
  INDEX `fwfid`(`fwfid` ASC) USING BTREE,
  INDEX `PeriodID`(`PeriodID` ASC) USING BTREE,
  INDEX `PeriodStartDate`(`PeriodStartDate` ASC) USING BTREE,
  INDEX `PeriodEndDate`(`PeriodEndDate` ASC) USING BTREE,
  INDEX `CID`(`CID` ASC) USING BTREE,
  INDEX `FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `regisdate`(`regisdate` ASC) USING BTREE,
  INDEX `idx_card_price_on_fwfdatabase`(`card_price` ASC) USING BTREE,
  INDEX `idx_fwfdatabase_card_price`(`card_price` ASC) USING BTREE,
  INDEX `idx_fwfdatabase_FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `idx_fwf_cid_period`(`CID` ASC, `PeriodStartDate` ASC, `PeriodEndDate` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4234951 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for health_office_stg
-- ----------------------------
DROP TABLE IF EXISTS `health_office_stg`;
CREATE TABLE `health_office_stg`  (
  `row_no` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ลำดับแถวที่นำเข้า (ไม่มีใน Excel)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 1: ชื่อ',
  `code9` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 2: รหัส 9 หลักใหม่',
  `code9_old` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 3: รหัส 9 หลัก',
  `hcode5` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 4: รหัส 5 หลัก',
  `license_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 5: เลขอนุญาตให้ประกอบสถานบริการสุขภาพ 11 หลัก',
  `org_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 6: ประเภทองค์กร',
  `facility_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 7: ประเภทหน่วยบริการสุขภาพ',
  `affiliation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 8: สังกัด',
  `department` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 9: แผนก/กรม',
  `hospital_level` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 10: ระดับโรงพยาบาล',
  `bed_count` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 11: เตียงที่ใช้จริง',
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 12: สถานะการใช้งาน',
  `region` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 13: เขตบริการ',
  `address_line` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 14: ที่อยู่',
  `province_code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 15: รหัสจังหวัด',
  `province_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 16: จังหวัด',
  `district_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 17: รหัสอำเภอ',
  `district_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 18: อำเภอ/เขต',
  `subdistrict_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 19: รหัสตำบล',
  `subdistrict_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 20: ตำบล/แขวง',
  `moo` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 21: หมู่',
  `postal_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 22: รหัสไปรษณีย์',
  `parent_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 23: แม่ข่าย',
  `established_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 24: วันที่ก่อตั้ง',
  `closed_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 25: วันที่ปิดบริการ',
  `source_updated_date` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คอลัมน์ที่ 26: อัพเดตล่าสุด(เริ่ม 05/09/2566)',
  PRIMARY KEY (`row_no`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 44488 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'ทะเบียนหน่วยบริการสุขภาพ (จาก health_office.xlsx' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for insurance_policies
-- ----------------------------
DROP TABLE IF EXISTS `insurance_policies`;
CREATE TABLE `insurance_policies`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `agency_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'รหัสหน่วยงาน',
  `agency_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หน่วยงาน',
  `province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'จังหวัด',
  `f_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'FCode',
  `passport_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง',
  `citizen_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขบัตรประจำตัว',
  `alien_ref_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว',
  `title_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'คำนำหน้า',
  `full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อ-นามสกุล',
  `nationality` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'สัญชาติ',
  `ethnicity` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เชื้อชาติ',
  `hn_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'HN',
  `purchase_date` date NULL DEFAULT NULL COMMENT 'วันที่ซื้อกรมธรรม์',
  `effective_date` date NULL DEFAULT NULL COMMENT 'วันที่เกิดสิทธิ',
  `expiry_date` date NULL DEFAULT NULL COMMENT 'วันที่หมดอายุ',
  `policy_status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'สถานะกรมธรรม์',
  `card_price` decimal(10, 2) NULL DEFAULT NULL COMMENT 'ราคาบัตร',
  `coverage_period` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'ระยะเวลาคุ้มครอง',
  `father_passport_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง บิดา',
  `father_citizen_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขบัตรประจำตัว บิดา',
  `father_alien_ref_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว บิดา',
  `father_full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อ-นามสกุล บิดา',
  `mother_passport_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หมายเลขหนังสือเดินทาง มารดา',
  `mother_citizen_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขบัตรประจำตัว มารดา',
  `mother_alien_ref_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขอ้างอิงคนต่างด้าว มารดา',
  `mother_full_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อ-นามสกุล มารดา',
  `created_at` timestamp NULL DEFAULT current_timestamp,
  `birth_date` date NULL DEFAULT NULL COMMENT 'วันเดือนปีเกิด',
  `age_at_purchase` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'อายุ ณ วันที่ซื้อ',
  `house_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'บ้านเลขที่',
  `village_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หมู่',
  `alley` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ซอย',
  `road` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ถนน',
  `address_province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'จังหวัดที่อยู่',
  `subdistrict` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ตำบล',
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'อำเภอ',
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'รหัสไปรษณีย์',
  `reference_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขอ้างอิง',
  `allocation_service_unit` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรรหน่วยบริการ',
  `allocation_provincial_pp` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรร สสจ. PP',
  `allocation_provincial_admin` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรร สสจ. ค่าบริหารจัดการ',
  `allocation_provincial_total` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรร สสจ. รวม',
  `allocation_central_hc` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรรส่วนกลาง HC',
  `allocation_central_admin` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรรส่วนกลางค่าบริหารจัดการ',
  `allocation_central_total` decimal(12, 2) NULL DEFAULT NULL COMMENT 'จัดสรรส่วนกลางรวม',
  `occupation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'อาชีพ',
  `employer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อนายจ้าง',
  `employer_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'ที่อยู่สถานประกอบการนายจ้าง',
  `employer_phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เบอร์โทรนายจ้าง',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_card_price_on_insurance_policies`(`card_price` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 30162 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for ipdata
-- ----------------------------
DROP TABLE IF EXISTS `ipdata`;
CREATE TABLE `ipdata`  (
  `ipid` int NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `admitdate` date NOT NULL,
  `dischartdate` date NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fund` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `cid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `hn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `an` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icd10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `icd9` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `los` int NULL DEFAULT NULL,
  `cost` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `adjrw` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `costadjrw` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `FundHolderID` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `refer_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขที่ refer',
  `refertohospcode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'รหัสหน่วยบริการที่ส่งต่อ',
  `sex` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '1=ชาย, 2=หญิง',
  `p_age` smallint UNSIGNED NULL DEFAULT NULL COMMENT 'อายุเป็นปี',
  `nation` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '048=เมียนมา, 265=เมียนมา(แรงงาน), 056=ลาว, 266=ลาว(แรงงาน), 046=เวียดนาม, 057=กัมพูชา, 264=กัมพูชา(แรงงาน)',
  PRIMARY KEY (`ipid`) USING BTREE,
  INDEX `ipid`(`ipid` ASC) USING BTREE,
  INDEX `hospcode`(`hospcode` ASC) USING BTREE,
  INDEX `admitdate`(`admitdate` ASC) USING BTREE,
  INDEX `dischartdate`(`dischartdate` ASC) USING BTREE,
  INDEX `cid`(`cid` ASC) USING BTREE,
  INDEX `hn`(`hn` ASC) USING BTREE,
  INDEX `an`(`an` ASC) USING BTREE,
  INDEX `icd10`(`icd10` ASC) USING BTREE,
  INDEX `icd9`(`icd9` ASC) USING BTREE,
  INDEX `los`(`los` ASC) USING BTREE,
  INDEX `cost`(`cost` ASC) USING BTREE,
  INDEX `adjrw`(`adjrw` ASC) USING BTREE,
  INDEX `costadjrw`(`costadjrw` ASC) USING BTREE,
  INDEX `FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `exsfile`(`exsfile` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 38096 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for login_history
-- ----------------------------
DROP TABLE IF EXISTS `login_history`;
CREATE TABLE `login_history`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hoscode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `device_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `login_at` int UNSIGNED NOT NULL,
  `logout_at` int NULL DEFAULT NULL COMMENT 'เวลา logout (Unix timestamp)',
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'success',
  `fail_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เหตุผลที่ล้มเหลว',
  `session_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'Session ID (hashed)',
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'login' COMMENT 'login/logout',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_login_history_user`(`user`) USING BTREE,
  INDEX `idx_login_history_login_at`(`login_at`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2566 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for m130523_legacy_table_state
-- ----------------------------
DROP TABLE IF EXISTS `m130523_legacy_table_state`;
CREATE TABLE `m130523_legacy_table_state`  (
  `table_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`table_name`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for migration
-- ----------------------------
DROP TABLE IF EXISTS `migration`;
CREATE TABLE `migration`  (
  `version` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `apply_time` int NULL DEFAULT NULL,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for opdata
-- ----------------------------
DROP TABLE IF EXISTS `opdata`;
CREATE TABLE `opdata`  (
  `opid` int NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dateserv` date NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fund` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `cid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `hn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `seq` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icd10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `op_pp_type` char(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ประเภทย OP PP',
  `m1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m3` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m4` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m5` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m6` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m7` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m8` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m9` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m10` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m11` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m12` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m13` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m14` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m15` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `m16` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `alltotal` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `FundHolderID` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `refer_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขที่ refer',
  `refertohospcode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'รหัสหน่วยบริการที่ส่งต่อ',
  `sex` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '1=ชาย, 2=หญิง',
  `p_age` smallint UNSIGNED NULL DEFAULT NULL COMMENT 'อายุเป็นปี',
  `nation` char(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '048=เมียนมา, 265=เมียนมา(แรงงาน), 056=ลาว, 266=ลาว(แรงงาน), 046=เวียดนาม, 057=กัมพูชา, 264=กัมพูชา(แรงงาน)',
  PRIMARY KEY (`opid`) USING BTREE,
  INDEX `opid`(`opid` ASC) USING BTREE,
  INDEX `hospcode`(`hospcode` ASC) USING BTREE,
  INDEX `dateserv`(`dateserv` ASC) USING BTREE,
  INDEX `hn`(`hn` ASC) USING BTREE,
  INDEX `seq`(`seq` ASC) USING BTREE,
  INDEX `icd10`(`icd10` ASC) USING BTREE,
  INDEX `alltotal`(`alltotal` ASC) USING BTREE,
  INDEX `FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `exsfile`(`exsfile` ASC) USING BTREE,
  INDEX `idx_opdata_fundholder_fund`(`FundHolderID` ASC, `fund` ASC) USING BTREE,
  INDEX `idx_opdata_cid_dateserv`(`cid` ASC, `dateserv` ASC) USING BTREE,
  INDEX `idx_opdata_icd10`(`icd10` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 729644 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for periodtbl
-- ----------------------------
DROP TABLE IF EXISTS `periodtbl`;
CREATE TABLE `periodtbl`  (
  `periodtbl_id` int NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodStartDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodEndDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ClaimEndDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PricePerHead` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`periodtbl_id`) USING BTREE,
  INDEX `PeriodID`(`PeriodID` ASC) USING BTREE,
  INDEX `PeriodName`(`PeriodName` ASC) USING BTREE,
  INDEX `PeriodStartDate`(`PeriodStartDate` ASC) USING BTREE,
  INDEX `PeriodEndDate`(`PeriodEndDate` ASC) USING BTREE,
  INDEX `ClaimEndDate`(`ClaimEndDate` ASC) USING BTREE,
  INDEX `PricePerHead`(`PricePerHead` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1408 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for periodtbl_tmp
-- ----------------------------
DROP TABLE IF EXISTS `periodtbl_tmp`;
CREATE TABLE `periodtbl_tmp`  (
  `periodtbl_id` int NOT NULL AUTO_INCREMENT,
  `PeriodID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodStartDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodEndDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `ClaimEndDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PricePerHead` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`periodtbl_id`) USING BTREE,
  INDEX `PeriodID`(`PeriodID` ASC) USING BTREE,
  INDEX `PeriodName`(`PeriodName` ASC) USING BTREE,
  INDEX `PeriodStartDate`(`PeriodStartDate` ASC) USING BTREE,
  INDEX `PeriodEndDate`(`PeriodEndDate` ASC) USING BTREE,
  INDEX `ClaimEndDate`(`ClaimEndDate` ASC) USING BTREE,
  INDEX `PricePerHead`(`PricePerHead` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1827 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for refer_claim
-- ----------------------------
DROP TABLE IF EXISTS `refer_claim`;
CREATE TABLE `refer_claim`  (
  `rcid` int NOT NULL AUTO_INCREMENT,
  `hospcode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'รหัส รพ.ต้นทาง (ผู้ส่งต่อ/ผู้จ่าย) เชื่อม opdata.hospcode',
  `serv_hospcode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'รหัสหน่วยบริการที่รักษา/เรียกเก็บ เชื่อม opdata.refertohospcode',
  `book_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'เลขที่หนังสือ',
  `book_date` date NOT NULL COMMENT 'วันที่หนังสือ',
  `dateserv` date NOT NULL COMMENT 'วันที่รับบริการ (ค.ศ.)',
  `hn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'HN ของหน่วยบริการที่รักษา',
  `seq_an` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขที่รับบริการ VN/AN',
  `cid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขที่บัตร',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อ-สกุล',
  `refer_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'เลขที่ refer',
  `sex` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '1=ชาย, 2=หญิง',
  `p_age` smallint UNSIGNED NULL DEFAULT NULL COMMENT 'อายุเป็นปี',
  `icd10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'การวินิจฉัยโรค ICD-10',
  `icd9` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'การผ่าตัด/หัตถการ ICD-9-CM',
  `cost_lab` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'ชันสูตร',
  `cost_xray` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'รังสี',
  `cost_radiother` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'รังสีรักษา',
  `cost_drug` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'ยาและเวชภัณฑ์',
  `cost_other` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'บำบัดรักษาอื่นๆ',
  `cost_total` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'รวมทั้งสิ้น',
  `central_pay` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'เบิกส่วนกลาง',
  `claim_amount` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'จำนวนเรียกเก็บ',
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อผู้บันทึก',
  `username_edit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อผู้แก้ไข',
  `record_date` date NULL DEFAULT NULL COMMENT 'วันที่บันทึก',
  `record_time` time NULL DEFAULT NULL COMMENT 'เวลาบันทึก',
  `modified_date` date NULL DEFAULT NULL COMMENT 'วันที่แก้ไข',
  `modified_time` time NULL DEFAULT NULL COMMENT 'เวลาแก้ไข',
  `approved_by` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อผู้อนุมัติ',
  `approved_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'สถานะการอนุมัติ = จ่าย/ปฏิเสธ',
  `approved_date` date NULL DEFAULT NULL COMMENT 'วันที่อนุมัติ จ่าย/ปฏิเสธ',
  `approved_time` time NULL DEFAULT NULL COMMENT 'เวลาอนุมัติ จ่าย/ปฏิเสธ',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'หมายเหตุ',
  `p_visit_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ประเภทการเข้ารับบริการ IPD/OPD',
  `discharge_date_ipd` date NULL DEFAULT NULL COMMENT 'วันที่จำหน่ายผู้ป่วยใน',
  `adj_rw` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'adj.rw มีเฉพาะ ipd',
  `pay_total` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT 'จำนวนจ่าย',
  PRIMARY KEY (`rcid`) USING BTREE,
  UNIQUE INDEX `uk_claim`(`hospcode` ASC, `refer_number` ASC, `serv_hospcode` ASC, `hn` ASC, `seq_an` ASC, `dateserv` ASC) USING BTREE,
  UNIQUE INDEX `uk_refer_claim_serv_seq_an`(`serv_hospcode` ASC, `seq_an` ASC) USING BTREE,
  INDEX `idx_refer`(`refer_number` ASC, `hospcode` ASC) USING BTREE,
  INDEX `idx_refer_to`(`refer_number` ASC, `serv_hospcode` ASC) USING BTREE,
  INDEX `idx_dateserv`(`dateserv` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'ค่ารักษาผู้ป่วยนอกส่งต่อ ประกันสุขภาพแรงงานต่างด้าว (แบบ นค.1)' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for registration_window
-- ----------------------------
DROP TABLE IF EXISTS `registration_window`;
CREATE TABLE `registration_window`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `expires_at` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for registrationtbl
-- ----------------------------
DROP TABLE IF EXISTS `registrationtbl`;
CREATE TABLE `registrationtbl`  (
  `registrationtbl_id` int NOT NULL AUTO_INCREMENT,
  `CID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `FundHolderID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `RegistrationDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `birthdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `HospitalID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `race` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Office` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `employer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `employerTel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `SentDataDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status_sendm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `period_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`registrationtbl_id`) USING BTREE,
  INDEX `CID`(`CID` ASC) USING BTREE,
  INDEX `PeriodID`(`PeriodID` ASC) USING BTREE,
  INDEX `FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `RegistrationDate`(`RegistrationDate` ASC) USING BTREE,
  INDEX `fname`(`fname` ASC) USING BTREE,
  INDEX `lname`(`lname` ASC) USING BTREE,
  INDEX `period_name`(`period_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26664 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for registrationtbl_tmp
-- ----------------------------
DROP TABLE IF EXISTS `registrationtbl_tmp`;
CREATE TABLE `registrationtbl_tmp`  (
  `registrationtbl_id` int NOT NULL AUTO_INCREMENT,
  `CID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `PeriodID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `FundHolderID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `RegistrationDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `lname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `birthdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `HospitalID` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `race` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `Office` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `employer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `employerTel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `SentDataDate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status_sendm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `period_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`registrationtbl_id`) USING BTREE,
  INDEX `CID`(`CID` ASC) USING BTREE,
  INDEX `PeriodID`(`PeriodID` ASC) USING BTREE,
  INDEX `FundHolderID`(`FundHolderID` ASC) USING BTREE,
  INDEX `RegistrationDate`(`RegistrationDate` ASC) USING BTREE,
  INDEX `fname`(`fname` ASC) USING BTREE,
  INDEX `lname`(`lname` ASC) USING BTREE,
  INDEX `period_name`(`period_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 316354 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for sql_code
-- ----------------------------
DROP TABLE IF EXISTS `sql_code`;
CREATE TABLE `sql_code`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `his` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อระบบ HIS',
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ประเภท: OP หรือ IP',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'ชื่อ/คำอธิบายคำสั่ง SQL',
  `sql_command` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'คำสั่ง SQL',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT 'หมายเหตุ',
  `created_at` int UNSIGNED NULL DEFAULT NULL,
  `updated_at` int UNSIGNED NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_sql_code_his`(`his`) USING BTREE,
  INDEX `idx_sql_code_type`(`type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for typehealth
-- ----------------------------
DROP TABLE IF EXISTS `typehealth`;
CREATE TABLE `typehealth`  (
  `typehealth_id` int NOT NULL AUTO_INCREMENT,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`typehealth_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for typeprice
-- ----------------------------
DROP TABLE IF EXISTS `typeprice`;
CREATE TABLE `typeprice`  (
  `typeprice_id` int NOT NULL AUTO_INCREMENT,
  `card_id` int NULL DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `price` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sentprice` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`typeprice_id`) USING BTREE,
  INDEX `idx_typeprice_card_id`(`card_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 47 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `userid` int NOT NULL AUTO_INCREMENT,
  `fname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ชื่อ',
  `lname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'นามสกุล',
  `user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `orgname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ชื่อหน่วยบริการ',
  `hoscode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phonenumber` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `hmain` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1,
  `approval_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'approved',
  `requested_role` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `approved_by` int NULL DEFAULT NULL,
  `approved_at` int NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 396 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for user_manager_role_migration
-- ----------------------------
DROP TABLE IF EXISTS `user_manager_role_migration`;
CREATE TABLE `user_manager_role_migration`  (
  `userid` int NOT NULL,
  `previous_requested_role` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`userid`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for xlsfileuploads
-- ----------------------------
DROP TABLE IF EXISTS `xlsfileuploads`;
CREATE TABLE `xlsfileuploads`  (
  `xlsid` int NOT NULL AUTO_INCREMENT,
  `exsfile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `hoscode` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `typefileupload` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `uploaddatetime` datetime NOT NULL,
  `importstatus` int NOT NULL DEFAULT 0,
  `startdatetime` datetime NULL DEFAULT NULL,
  `enddatetime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`xlsid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8376 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Function structure for fn_get_op_pp_type
-- ----------------------------
DROP FUNCTION IF EXISTS `fn_get_op_pp_type`;
delimiter ;;
CREATE FUNCTION `fn_get_op_pp_type`(p_icd10 VARCHAR(50))
 RETURNS varchar(2) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
  DETERMINISTIC
RETURN CASE
    WHEN (
        p_icd10 LIKE '%Z34%' OR p_icd10 LIKE '%Z13%' OR p_icd10 LIKE '%Z35%'
        OR p_icd10 LIKE '%Z30%' OR p_icd10 LIKE '%Z31%' OR p_icd10 LIKE '%Z32%'
        OR p_icd10 LIKE '%Z33%' OR p_icd10 LIKE '%Z39%' OR p_icd10 LIKE '%Z364%'
        OR p_icd10 LIKE '%Z37%' OR p_icd10 LIKE '%Z21%' OR p_icd10 LIKE '%Z232%'
        OR p_icd10 LIKE '%Z240%' OR p_icd10 LIKE '%Z244%' OR p_icd10 LIKE '%Z245%'
        OR p_icd10 LIKE '%Z271%' OR p_icd10 LIKE '%Z246%' OR p_icd10 LIKE '%Z251%'
        OR p_icd10 LIKE '%Z274%' OR p_icd10 LIKE '%Z273%' OR p_icd10 LIKE '%Z11%'
        OR p_icd10 LIKE '%Z10%' OR p_icd10 LIKE '%Z298%' OR p_icd10 LIKE '%Z001%'
        OR p_icd10 LIKE '%Z115%' OR p_icd10 LIKE '%U119%'
    ) THEN 'pp'
    ELSE 'op'
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for import_insurance_batch
-- ----------------------------
DROP PROCEDURE IF EXISTS `import_insurance_batch`;
delimiter ;;
CREATE PROCEDURE `import_insurance_batch`(IN p_batch_size INT)
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_last_id INT DEFAULT 0;
  DECLARE v_rows INT DEFAULT 0;

  -- ถ้าต้องการเริ่มจาก id ใด ให้กำหนด v_last_id ก่อนเรียก procedure
  -- Loop หลัก
  read_loop: LOOP
    -- ทำการ INSERT ทีละก้อน โดยเลือกจาก id > v_last_id เพื่อหลีกเลี่ยง OFFSET (ประสิทธิภาพดีกว่า)
    INSERT INTO fwfdatabase (
      PeriodID,
      PeriodStartDate,
      PeriodEndDate,
      CID,
      FundHolderID,
      fname,
      lname,
      timeregis,
      regisdate,
      exsfile,
      card_price         -- เพิ่มตรงนี้
    )
    SELECT
      src.f_code AS PeriodID,
      src.effective_date AS PeriodStartDate,
      src.expiry_date AS PeriodEndDate,
      CONCAT(
        IFNULL(src.f_code, ''),
        ' , ',
        IFNULL(src.passport_no, ''),
        ' , ',
        IFNULL(src.citizen_id, ''),
        ' , ',
        IFNULL(src.alien_ref_id, '')
      ) AS CID,
      LEFT(src.agency_code, 5) AS FundHolderID,
      src.full_name AS fname,
      src.nationality AS lname,
      COALESCE(src.coverage_period, '') AS timeregis,
      COALESCE(DATE_FORMAT(src.purchase_date, '%Y-%m-%d'), '') AS regisdate,
      'FDH' AS exsfile,
      src.card_price     -- เพิ่มตรงนี้
    FROM insurance_policies src
    LEFT JOIN fwfdatabase tgt
      ON tgt.PeriodID = src.f_code COLLATE utf8mb4_general_ci
      AND tgt.PeriodStartDate = src.effective_date
    WHERE tgt.PeriodID IS NULL
      AND src.effective_date IS NOT NULL
      AND src.expiry_date IS NOT NULL
      AND src.id > v_last_id
    ORDER BY src.id
    LIMIT p_batch_size;

    -- จำนวนแถวที่ถูก INSERT ในรอบนี้
    SET v_rows = ROW_COUNT();

    -- ถ้าไม่มีแถวถูก insert ให้จบ loop
    IF v_rows = 0 THEN
      LEAVE read_loop;
    END IF;

    -- อัพเดต v_last_id เป็น id สูงสุดที่เพิ่งประมวลผล (เพื่อให้รอบถัดไปเริ่มหลังจากนี้)
    -- หา id สูงสุดจากแถวที่เพิ่งถูกเลือก (ใช้ MAX(id) จาก source ที่ id > previous v_last_id)
    SELECT MAX(id) INTO v_last_id
    FROM insurance_policies
    WHERE id > v_last_id
    ORDER BY id
    LIMIT v_rows;

    -- (ไม่บังคับ) แสดงสถานะแบบเรียลไทม์เมื่อรันใน client
    SELECT CONCAT('Inserted rows this batch: ', v_rows, ' ; last_id now: ', v_last_id) AS batch_status;

  END LOOP read_loop;

  -- สรุปผลเมื่อจบ
  SELECT 'Import finished' AS status, v_last_id AS last_processed_id;
END
;;
delimiter ;

-- ----------------------------
-- Procedure structure for InsertIntoFWFDatabase
-- ----------------------------
DROP PROCEDURE IF EXISTS `InsertIntoFWFDatabase`;
delimiter ;;
CREATE PROCEDURE `InsertIntoFWFDatabase`()
BEGIN
    INSERT INTO fwfdatabase 
        (PeriodID, PeriodStartDate, PeriodEndDate, CID, FundHolderID, fname, lname, timeregis, regisdate, exsfile)
    SELECT DISTINCT
        pt.PeriodID,
        pt.PeriodStartDate,
        pt.PeriodEndDate,
        rt.CID,
        rt.FundHolderID,
        rt.fname,
        rt.lname,
        rt.period_name AS timeregis,
        rt.RegistrationDate AS regisdate,
        NOW() AS exsfile
    FROM registrationtbl_tmp rt
    INNER JOIN periodtbl_tmp pt ON pt.PeriodID = rt.PeriodID
    WHERE NOT EXISTS (
        SELECT 1 
        FROM fwfdatabase fwf
        WHERE fwf.CID = rt.CID 
          AND fwf.PeriodStartDate = pt.PeriodStartDate
          AND fwf.PeriodEndDate = pt.PeriodEndDate
          AND fwf.FundHolderID = rt.FundHolderID
    );
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;

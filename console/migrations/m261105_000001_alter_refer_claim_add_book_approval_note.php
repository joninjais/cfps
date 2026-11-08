<?php

use yii\db\Migration;

/**
 * ปรับโครงสร้างตาราง refer_claim
 * - hospcode, serv_hospcode ขยายเป็น varchar(20)
 * - เพิ่ม book_number, book_date (เลขที่/วันที่หนังสือ) ต่อจาก serv_hospcode
 * - เพิ่ม approved_by, approved_status, approved_date, approved_time, note ต่อจาก modified_time
 *
 * แถวเดิมจะถูกเติม book_number = '-' และ book_date = วันที่บันทึก (หรือวันที่รับบริการ ถ้าไม่มี)
 * เพราะสองคอลัมน์นี้เป็น NOT NULL ผู้ใช้ควรแก้ไขให้ถูกต้องภายหลัง
 */
class m261105_000001_alter_refer_claim_add_book_approval_note extends Migration
{
    public function up()
    {
        $db = $this->db;

        $db->createCommand("ALTER TABLE `refer_claim`
            MODIFY `hospcode` varchar(20) NOT NULL COMMENT 'รหัส รพ.ต้นทาง (ผู้ส่งต่อ/ผู้จ่าย) เชื่อม opdata.hospcode',
            MODIFY `serv_hospcode` varchar(20) NOT NULL COMMENT 'รหัสหน่วยบริการที่รักษา/เรียกเก็บ เชื่อม opdata.refertohospcode',
            ADD COLUMN `book_number` varchar(50) NULL COMMENT 'เลขที่หนังสือ' AFTER `serv_hospcode`,
            ADD COLUMN `book_date` date NULL COMMENT 'วันที่หนังสือ' AFTER `book_number`,
            ADD COLUMN `approved_by` varchar(255) DEFAULT NULL COMMENT 'ชื่อผู้อนุมัติ' AFTER `modified_time`,
            ADD COLUMN `approved_status` varchar(50) DEFAULT NULL COMMENT 'สถานะการอนุมัติ = จ่าย/ปฏิเสธ' AFTER `approved_by`,
            ADD COLUMN `approved_date` date DEFAULT NULL COMMENT 'วันที่อนุมัติ จ่าย/ปฏิเสธ' AFTER `approved_status`,
            ADD COLUMN `approved_time` time DEFAULT NULL COMMENT 'เวลาอนุมัติ จ่าย/ปฏิเสธ' AFTER `approved_date`,
            ADD COLUMN `note` varchar(255) DEFAULT NULL COMMENT 'หมายเหตุ' AFTER `approved_time`")->execute();

        // เติมค่าให้แถวเดิม แล้วจึงบังคับ NOT NULL
        $db->createCommand("UPDATE `refer_claim`
            SET `book_number` = '-', `book_date` = COALESCE(`record_date`, `dateserv`)
            WHERE `book_number` IS NULL OR `book_date` IS NULL")->execute();

        $db->createCommand("ALTER TABLE `refer_claim`
            MODIFY `book_number` varchar(50) NOT NULL COMMENT 'เลขที่หนังสือ',
            MODIFY `book_date` date NOT NULL COMMENT 'วันที่หนังสือ'")->execute();
    }

    public function down()
    {
        $this->db->createCommand("ALTER TABLE `refer_claim`
            DROP COLUMN `note`,
            DROP COLUMN `approved_time`,
            DROP COLUMN `approved_date`,
            DROP COLUMN `approved_status`,
            DROP COLUMN `approved_by`,
            DROP COLUMN `book_date`,
            DROP COLUMN `book_number`,
            MODIFY `hospcode` varchar(5) NOT NULL COMMENT 'รหัส รพ.ต้นทาง (ผู้ส่งต่อ/ผู้จ่าย) เชื่อม opdata.hospcode',
            MODIFY `serv_hospcode` varchar(5) NOT NULL COMMENT 'รหัสหน่วยบริการที่รักษา/เรียกเก็บ เชื่อม opdata.refertohospcode'")->execute();
    }
}

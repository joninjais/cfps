<?php

use yii\db\Migration;

/**
 * ลบแถวซ้ำใน opdata (hospcode + seq) แล้วเพิ่ม unique index
 * แถวที่ถูกลบจะถูกสำรองไว้ในตาราง opdata_dedupe_backup_20261124
 */
class m261124_000001_dedupe_opdata_add_unique_hospcode_seq extends Migration
{
    private const BACKUP = 'opdata_dedupe_backup_20261124';
    private const INDEX  = 'uq_opdata_hospcode_seq';

    public function up()
    {
        // ตัดช่องว่างหน้า/หลัง เพื่อให้แถวที่ต่างกันแค่ช่องว่างถูกนับเป็นซ้ำ
        $this->execute("UPDATE opdata SET hospcode = TRIM(hospcode) WHERE hospcode <> TRIM(hospcode)");
        $this->execute("UPDATE opdata SET seq = TRIM(seq) WHERE seq <> TRIM(seq)");

        // เก็บแถวที่ดีที่สุดไว้ 1 แถวต่อ (hospcode, seq): ที่มี FundHolderID จริงก่อน แล้วค่อย opid น้อยสุด
        $loser = "
            FROM opdata o
            INNER JOIN opdata b
                ON b.hospcode = o.hospcode AND b.seq = o.seq AND b.opid <> o.opid
            WHERE (
                (b.FundHolderID IS NULL OR b.FundHolderID IN ('', 'ccccc')) <
                (o.FundHolderID IS NULL OR o.FundHolderID IN ('', 'ccccc'))
            ) OR (
                (b.FundHolderID IS NULL OR b.FundHolderID IN ('', 'ccccc')) =
                (o.FundHolderID IS NULL OR o.FundHolderID IN ('', 'ccccc'))
                AND b.opid < o.opid
            )";

        $this->execute('DROP TABLE IF EXISTS ' . self::BACKUP);
        $this->execute('CREATE TABLE ' . self::BACKUP . ' AS SELECT DISTINCT o.* ' . $loser);
        $this->execute('DELETE o ' . $loser);

        $this->createIndex(self::INDEX, 'opdata', ['hospcode', 'seq'], true);
    }

    public function down()
    {
        $this->dropIndex(self::INDEX, 'opdata');

        if ($this->db->getTableSchema(self::BACKUP, true) !== null) {
            $this->execute('INSERT INTO opdata SELECT * FROM ' . self::BACKUP);
            $this->dropTable(self::BACKUP);
        }
    }
}

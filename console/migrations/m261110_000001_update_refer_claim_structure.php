<?php

use yii\db\Migration;

/**
 * Align refer_claim with the current referral claim form.
 */
class m261110_000001_update_refer_claim_structure extends Migration
{
    public function safeUp()
    {
        $table = $this->db->getTableSchema('{{%refer_claim}}', true);
        if ($table === null) {
            throw new \RuntimeException('The refer_claim table does not exist.');
        }

        $seqAn = $table->getColumn('seq_an');
        $vnAn = $table->getColumn('vn_an');
        if ($seqAn === null && $vnAn !== null) {
            $this->renameColumn('{{%refer_claim}}', 'vn_an', 'seq_an');
        } elseif ($seqAn !== null && $vnAn !== null) {
            throw new \RuntimeException('Both seq_an and vn_an exist; resolve the column data before migrating.');
        } elseif ($seqAn === null) {
            throw new \RuntimeException('Neither seq_an nor vn_an exists in refer_claim.');
        }

        $columns = [
            'p_visit_type' => $this->string(255)->null()->comment('ประเภทการเข้ารับบริการ IPD/OPD'),
            'discharge_date_ipd' => $this->date()->null()->comment('วันที่จำหน่ายผู้ป่วยใน'),
            'adj_rw' => $this->string(255)->null()->comment('adj.rw มีเฉพาะ ipd'),
            'pay_total' => $this->decimal(12, 2)->notNull()->defaultValue(0)->comment('จำนวนจ่าย'),
        ];
        foreach ($columns as $name => $definition) {
            if ($this->db->getTableSchema('{{%refer_claim}}', true)->getColumn($name) === null) {
                $this->addColumn('{{%refer_claim}}', $name, $definition);
            }
        }

        $index = $this->findIndex('uk_claim');
        if (
            $index === null
            || !$index->isUnique
            || $index->columnNames !== ['hospcode', 'refer_number', 'serv_hospcode', 'hn', 'seq_an', 'dateserv']
        ) {
            if ($index !== null) {
                $this->dropIndex('uk_claim', '{{%refer_claim}}');
            }
            $this->createIndex(
                'uk_claim',
                '{{%refer_claim}}',
                ['hospcode', 'refer_number', 'serv_hospcode', 'hn', 'seq_an', 'dateserv'],
                true
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('{{%refer_claim}}', true) === null) {
            return;
        }

        $index = $this->findIndex('uk_claim');
        if ($index !== null) {
            $this->dropIndex('uk_claim', '{{%refer_claim}}');
        }
        $this->createIndex(
            'uk_claim',
            '{{%refer_claim}}',
            ['hospcode', 'refer_number', 'serv_hospcode', 'hn', 'dateserv'],
            true
        );

        foreach (['pay_total', 'adj_rw', 'discharge_date_ipd', 'p_visit_type'] as $column) {
            if ($this->db->getTableSchema('{{%refer_claim}}', true)->getColumn($column) !== null) {
                $this->dropColumn('{{%refer_claim}}', $column);
            }
        }

        $table = $this->db->getTableSchema('{{%refer_claim}}', true);
        if ($table->getColumn('seq_an') !== null && $table->getColumn('vn_an') === null) {
            $this->renameColumn('{{%refer_claim}}', 'seq_an', 'vn_an');
        }
    }

    private function findIndex(string $name)
    {
        $tableName = $this->db->getSchema()->getRawTableName('{{%refer_claim}}');
        foreach ($this->db->getSchema()->getTableIndexes($tableName, true) as $index) {
            if ($index->name === $name) {
                return $index;
            }
        }

        return null;
    }
}

<?php

use yii\db\Migration;

/**
 * Require VN/AN values to be unique within each service hospital.
 */
class m261110_000003_unique_seq_an_per_serv_hospcode extends Migration
{
    private const INDEX_NAME = 'uk_refer_claim_serv_seq_an';

    public function safeUp()
    {
        $table = $this->db->getTableSchema('{{%refer_claim}}', true);
        if ($table === null) {
            throw new \RuntimeException('The refer_claim table does not exist.');
        }
        if ($table->getColumn('seq_an') === null || $table->getColumn('serv_hospcode') === null) {
            throw new \RuntimeException('The seq_an or serv_hospcode column does not exist.');
        }

        $this->update('{{%refer_claim}}', ['seq_an' => null], "TRIM([[seq_an]]) = ''");

        $hasDuplicates = (new \yii\db\Query())
            ->select(['serv_hospcode', 'seq_an'])
            ->from('{{%refer_claim}}')
            ->where(['not', ['seq_an' => null]])
            ->groupBy(['serv_hospcode', 'seq_an'])
            ->having('COUNT(*) > 1')
            ->limit(1)
            ->exists($this->db);
        if ($hasDuplicates) {
            throw new \RuntimeException(
                'Cannot add unique VN/AN constraint: duplicate service/VN-AN pair(s) already exist.'
            );
        }

        if ($this->findIndex() === null) {
            $this->createIndex(
                self::INDEX_NAME,
                '{{%refer_claim}}',
                ['serv_hospcode', 'seq_an'],
                true
            );
        }
    }

    public function safeDown()
    {
        if ($this->findIndex() !== null) {
            $this->dropIndex(self::INDEX_NAME, '{{%refer_claim}}');
        }
    }

    private function findIndex()
    {
        $tableName = $this->db->getSchema()->getRawTableName('{{%refer_claim}}');
        foreach ($this->db->getSchema()->getTableIndexes($tableName, true) as $index) {
            if ($index->name === self::INDEX_NAME) {
                return $index;
            }
        }

        return null;
    }
}

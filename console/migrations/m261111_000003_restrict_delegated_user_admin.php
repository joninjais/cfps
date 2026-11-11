<?php

use yii\db\Migration;

class m261111_000003_restrict_delegated_user_admin extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $manageUsers = $auth->getPermission('manageUsers');
        $accessApplication = $auth->getPermission('accessApplication');
        $provincialAdmin = $auth->getRole('provincialAdmin');
        if ($manageUsers === null || $accessApplication === null) {
            throw new \RuntimeException('ไม่พบ permission ที่จำเป็นสำหรับผู้ดูแลจัดการผู้ใช้');
        }
        if ($provincialAdmin === null) {
            throw new \RuntimeException('ไม่พบ role ผู้ดูแล สสจ.');
        }

        $this->createTable('{{%user_manager_role_migration}}', [
            'userid' => $this->integer()->notNull(),
            'previous_requested_role' => $this->string(30)->null(),
            'PRIMARY KEY ([[userid]])',
        ]);
        $role = $auth->getRole('userManager');
        if ($role === null) {
            $role = $auth->createRole('userManager');
            $role->description = 'ผู้ดูแลจัดการผู้ใช้ที่ได้รับมอบหมายจาก สสจ.';
            $auth->add($role);
        }
        foreach ([$accessApplication, $manageUsers] as $permission) {
            if (!$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }
        foreach ($auth->getChildren('userManager') as $child) {
            if (!in_array($child->name, ['accessApplication', 'manageUsers'], true)) {
                $auth->removeChild($role, $child);
            }
        }

        foreach ($auth->getUserIdsByRole('provincialAdmin') as $userId) {
            $user = (new \yii\db\Query())
                ->select(['hoscode', 'requested_role'])
                ->from('{{%user}}')
                ->where(['userid' => $userId])
                ->one($this->db);
            if ($user === false) {
                throw new \RuntimeException('ไม่พบข้อมูลผู้ใช้ที่ได้รับสิทธิ์ผู้ดูแลจังหวัด');
            }
            if (trim((string)$user['hoscode']) === '00064') {
                continue;
            }

            $this->insert('{{%user_manager_role_migration}}', [
                'userid' => $userId,
                'previous_requested_role' => $user['requested_role'],
            ]);
            $auth->revokeAll((string)$userId);
            $facilityRole = $auth->getRole($this->facilityRoleName((string)$user['hoscode']));
            if ($facilityRole === null
                || $auth->assign($facilityRole, (string)$userId) === null
                || $auth->assign($role, (string)$userId) === null) {
                throw new \RuntimeException('ไม่สามารถจำกัดสิทธิ์ผู้ดูแลที่ได้รับมอบหมายได้');
            }
            $this->update('{{%user}}', ['requested_role' => $facilityRole->name], ['userid' => $userId]);
        }
    }

    private function facilityRoleName(string $hoscode): string
    {
        $cupcode = (new \yii\db\Query())
            ->select('cupcode')
            ->from('chospital2')
            ->where(['hospcode' => trim($hoscode)])
            ->scalar($this->db);

        return trim((string)$cupcode) === trim($hoscode) ? 'hospitalStaff' : 'pcuStaff';
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('userManager');
        if ($role === null || $this->db->schema->getTableSchema('{{%user_manager_role_migration}}', true) === null) {
            throw new \RuntimeException('ไม่พบข้อมูลที่จำเป็นสำหรับย้อนกลับการจำกัดสิทธิ์ผู้ดูแล');
        }

        $provincialAdmin = $auth->getRole('provincialAdmin');
        if ($provincialAdmin === null) {
            throw new \RuntimeException('ไม่พบ role ผู้ดูแล สสจ.');
        }

        $migratedUsers = (new \yii\db\Query())
            ->from('{{%user_manager_role_migration}}')
            ->all($this->db);
        $migratedUserIds = array_map('strval', array_column($migratedUsers, 'userid'));
        $untrackedAssignments = array_diff(
            array_map('strval', $auth->getUserIdsByRole('userManager')),
            $migratedUserIds
        );
        if ($untrackedAssignments !== []) {
            throw new \RuntimeException(
                'พบผู้ดูแลจัดการผู้ใช้ที่ไม่ได้ถูกลดสิทธิ์โดย migration นี้ กรุณาจัดการ assignment ก่อน rollback'
            );
        }

        foreach ($migratedUsers as $migratedUser) {
            $userId = (string)$migratedUser['userid'];
            $auth->revokeAll($userId);
            if ($auth->assign($provincialAdmin, $userId) === null) {
                throw new \RuntimeException('ไม่สามารถคืน role ผู้ดูแลเดิมได้');
            }
            $this->update(
                '{{%user}}',
                ['requested_role' => $migratedUser['previous_requested_role']],
                ['userid' => $userId]
            );
        }

        $auth->removeChildren($role);
        $auth->remove($role);
        $this->dropTable('{{%user_manager_role_migration}}');
    }
}

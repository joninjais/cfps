<?php

use yii\db\Migration;
use yii\rbac\Role;

class m261111_000002_add_access_control_rbac extends Migration
{
    private const PERMISSIONS = [
        'accessApplication' => 'เข้าใช้งานระบบ',
        'manageOwnFacilityData' => 'จัดการข้อมูลหน่วยบริการตนเอง',
        'viewNetworkData' => 'ดูข้อมูลหน่วยบริการใน CUP เดียวกัน',
        'manageUsers' => 'จัดการบัญชีและอนุมัติผู้ใช้งาน',
        'manageSqlCodes' => 'จัดการคำสั่ง SQL',
        'manageReferClaims' => 'จัดการข้อมูล refer claim',
        'manageInsurancePolicies' => 'จัดการข้อมูลกรมธรรม์ทุกหน่วยบริการ',
        'manageFwfData' => 'จัดการและนำเข้าข้อมูลกองทุน',
        'manageLockState' => 'จัดการสถานะล็อกข้อมูล',
    ];

    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'approval_status', $this->string(20)->notNull()->defaultValue('approved'));
        $this->addColumn('{{%user}}', 'requested_role', $this->string(30)->null());
        $this->addColumn('{{%user}}', 'approved_by', $this->integer()->null());
        $this->addColumn('{{%user}}', 'approved_at', $this->integer()->null());

        $auth = Yii::$app->authManager;
        $permissions = [];
        foreach (self::PERMISSIONS as $name => $description) {
            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
            $permissions[$name] = $permission;
        }

        $admin = $auth->createRole('provincialAdmin');
        $admin->description = 'ผู้ดูแลระบบ สสจ.';
        $auth->add($admin);

        $hospital = $auth->createRole('hospitalStaff');
        $hospital->description = 'เจ้าหน้าที่โรงพยาบาล';
        $auth->add($hospital);

        $pcu = $auth->createRole('pcuStaff');
        $pcu->description = 'เจ้าหน้าที่โรงพยาบาลส่งเสริมสุขภาพตำบล';
        $auth->add($pcu);

        foreach ($permissions as $permission) {
            $auth->addChild($admin, $permission);
        }
        $auth->addChild($hospital, $permissions['accessApplication']);
        $auth->addChild($hospital, $permissions['manageOwnFacilityData']);
        $auth->addChild($hospital, $permissions['viewNetworkData']);
        $auth->addChild($pcu, $permissions['accessApplication']);
        $auth->addChild($pcu, $permissions['manageOwnFacilityData']);

        $users = (new \yii\db\Query())
            ->select(['userid', 'hoscode'])
            ->from('{{%user}}')
            ->all($this->db);

        foreach ($users as $user) {
            $hoscode = trim((string)$user['hoscode']);
            if ($hoscode === '00064') {
                $roleName = 'provincialAdmin';
            } else {
                $cupcode = (new \yii\db\Query())
                    ->select('cupcode')
                    ->from('chospital2')
                    ->where(['hospcode' => $hoscode])
                    ->scalar($this->db);
                $roleName = trim((string)$cupcode) === $hoscode ? 'hospitalStaff' : 'pcuStaff';
            }

            $role = $auth->getRole($roleName);
            if ($role instanceof Role) {
                $auth->assign($role, (string)$user['userid']);
                if ($roleName !== 'provincialAdmin') {
                    $this->update(
                        '{{%user}}',
                        ['requested_role' => $roleName],
                        ['userid' => $user['userid']]
                    );
                }
            }
        }
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (['provincialAdmin', 'hospitalStaff', 'pcuStaff'] as $name) {
            $role = $auth->getRole($name);
            if ($role !== null) {
                $auth->removeChildren($role);
                $auth->remove($role);
            }
        }

        foreach (array_keys(self::PERMISSIONS) as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }

        $this->dropColumn('{{%user}}', 'approved_at');
        $this->dropColumn('{{%user}}', 'approved_by');
        $this->dropColumn('{{%user}}', 'requested_role');
        $this->dropColumn('{{%user}}', 'approval_status');
    }
}

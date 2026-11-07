<?php

use yii\db\Migration;

/**
 * Migration:
 * 1. Add fname / lname columns to {{%user}} table
 * 2. Migrate existing fullname → fname (keep fullname as-is for safety, DBA can drop later)
 * 3. Add logout_at, fail_reason, session_id, action to {{%login_history}} for legal compliance
 */
class m260316_100000_user_fname_lname_and_login_history_enhance extends Migration
{
    public function safeUp()
    {
        // --- user table ---
        $this->addColumn('{{%user}}', 'fname', $this->string(100)->null()->after('userid')->comment('ชื่อ'));
        $this->addColumn('{{%user}}', 'lname', $this->string(100)->null()->after('fname')->comment('นามสกุล'));

        // Migrate existing fullname: split by first space
        $this->execute("
            UPDATE {{%user}}
            SET
                fname = TRIM(SUBSTRING_INDEX(fullname, ' ', 1)),
                lname = TRIM(SUBSTRING(fullname, LOCATE(' ', fullname) + 1))
            WHERE fullname IS NOT NULL AND fullname <> ''
        ");

        // --- login_history table ---
        $this->addColumn('{{%login_history}}', 'logout_at',   $this->integer()->null()->after('login_at')->comment('เวลา logout (Unix timestamp)'));
        $this->addColumn('{{%login_history}}', 'fail_reason', $this->string(255)->null()->after('status')->comment('เหตุผลที่ล้มเหลว'));
        $this->addColumn('{{%login_history}}', 'session_id',  $this->string(64)->null()->after('fail_reason')->comment('Session ID (hashed)'));
        $this->addColumn('{{%login_history}}', 'action',      $this->string(20)->notNull()->defaultValue('login')->after('session_id')->comment('login/logout'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'fname');
        $this->dropColumn('{{%user}}', 'lname');

        $this->dropColumn('{{%login_history}}', 'logout_at');
        $this->dropColumn('{{%login_history}}', 'fail_reason');
        $this->dropColumn('{{%login_history}}', 'session_id');
        $this->dropColumn('{{%login_history}}', 'action');
    }
}

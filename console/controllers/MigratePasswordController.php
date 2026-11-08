<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use frontend\models\User;

/**
 * Migrate legacy plaintext passwords to secure bcrypt hashes.
 *
 * Usage:
 *   php yii migrate-password/run          # dry-run (preview only)
 *   php yii migrate-password/run --apply  # apply changes
 */
class MigratePasswordController extends Controller
{
    /**
     * @var bool Apply changes (false = dry-run)
     */
    public $apply = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apply']);
    }

    /**
     * Migrate all plaintext passwords to bcrypt hash with Cfps@ prefix.
     */
    public function actionRun()
    {
        // Load frontend config so Yii::$app->security is available
        $this->stdout("=== Password Migration Tool ===\n");
        $this->stdout($this->apply ? "MODE: APPLY\n" : "MODE: DRY-RUN (use --apply to save changes)\n");
        $this->stdout("---\n");

        $users = User::find()->all();
        $total   = count($users);
        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            /** @var User $user */
            if (preg_match('/^\$2y\$|^\$argon2/i', (string) $user->pass)) {
                $this->stdout("[SKIP]   {$user->user} — already hashed\n");
                $skipped++;
                continue;
            }

            $plainPass   = (string) $user->pass;           // เก็บค่าเดิม
            $newPlain    = 'Cfps@' . $plainPass;           // prefix ตามนโยบาย
            $newHash     = Yii::$app->security->generatePasswordHash($newPlain);

            if ($this->apply) {
                Yii::$app->db->createCommand()
                    ->update('{{%user}}', ['pass' => $newHash], ['userid' => $user->userid])
                    ->execute();
                $this->stdout("[UPDATE] {$user->user} — migrated to hash\n");
            } else {
                $this->stdout("[DRY]    {$user->user} — would be migrated (plain=\"{$plainPass}\" → hash)\n");
            }
            $updated++;
        }

        $this->stdout("---\n");
        $this->stdout("Total: {$total} | Updated: {$updated} | Skipped (already hashed): {$skipped}\n");
        if (!$this->apply && $updated > 0) {
            $this->stdout("Run with --apply to apply changes.\n");
        }

        return ExitCode::OK;
    }
}

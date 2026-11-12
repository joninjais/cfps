<?php

namespace frontend\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Query;

class RegistrationWindow
{
    public static function current(bool $lock = false): array
    {
        $db = Yii::$app->db;
        $query = (new Query())->from('{{%registration_window}}')->where(['id' => 1]);
        $command = $query->createCommand($db);
        if ($lock && $db->driverName !== 'sqlite') {
            $params = $command->params;
            $command->setSql($command->getSql() . ' FOR UPDATE')->bindValues($params);
        }
        $row = $command->queryOne();
        if ($row === false) {
            throw new InvalidConfigException('Registration window record is missing; run migrations.');
        }
        return $row;
    }

    public static function isOpen(array $window, ?string $token = null): bool
    {
        return is_string($window['token']) && $window['token'] !== ''
            && (int)$window['expires_at'] > time()
            && ($token === null || hash_equals($window['token'], $token));
    }

    public static function open(int $minutes): void
    {
        self::current();
        Yii::$app->db->createCommand()->update('{{%registration_window}}', [
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => time() + $minutes * 60,
        ], ['id' => 1])->execute();
    }

    public static function close(): void
    {
        self::current();
        Yii::$app->db->createCommand()->update('{{%registration_window}}', [
            'token' => null,
            'expires_at' => null,
        ], ['id' => 1])->execute();
    }
}

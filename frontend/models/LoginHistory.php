<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Login history model — stores every login/logout attempt with IP, device and timestamp.
 *
 * @property int         $id
 * @property string      $user
 * @property string      $hoscode
 * @property string      $ip_address
 * @property string      $user_agent
 * @property string      $device_type
 * @property int         $login_at
 * @property string      $status
 * @property int|null    $logout_at
 * @property string|null $fail_reason
 * @property string|null $session_id   SHA-256 hash of the PHP session ID
 * @property string      $action       'login' | 'logout'
 */
class LoginHistory extends ActiveRecord
{
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_MOBILE  = 'mobile';
    const DEVICE_TABLET  = 'tablet';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%login_history}}';
    }

    /**
     * Record a login attempt (success or failure).
     *
     * @param string      $user
     * @param string|null $hoscode
     * @param string      $status     'success' | 'failed'
     * @param string|null $failReason Human-readable reason for failure (stored for failed logins)
     */
    public static function record(
        string $user,
        string $hoscode = null,
        string $status = 'success',
        string $failReason = null
    ): void {
        $request   = Yii::$app->request;
        $userAgent = (string) $request->userAgent;
        $sessionId = null;
        try {
            $rawId = Yii::$app->session->getId();
            if ($rawId !== null && $rawId !== '') {
                $sessionId = hash('sha256', $rawId);
            }
        } catch (\Exception $e) {
            // session not available
        }

        $record              = new self();
        $record->user        = $user;
        $record->hoscode     = $hoscode;
        $record->ip_address  = $request->userIP;
        $record->user_agent  = $userAgent;
        $record->device_type = self::detectDevice($userAgent);
        $record->login_at    = time();
        $record->status      = $status;
        $record->action      = 'login';
        $record->session_id  = $sessionId;
        $record->fail_reason = ($status === 'failed') ? $failReason : null;
        $record->save(false);
    }

    /**
     * Record logout: update the matching login record with logout_at timestamp.
     * Matches by hashed session ID so each session's logout is tracked precisely.
     *
     * @param string $user
     */
    public static function recordLogout(string $user): void
    {
        try {
            $rawId = Yii::$app->session->getId();
            if ($rawId === null || $rawId === '') {
                return;
            }
            $sessionId = hash('sha256', $rawId);

            Yii::$app->db->createCommand()
                ->update(
                    '{{%login_history}}',
                    ['logout_at' => time(), 'action' => 'logout'],
                    ['user' => $user, 'session_id' => $sessionId, 'action' => 'login']
                )
                ->execute();
        } catch (\Exception $e) {
            // non-critical — do not break logout flow
        }
    }

    /**
     * Detect device type from User-Agent string.
     */
    public static function detectDevice(string $ua): string
    {
        $ua = strtolower($ua);
        if (preg_match('/tablet|ipad|kindle|playbook|silk/i', $ua)) {
            return self::DEVICE_TABLET;
        }
        if (preg_match('/mobile|android|iphone|ipod|blackberry|opera mini|windows phone/i', $ua)) {
            return self::DEVICE_MOBILE;
        }
        return self::DEVICE_DESKTOP;
    }

    /**
     * Human-readable login time.
     */
    public function getLoginAtFormatted(): string
    {
        return date('d/m/Y H:i:s', $this->login_at);
    }

    /**
     * Human-readable logout time (or '-' if not yet logged out).
     */
    public function getLogoutAtFormatted(): string
    {
        return $this->logout_at ? date('d/m/Y H:i:s', $this->logout_at) : '-';
    }
}

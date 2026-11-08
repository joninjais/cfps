<?php

namespace frontend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * โมเดลนี้แทนตาราง "{{%user}}" ในฐานข้อมูล
 *
 * @property int    $userid      ไอดีผู้ใช้
 * @property string $user        ชื่อผู้ใช้
 * @property string $pass        รหัสผ่าน
 * @property string $fname       ชื่อ
 * @property string $lname       นามสกุล
 * @property string $orgname     ชื่อหน่วยบริการ
 * @property string $hoscode     รหัสหน่วยบริการ
 * @property string $phonenumber เบอร์โทรศัพท์
 * @property string $email       อีเมล
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    public $newPassword;
    public $newPasswordConfirm;

    /** ชื่อ-นามสกุล เต็ม (virtual) */
    public function getDisplayName(): string
    {
        $full = trim(($this->fname ?? '') . ' ' . ($this->lname ?? ''));
        return $full !== '' ? $full : (string)($this->orgname ?? $this->user);
    }

    /**
     * {@inheritdoc}
     * คืนชื่อตารางในฐานข้อมูล
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     * กำหนดกฎการตรวจสอบข้อมูล (Validation Rules)
     */
    public function rules()
    {
        return [
            [['user', 'fname', 'lname', 'hoscode', 'orgname'], 'required'],
            [['user'], 'string', 'max' => 50],
            [['pass', 'orgname', 'email'], 'string', 'max' => 255],
            [['fname', 'lname'], 'string', 'max' => 100],
            [['hoscode'], 'string', 'max' => 5],
            [['phonenumber'], 'string', 'max' => 10],
            [['user'], 'unique'],
            [['email'], 'email'],
            [['fname', 'lname', 'phonenumber', 'email'], 'default', 'value' => ''],  // allow empty
            // เงื่อนไขรหัสผ่านใหม่
            [
                'newPassword',
                'match',
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
                'message' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลขอย่างน้อย 1 ตัว'
            ],
            [['newPassword'], 'string', 'min' => 8],
            [
                'newPasswordConfirm',
                'compare',
                'compareAttribute' => 'newPassword',
                'message' => 'รหัสผ่านและการยืนยันต้องตรงกัน'
            ],
            [['must_change_password'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     * กำหนด label ของแต่ละฟิลด์
     */
    public function attributeLabels()
    {
        return [
            'userid'              => 'ไอดี',
            'user'                => 'ชื่อผู้ใช้',
            'pass'                => 'รหัสผ่าน',
            'fname'               => 'ชื่อ',
            'lname'               => 'นามสกุล',
            'orgname'             => 'ชื่อหน่วยบริการ',
            'hoscode'             => 'รหัสสถานพยาบาล',
            'phonenumber'         => 'เบอร์โทรศัพท์',
            'email'               => 'อีเมล',
            'newPassword'         => 'รหัสผ่านใหม่',
            'newPasswordConfirm'  => 'ยืนยันรหัสผ่าน',
            'must_change_password'=> 'ต้องเปลี่ยนรหัสผ่าน',
        ];
    }

    /**
     * @inheritdoc
     * ค้นหาผู้ใช้จาก userid
     */
    public static function findIdentity($id)
    {
        return static::findOne(['userid' => $id]);
    }

    /**
     * @inheritdoc
     * ไม่รองรับการค้นหาด้วย access token
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     * คืนค่า userid ของผู้ใช้
     */
    public function getId()
    {
        return $this->userid;
    }

    /**
     * @inheritdoc
     * คืนค่า auth_key (ถ้ามี)
     */
    public function getAuthKey()
    {
        return property_exists($this, 'auth_key') ? $this->auth_key : null;
    }

    /**
     * @inheritdoc
     * ตรวจสอบความถูกต้องของ auth_key
     */
    public function validateAuthKey($authKey)
    {
        $current = $this->getAuthKey();
        return $current !== null && hash_equals($current, (string) $authKey);
    }

    /**
     * ค้นหาผู้ใช้จากชื่อผู้ใช้ (username)
     *
     * @param string $username ชื่อผู้ใช้
     * @return static|null คืนอ็อบเจกต์ User หรือ null ถ้าไม่พบ
     */
    public static function findByUsername($username)
    {
        return static::findOne(['user' => $username]);
    }

    /**
     * ตรวจสอบรหัสผ่านที่กรอก (รองรับทั้งรูปแบบใหม่และเก่า)
     *
     * รูปแบบที่รองรับ:
     *  - "Cfps@xxxx"  — รหัสผ่านมาตรฐานใหม่ (ผู้ใช้กรอกแบบมี prefix)
     *  - "xxxx"       — รหัสผ่านเก่า (จะเติม prefix ให้อัตโนมัติเมื่อตรวจสอบ)
     *
     * @param string $password รหัสผ่านที่กรอก
     * @return bool คืนค่า true ถ้ารหัสผ่านถูกต้อง
     */
    public function validatePassword($password)
    {
        if (empty($this->pass) || !is_string($password)) {
            return false;
        }

        if ($this->isPasswordHash($this->pass)) {
            return Yii::$app->security->validatePassword($password, $this->pass);
        }

        // Legacy plaintext — auto-upgrade to bcrypt on success
        $legacyMatched = hash_equals((string) $this->pass, (string) $password);

        if ($legacyMatched) {
            $this->pass = Yii::$app->security->generatePasswordHash($password);
            $this->save(false, ['pass']);
        }

        return $legacyMatched;
    }

    /**
     * ตั้งค่ารหัสผ่าน
     *
     * @param string $password รหัสผ่านใหม่
     */
    public function setPassword($password)
    {
        $this->pass = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * ตรวจสอบว่าค่าเป็นรหัสผ่านที่ถูก hash แล้วหรือไม่
     *
     * @return bool
     */
    private function isPasswordHash($value)
    {
        return is_string($value)
            && preg_match('/^\$2y\$|^\$argon2/i', $value) === 1;
    }
}

<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\db\Query;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $passwordConfirm;
    public $phonenumber;
    public $fname;
    public $lname;
    public $hoscode;
    public $requestedRole;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'user', 'message' => 'ชื่อผู้ใช้นี้ถูกใช้แล้ว'],
            ['username', 'string', 'min' => 2, 'max' => 50],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'email', 'message' => 'อีเมลนี้ถูกใช้แล้ว'],

            ['password', 'required'],
            ['password', 'match', 'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', 'message' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร ประกอบด้วยตัวพิมพ์ใหญ่ ตัวพิมพ์เล็ก และตัวเลข'],
            ['passwordConfirm', 'required'],
            ['passwordConfirm', 'compare', 'compareAttribute' => 'password', 'message' => 'รหัสผ่านและการยืนยันต้องตรงกัน'],
            ['phonenumber', 'trim'],
            ['phonenumber', 'required'],
            ['phonenumber', 'string', 'max' => 10],
            [['fname', 'lname'], 'trim'],
            [['fname', 'lname', 'hoscode', 'requestedRole'], 'required'],
            [['fname', 'lname'], 'string', 'max' => 100],
            ['hoscode', 'match', 'pattern' => '/^\d{5}$/D', 'message' => 'รหัสหน่วยบริการต้องเป็นตัวเลข 5 หลัก'],
            ['requestedRole', 'in', 'range' => ['hospitalStaff', 'pcuStaff']],
            ['hoscode', 'validateFacility'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'ชื่อผู้ใช้',
            'email' => 'อีเมล',
            'password' => 'รหัสผ่าน',
            'passwordConfirm' => 'ยืนยันรหัสผ่าน',
            'phonenumber' => 'เบอร์โทรศัพท์',
            'fname' => 'ชื่อ',
            'lname' => 'นามสกุล',
            'hoscode' => 'รหัสหน่วยบริการ',
            'requestedRole' => 'ประเภทหน่วยบริการ',
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }

        $role = Yii::$app->facilityAccess->expectedStaffRole((string)$this->hoscode);
        $this->requestedRole = $role ?? '';

        return true;
    }

    public function validateFacility($attribute)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }

        $facility = (new Query())
            ->select(['hospcode', 'hosname'])
            ->from('chospital2')
            ->where(['hospcode' => $this->$attribute])
            ->one();

        if ($facility === false || $this->$attribute === '00064') {
            $this->addError($attribute, 'ไม่พบหน่วยบริการหรือหน่วยบริการนี้ไม่สามารถสมัครเป็นบัญชีทั่วไปได้');
            return;
        }

        $expectedRole = Yii::$app->facilityAccess->expectedStaffRole($this->$attribute);
        if ($expectedRole === null) {
            $this->addError($attribute, 'ไม่สามารถระบุประเภทหน่วยบริการจากข้อมูล CUP ได้');
        } elseif ($this->requestedRole !== $expectedRole) {
            $this->addError('requestedRole', 'ประเภทบัญชีไม่ตรงกับข้อมูลหน่วยบริการ กรุณาตรวจสอบรหัสหน่วยบริการ');
        }
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->user = $this->username;
        $user->email = $this->email;
        $user->phonenumber = $this->phonenumber;
        $user->fname = $this->fname;
        $user->lname = $this->lname;
        $user->hoscode = $this->hoscode;
        $user->orgname = Yii::$app->facilityAccess->facilityName((string)$this->hoscode) ?? '';
        $user->approval_status = User::APPROVAL_PENDING;
        $user->requested_role = $this->requestedRole;
        $user->setPassword($this->password);

        if (!$user->save()) {
            Yii::error($user->getErrors(), __METHOD__);
            $this->addError('username', 'ไม่สามารถส่งคำขอสมัครได้ กรุณาตรวจสอบข้อมูลและลองอีกครั้ง');
            return null;
        }

        return $user;
    }
}

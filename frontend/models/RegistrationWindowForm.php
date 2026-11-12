<?php

namespace frontend\models;

use yii\base\Model;

class RegistrationWindowForm extends Model
{
    public $duration = 30;
    public $unit = 'minutes';

    public function rules()
    {
        return [
            [['duration', 'unit'], 'required'],
            ['duration', 'integer', 'min' => 1, 'max' => 525600],
            ['unit', 'in', 'range' => ['minutes', 'hours']],
            ['duration', 'validateMaximum'],
        ];
    }

    public function validateMaximum($attribute)
    {
        if (!$this->hasErrors() && $this->minutes() > 525600) {
            $this->addError($attribute, 'ระยะเวลาต้องไม่เกิน 1 ปี (525,600 นาที)');
        }
    }

    public function minutes(): int
    {
        return (int)$this->duration * ($this->unit === 'hours' ? 60 : 1);
    }

    public function attributeLabels()
    {
        return ['duration' => 'ระยะเวลา', 'unit' => 'หน่วย'];
    }
}

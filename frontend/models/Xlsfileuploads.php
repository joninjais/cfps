<?php

namespace frontend\models;

use Yii;
use \yii\web\UploadedFile;

/**
 * This is the model class for table "{{%xlsfileuploads}}".
 *
 * @property int $xlsid
 * @property string $exsfile
 * @property string $hoscode
 * @property string $user
 */
class Xlsfileuploads extends \yii\db\ActiveRecord
{
    public $upload_foler ='uploads';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%xlsfileuploads}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hoscode', 'user','typefileupload'], 'required'],
            [['uploaddatetime	'], 'safe'],
            [['startdatetime	'], 'safe'],
            [['enddatetime	'], 'safe'],
            [['importstatus'], 'safe'],
            [['hoscode'], 'string', 'max' => 5],
            [['user'], 'string', 'max' => 50],
            [['typefileupload'], 'string', 'max' => 50],
            [['exsfile'], 'file',
            'skipOnEmpty' => true,
            'extensions' => 'xls,xlsx'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'xlsid' => 'Xlsid',
            'uploaddatetime' => 'วันที่เวลาที่อัพโหลดไฟล์',
            'typefileupload' => 'ประเภทไฟล์',
            'exsfile' => 'ไฟล์เอ็กเซล',
            'hoscode' => 'รหัสสถานบริการ',
            'user' => 'ชื่อผู้ใช้',
            'importstatus' => 'สถานะการนำเข้า',
        ];
    }
    public function upload($model,$attribute)
    {
        $exsfile  = UploadedFile::getInstance($model, $attribute);
          $path = $this->getUploadPath();
        if ($this->validate() && $exsfile !== null) {
    
            $fileName = md5($exsfile->baseName.time()) . '.' . $exsfile->extension;
            //$fileName = $photo->baseName . '.' . $photo->extension;
            if($exsfile->saveAs($path.$fileName)){
              return $fileName;
            }
        }
        return $model->isNewRecord ? false : $model->getOldAttribute($attribute);
    }
    
    public function getUploadPath(){
      return Yii::getAlias('@webroot').'/'.$this->upload_foler.'/';
    }
    
    public function getUploadUrl(){
      return Yii::getAlias('@web').'/'.$this->upload_foler.'/';
    }
    public function getPhotoViewer(){
        return empty($this->exsfile) ? Yii::getAlias('@web').'/img/none.png' : $this->getUploadUrl().$this->exsfile;
      }


}

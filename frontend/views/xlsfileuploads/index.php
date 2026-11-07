<?php

use yii\helpers\Html;
use yii\grid\GridView;
use frontend\models\User;


$session = Yii::$app->session;
$checklogin = User::findOne(['user'=>$session['user'],]);
if(!$checklogin){
    echo "<script>window.location='index.php?r=site/login';</script>";
}
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\XlsfileuploadsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'นำเข้าข้อมูล';
$this->params['breadcrumbs'][] = $this->title;
//echo date('Y-m-d,h:i:s');

 function dateDiv($t1,$t2){ // ส่งวันที่ที่ต้องการเปรียบเทียบ ในรูปแบบ มาตรฐาน 2006-03-27 21:39:12

    $Time1 = strtotime((string)$t1);
    $Time2 = strtotime((string)$t2);
    if ($Time1 === false || $Time2 === false) {
        return ["D" => 0, "H" => 0, "M" => 0, "S" => 0];
    }

    $TimeDiv=abs($Time2-$Time1);

    $Time["D"]=intval($TimeDiv/86400); // จำนวนวัน
    $Time["H"]=intval(($TimeDiv%86400)/3600); // จำนวน ชั่วโมง
    $Time["M"]=intval((($TimeDiv%86400)%3600)/60); // จำนวน นาที
    $Time["S"]=intval(((($TimeDiv%86400)%3600)%60)); // จำนวน วินาที
 return $Time;
}



 function splitTime($time){ // เวลาในรูปแบบ มาตรฐาน 2006-03-27 21:39:12 
$timeArr["Y"]= substr($time,2,2);
$timeArr["M"]= substr($time,5,2);
$timeArr["D"]= substr($time,8,2);
$timeArr["h"]= substr($time,11,2);
$timeArr["m"]= substr($time,14,2);
 $timeArr["s"]= substr($time,17,2);
return $timeArr;
}
/*
$xlsdata = (new \yii\db\Query())
->from('xlsfileuploads')
->where(['xlsid' => 18])
->one();
echo $file = Yii::getAlias('@webroot').'/'.'uploads'.'/'.$xlsdata['exsfile'];
*/
//echo $id;
?>
<div class="xlsfileuploads-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php // echo Html::a('uploads file', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            ['class' => 'yii\grid\ActionColumn',
            'template'=>'{delete}',
            'buttons' => [
                'delete' => function($url, $model){
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->xlsid], [
                        'class' => '',
                        'data' => [
                            'confirm' => 'โปรดยืนยันการลบไฟล์ EXCEL และข้อมูลในไฟล์ทั้งหมด!',
                            'method' => 'post',
                        ],
                    ]);
                }
            ]
            ],
            //'xlsid',
            //'exsfile',
            [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
                'attribute' => 'exsfile',
                'label' => 'ไฟล์เอ็กเซล',
                'format'=>'raw',
                'value'=>function($model){
  
                //  return $typefile=$model->importstatus;
                return Html::a($model->exsfile, Yii::getAlias('@web') . '/uploads/' . $model->exsfile);
              }//function
              ],
            'hoscode',
            'typefileupload',
            'user',
            'uploaddatetime',
            //'importstatus',
            [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
              'attribute' => 'importstatus',
              //'label' => 'importstatus',
              'format'=>'raw',
              'value'=>function($model){
               
                if(0==$model["importstatus"]) { 
                $typefile=$model->typefileupload;
                if($typefile=='op pp'){ return Html::a('<i class="glyphicon glyphicon-upload"></i>', ['/opdatainfo/import', 'id' => $model->xlsid]           , ['class' => 'btn btn-primary']);}
                if($typefile=='ip'){ return Html::a('<i class="glyphicon glyphicon-upload"></i>', ['/ipdatainfo/import', 'id' => $model->xlsid]           , ['class' => 'btn btn-primary']);}
                if($typefile=='fwfdata'){ return Html::a('<i class="glyphicon glyphicon-upload"></i>', ['/fwfdatabase/import', 'id' => $model->xlsid] , ['class' => 'btn btn-primary']);}
                if($typefile=='fwfhealth'){ return Html::a('<i class="glyphicon glyphicon-upload"></i>', ['/fwfhealth/import', 'id' => $model->xlsid] , ['class' => 'btn btn-primary']);}
                if($typefile=='insurance'){ return Html::a('<i class="glyphicon glyphicon-upload"></i>', ['/insurancepolicies/import', 'id' => $model->xlsid] , ['class' => 'btn btn-primary']);}   
                }else{
                  return '<span class="label label-success">นำเข้าข้อมูลแล้ว</span>';//ถ้า query มีค่าว่างต้องเช็คก่อน
              }
            }//function
            ],
            [ // แสดงข้อมูลออกเป็นสีตามเงื่อนไข
              'attribute' => 'importstatus',
              'label' => 'ใช้เวลานำเข้าข้อมูล',
              'format'=>'raw',
              'value'=>function($model){
                $t1=$model["startdatetime"];
                $t2=$model["enddatetime"];
                $time=dateDiv($t1,$t2);
                return $time['H'].' ชม. '.$time['M'].' น. '.$time['S'].' ว.';
            }//function
            ],
            [ // นำเข้าข้อมูลอีกครั้ง
              'attribute' => 'importstatus',
              'label' => 'นำเข้าข้อมูลอีกครั้ง',
              'format'=>'raw',
              'value'=>function($model){
              return Html::a('<i class="fa fa-fw fa-unlock"></i>', ['/xlsfileuploads/reimport', 'id' => $model->xlsid] , [
                'class' => 'btn btn-warning',
                'data' => ['method' => 'post', 'confirm' => 'ยืนยันการนำเข้าข้อมูลอีกครั้ง?'],
              ]);
            }//function
            ],

        ],
    ]); ?>
</div>

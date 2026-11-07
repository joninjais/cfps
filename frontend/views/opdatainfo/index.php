<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use frontend\models\User;
$session = Yii::$app->session;
$checklogin = User::findOne(['user'=>$session['user'],]);
if(!$checklogin){
    echo "<script>window.location='index.php?r=site/login';</script>";
}
/* @var $this yii\web\View */
/* @var $searchModel frontend\models\OpdatainfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Opdatas';
$this->params['breadcrumbs'][] = $this->title;

$session = Yii::$app->session;
$del="deletedata";
$chk="checkdata";

// $result = isset($result) ? $result : $result='';
// print_r($result);
// foreach ($result as $key => $val) {
//     echo $result[$key]['cid'];
//     echo "</br>";
//     echo $result[$key]['dateserv'];
//     echo "</br>";
    
// }
// echo $checklogin['hoscode'];
?>
<div class="opdata-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
        
        <?= Html::a('ปรับปรุงข้อมูล ', ['index','chk' => $chk],['class' => 'btn btn-info']) ?> 
        <div class="callout callout-danger">
        (กดปุ่ม ปรับปรุงข้อมูล แก้ติด C กรณีมีการอัปโหลดข้อมูลการรับบริการก่อนอัปโหลดข้อมูลบัตรประกันสุขภาพ) 
        </div>
        <?php 
        // if($session['hoscode']!='00064'){
        // echo Html::a('ลบทั้งหมด', ['index','del' => $del],['class' => 'btn btn-danger']);
        // }
        ?>

    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],        
        'panel'=>[
            'before'=>'ข้อมูล OPDATA',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
                    ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->opid], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'โปรดยืนยันการลบข้อมูล!',
                                'method' => 'post',
                            ],
                        ]);
                    }
                ]
            ],
            //'opid',
            'hospcode',
            'dateserv',
            'name',
            'fund',
            'cid',
            'hn',
            'seq',
            'icd10',
            'm1',
            'm2',
            'm3',
            'm4',
            'm5',
            'm6',
            'm7',
            'm8',
            'm9',
            'm10',
            'm11',
            'm12',
            'm13',
            'm14',
            'm15',
            'm16',
            'alltotal',
            'FundHolderID',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>

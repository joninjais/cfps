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
/* @var $searchModel frontend\models\FwfdatabaseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ฐานข้อมูล FWF';
$this->params['breadcrumbs'][] = $this->title;
$session = Yii::$app->session;
$del="deletedata";
$chk="checkdata";
?>
<div class="fwfdatabase-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
    <?php // echo Html::a('เพิ่มข้อมูล FWF', ['create'], ['class' => 'btn btn-success']) ?>
    <?php // echo Html::a('ปรับปรุงข้อมูล', ['index','chk' => $chk],['class' => 'btn btn-info']) ?> 
    <?php 
        if(Yii::$app->facilityAccess->isProvincialAdmin()){
        echo Html::a('ปรับปรุงข้อมูล', ['index'], [
            'class' => 'btn btn-info',
            'data' => ['method' => 'post', 'params' => ['chk' => $chk]],
        ]);
        }
        ?>
        <?php 
        // if($session['hoscode']=='00064'){
        // echo Html::a('ลบทั้งหมด', ['index', 'del' => $del,],['class' => 'btn btn-danger']);
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
            'before'=>'ข้อมูล FWF',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
                    ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // ['class' => 'yii\grid\ActionColumn',
            // 'template'=>'<div class="btn-group btn-group-sm text-center" role="group"> {delete} </div>'
            // ],

            //'fwfid',
            'PeriodID',
            'PeriodStartDate',
            'PeriodEndDate',
            'CID',
            'FundHolderID',
            'fname',
            'lname',
            'timeregis',


        ],
    ]); ?>
</div>

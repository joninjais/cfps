<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use frontend\models\User;
use yii\web\view;
$session = Yii::$app->session;
$checklogin = User::findOne(['user'=>$session['user'],]);
if(!$checklogin){
    echo "<script>window.location='index.php?r=site/login';</script>";
}

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\IpdatainfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Ipdatas';
$this->params['breadcrumbs'][] = $this->title;

$session = Yii::$app->session;
$del="deletedata";
$chk="checkdata";
?>
<div class="ipdata-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">

        <?php // echo Html::a('Create Ipdata', ['create'], ['class' => 'btn btn-success']) ?>
        
        <?php // echo Html::a('ปรับปรุงข้อมูล', ['index','chk' => $chk],['class' => 'btn btn-info']) ?> 
        <?= Html::beginForm(['ipdatainfo/reprocess-ccccc'], 'post', ['style' => 'display:inline']) ?>
        <?= Html::submitButton('ปรับปรุงข้อมูล', [
            'class' => 'btn btn-warning',
            'style' => 'margin-left:8px;',
            'onclick' => 'return confirm("ยืนยันการ reprocess รายการ FundHolderID = ccccc ทั้งหมดของหน่วยบริการนี้?\nระบบจะลองคำนวณ FundHolderID ใหม่อีกครั้ง");',
        ]) ?>
        <?= Html::endForm() ?>
    </div>
        <div class="callout callout-danger">
        (กดปุ่ม ปรับปรุงข้อมูล แก้ติด C กรณีมีการอัปโหลดข้อมูลการรับบริการก่อนอัปโหลดข้อมูลบัตรประกันสุขภาพ) 
        </div>        
        <?php
        // if($session['hoscode']!='00064'){
        // echo Html::a('ลบทั้งหมด', ['index','del' => $del,],['class' => 'btn btn-danger']);
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
            'before'=>'ข้อมูล IPDATA',
            'after'=>'ประมวลผล ณ '.date('Y-m-d H:i:s'),
                    ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
/*
            ['class' => 'yii\grid\ActionColumn',
            'template'=>'<div class="btn-group btn-group-sm text-center" role="group"> {delete} </div>'
            ],
*/
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function($url, $model){
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->ipid], [
                            'class' => '',
                            'data' => [
                                'confirm' => 'โปรดยืนยันการลบข้อมูล!',
                                'method' => 'post',
                            ],
                        ]);
                    }
                ]
            ],
            //'ipid',
            'hospcode',
            'admitdate',
            'dischartdate',
            'name',
            'fund',
            'cid',
            'hn',
            'an',
            'icd10',
            'icd9',
            'los',
            'cost',
            'adjrw',            
            'costadjrw',
            'FundHolderID',
        ],
    ]); ?>
</div>
<?php
$this->registerJS("
        $('.delete').click(function(){
            if(!confirm('Please confirm.')){
                return false;
            }
        });
");
?>
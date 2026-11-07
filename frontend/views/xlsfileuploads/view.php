<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
//use yii\grid\GridView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model frontend\models\Xlsfileuploads */

$this->title = 'Step 2 : นำเข้าข้อมูลในไฟล์ => '.$model->exsfile;
$this->params['breadcrumbs'][] = ['label' => 'Upload file ข้อมูล', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

//echo $model['typefileupload'];
?>
<div class="xlsfileuploads-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>

        <?= Html::a('ลบไฟล์', ['delete', 'id' => $model->xlsid], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>

        <?php
        
        $typefile=$model->typefileupload;
        if($typefile=='ip'){ echo Html::a('นำเข้าข้อมูล', ['/ipdatainfo/import', 'id' => $model->xlsid], ['class' => 'btn btn-success']);}
        if($typefile=='op pp'){ echo Html::a('นำเข้าข้อมูล', ['/opdatainfo/import', 'id' => $model->xlsid], ['class' => 'btn btn-success']);}
        if($typefile=='fwfdata'){ echo Html::a('นำเข้าข้อมูล', ['/fwfdatabase/import', 'id' => $model->xlsid], ['class' => 'btn btn-success']);}
        if($typefile=='fwfhealth'){ echo Html::a('นำเข้าข้อมูล', ['/fwfhealth/import', 'id' => $model->xlsid], ['class' => 'btn btn-success']);}
        if($typefile=='insurance'){ echo Html::a('นำเข้าข้อมูล', ['/insurancepolicies/import', 'id' => $model->xlsid], ['class' => 'btn btn-success']);}
         ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'xlsid',
            'exsfile',
            'hoscode',
            'typefileupload',
            'user',
            'uploaddatetime',
        ],
    ]) ?>
<hr>


</div>

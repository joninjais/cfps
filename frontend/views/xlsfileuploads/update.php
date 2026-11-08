<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\models\Xlsfileuploads */

$this->title = 'Update Xlsfileuploads: ' . $model->xlsid;
$this->params['breadcrumbs'][] = ['label' => 'Xlsfileuploads', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->xlsid, 'url' => ['view', 'id' => $model->xlsid]];
$this->params['breadcrumbs'][] = 'Update';
$this->registerCssFile('@web/css/xls-import.css?v=2', ['depends' => ['dmstr\web\AdminLteAsset']]);
?>
<div class="xlsfileuploads-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

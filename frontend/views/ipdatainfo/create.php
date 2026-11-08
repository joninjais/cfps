<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\models\Ipdata */

$this->title = 'Create Ipdata';
$this->params['breadcrumbs'][] = ['label' => 'Ipdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ipdata-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

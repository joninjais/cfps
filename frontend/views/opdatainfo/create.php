<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\models\Opdata */

$this->title = 'Create Opdata';
$this->params['breadcrumbs'][] = ['label' => 'Opdatas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="opdata-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfhealth */

$this->title = 'Create Fwfhealth';
$this->params['breadcrumbs'][] = ['label' => 'Fwfhealths', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fwfhealth-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

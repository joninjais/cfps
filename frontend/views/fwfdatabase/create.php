<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\models\Fwfdatabase */

$this->title = 'Create Fwfdatabase';
$this->params['breadcrumbs'][] = ['label' => 'Fwfdatabases', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="fwfdatabase-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

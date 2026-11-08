<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Data Correct';
$this->params['breadcrumbs'][] = $this->title;
//echo "</br>";
$session = Yii::$app->session;
//echo $ucid=$session['cid'];
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    
    <p><?php echo Html::a('ข้อมูล PERSON ทั้งหมด', ['/person/index']); ?></p>
    <p><?php echo Html::a('data_correct', ['/datacorrect/index']); ?></p>
    <p><?php echo Html::a('ลบข้อมูล PERSON', ['/delperson/index']); ?></p>
    <p><?php echo Html::a('ค้นหาผู้ป่วย DM ที่ต้องการส่งออก Datacorrect', ['/ncddm/index']); ?></p>
    <p><?php echo Html::a('ค้นหาผู้ป่วย HT ที่ต้องการส่งออก Datacorrect', ['/ncdht/index']); ?></p>
    
</div>
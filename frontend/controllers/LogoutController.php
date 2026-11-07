<?php

namespace frontend\controllers;
use Yii;

class LogoutController extends \yii\web\Controller
{
    public function actionIndex()
    {
        Yii::$app->user->logout(false);
        $session = Yii::$app->session;
        $session->destroy();
        return $this->redirect(['/site/login']);
    }

}

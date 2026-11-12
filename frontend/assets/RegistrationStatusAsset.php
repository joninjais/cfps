<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class RegistrationStatusAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $js = ['js/registration-status.js'];
    public $depends = ['yii\web\YiiAsset'];
}

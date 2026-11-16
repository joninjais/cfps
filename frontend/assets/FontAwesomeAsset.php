<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * โหลดไอคอน Font Awesome 4.7 จาก vendor/fortawesome/font-awesome
 */
class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/fortawesome/font-awesome';

    public $css = [
        'css/font-awesome.min.css',
    ];
}

<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * AssetBundle หลักของธีม CFPS (ใช้แทน dmstr\web\AdminLteAsset)
 */
class CfpsThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/cfps-adminlte-compat.css',
        'css/cfps-theme.css',
        'css/cfps-topmenu.css',
        'css/site.css',
    ];

    public $js = [
        'js/cfps-ui.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'frontend\assets\FontAwesomeAsset',
    ];
}

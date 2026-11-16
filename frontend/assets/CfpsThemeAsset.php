<?php

namespace frontend\assets;

use Yii;
use yii\web\AssetBundle;

class CfpsThemeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/cfps-adminlte-compat.css',
        'css/cfps-theme.css',
        'css/cfps-topmenu.css',
        'css/cfps-mobile.css',
    ];

    public $js = [
        'js/cfps-ui.js',
        'js/cfps-mobile.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'frontend\assets\FontAwesomeAsset',
    ];

    public function init()
    {
        parent::init();
        $webroot = Yii::getAlias('@webroot', false);
        if (!$webroot) {
            return;
        }
        foreach ($this->css as $i => $file) {
            $clean = strtok($file, '?');
            $fullPath = $webroot . '/' . ltrim($clean, '/');
            if (is_file($fullPath)) {
                $this->css[$i] = $clean . '?v=' . filemtime($fullPath);
            }
        }
        foreach ($this->js as $i => $file) {
            $clean = strtok($file, '?');
            $fullPath = $webroot . '/' . ltrim($clean, '/');
            if (is_file($fullPath)) {
                $this->js[$i] = $clean . '?v=' . filemtime($fullPath);
            }
        }
    }
}

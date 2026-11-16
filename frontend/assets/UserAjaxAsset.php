<?php

namespace frontend\assets;

use Yii;
use yii\web\AssetBundle;

class UserAjaxAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/user-ajax.css',
    ];

    public $js = [
        'js/user-ajax.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public function init()
    {
        parent::init();
        $webroot = Yii::getAlias('@webroot', false);
        if (!$webroot) {
            return;
        }
        foreach ($this->css as $i => $f) {
            $p = $webroot . '/' . ltrim(strtok($f, '?'), '/');
            if (is_file($p)) {
                $this->css[$i] = strtok($f, '?') . '?v=' . filemtime($p);
            }
        }
        foreach ($this->js as $i => $f) {
            $p = $webroot . '/' . ltrim(strtok($f, '?'), '/');
            if (is_file($p)) {
                $this->js[$i] = strtok($f, '?') . '?v=' . filemtime($p);
            }
        }
    }
}

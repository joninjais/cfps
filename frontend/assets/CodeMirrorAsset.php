<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class CodeMirrorAsset extends AssetBundle
{
    public $sourcePath = '@vendor/almasaeed2010/adminlte/bower_components/ckeditor/samples/toolbarconfigurator/lib/codemirror';
    public $css = [
        'codemirror.css',
    ];
    public $js = [
        'codemirror.js',
    ];
}

<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class CodeMirrorAsset extends AssetBundle
{
    public $basePath = '@webroot/vendor/codemirror-5.65.16';
    public $baseUrl = '@web/vendor/codemirror-5.65.16';
    public $css = [
        'lib/codemirror.css',
        'theme/eclipse.css',
    ];
    public $js = [
        'lib/codemirror.js',
        'mode/sql/sql.js',
    ];
}

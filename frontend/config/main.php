<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? null) == 443)
    || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

return [
    'id' => '',
    'name' => 'CFPS : Claim Foreigner work fund Phang-nga system',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'csrfCookie' => ['httpOnly' => true, 'secure' => $isHttps],
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]            
        ],
        'user' => [
            'identityClass' => 'frontend\models\User',
            'enableAutoLogin' => false,
            'loginUrl' => ['/login'],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name'    => 'advanced-frontend',
            // expire session 4 hours after the last activity
            'timeout' => 14400,
            'cookieParams' => [
                'httpOnly' => true,
                'secure'   => $isHttps,
                'sameSite' => 'Lax',
                'lifetime' => 14400,
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        //CFPS custom theme (replaces AdminLTE visual style)
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@frontend/views' => '@frontend/themes/cfps'
                ],
            ],
       ],
       //end theme
              
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                'login' => 'site/login',
                'logout' => 'site/logout',
            ],
        ],

    ],
    'params' => $params,
];

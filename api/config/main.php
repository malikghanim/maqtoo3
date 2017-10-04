<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'), require(__DIR__ . '/../../common/config/params-local.php'), require(__DIR__ . '/params.php'), require(__DIR__ . '/params-local.php')
);
return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module'
        ],
        'redactor' => 'yii\redactor\RedactorModule',
        'categories' => [
            'class' => 'yiimodules\categories\Module',
        ],
    ],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'profile'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error'],
                    'categories' => ['api_common_exceptions'],
                    'logVars' => ['_SERVER', '_POST', '_GET', '_FILES'],
                    'logFile' => '@app/runtime/logs/v1/api_common_exceptions/exceptions.log',
                    'maxFileSize' => 1024 * 2,
                    'maxLogFiles' => 20,
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'cookieValidationKey' => 'RUYoLzKZirg-OycMbFre7m6P-3n7BieH',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'facebook' => [
                    'class' => 'yii\authclient\clients\Facebook',
                    'clientId' => $params['integrations']['facebook']['client_id'],
                    'clientSecret' => $params['integrations']['facebook']['client_secret'],
                ],
                'google' => [
                    'class' => 'yii\authclient\clients\Google',
                    'clientId' => $params['integrations']['google']['client_id'],
                    'clientSecret' => $params['integrations']['google']['client_secret'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true, 
            'showScriptName' => false,              
            'enableStrictParsing' => false, 
            'rules' => [
                [
                        'class' => 'yii\rest\UrlRule',
                        'controller' => 'site',
                ],
                'authorize' => 'site/authorize',
                'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
                'v1/categories' => 'v1/category/all',
                'v1/categories/filtered-categories' => 'v1/category/filtered-categories',
                'v1/categories/<category_id:\d+>' => 'v1/category/',
                'v1/countries' => 'v1/location/all-countries',
                'v1/countries/<country_id:\w+>' => 'v1/location/country',
                'v1/cities' => 'v1/location/all-cities',
                'v1/cities/<country_id:\w+>' => 'v1/location/city',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/job',
                    'tokens' => ['{id}' => '<id:[a-z0-9]*>'],
                    'except' => ['delete']
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/package',
                    'tokens' => ['{id}' => '<id:[a-z0-9]*>'],
                    'except' => ['create','update','delete']
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/favorite',
                    'tokens' => ['{id}' => '<id:[a-z0-9]*>'],
                    'except' => ['update','delete']
                ],
            ], 
        ]
    ],
    'params' => $params,
];

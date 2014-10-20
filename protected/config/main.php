<?php
define('PUB_PAYPAL_SERVICE_URI', 'https://www.paypal.com');
//define('PUB_PAYPAL_SDK_DIR',dirname(__DIR__) . '/../../paypal-sdk');
define('PUB_PAYPAL_SDK_DIR', '/var/www/SITE_DEVELOPERS/SITE_JK_COMPONENT/paypal-sdk/');

Yii::setPathOfAlias('paypal-sdk',  '/var/www/SITE_DEVELOPERS/SITE_JK_COMPONENT/paypal-sdk/');

//if(file_exists(PUB_PAYPAL_SDK_DIR.'/lib/PayPal/Api/Address.php')){
//    echo ' Address.php existes';
//}else{
//    echo PUB_PAYPAL_SDK_DIR.'/lib/PayPal/Api/Address.php';
//    echo '<br/>';
//    echo ' Address.php not existes';
//}

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Pay port',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
                
                'paypal-sdk.lib.PayPal.Api.*',
                'paypal-sdk.lib.PayPal.Auth.*',
                'paypal-sdk.lib.PayPal.Common.*',
                'paypal-sdk.lib.PayPal.Core.*',
                'paypal-sdk.lib.PayPal.Exception.*',
                'paypal-sdk.lib.PayPal.Handler.*',
                'paypal-sdk.lib.PayPal.Rest.*',
                'paypal-sdk.lib.PayPal.Transport.*',
                'paypal-sdk.lib.PayPal.Validation.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/testdrive.db',
		),
		// uncomment the following to use a MySQL database
		/*
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=testdrive',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),
		*/
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
	),
);
<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../../yii-1.1.13.e9e4a0/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
defined('PUB_IS_PAYPAL_LIVE') or define('PUB_IS_PAYPAL_LIVE',false);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yii);
Yii::createWebApplication($config)->run();

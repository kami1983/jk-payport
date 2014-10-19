<?php

/**
 * 
 */
class PaypalController extends Controller
{
    
    /**
     * paypal 付款类的构造方法，一些参数初始化应该隔离在这里
     */
    public function __construct() {
        $composerAutoload = dirname(__DIR__) . '/../../paypal-sdk/vendor/autoload.php';
        echo $composerAutoload;
        if (!file_exists($composerAutoload)) {
            echo "You need sdk. ";
//            echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
            Yii::app()->end();
        }
        
        
    }
    

    /**
     * Declares class-based actions.
     */
    public function actions()
    {
            return array(
//			// captcha action renders the CAPTCHA image displayed on the contact page
//			'captcha'=>array(
//				'class'=>'CCaptchaAction',
//				'backColor'=>0xFFFFFF,
//			),
//			// page action renders "static" pages stored under 'protected/views/site/pages'
//			// They can be accessed via: index.php?r=site/page&view=FileName
//			'page'=>array(
//				'class'=>'CViewAction',
//			),
            );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
            // renders the view file 'protected/views/site/index.php'
            // using the default layout 'protected/views/layouts/main.php'
//            $this->render('index');
    }

    /**
     * 创建付款页面
     */
    public function actionPayment(){

    }

}
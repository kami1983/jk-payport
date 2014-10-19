<?php

/**
 * 
 */
class PaypalController extends Controller
{
    
    /**
     * paypal 付款类的构造方法，一些参数初始化应该隔离在这里
     * @param string $id id of this controller
     * @param CWebModule $module the module that this controller belongs to.
     */
    public function __construct($id,$module=null)
    {
        $composerAutoload = PUB_PAYPAL_SDK_DIR.'/vendor/autoload.php';
        echo $composerAutoload ;
        echo 'RUN 3 ';
        if (!file_exists($composerAutoload)) {
            echo "You need sdk. ";
//            echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
            Yii::app()->end();
        }
        
        require $composerAutoload;
        require dirname(__DIR__) . '/../../paypal-sdk/sample/common.php';

//        use PayPal\Rest\ApiContext;
//        use PayPal\Auth\OAuthTokenCredential;
        error_reporting(E_ALL);
        
        // Replace these values by entering your own ClientId and Secret by visiting https://developer.paypal.com/webapps/developer/applications/myapps
        $clientId = 'AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS';
        $clientSecret = 'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL';

        /** @var \Paypal\Rest\ApiContext $apiContext */
        $apiContext = $this->getApiContext($clientId, $clientSecret);

        
        //调用父类否则VIEW 无法解析
        parent::__construct($id, $module);
    }
    
   /**
    * Helper method for getting an APIContext for all calls
    *
    * @return PayPal\Rest\ApiContext
    */
   function getApiContext($clientId, $clientSecret)
   {

       // ### Api context
       // Use an ApiContext object to authenticate
       // API calls. The clientId and clientSecret for the
       // OAuthTokenCredential class can be retrieved from
       // developer.paypal.com

       $apiContext = new ApiContext(
           new OAuthTokenCredential(
               $clientId,
               $clientSecret
           )
       );


       // #### SDK configuration

       // Comment this line out and uncomment the PP_CONFIG_PATH
       // 'define' block if you want to use static file
       // based configuration

       $apiContext->setConfig(
           array(
               'mode' => 'sandbox',
               'http.ConnectionTimeOut' => 30,
               'log.LogEnabled' => true,
               'log.FileName' => '../PayPal.log',
               'log.LogLevel' => 'FINE',
               'validation.level' => 'log'
           )
       );

       /*
       // Register the sdk_config.ini file in current directory
       // as the configuration source.
       if(!defined("PP_CONFIG_PATH")) {
           define("PP_CONFIG_PATH", __DIR__);
       }
       */

       return $apiContext;
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
            $this->render('index');
    }

    /**
     * 创建付款页面
     */
    public function actionPayment(){

    }

}
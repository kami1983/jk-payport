<?php

/**
 * 
 */
class PaypalController extends Controller
{
    /* @var ApiContext $_apiContent */
    private $_apiContent=null;


    private function apiContext(){
        if($this->_apiContent instanceof ApiContext){
           return $this->_apiContent; 
        }
        
        //初始化：
        $composerAutoload = PUB_PAYPAL_SDK_DIR.'/vendor/autoload.php';
        echo $composerAutoload ;
        if (!file_exists($composerAutoload)) {
            echo "You need sdk. ";
//            echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
            Yii::app()->end();
        }
        
        echo 'RUN X1 ';
        
//        require $composerAutoload;
//        require dirname(__DIR__) . '/../../paypal-sdk/sample/common.php';
//
////        use PayPal\Rest\ApiContext;
////        use PayPal\Auth\OAuthTokenCredential;
        error_reporting(E_ALL);
        
        // Replace these values by entering your own ClientId and Secret by visiting https://developer.paypal.com/webapps/developer/applications/myapps
        $clientId = 'AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS';
        $clientSecret = 'EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL';
        echo 'RUN X2 ';
        /** @var \Paypal\Rest\ApiContext $apiContext */
        $this->_apiContent = $this->_getApiContext($clientId, $clientSecret);

        echo 'RUN X3 ';    
    }
    
    
    /**
    * Helper method for getting an APIContext for all calls
    *
    * @return PayPal\Rest\ApiContext
    */
   private function _getApiContext($clientId, $clientSecret)
   {
       echo 'RUN X4 ';
       // ### Api context
       // Use an ApiContext object to authenticate
       // API calls. The clientId and clientSecret for the
       // OAuthTokenCredential class can be retrieved from
       // developer.paypal.com
       
       if(!class_exists('OAuthTokenCredential')){
           echo 'not found class OAuthTokenCredentia ';
           //throw new Exception('Api not found. OAuthTokenCredentia','141020-1107');
       }
       
//       $authToken=new OAuthTokenCredential($clientId,$clientSecret);
//        echo 'RUN X4.1 ';
//       $apiContext = new ApiContext($authToken);
//
//       echo 'RUN X5 ';
//       // #### SDK configuration
//
//       // Comment this line out and uncomment the PP_CONFIG_PATH
//       // 'define' block if you want to use static file
//       // based configuration
//
//       $apiContext->setConfig(
//           array(
//               'mode' => 'sandbox',
//               'http.ConnectionTimeOut' => 30,
//               'log.LogEnabled' => true,
//               'log.FileName' => '../PayPal.log',
//               'log.LogLevel' => 'FINE',
//               'validation.level' => 'log'
//           )
//       );
//
//       /*
//       // Register the sdk_config.ini file in current directory
//       // as the configuration source.
//       if(!defined("PP_CONFIG_PATH")) {
//           define("PP_CONFIG_PATH", __DIR__);
//       }
//       */
//       echo 'RUN X6 ';
//       return $apiContext;
   }

    
    /**
     * paypal 付款类的构造方法，一些参数初始化应该隔离在这里
     * @param string $id id of this controller
     * @param CWebModule $module the module that this controller belongs to.
     */
    public function __construct($id,$module=null)
    {
        echo 'RUN A1 ';
        $this->apiContext();
        echo 'RUN A2 ';
        //调用父类否则VIEW 无法解析
        parent::__construct($id, $module);
        echo 'RUN A3 ';
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
        //Yii::app()->request->getParam(''); 
        echo 'RUN 1 ';
        // ### Payer
        // A resource representing a Payer that funds a payment
        // For paypal account payments, set payment method
        // to 'paypal'.
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // ### Itemized information
        // (Optional) Lets you specify item wise
        // information
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setPrice('7.50');
        $item2 = new Item();
        $item2->setName('Granola bars')
                ->setCurrency('USD')
                ->setQuantity(5)
                ->setPrice('2.00');

        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        // ### Additional payment details
        // Use this optional field to set additional
        // payment information such as tax, shipping
        // charges etc.
        $details = new Details();
        $details->setShipping('1.20')
                ->setTax('1.30')
                ->setSubtotal('17.50');

        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
        $amount = new Amount();
        $amount->setCurrency("USD")
                ->setTotal("20.00")
                ->setDetails($details);

        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it. 
        $transaction = new Transaction();
        $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription("Payment description");

        // ### Redirect urls
        // Set the urls that the buyer must be redirected to after 
        // payment approval/ cancellation.
        $baseUrl = getBaseUrl();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/ExecutePayment.php?success=true")
                ->setCancelUrl("$baseUrl/ExecutePayment.php?success=false");

        // ### Payment
        // A Payment Resource; create one using
        // the above types and intent set to 'sale'
        $payment = new Payment();
        $payment->setIntent("sale")
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));

        // ### Create Payment
        // Create a payment by calling the 'create' method
        // passing it a valid apiContext.
        // (See bootstrap.php for more on `ApiContext`)
        // The return object contains the state and the
        // url to which the buyer must be redirected to
        // for payment approval
        try {
                $payment->create($this->apiContext());
        } catch (PPConnectionException $ex) {
                echo "Exception: " . $ex->getMessage() . PHP_EOL;
                var_dump($ex->getData());	
                exit(1);
        }

        // ### Get redirect url
        // The API response provides the url that you must redirect
        // the buyer to. Retrieve the url from the $payment->getLinks()
        // method
        foreach($payment->getLinks() as $link) {
                if($link->getRel() == 'approval_url') {
                        $redirectUrl = $link->getHref();
                        break;
                }
        }

        // ### Redirect buyer to PayPal website
        // Save the payment id so that you can 'complete' the payment
        // once the buyer approves the payment and is redirected
        // back to your website.
        //
        // It is not a great idea to store the payment id
        // in the session. In a real world app, you may want to 
        // store the payment id in a database.
        echo '<br/>';
        echo $redirectUrl;
        echo 'RUN 2 ';
        $_SESSION['paymentId'] = $payment->getId();
        if(isset($redirectUrl)) {
                header("Location: $redirectUrl");
                exit;
        }
        
        
    }

}

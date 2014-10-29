<?php

//use PayPal\Common\PPUserAgent;
//use PayPal\Core\PPConstants;
//use PayPal\Core\PPHttpConfig;
//use PayPal\Core\PPHttpConnection;
//use PayPal\Core\PPLoggingManager;
//use PayPal\Exception\PPConfigurationException;
//use PayPal\Rest\RestHandler;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

//use PayPal\Api\Address;
//use PayPal\Api\CreditCard;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
//use PayPal\Api\FundingInstrument;


//use PayPal\Api\Amount;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
//use PayPal\Api\CreditCard;
//use PayPal\Api\Payer;
//use PayPal\Api\Payment;
//use PayPal\Api\FundingInstrument;
//use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;




/**
 * Description of CPaypalHandler
 * Paypal 处理类
 * 
 * @author linhai
 */
class CPaypalHandler extends CBase {
    
    
   /**
    * @param Payment $payment_obj 付款对象
    * @return string
    */
    public static function ExtractId(Payment $payment_obj){

        return $payment_obj->getId();
    }

    /**
     * @param Payment $payment_obj 付款对象
     * @return string
     */
    public static function ExtractApprovalUrl(Payment $payment_obj){
         foreach($payment_obj->getLinks() as $link) {

             if($link->getRel() == 'approval_url') {
                 return $link->getHref();
             }
         }
    }
    
    ######################
    
    
    
    /* @var $_apiContext ApiContext  */
    protected $_apiContext=null;
    
    
    //--------------------
    
    /* @var $_item_arr Item[] */
    protected $_item_arr=array();
    
    /* @var $_details Details */
    protected $_details=null;
    
    /* @var $_return_url string *///成功后回跳的地址
    protected $_return_url=null;
    
    /* @var $_cancel_url string *///取消付款后的回跳地址
    protected $_cancel_url=null;
    

    
    public function __construct(ApiContext $api_context_obj) {
        //初始化：
//        $composerAutoload = $paypal_sdk_dir.'/vendor/autoload.php';
////        echo $composerAutoload ;
//        if (!file_exists($composerAutoload)) {
//            
//            throw new Exception('You need sdk. ','141020_1020');
////            echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
//        }
//        
//        require_once $composerAutoload; //引入APIs
        
        $this->_apiContext=$api_context_obj; //创建API 内容对象
        
        
    }
    
    /**
     * 设置付款成功后返回的地址
     * @param string $return_url 用户付款完成跳转的RUL
     * @return CPaypalHandler
     */
    public function setReturnUrl($return_url){
        $this->_return_url=$return_url;
        return $this;
    }
    
    /**
     * 设置付款退出后返回的地址
     * @param string $cancel_url 用户取消付款跳转的RUL
     * @return CPaypalHandler
     */
    public function setCancelUrl($cancel_url){
        $this->_cancel_url=$cancel_url;
        return $this;
    }
    
    

   /**
    * 添加一个支付信息
    * @param $price
    * @param $name CCY Payment.
    * @param $quantity 1
    * @param $currency USD
    * @return CPaypalHandler
    */
   public function addItem($price,$name='CCY Payment.',$quantity=1,$currency='USD'){
       
       foreach ($this->_item_arr as $item_obj) {
           if($currency != $item_obj->getCurrency()){
               //如果不想等那么throw
               throw new Exception('支付货币必须一致','141021_1437');
           }
       }
       
        $item = new Item();
        $item->setName($name)
                ->setCurrency($currency)
                ->setQuantity($quantity)
                ->setPrice($price);
        
        $this->_item_arr[]=$item;
        
        return $this;
   }
   
   /**
    * 制作金额
    * @param Details $details 支付细节
    * @return Amount
    */
   private function _makeAmount(Details $details){
       $amount = new Amount();
       $amount->setCurrency($this->_item_arr[0]->getCurrency())
                ->setTotal($details->getTax()+$details->getShipping()+$details->getSubtotal())
                ->setDetails($details);
       
       return $amount;
   }
   
   /**
    * 制作金额
    * @param Item[] $item_arr 付款条目数组
    * @return float
    */
   private function _getItemPriceSum(array $item_arr){
       $total_price=0.00;
       foreach($item_arr as $item_obj){
           /* @var $item_obj Item */
           $total_price+=($item_obj->getPrice() * $item_obj->getQuantity());
       }
       return $total_price;
   }
   
   /**
    * 添加付款税金和运费
    * @param string $shipping 运费
    * @param string $tax 税金
    * @return CPaypalHandler
    */
   public function setDetails($shipping='0.00',$tax='0.00'){
       
       $details = new Details();
       $details->setShipping($shipping)
                ->setTax($tax)
                ->setSubtotal($this->_getItemPriceSum($this->_item_arr));
        
       $this->_details=$details;
       return $this;
   }
   
   /**
    * 执行一个付款操作
    * @param string $payer_id 付款者的id
    * @return mixed
    */
   public function executePayment($payment_id,$payer_id){
        $payment = Payment::get($payment_id, $this->_apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($payer_id);

        //Execute the payment
        // (See bootstrap.php for more on `ApiContext`)
        try{
            return $payment->execute($execution, $this->_apiContext);
        }catch(PPConnectionException $ex){
            echo 'Is paid ?';
        }
        return null;
   }

   /**
    * 创建付款页面
    * Payment
    */
   public function createPaymentObj(){
        
        
        // ### Payer
        // A resource representing a Payer that funds a payment
        // For paypal account payments, set payment method
        // to 'paypal'.
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");
        
        // ### Itemized information
        // (Optional) Lets you specify item wise
        // information
//        $item1 = new Item();
//        $item1->setName('Ground Coffee 40 oz')
//                ->setCurrency('USD')
//                ->setQuantity(1)
//                ->setPrice('7.50');
//        $item2 = new Item();
//        $item2->setName('Granola bars')
//                ->setCurrency('USD')
//                ->setQuantity(5)
//                ->setPrice('2.00');

        $itemList = new ItemList();
        $itemList->setItems($this->_item_arr);
        
        
        // ### Additional payment details
        // Use this optional field to set additional
        // payment information such as tax, shipping
        // charges etc.
//        $details = new Details();
//        $details->setShipping('1.20')
//                ->setTax('1.30')
//                ->setSubtotal('17.50');
        
        
        // ### Amount
        // Lets you specify a payment amount.
        // You can also specify additional details
        // such as shipping, tax.
//        $amount = new Amount();
//        $amount->setCurrency("USD")
//                ->setTotal("20.00")
//                ->setDetails($details);
        
        // ### Transaction
        // A transaction defines the contract of a
        // payment - what is the payment for and who
        // is fulfilling it. 
        $transaction = new Transaction();
        $transaction->setAmount($this->_makeAmount($this->_details))
                ->setItemList($itemList)
                ->setDescription("Payment description of cancanyou.com");

        // ### Redirect urls
        // Set the urls that the buyer must be redirected to after 
        // payment approval/ cancellation.
//        $baseUrl = $this->getBaseUrl(); //这个注意这里设置的是回调页面成功的话，或者取消的话
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->_return_url)
                ->setCancelUrl($this->_cancel_url);


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
//        echo 'RUN 1 ';
//        echo get_class($this->_apiContext);
//        exit;
        try {
                $payment->create($this->_apiContext);
        } catch (PPConnectionException $ex) {
//                echo "Exception: " . $ex->getMessage() . PHP_EOL;
//                var_dump($ex->getData());	
//                exit(1);
            
            throw new Exception($ex->getMessage,'141020_1753');
            Yii::app()->end();
        }
        
        return $payment;
//        echo '<pre>';
//        print_r($payment);
//        echo '</pre>';
//        // ### Get redirect url
//        // The API response provides the url that you must redirect
//        // the buyer to. Retrieve the url from the $payment->getLinks()
//        // method
//        foreach($payment->getLinks() as $link) {
//                if($link->getRel() == 'approval_url') {
//                        $redirectUrl = $link->getHref();
//                        break;
//                }
//        }
//
//        // ### Redirect buyer to PayPal website
//        // Save the payment id so that you can 'complete' the payment
//        // once the buyer approves the payment and is redirected
//        // back to your website.
//        //
//        // It is not a great idea to store the payment id
//        // in the session. In a real world app, you may want to 
//        // store the payment id in a database.
//        
//        return $redirectUrl;
        
    }
    
    
//    private function getBaseUrl()
//    {
//
//        $protocol = 'http';
//        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
//            $protocol .= 's';
//            $protocol_port = $_SERVER['SERVER_PORT'];
//        } else {
//            $protocol_port = 80;
//        }
//
//        $host = $_SERVER['HTTP_HOST'];
//        $port = $_SERVER['SERVER_PORT'];
//        $request = $_SERVER['PHP_SELF'];
//        return dirname($protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request);
//    }

}

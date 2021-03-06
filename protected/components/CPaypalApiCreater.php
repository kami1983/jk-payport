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


/**
 * Description of CPaypalApiCreater
 * 用来创建Paypal 的链接API
 * 
 * @author linhai
 */
class CPaypalApiCreater {
    
    /* @var $_apiContent ApiContext  */
    protected $_apiContent=null;
    
    
    /* @var $_client_id string *///应用的id
    protected $_client_id=null;
    
    /* @var $_client_secret string *///应用的加密
    protected $_client_secret=null;
    
    
    /* @var $_ipn_url string *///IPN 通知地址
    protected $_ipn_url=null; 
    
    
    /**
     * 构造一个 ApiContext 对象
     * @param string $client_id 链入的客户id
     * @param string $client_secret 链入的客户散列码
     * @param string $paypal_sdk_dir Paypal SDK 所在的目录
     * @return void
     */
    public function __construct($client_id,$client_secret,$paypal_sdk_dir) {
        
        //初始化：
        $composerAutoload = $paypal_sdk_dir.'/vendor/autoload.php';
//        echo $composerAutoload ;
        if (!file_exists($composerAutoload)) {
            
            throw new Exception('You need sdk. ','141020_1020');
//            echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
        }
        
        require_once $composerAutoload; //引入APIs
        
        
        $this->_client_id=$client_id;
        $this->_client_secret=$client_secret;
        $this->_apiContent=$this->_createApiContext($this->_client_id,$this->_client_secret); //初始化。
        
    }
    
    /**
     * 返回可用的 ApiContext 对象
     * @return ApiContext
     */
    public function getApiContext(){
        
        $config_arr=array();

        if(PUB_IS_PAYPAL_LIVE){
           $config_arr['mode']='live';
        }else{
           $config_arr['mode']='sandbox';
        }
        $config_arr['http.ConnectionTimeOut']=30;
        $config_arr['log.LogEnabled']=true;
        $config_arr['log.FileName']=Yii::app()->getBasePath().'/runtime/PayPal.log';
        $config_arr['log.LogLevel']='FINE';
        $config_arr['validation.level']='log';
        if(null !== $this->_ipn_url){
            $config_arr['service.EndPoint.IPN'] = $this->_ipn_url;
        }
        //       }else{
        //           $config_arr['service.EndPoint.IPN'] = 'http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/ipn&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c';
        //       }

        $this->_apiContent->setConfig($config_arr);
           
        return $this->_apiContent; 
    }
    
    /**
     * 设置Paypal IPN 地址
     * @param string $ipn_url IPN 的URL 地址
     * @return CPaypalHandler
     */
    public function setIpnUrl($ipn_url){
        $this->_ipn_url=$ipn_url;
        return $this;
    }
    
    /**
        * Helper method for getting an APIContext for all calls
        *
        * @return PayPal\Rest\ApiContext
        */
       private function _createApiContext($clientId, $clientSecret){
           $authToken=new OAuthTokenCredential($clientId,$clientSecret);
           $apiContext = new ApiContext($authToken);
           return $apiContext;
       }
}

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

use PayPal\Api\Address;
use PayPal\Api\CreditCard;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\Transaction;
use PayPal\Api\FundingInstrument;


use PayPal\Api\Amount;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\CreditCard;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\FundingInstrument;
use PayPal\Api\Transaction;


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
        
        return $this->_apiContent; 
    }

    /**
     * 创建一个 ApiContext 对象
     * @return ApiContext
     */
    private function _createApiContext($client_id=null,$client_secret=null){
        if($this->_apiContent instanceof ApiContext){
           return $this->_apiContent; 
        }
        /** @var \Paypal\Rest\ApiContext $apiContext */
        $this->_apiContent = $this->_getApiContext($client_id, $client_secret);
    }
}

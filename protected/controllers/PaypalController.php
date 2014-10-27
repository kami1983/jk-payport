<?php




/**
 * 
 */
class PaypalController extends Controller {
    
    
    /**
     * paypal 付款类的构造方法，一些参数初始化应该隔离在这里
     * @param string $id id of this controller
     * @param CWebModule $module the module that this controller belongs to.
     */
    public function __construct($id,$module=null)
    {
        
        //调用父类否则VIEW 无法解析
        parent::__construct($id, $module);
        
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
     * 一个IPN 的着陆页面
     * 测试例：http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/ipn&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c
     * 
     * @return void
     */
    public function actionIpn(){
        //获取请求的地址信息
        $uid=Yii::app()->request->getQuery('uid','0');
        $masksign=Yii::app()->request->getQuery('masksign','');
        
        
        //效验请求合法性
        if(!CUser::CheckValid($uid,$masksign)){
            throw new Exception('无法识别调用用户','141022_1027');
        }
        
        
        ###########
        
        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_POST,true), 'jkdebug.PaypalController.actionIpn');
    }
    

    /**
     * 创建付款页面
     * 测试页面：http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/payment&client_id=AfSbYRAe0Li9JullQ41NFRZrSlOyDrs_TnOzwmXio7uk8-0TOS86vYWXRsF-&client_secret=EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c&price_arr[0]=2.77
     * 页面返回JSON 类似：{"redirect_url":"https:\/\/www.sandbox.paypal.com\/cgi-bin\/webscr?cmd=_express-checkout&token=EC-91V555525V512641V","payid":"PAY-0LK657034L9866308KRGQOQY","token":"EC-91V555525V512641V"}
     * 访问回调redirect_url 后：cancanyou-facilitator-buyer@yahoo.com 密码：12345678
     * 付款成功后回调举例：http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/recall&success=true&recordid=1&record_masksign=2fc7fd70fd1aafe36db926519507f77c&token=EC-91V555525V512641V&PayerID=RBJN2EXHT9MJY
     */
    public function actionPayment(){
        
        //获取请求的地址信息
        $uid=Yii::app()->request->getQuery('uid','0');
        $masksign=Yii::app()->request->getQuery('masksign','');
        
        
        //效验请求合法性
        if(!CUser::CheckValid($uid,$masksign)){
            throw new Exception('无法识别调用用户','141022_1027');
        }
        
        
        //获取币种
        $currency=Yii::app()->request->getQuery('currency','USD');
        $price_arr=Yii::app()->request->getQuery('price_arr',array());
        $quantity_arr=Yii::app()->request->getQuery('quantity_arr',array()); //默认
        $itemname_arr=Yii::app()->request->getQuery('itemname_arr',array()); //名称默认 CCY Payment
        $shipping=Yii::app()->request->getQuery('shipping','0.00'); //名称默认 CCY Payment
        $tax=Yii::app()->request->getQuery('tax','0.00'); //名称默认 CCY Payment
        
        //获取secret
        $client_id=Yii::app()->request->getQuery('client_id','none id');
        $client_secret=Yii::app()->request->getQuery('client_secret','none secret');
        
//        $clientId = 'AfSbYRAe0Li9JullQ41NFRZrSlOyDrs_TnOzwmXio7uk8-0TOS86vYWXRsF-';
//        $clientSecret = 'EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp';

        
        //记录请求信息
        $oper=new CDbPayportPayment();
        $oper->ipaddress=$_SERVER['REMOTE_ADDR'];
        $oper->creationdate=$oper->modificationdate=date('Y-m-d H:i:s');
        $oper->status=CDbPayportPayment::CONST_FIELD_STATUS_IS_INVALID; //默认无效
        $oper->type=CDbPayportPayment::CONST_FIELD_TYPE_IS_PAYPAL;
        $oper->post_json=  json_encode($_POST);
        $oper->get_json=  json_encode($_GET);
        $oper->payment_json= '';
        $insert_id= $oper->insert();
        
        
        $hostInfo=Yii::app()->request->hostInfo;
        $userdef_arr=CUser::GetAccountDefined($uid);
        $record_masksign=md5($insert_id.$userdef_arr['token']) ;
        $return_url=$hostInfo.$this->createUrl('recall',array('success'=>'true','recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
        $cancel_url=$hostInfo.$this->createUrl('recall',array('success'=>'false','recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
       
        $paypal_handler=new CPaypalHandler($client_id,$client_secret,PUB_PAYPAL_SDK_DIR);
        $paypal_handler->setReturnUrl($return_url);
        $paypal_handler->setCancelUrl($cancel_url);
        
        foreach($price_arr as $index=>$value){
            if(!isset($quantity_arr[$index])){
                $quantity_arr[$index]=1;
            }
            if(!isset($itemname_arr[$index])){
                $itemname_arr[$index]='CCY Payment.';
            }
//            echo "value={$value},";
//            echo "itemname_arr[\$index]={$itemname_arr[$index]},";
//            echo "quantity_arr[\$index]={$quantity_arr[$index]},";
//            echo "currency={$currency},";
//            echo '<br/>';
            $paypal_handler->addItem($value, $itemname_arr[$index],$quantity_arr[$index],$currency);
        }
        
//        echo "shipping={$shipping},";
//        echo "tax={$tax},";
//        echo '<br/>';
        $paypal_handler->setDetails($shipping, $tax);
        
        
//        $paypal_handler->addItem('2.01', 'links',7);
//        $paypal_handler->setDetails('0.00', '0.00');
//        
        $paymentObj= $paypal_handler->createPaymentObj();
        $redirect_url=CPaypalHandler::ExtractApprovalUrl($paymentObj);
        $payid=CPaypalHandler::ExtractId($paymentObj);
        $match_arr=array();
        preg_match('/&token=(.*)($|&| )/iU', $redirect_url,$match_arr);
        $token=$match_arr[1];
        
        //存储：$payid，$token，$post_json
        $result_arr=array();
        $result_arr['redirect_url']=$redirect_url;
        $result_arr['payid']=$payid;
        $result_arr['token']=$token;
        
        //return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
    }
    
    /**
     * 回调参数
     * @return array
     */
    public function actionRecall(){
        
        echo 'RECALL';
    }
}

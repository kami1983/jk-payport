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
        
        echo 'RUN action IPN <br/>';
        
        ###########
        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_GET,true), 'jkdebug.PaypalController.actionIpn');
        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_POST,true), 'jkdebug.PaypalController.actionIpn');
        Yii::trace('=======================','jkdebug.PaypalController.actionIpn');
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
        $currency=Yii::app()->request->getParam('currency','USD');
        $price_arr=Yii::app()->request->getParam('price_arr',array());
        $quantity_arr=Yii::app()->request->getParam('quantity_arr',array()); //默认
        $itemname_arr=Yii::app()->request->getParam('itemname_arr',array()); //名称默认 CCY Payment
        $shipping=Yii::app()->request->getParam('shipping','0.00'); //名称默认 CCY Payment
        $tax=Yii::app()->request->getParam('tax','0.00'); //名称默认 CCY Payment
        
        //获取secret
        $client_id=Yii::app()->request->getParam('client_id','none id');
        $client_secret=Yii::app()->request->getParam('client_secret','none secret');
        
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
        $insert_id=0;
        if($oper->insert()){
            $insert_id=$oper->getPrimaryKey();
        }
        
        if(0 == (int)$insert_id){
            $result_arr=array();
            $result_arr['status']='failure';
            $result_arr['info']='Db error. create a payment. ';
            $result_arr['code']='100001';
            return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
        }
        
        
        
        $hostInfo=Yii::app()->request->hostInfo;
        $userdef_arr=CUser::GetAccountDefined($uid);
        $record_masksign=md5($insert_id.$userdef_arr['token']) ;
        echo $return_url=$hostInfo.$this->createUrl('recall',array('success'=>'true','uid'=>$uid,'recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
        echo '<br/>';
        echo $cancel_url=$hostInfo.$this->createUrl('recall',array('success'=>'false',));
        echo '<br/>';
        echo $ipn_url=$hostInfo.$this->createUrl('ipn',array('uid'=>$uid,'masksign'=>$masksign,'recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
        echo '<br/>';
       
        $api_creater=new CPaypalApiCreater($client_id,$client_secret,PUB_PAYPAL_SDK_DIR);
        
        $paypal_handler=new CPaypalHandler($api_creater->getApiContext());
        
        $paypal_handler->setReturnUrl($return_url);
        $paypal_handler->setCancelUrl($cancel_url);
        $paypal_handler->setIpnUrl($ipn_url);
        
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
//        $match_arr=array();
//        preg_match('/&token=(.*)($|&| )/iU', $redirect_url,$match_arr);
//        $token=$match_arr[1];
        
//        header("location:{$redirect_url}");
//        exit;
        
        //存储：$payid，$token，$post_json
        $result_arr=array();
        $result_arr['status']='success';
        $result_arr['redirect_url']=$redirect_url;
        $result_arr['payid']=$payid;
        $result_arr['client_id']=$client_id;
        $result_arr['client_secret']=$client_secret;
        
        $oper->payment_json=  json_encode($result_arr); //将结果数据记录到数据库
        if(!$oper->update()){
            $result_arr=array();
            $result_arr['status']='failure';
            $result_arr['info']='Db error. update a payment. ';
            $result_arr['code']='100002';
            return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
        }
        
        return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
        
    }
    
    /**
     * 回调参数
     * @return array
     */
    public function actionRecall(){
        //&uid=1&recordid=69&record_masksign=7b15801a74e62bad1e04d8f9c076c91a
        $uid=(int)Yii::app()->request->getQuery('uid',0);
        $recordid=(int)Yii::app()->request->getQuery('recordid',0);
        $record_masksign=Yii::app()->request->getQuery('record_masksign','');
        $success=Yii::app()->request->getQuery('success','false');
        if('false' == $success){
            $cancel_redirect_url=Yii::app()->request->getQuery('cancel_redirect_url','');
            if('' == $cancel_redirect_url){
                echo "User cancelled payment.";
            }else{
                Yii::app()->request->redirect($cancel_redirect_url);
            }
            Yii::app()->end();
        }
        
        
        //验证请求合法性
//        $hostInfo=Yii::app()->request->hostInfo;
        $userdef_arr=CUser::GetAccountDefined($uid);
        if($record_masksign != md5($recordid.$userdef_arr['token'])){
            throw new Exception('Authorization Failed.','141029_1103');
        }
        
        //正式进入执行操作--------
        //获取数据
        $dbinfo=CDbPayportPayment::model()->findByPk($recordid);
        /* @var $dbinfo CDbPayportPayment */
        if('' == $dbinfo->payment_json){
            throw new Exception('Record Error. payment_json is empty. ','141029_1109');
        }
        
        $payment_obj=  json_decode($dbinfo->payment_json, false);
        if('' == $payment_obj->payid){
            throw new Exception('Record Error. payment_json->payid is empty. ','141029_1112');
        }
        
        //执行付款操作
        $payer_id=Yii::app()->request->getQuery('PayerID','');
        
        //获取执行对象
        $api_creater=new CPaypalApiCreater($payment_obj->client_id,$payment_obj->client_secret,PUB_PAYPAL_SDK_DIR);
        $paypal_handler=new CPaypalHandler($api_creater->getApiContext());
        $result=$paypal_handler->executePayment($payer_id);
        
        
        

        echo "<html><body><pre>";
        echo $result->toJSON(JSON_PRETTY_PRINT);
        echo "</pre><a href='../index.html'>Back</a></body></html>";

        
    }
}

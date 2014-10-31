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
    public function actionPaidtip(){
        $is_pay_success=Yii::app()->request->getQuery('is_pay_success','false');
        if('true' == $is_pay_success){
            echo 'Payment is successful. ';
        }else{
            echo 'User cancelled payment. ';
        }
    }
    
    /**
     * 一个IPN 的着陆页面
     * 测试例：http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/ipn&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c
     * 
     * @return void
     */
    public function actionPaiddo(){
        //获取请求的地址信息
//        $uid=Yii::app()->request->getQuery('uid','0');
//        $masksign=Yii::app()->request->getQuery('masksign','');
//        
//        
//        //效验请求合法性
//        if(!CUser::CheckValid($uid,$masksign)){
//            throw new Exception('无法识别调用用户','141022_1027');
//        }
        
        echo 'RUN action IPN <br/>';
        
        ###########
        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_GET,true), 'jkdebug.PaypalController.actionIpn');
        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_POST,true), 'jkdebug.PaypalController.actionIpn');
        Yii::trace('=======================','jkdebug.PaypalController.actionIpn');
    }
    

    /**
     * 创建付款页面
     * 测试页面：http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/payment&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c&price_arr[0]=5&client_secret=EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp
     * 页面返回JSON 类似：{"status":"success","redirect_url":"https:\/\/www.sandbox.paypal.com\/cgi-bin\/webscr?cmd=_express-checkout&token=EC-0TF07914SR289291E","payment_id":"PAY-8V260968H2085143JKRILQXQ","client_id":"AfSbYRAe0Li9JullQ41NFRZrSlOyDrs_TnOzwmXio7uk8-0TOS86vYWXRsF-","client_secret":"EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp","tip_url":"http:\/\/develop.jk-payport.git.cancanyou.com\/test_index.php?r=paypal\/paidtip","do_url":"http:\/\/develop.jk-payport.git.cancanyou.com\/test_index.php?r=paypal\/paiddo"}
     * 访问回调redirect_url 后：cancanyou-facilitator-buyer@yahoo.com 密码：12345678
     * 付款成功后回调举例：
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
        $tip_url=Yii::app()->request->getParam('tip_url',''); //用于提示付款结果的地址
        $do_url=Yii::app()->request->getParam('do_url',''); //用于进行后续操作的URL
        
        //获取secret
//        $client_id=Yii::app()->request->getParam('client_id','none id');
        $client_secret=Yii::app()->request->getParam('client_secret','none secret');
        
        
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
        $return_url=$hostInfo.$this->createUrl('recall',array('success'=>'true','uid'=>$uid,'recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
//        echo '<br/>';
        $cancel_url=$hostInfo.$this->createUrl('recall',array('success'=>'false','uid'=>$uid,'recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
//        echo '<br/>';
        if('' == $tip_url){
            $tip_url=$hostInfo.$this->createUrl('paidtip',array());
//            echo '<br/>';
        }
        if('' == $do_url){
            $do_url=$hostInfo.$this->createUrl('paiddo',array());
//            echo '<br/>';
        }
        
//        echo $ipn_url=$hostInfo.$this->createUrl('paiddo',array('uid'=>$uid,'masksign'=>$masksign,'recordid'=>$insert_id,'record_masksign'=>$record_masksign,));
//        echo '<br/>';
        $client_id=$userdef_arr['client_id'];
                
        $api_creater=new CPaypalApiCreater($client_id,$client_secret,PUB_PAYPAL_SDK_DIR);
//        $api_creater->setIpnUrl($ipn_url);
        
        $paypal_handler=new CPaypalHandler($api_creater->getApiContext());
        $paypal_handler->setReturnUrl($return_url);
        $paypal_handler->setCancelUrl($cancel_url);
        
        
        print_r($price_arr);
        foreach($price_arr as $index=>$value){
            if(!isset($quantity_arr[$index])){
                $quantity_arr[$index]=1;
            }
            if(!isset($itemname_arr[$index])){
                $itemname_arr[$index]='CCY Payment.';
            }
            
            echo "{$value}, {$itemname_arr[$index]},{$quantity_arr[$index]},{$currency}";
            $paypal_handler->addItem(sprintf("%.2f",$value), $itemname_arr[$index],$quantity_arr[$index],$currency);

        }
        echo 'RUN 1 <br/>';
        
        $paypal_handler->setDetails($shipping, $tax);
        $paymentObj= $paypal_handler->createPaymentObj();
        $redirect_url=CPaypalHandler::ExtractApprovalUrl($paymentObj);
        $payment_id=CPaypalHandler::ExtractId($paymentObj);
        
        //存储：$payid，$token，$post_json
        $result_arr=array();
        $result_arr['status']='success';
        $result_arr['payment_url']=$redirect_url;
        $result_arr['payment_id']=$payment_id;
        $result_arr['client_id']=$client_id;
        $result_arr['client_secret']=$client_secret;
        $result_arr['tip_url']=$tip_url;
        $result_arr['do_url']=$do_url;
        
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
        $payer_id=trim(Yii::app()->request->getQuery('PayerID','')); 
        
        //验证请求合法性
//        $hostInfo=Yii::app()->request->hostInfo;
        $userdef_arr=CUser::GetAccountDefined($uid);
        if($record_masksign != md5($recordid.$userdef_arr['token'])){
            throw new Exception('Authorization Failed.','141029_1103');
        }
        
        //获取数据
        $dbinfo=CDbPayportPayment::model()->findByPk($recordid);
        //正式进入执行操作--------
        /* @var $dbinfo CDbPayportPayment */
        if('' == $dbinfo->payment_json){
            throw new Exception('Record Error. payment_json is empty. ','141029_1109');
        }
        
        $payment_obj=  json_decode($dbinfo->payment_json, false);
        if('' == $payment_obj->payment_id){
            throw new Exception('Record Error. payment_json->payment_id is empty. ','141029_1112');
        }
        
        if('false' == $success){
            $cancel_redirect_url=$this->_urlAddParam($payment_obj->tip_url, array('is_pay_success'=>'false',));
            Yii::app()->request->redirect($cancel_redirect_url);
            Yii::app()->end();
        }
        
        //如果成功那么肯定有 $payer_id 
        if('' == $payer_id){
            throw new Exception('payer_id not empty.','141029_1215');
        }
        
        
        //获取执行对象
//        $hostInfo=Yii::app()->request->hostInfo;
//        $ipn_url=$hostInfo.$this->createUrl('ipn',array('recordid'=>$recordid,'record_masksign'=>$record_masksign,));
//        echo $ipn_url;
//        echo '<br/>';
        
        $api_creater=new CPaypalApiCreater($payment_obj->client_id,$payment_obj->client_secret,PUB_PAYPAL_SDK_DIR);
//        $api_creater->setIpnUrl($ipn_url);
         
        $paypal_handler=new CPaypalHandler($api_creater->getApiContext());
        $result=$paypal_handler->executePayment($payment_obj->payment_id,$payer_id);
        
        

        $tip_url=$this->_urlAddParam($payment_obj->tip_url, array('is_pay_success'=>'true',));
//        echo '<br/>';
        $do_url=$this->_urlAddParam($payment_obj->do_url, array('recordid'=>$recordid,'record_masksign'=>$record_masksign,));
//        echo '<br/>';
        
        $post_sender=new CJKPostSender();
        $post_sender->setSender($do_url, array('post_json'=>$dbinfo->post_json,));
        $post_sender->getDatas();
        
        Yii::app()->request->redirect($tip_url);
        Yii::app()->end();
        
//        print_r($result);
    }
    
    /**
     * 给一个已有的URL 尾部添加参数
     * @param array $param_arr 参数数组
     * @return string
     */
    private function _urlAddParam($url,array $param_arr){
        if(!preg_match('/\?/iU', $url)){
            $url.='?';
        }else{
            $url.='&';
        }
        
        foreach($param_arr as $param_key=>$param_value){
            $param_value_encode=  urlencode($param_value);
            $url.="{$param_key}={$param_value_encode}&";
        }
        
        $url.='1=1';
        
        return $url;
    }
}

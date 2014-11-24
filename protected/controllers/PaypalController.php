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
     * 发送Email
     * http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/sendemail&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c&m_fromname=service&m_address=kami@cancanyou.com&m_replyto=meetcancanyou@yahoo.com&m_subject=subject_text222&m_body=body_text222
     * 
     */
    public function actionSendemail(){
        //获取请求的地址信息
        $uid=Yii::app()->request->getQuery('uid','0');
        $masksign=Yii::app()->request->getQuery('masksign','');
        
        
        //效验请求合法性
        if(!CUser::CheckValid($uid,$masksign)){
            return $this->_resultJson(false, new Exception('无法识别调用用户','141022_1027'));
        }
        
        //-----
        $m_replyto=Yii::app()->request->getParam('m_replyto');
        $m_fromname=Yii::app()->request->getParam('m_fromname');
        $m_address=Yii::app()->request->getParam('m_address');
        $m_subject=Yii::app()->request->getParam('m_subject');
        $m_body=Yii::app()->request->getParam('m_body');
        
        
        $file_name_emailsmtp= Yii::app()->getBasePath().'/config/emailsmtp.conf.php'; //配置文件
        $emailsmtp_conf_arr=@include $file_name_emailsmtp ; //读取并加载
        
        if(!is_array($emailsmtp_conf_arr)){
            return $this->_resultJson(false,  new Exception('请先配置SMTP 服务器','141120_1759'));
        }
        
        Yii::app()->mailer->Host = $emailsmtp_conf_arr['smtp_host'];
        Yii::app()->mailer->Username = $emailsmtp_conf_arr['smtp_user'];  // SMTP username
        Yii::app()->mailer->Password = $emailsmtp_conf_arr['smtp_pwd']; // SMTP password
        
        Yii::app()->mailer->IsSMTP();
        Yii::app()->mailer->SMTPAuth = true;
        
        Yii::app()->mailer->From = "service@cancanyou.com";
        Yii::app()->mailer->FromName = $m_fromname;
        Yii::app()->mailer->AddAddress($m_address, $m_address);                // name is optional
        if('' != $m_replyto){
            Yii::app()->mailer->AddReplyTo($m_replyto, $m_replyto);
        }

//        Yii::app()->mailer->WordWrap = 50;                                 // set word wrap to 50 characters
//        Yii::app()->mailer->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
//        Yii::app()->mailer->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
        Yii::app()->mailer->IsHTML(true);                                  // set email format to HTML

        Yii::app()->mailer->Subject = $m_subject;
        Yii::app()->mailer->Body    = $m_body;
//        Yii::app()->mailer->AltBody = "This is the body in plain text for non-HTML mail clients99";

        
        if(!Yii::app()->mailer->Send()){
            //echo Yii::app()->mailer->ErrorInfo;
            return $this->_resultJson(false, new Exception('邮件发送失败：'.Yii::app()->mailer->ErrorInfo,'141120_2209'));
        }
        
        return $this->_resultJson(true,true);
    }
    
    /**
     * 用来生成并返回结果JSON 数据
     * @param boolean $is_success
     * @param mixed $back_value
     * @return array
     */
    protected function _resultJson($is_success,$back_value){
        $result_arr=array();
        if($is_success){
            $result_arr['result']="success";
            $result_arr['back_value']=$back_value;
        }else{
            /* @var $back_value Exception */
            $result_arr['result']="failure";
            $result_arr['error_code']=$back_value->getCode();
            $result_arr['error_info']=$back_value->getMessage();
        }
        return $this->renderPartial('_result_json',array('result_arr'=>$result_arr,),$this->is_jktesting);
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
     * 显示JSON 信息
     */
    public function actionShowjson(){
        $json_str=Yii::app()->request->getPost('json_str');
        
        $json=  json_decode(base64_decode($json_str));
        if(is_string($json)){
            echo $json;
        }else{
            echo '<pre>';
            print_r($json);
            echo '</pre>';
        }
    }


    /**
     * 显示付款列表
     * @return array
     */
    public function actionPaymentlist(){
        if(Yii::app()->user->isGuest){
            return Yii::app()->request->redirect($this->createUrl('site/login'));
        }
        
        $payportmentobj_arr=CDbPayportPayment::model()->findAll ('1=1 ORDER BY id DESC');
        return $this->render('paymentlist', array('payportmentobj_arr'=>$payportmentobj_arr,));
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
        
        echo 'Pay do.';
        
        ###########
//        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_GET,true), 'jkdebug.PaypalController.actionIpn');
//        Yii::trace(date('Y-m-d H-i-s')."\n".print_r($_POST,true), 'jkdebug.PaypalController.actionIpn');
//        Yii::trace('=======================','jkdebug.PaypalController.actionIpn');
    }
    

    /**
     * 创建付款页面
     * 测试页面：http://develop.jk-payport.git.cancanyou.com/test_index.php?r=paypal/payment&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c&price_arr[0]=5&client_secret=EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp
     * 页面返回JSON 类似：{"result":"success","redirect_url":"https:\/\/www.sandbox.paypal.com\/cgi-bin\/webscr?cmd=_express-checkout&token=EC-0TF07914SR289291E","payment_id":"PAY-8V260968H2085143JKRILQXQ","client_id":"AfSbYRAe0Li9JullQ41NFRZrSlOyDrs_TnOzwmXio7uk8-0TOS86vYWXRsF-","client_secret":"EPkh2BDXwnw3604-BQa4Hxdu1aZWAAjStHeymfOsveTE-8m5YsG_VhBlUXIp","tip_url":"http:\/\/develop.jk-payport.git.cancanyou.com\/test_index.php?r=paypal\/paidtip","do_url":"http:\/\/develop.jk-payport.git.cancanyou.com\/test_index.php?r=paypal\/paiddo"}
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
        $oper->do_response='';
        $oper->get_json=  json_encode($_GET);
        $oper->payment_json= '';
        $insert_id=0;
        if($oper->insert()){
            $insert_id=$oper->getPrimaryKey();
        }
        
        if(0 == (int)$insert_id){
            return $this->_resultJson(false, new Exception('Db error. create a payment. ','141120_1021'));
//            $result_arr=array();
//            $result_arr['status']='failure';
//            $result_arr['info']='Db error. create a payment. ';
//            $result_arr['code']='100001';
//            return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
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
        
        foreach($price_arr as $index=>$value){
            if(!isset($quantity_arr[$index])){
                $quantity_arr[$index]=1;
            }
            if(!isset($itemname_arr[$index])){
                $itemname_arr[$index]='CCY Payment.';
            }
            
            $paypal_handler->addItem($value, $itemname_arr[$index],$quantity_arr[$index],$currency);

        }
        
        
        $paypal_handler->setDetails($shipping, $tax);
        $paymentObj= $paypal_handler->createPaymentObj();
        $redirect_url=CPaypalHandler::ExtractApprovalUrl($paymentObj);
        $payment_id=CPaypalHandler::ExtractId($paymentObj);
        
        //存储：$payid，$token，$post_json
        $result_arr=array();
//        $result_arr['status']='success';
        $result_arr['payment_url']=$redirect_url;
        $result_arr['payment_id']=$payment_id;
        $result_arr['client_id']=$client_id;
        $result_arr['client_secret']=$client_secret;
        $result_arr['tip_url']=$tip_url;
        $result_arr['do_url']=$do_url;
        
        $oper->payment_json=  json_encode($result_arr); //将结果数据记录到数据库
        if(!$oper->update()){
            return $this->_resultJson(false, new Exception('Db error. update a payment. ','141120_1022'));
//            $result_arr=array();
//            $result_arr['status']='failure';
//            $result_arr['info']='Db error. update a payment. ';
//            $result_arr['code']='100002';
//            return $this->renderPartial('payment',array('result_arr'=>$result_arr,),$this->is_jktesting);
        }
        
        return $this->_resultJson(true,$result_arr);
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
        /* @var $result PayPal\Api\Payment */
        $payer_info_obj=$result->getPayer()->getPayerInfo();
        $payer_info_arr=array();
        $payer_info_arr['email']=$payer_info_obj->getEmail();
        $payer_info_arr['firstname']=$payer_info_obj->getFirstName();
        $payer_info_arr['lastname']=$payer_info_obj->getLastName();
        $payer_info_arr['payerid']=$payer_info_obj->getPayerId();
        $payer_info_obj_shippingaddress_obj=$payer_info_obj->getShippingAddress();
        $payer_info_arr['shipping_address']=array();
        $payer_info_arr['shipping_address']['line1']=$payer_info_obj_shippingaddress_obj->getLine1();
        $payer_info_arr['shipping_address']['line2']=$payer_info_obj_shippingaddress_obj->getLine2();
        $payer_info_arr['shipping_address']['city']=$payer_info_obj_shippingaddress_obj->getCity();
        $payer_info_arr['shipping_address']['state']=$payer_info_obj_shippingaddress_obj->getState();
        $payer_info_arr['shipping_address']['postalcode']=$payer_info_obj_shippingaddress_obj->getPostalCode();
        $payer_info_arr['shipping_address']['countrycode']=$payer_info_obj_shippingaddress_obj->getCountryCode();
        $payer_info_arr['shipping_address']['recipientname']=$payer_info_obj_shippingaddress_obj->getRecipientName();
        
//        Yii::trace(date('Y-m-d H-i-s')."\n".  '--'.print_r($payer_info_arr,true).'--', 'jkdebug.PaypalController.actionRecall'); 

        $tip_url=$this->_urlAddParam($payment_obj->tip_url, array('is_pay_success'=>'true',));
//        echo '<br/>';
        $do_url=$this->_urlAddParam($payment_obj->do_url, array('recordid'=>$recordid,'record_masksign'=>$record_masksign,));
//        echo '<br/>';
        
        $dbinfo->payerinfo=  json_encode($payer_info_arr);
        $dbinfo->update();
        
        $post_sender=new CJKPostSender();
        $post_sender->setSender($do_url, array('post_json'=>$dbinfo->post_json,'payerinfo'=>$dbinfo->payerinfo));
        $response_data=$post_sender->getDatas();
        $dbinfo->do_response=  json_encode($response_data);
        $dbinfo->update();
        
        //Yii::trace(date('Y-m-d H-i-s')."\n".$response_data, 'DO_URL RESPONSE.');
        
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

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
     * 创建付款页面
     * 测试页面：http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/payment&uid=1&masksign=2fc7fd70fd1aafe36db926519507f77c&price_arr[0]=2.77
     * 页面生成：          https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=EC-7BG06900LT687871A 
     * 付款成功后回调举例：http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/recall&success=true&token=EC-7BG06900LT687871A&PayerID=RBJN2EXHT9MJY
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
        
        $hostInfo=Yii::app()->request->hostInfo;
        $return_url=$hostInfo.$this->createUrl('recall',array('success'=>'true',));
        $cancel_url=$hostInfo.$this->createUrl('recall',array('success'=>'false',));
       
        $paypal_handler=new CPaypalHandler($return_url,$cancel_url);
        
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
        preg_match('/&token=(.*?)/iU', $redirect_url,$match_arr);
        print_r($match_arr);
        echo '<br/>';
        
        $this->layout='';
        return $this->render('payment',array('name'=>'lin',),$this->is_jktesting);
    }
}

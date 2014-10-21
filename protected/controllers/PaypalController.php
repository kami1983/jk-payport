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
     */
    public function actionPayment(){
        
        $paypal_handler=new CPaypalHandler();
        $paypal_handler->createPayment();
        
        $this->render('payment');
    }
}

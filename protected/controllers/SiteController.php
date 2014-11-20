<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions(){
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex(){
		// renders the view file 'protected/views/site/index.php'
		// using the default layout 'protected/views/layouts/main.php'
		$this->render('index');
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
        
        
       /**
        * 程序设置页面
        * 
        */
       public function actionSetting(){
           
           $is_post_change=false;
           
           
           //--------- 管理员配置文件修改
           $file_name_adminlist=Yii::app()->getBasePath().'/config/adminlist.conf.php'; //配置文件
           $adminlist_conf_arr= @include $file_name_adminlist; //读取并加载
           
           $post_adminlist_conf_arr_name=Yii::app()->request->getPost('adminlist_conf_arr_name');
           if(is_array($post_adminlist_conf_arr_name)){ //如果有修改
                $is_post_change=true;
                $post_adminlist_conf_arr_pwd=Yii::app()->request->getPost('adminlist_conf_arr_pwd');

                $content='<?php $user_def=array(); ';
                $content.="\n";
                foreach($post_adminlist_conf_arr_name as $index=>$adminname){
                    $password=$post_adminlist_conf_arr_pwd[$index];
                    if('' == trim($adminname) || '' == $password)
                                                continue;
                    $content.='$user_def["'.$adminname.'"]="'.$password.'";';
                    $content.="\n";
                }
                $content.='return $user_def;';
                
                @file_put_contents($file_name_adminlist, $content);
           }
           
           //--------- END
           //--------- 管理员配置文件修改
           
           $file_name_userlist= Yii::app()->getBasePath().'/config/userlist.conf.php'; //配置文件
           $userlist_conf_arr=@include $file_name_userlist ; //读取并加载
           
           $userlist_conf_arr_token=Yii::app()->request->getPost('userlist_conf_arr_token');
           
           if(is_array($userlist_conf_arr_token)){ //如果有修改
                $is_post_change=true;
                $userlist_conf_arr_client_id=Yii::app()->request->getPost('userlist_conf_arr_client_id');


                $content='<?php $user_def=array(); ';
                $content.="\n";
                foreach($userlist_conf_arr_token as $index=>$token){
                    $client_id=$userlist_conf_arr_client_id[$index];
                    if('' == trim($token) || '' == $client_id)
                                                continue;
                    
                    $user_index=$index+1;
                    $content.="\$user_def['{$user_index}']=array();";
                    $content.="\n";
                    $content.="\$user_def['{$user_index}']['token']='{$token}';";
                    $content.="\n";
                    $content.="\$user_def['{$user_index}']['client_id']='{$client_id}';";
                    $content.="\n";
                }
                $content.='return $user_def;';
                @file_put_contents($file_name_userlist, $content);
           }
           
           
           //--------- END
           //--------- 邮件发送器的配置
           
           $file_name_emailsmtp= Yii::app()->getBasePath().'/config/emailsmtp.conf.php'; //配置文件
           $emailsmtp_conf_arr=@include $file_name_emailsmtp ; //读取并加载

           $emailsmtp_conf_smtp_host=trim(Yii::app()->request->getPost('emailsmtp_conf_smtp_host'));
           $emailsmtp_conf_smtp_user=trim(Yii::app()->request->getPost('emailsmtp_conf_smtp_user'));
           $emailsmtp_conf_smtp_pwd=Yii::app()->request->getPost('emailsmtp_conf_smtp_pwd');
           if('' != $emailsmtp_conf_smtp_host){ //如果有修改
                $is_post_change=true;
                


                $content='<?php $user_def=array(); ';
                $content.="\n";
                
                $content.="\$user_def['smtp_host']='{$emailsmtp_conf_smtp_host}';";
                $content.="\n";
                $content.="\$user_def['smtp_user']='{$emailsmtp_conf_smtp_user}';";
                $content.="\n";
                $content.="\$user_def['smtp_pwd']='{$emailsmtp_conf_smtp_pwd}';";
                $content.="\n";
                
                $content.='return $user_def;';
                @file_put_contents($file_name_emailsmtp, $content);
           }
           
           
           
           //--------- END
           
           if($is_post_change){
                return Yii::app()->request->redirect($this->createUrl('setting'));
           }
           
           $test_emailaddress=trim(Yii::app()->request->getPost('test_emailaddress'));
           if('' != $test_emailaddress){
                Yii::app()->mailer->Host = $emailsmtp_conf_arr['smtp_host'];
                Yii::app()->mailer->Username = $emailsmtp_conf_arr['smtp_user'];  // SMTP username
                Yii::app()->mailer->Password = $emailsmtp_conf_arr['smtp_pwd']; // SMTP password
                Yii::app()->mailer->IsSMTP();
                if('' != Yii::app()->mailer->Username){
                    Yii::app()->mailer->SMTPAuth = true;
                }

                Yii::app()->mailer->From = $emailsmtp_conf_arr['smtp_user'];
                Yii::app()->mailer->FromName = "Payport email service test.";
                Yii::app()->mailer->AddAddress($test_emailaddress,"Your name.");    
//                Yii::app()->mailer->AddAddress("kami@cancanyou.com", "Hello Kami");
//                Yii::app()->mailer->AddReplyTo($emailsmtp_conf_arr['smtp_user'], "Payport Service");

        //        Yii::app()->mailer->WordWrap = 50;                                 // set word wrap to 50 characters
        //        Yii::app()->mailer->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
        //        Yii::app()->mailer->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
                Yii::app()->mailer->IsHTML(true);                                  // set email format to HTML

                Yii::app()->mailer->Subject = "Here is the subject";
                Yii::app()->mailer->Body    = "This is the HTML message body <b>in bold!</b>";
                Yii::app()->mailer->AltBody = "This is the body in plain text for non-HTML mail clients";

                if(!Yii::app()->mailer->Send()){
                   echo "Message could not be sent. <p>";
                   echo "Mailer Error: " . Yii::app()->mailer->ErrorInfo;
                   exit;
                }
           }
           
           
           // display the login form
           $this->render('setting', array('adminlist_conf_arr'=>$adminlist_conf_arr,
                                            'userlist_conf_arr'=>$userlist_conf_arr,
                                            'emailsmtp_conf_arr'=>$emailsmtp_conf_arr,));
       }

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
            
            
//        $mailer = Yii::createComponent('application.extensions.mailer.EMailer');
        
//        Yii::app()->mailer->Host = 'smtp.ym.163.com';
//        Yii::app()->mailer->Username = "service@cancanyou.com";  // SMTP username
//        Yii::app()->mailer->Password = "service"; // SMTP password
//        Yii::app()->mailer->IsSMTP();
//        Yii::app()->mailer->SMTPAuth = true;
//        
//        Yii::app()->mailer->From = "service@cancanyou.com";
//        Yii::app()->mailer->FromName = "Customer Service";
//        Yii::app()->mailer->AddAddress("kami@cancanyou.com", "Hello Kami");
//        Yii::app()->mailer->AddAddress("linhai_q8@163.com","Hello linhai");                  // name is optional
//        Yii::app()->mailer->AddReplyTo("service@cancanyou.com", "Replay service");
//
////        Yii::app()->mailer->WordWrap = 50;                                 // set word wrap to 50 characters
////        Yii::app()->mailer->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
////        Yii::app()->mailer->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
//        Yii::app()->mailer->IsHTML(true);                                  // set email format to HTML
//
//        Yii::app()->mailer->Subject = "Here is the subject";
//        Yii::app()->mailer->Body    = "This is the HTML message body <b>in bold!</b>";
//        Yii::app()->mailer->AltBody = "This is the body in plain text for non-HTML mail clients";
//
//        if(!Yii::app()->mailer->Send())
//        {
//           echo "Message could not be sent. <p>";
//           echo "Mailer Error: " . Yii::app()->mailer->ErrorInfo;
//           exit;
//        }

//        echo "Message has been sent";
        
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
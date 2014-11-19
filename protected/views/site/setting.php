<?php
/* @var $this SiteController */
/* @var $adminlist_conf_arr array */
/* @var $userlist_conf_arr array */

?>

<h1>Set admin list.</h1>

<hr/>

<form action='<?php echo $this->createUrl('setting', array()); ?>' method="POST" >
<?php foreach($adminlist_conf_arr as $name=>$pwd): ?>
<p>
修改用户名：<input name='adminlist_conf_arr_name[]' type='text' value='<?php echo $name; ?>' />
修改密码：<input name='adminlist_conf_arr_pwd[]' type='text' value='<?php echo $pwd; ?>' />
</p>
<?php endforeach; ?>
<p>
新增用户名：<input name='adminlist_conf_arr_name[]' type='text' value='' />
新增密码：<input name='adminlist_conf_arr_pwd[]' type='text' value='' />
</p>
<p>
<input type='submit' value='SUBMIT' />
</p>
</form>



<h1>Set paypal token list.</h1>
<hr/>
<form action='<?php echo $this->createUrl('setting', array()); ?>' method="POST"  >
<?php foreach($userlist_conf_arr as $conf_arr): ?>
<p>
修改 TOKEN：<input name='userlist_conf_arr_token[]' type='text' value='<?php echo $conf_arr['token']; ?>' />
修改 CLIENT ID：<input name='userlist_conf_arr_client_id[]' type='text' value='<?php echo $conf_arr['client_id']; ?>' />
</p>
<?php endforeach; ?>
<p>
新增 TOKEN：<input name='userlist_conf_arr_token[]' type='text' value='' />
新增 CLIENT ID：<input name='userlist_conf_arr_client_id[]' type='text' value='' />
</p>
<p>
<input type='submit' value='SUBMIT' />
</p>
</form>

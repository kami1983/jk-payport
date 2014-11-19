<?php
/* @var $this SiteController */
/* @var $adminlist_conf_arr array */
/* @var $userlist_conf_arr array */


?>

<h1>Set admin list.</h1>
<hr/>

<form action='<?php $this->createUrl('setting', array()); ?>'  >
<?php foreach($adminlist_conf_arr as $name=>$pwd): ?>
<p>
用户名：<input name='adminlist_conf_arr_name[]' type='text' value='<?php echo $name; ?>' />
密码：<input name='adminlist_conf_arr_pwd[]' type='text' value='<?php echo $pwd; ?>' />
</p>
<?php endforeach; ?>
<input type='submit' value='SUBMIT' />
</form>


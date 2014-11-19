<?php
/* @var $this SiteController */
/* @var $adminlist_conf_arr array */
/* @var $userlist_conf_arr array */


?>

<h1>Set admin list.</h1>
<hr/>
<?php foreach($adminlist_conf_arr as $name=>$pwd): ?>
<p>
用户名：<?php echo $name; ?> 
密码：<?php echo $pwd; ?> 
</p>
<?php endforeach; ?>


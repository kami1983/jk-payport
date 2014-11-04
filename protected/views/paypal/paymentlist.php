<?php
/* @var $this PaypalController */
/* @var $payportmentobj_arr CDbPayportPayment[] */

//echo '<pre>';
//print_r($payportmentobj_arr);
//echo '</pre>';
?>
<table>
    <tr>
        <td>Id</td>
        <td>IP</td>
        <td style="width:100px;overflow:hidden;">Do Response</td>
        <td style="width:100px;overflow:hidden;">Payment Info</td>
        <td style="width:100px;overflow:hidden;">Payment Get</td>
        <td style="width:100px;overflow:hidden;">Payment Post</td>
        
    </tr>
    <?php foreach($payportmentobj_arr as $payportmentobj): ?>
    <tr>
        <td><?php echo $payportmentobj->id; ?></td>
        <td><?php echo $payportmentobj->ipaddress; ?></td>
        <td><input type='button' value="do_response" onclick="alert('<?php echo htmlentities($payportmentobj->do_response); ?>');" /></td>
        <td><input type='button' value="payment_json" onclick="alert('<?php echo htmlentities($payportmentobj->payment_json); ?>');" /></td>
        <td><input type='button' value="get_json" onclick="alert('<?php echo htmlentities($payportmentobj->get_json); ?>');" /></td>
        <td><input type='button' value="post_json" onclick="alert('<?php echo htmlentities($payportmentobj->post_json); ?>');" /></td>
    </tr>
    <?php endforeach; ?>
</table>
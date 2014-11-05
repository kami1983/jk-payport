<?php
/* @var $this PaypalController */
/* @var $payportmentobj_arr CDbPayportPayment[] */

//echo '<pre>';
//print_r($payportmentobj_arr);
//echo '</pre>'; http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/paymentlist
?>
<form action="<?php echo $this->createUrl('paypal/showjson');?>" method="POST" target="_black">
    <input id="id_json_str" name="json_str" type="text" value="" />
<table>
    <tr>
        <td>Id</td>
        <td>IP</td>
        <td>Do Response</td>
        <td>Payment Info</td>
        <td>Payment Get</td>
        <td>Payment Post</td>
        
    </tr>
    <?php foreach($payportmentobj_arr as $payportmentobj): ?>
    <tr>
        <td><?php echo $payportmentobj->id; ?></td>
        <td><?php echo $payportmentobj->ipaddress; ?></td>
        <td>
            <?php if('' != $payportmentobj->do_response): ?>
            <input type='button' value="do_response" onclick="$('#id_json_str').val('<?php echo base64_encode($payportmentobj->do_response); ?>');" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->payment_json): ?>
            <input type='button' value="payment_json" onclick="$('#id_json_str').val('<?php echo base64_encode($payportmentobj->payment_json); ?>');" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->get_json): ?>
            <input type='button' value="get_json" onclick="$('#id_json_str').val('<?php echo base64_encode($payportmentobj->get_json); ?>');" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->post_json): ?>
            <input type='button' value="post_json" onclick="$('#id_json_str').val('<?php echo base64_encode($payportmentobj->post_json); ?>');" />
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</form>    
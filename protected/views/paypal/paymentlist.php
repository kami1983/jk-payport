<?php
/* @var $this PaypalController */
/* @var $payportmentobj_arr CDbPayportPayment[] */

//echo '<pre>';
//print_r($payportmentobj_arr);
//echo '</pre>'; http://develop.jk-payport.git.cancanyou.com/index.php?r=paypal/paymentlist
?>
<form action="<?php echo $this->createUrl('paypal/showjson');?>" method="POST" target="_blank">
    <input id="id_json_str" name="json_str" type="hidden" value="" />
<table>
    <tr>
        <td>Id</td>
        <td>IP</td>
        <td>Do Response</td>
        <td>Payer Info</td>
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
            <input type='submit' value="do_response" onclick="document.getElementById('id_json_str').value='<?php echo base64_encode($payportmentobj->do_response); ?>';" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->payerinfo): ?>
            <input type='submit' value="payerinfo" onclick="document.getElementById('id_json_str').value='<?php echo base64_encode($payportmentobj->payerinfo); ?>';" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->payment_json): ?>
            <input type='submit' value="payment_json" onclick="document.getElementById('id_json_str').value='<?php echo base64_encode($payportmentobj->payment_json); ?>';" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->get_json): ?>
            <input type='submit' value="get_json" onclick="document.getElementById('id_json_str').value='<?php echo base64_encode($payportmentobj->get_json); ?>';" />
            <?php endif; ?>
        </td>
        <td>
            <?php if('' != $payportmentobj->post_json): ?>
            <input type='submit' value="post_json" onclick="document.getElementById('id_json_str').value='<?php echo base64_encode($payportmentobj->post_json); ?>';" />
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</form>    
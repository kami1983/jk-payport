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
        <td style="width:100px;table-layout:fixed; word-break: break-all; overflow:hidden;">Do Response</td>
        <td style="width:100px;table-layout:fixed; word-break: break-all; overflow:hidden;">Payment Info</td>
        <td style="width:100px;table-layout:fixed; word-break: break-all; overflow:hidden;">Payment Get</td>
        <td style="width:100px;table-layout:fixed; word-break: break-all; overflow:hidden;">Payment Post</td>
        
    </tr>
    <?php foreach($payportmentobj_arr as $payportmentobj): ?>
    <tr>
        <td><?php echo $payportmentobj->id; ?></td>
        <td><?php echo $payportmentobj->ipaddress; ?></td>
        <td><?php echo $payportmentobj->do_response; ?></td>
        <td><?php echo $payportmentobj->payment_json; ?></td>
        <td><?php echo $payportmentobj->get_json; ?></td>
        <td><?php echo $payportmentobj->post_json; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
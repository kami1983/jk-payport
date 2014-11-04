<?php
/* @var $this PaypalController */
/* @var $payportmentobj_arr CDbPayportPayment[] */

echo '<pre>';
print_r($payportmentobj_arr);
echo '</pre>';
?>
<table>
    <tr>
        <td>Id</td>
        <td>IP</td>
        <td>Payment Info</td>
        <td>Payment Get</td>
        <td>Payment Post</td>
        
    </tr>
    <?php foreach($payportmentobj_arr as $payportmentobj): ?>
    <tr>
        <td><?php $payportmentobj->id; ?></td>
        <td><?php $payportmentobj->ipaddress; ?></td>
        <td><?php $payportmentobj->payment_json; ?></td>
        <td><?php $payportmentobj->get_json; ?></td>
        <td><?php $payportmentobj->post_json; ?></td>
    </tr>
    <?php endforeach; ?>
</table>
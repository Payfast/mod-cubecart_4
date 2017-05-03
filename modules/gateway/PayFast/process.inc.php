<?php
if( !defined( 'CC_INI_SET' ) ) die( "Access Denied" );
/**
 * process.inc.php
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Jonathan Smit
 * @link       http://www.payfast.co.za/help/cube_cart
 */

$status = $db->select(
    "SELECT `status`
    FROM `". $glob['dbprefix'] ."CubeCart_order_sum`
    WHERE `cart_order_id` = ". $db->MySQLSafe( $_GET['cart_order_id'] ) );

if( $status == TRUE )
{
    // Used in remote.php $cart_order_id is important for failed orders
    $cart_order_id = $_GET['cart_order_id'];

	if( $status[0]['status'] == 2 || $status[0]['status'] == 3 )
		$paymentResult = 2; // Success
	elseif($_GET['c']==1)
		$paymentResult = 1;
	else
		$paymentResult = 3; // Not processed yet or unknown
}
else
	die( "<strong>Fatal Error:</strong> Order id not found!" );
?>
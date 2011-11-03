<?php
if( !defined( 'CC_INI_SET' ) ) die( "Access Denied" );
/**
 * process.inc.php
 *
 * Copyright (c) 2009-2011 PayFast (Pty) Ltd
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.
 * 
 * @author     Jonathan Smit
 * @copyright  Portions Copyright Devellion Limited 2006
 * @copyright  2009-2011 PayFast (Pty) Ltd
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
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
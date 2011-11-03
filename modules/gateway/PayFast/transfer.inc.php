<?php
if( !defined( 'CC_INI_SET' ) ) die( "Access Denied" );
/**
 * transfer.inc.php
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

// {{{ repeatVars()
/**
 * repeatVars
 */
function repeatVars()
{
    return( false );
}
// }}}
// {{{ fixedVars()
/**
 * fixedVars
 */
function fixedVars()
{
    // Variable initialization
	global $module, $orderSum, $config;
    $basket = unserialize( $orderSum['basket'] );

    // Include PayFast common file
    define( 'PF_DEBUG', ( $module['debug_log'] ? true : false ) );
    include_once( 'payfast_common.inc' );

    // Use appropriate merchant identifiers
    // Live
    if( $module['server'] == 1 )
    {
        $merchantId = $module['merchant_id']; 
        $merchantKey = $module['merchant_key'];
    }
    // Sandbox
    else
    {
        $merchantId = '10000100'; 
        $merchantKey = '46f0cd694581a';
    }

    // Create URLs
	$returnUrl = $GLOBALS['storeURL'] .'/index.php?_g=rm&amp;type=gateway&amp;cmd=process&amp;module=PayFast&amp;cart_order_id='.$orderSum['cart_order_id'];
	$cancelUrl = $GLOBALS['storeURL'] .'/index.php?_g=rm&amp;type=gateway&amp;cmd=process&amp;module=PayFast&amp;cart_order_id='.$orderSum['cart_order_id'].'&amp;c=1';
    $notifyUrl = $GLOBALS['storeURL'] .'/index.php?_g=rm&amp;type=gateway&amp;cmd=call&amp;module=PayFast';

    // Get customer name
    $billingName = makeName( $orderSum['name'] );

    // Create description
    $description = '';
    foreach( $basket['invArray'] as $item )
        $description .= $item['quantity'] .' x '. $item['name'] .' @ '.
            number_format( $item['price']/$item['quantity'], 2, '.', ',' ) .'ea = '.
            number_format( $item['price'], 2, '.', ',' ) .'; ';  
    $description .= 'Shipping = '. $basket['shipCost'] .'; ';
    $description .= 'Tax = '. $basket['tax'] .'; ';
    $description .= 'Total = '. $basket['grandTotal'];

    // Set data for form posting
    $data = array(
        //// Merchant details
        'merchant_id' => $merchantId,
        'merchant_key' => $merchantKey,
        'return_url' => $returnUrl,
        'cancel_url' => $cancelUrl,
        'notify_url' => $notifyUrl,

        //// Customer details
		'name_first' => substr( trim( $billingName[2] ), 0, 100 ),
		'name_last' => substr( trim( $billingName[3] ), 0, 100 ),
        'email_address' => substr( trim( $orderSum['email'] ), 0, 255 ),
		//'address1' => $orderSum['add_1'],
		//'address2' => $orderSum['add_2'],
		//'city' => $orderSum['town'],
		//'state' => $orderSum['county']
		//'country' => getCountryFormat( $orderSum['country'], "id", "iso" ),
		//'zip' => $orderSum['postcode'],

        //// Item details
		'item_name' => $GLOBALS['config']['storeName'] .' Purchase, Order #'. $orderSum['cart_order_id'],
		'item_description' => substr( trim( $description ), 0, 255 ),
        'amount' => number_format( $orderSum['prod_total'], 2, '.', '' ),
		'm_payment_id' => $orderSum['cart_order_id'],
		'currency_code' => $config['defaultCurrency'],
        
        // Other details
        'user_agent' => PF_USER_AGENT,
    );

    // Create hidden form variables
    $hiddenVars = '';
    foreach( $data as $key => $val )
	   $hiddenVars .= '<input type="hidden" name="'. $key .'" value="'. $val .'">';

	return( $hiddenVars );
}
// }}}

//// Select which gateway to use
// Sandbox (server = 0)
if( $module['server'] == 0 )
{
	$formAction = "https://sandbox.payfast.co.za/eng/process";
	$formMethod = "post";
	$formTarget = "_self";
}
// Live (server = 1)
else
{
	$formAction = "https://www.payfast.co.za/eng/process";
	$formMethod = "post";
	$formTarget = "_self";
}
?>
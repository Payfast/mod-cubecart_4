<?php
if( !defined( 'CC_INI_SET' ) ) die( "Access Denied" );
/**
 * call.inc.php
 * 
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * 
 * @author     Jonathan Smit
 * @link       http://www.payfast.co.za/help/cube_cart
 */

// Include PayFast common file
define( 'PF_DEBUG', ( $module['debug_log'] ? true : false ) );
include_once( 'payfast_common.inc' );

// Variable Initialization
$pfError = false;
$pfNotes = array();
$pfData = array();
$pfHost = ( ( $module['server'] != 1 ) ? 'sandbox' : 'www' ) .'.payfast.co.za';
$orderId = '';
$pfParamString = '';

$pfErrors = array();

pflog( 'PayFast ITN call received' );

//// Set debug email address
$pfDebugEmail = ( strlen( $module['debug_email'] ) > 0 ) ?
    $module['debug_email'] : $GLOBALS['config']['masterEmail'];

//// Notify PayFast that information has been received
if( !$pfError )
{
    header( 'HTTP/1.0 200 OK' );
    flush();
}

//// Get data sent by PayFast
if( !$pfError )
{
    pflog( 'Get posted data' );

    // Posted variables from ITN
    $pfData = pfGetData();

    pflog( 'PayFast Data: '. print_r( $pfData, true ) );

    if( $pfData === false )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_ACCESS;
    }
}

//// Verify security signature
if( !$pfError )
{
    pflog( 'Verify security signature' );

    // If signature different, log for debugging
    if( !pfValidSignature( $pfData, $pfParamString ) )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_INVALID_SIGNATURE;
    }
}

//// Verify source IP (If not in debug mode)
if( !$pfError && !PF_DEBUG )
{
    pflog( 'Verify source IP' );
    
    if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_SOURCE_IP;
    }
}

//// Retrieve order from CubeCart
if( !$pfError )
{
    pflog( 'Get order' );

    $orderId = $pfData['m_payment_id'];
    $order->getOrderSum( $orderId );

    pflog( 'Order ID = '. $orderId );
}

//// Verify data
if( !$pfError )
{
    pflog( 'Verify data received' );

    if( $config['proxy'] == 1 )
        $pfValid = pfValidData( $pfHost, $pfParamString, $config['proxyHost'] .":". $config['proxyPort'] );
    else
        $pfValid = pfValidData( $pfHost, $pfParamString );

    if( !$pfValid )
    {
        $pfError = true;
        $pfNotes[] = PF_ERR_BAD_ACCESS;
    }
}

//// Check status and update order & transaction table
if( !$pfError )
{
    pflog( 'Check status and update order' );

    $success = true;

	// Check the payment_status is Completed
	if( $pfData['payment_status'] !== 'COMPLETE' )
    {
		$success = false;

		switch( $pfData['payment_status'] )
        {
    		case 'FAILED':
                $pfNotes = PF_MSG_FAILED;
    			break;

			case 'PENDING':
                $pfNotes = PF_MSG_PENDING;
    			break;

			default:
                $pfNotes = PF_ERR_UNKNOWN;
    			break;
		}
	}

	// Check if the transaction has already been processed
	// This checks for a "transaction" in CubeCart of the same status (status)
    // for the same order (order_id) and same payfast payment id (trans_id)
	$trnId = $db->select(
        "SELECT `id`
        FROM `". $glob['dbprefix'] ."CubeCart_transactions`
        WHERE `order_id` = ". $db->mySQLsafe( $orderId ) ."
            AND `trans_id` = ". $db->mySQLsafe( $pfData['pf_payment_id'] ) ."
            AND `status` = ". $db->mySQLsafe( $pfData['payment_status'] ) );

	if( $trnId == true )
    {
		$success = false;
		$pfNotes[] = PF_ERR_ORDER_PROCESSED;
	}

	// Check PayFast amount matches order amount
	if( !pfAmountsEqual( $pfData['amount_gross'], $order->orderSum['prod_total'] ) )
    {
		$success = false;
		$pfNotes[] = PF_ERR_AMOUNT_MISMATCH;
	}

    // If transaction is successful and correct, update order status
	if( $success == true )
    {
		$pfNotes[] = PF_MSG_OK;
		$order->orderStatus( 3, $orderId );
	}
}

//// Insert transaction entry
// This gets done for every ITN call no matter whether successful or not.
// The notes field is used to provide feedback to the user.
pflog( 'Create transaction data and save' );

$pfNoteMsg = '';
if( sizeof( $pfNotes ) > 1 )
    foreach( $pfNotes as $note )
        $pfNoteMsg .= $note ."; ";
else
    $pfNoteMsg .= $pfNotes[0];

$transData = array();
$transData['customer_id'] = $order->orderSum["customer_id"];
$transData['gateway']     = "PayFast ITN";
$transData['trans_id']    = $pfData['pf_payment_id'];
$transData['order_id']    = $orderId;
$transData['status']      = $pfData['payment_status'];
$transData['amount']      = $pfData['amount_gross'];
$transData['notes']       = $pfNoteMsg;

pflog( "Transaction log data: \n". print_r( $transData, true ) );

$order->storeTrans( $transData );

// Close log
pflog( '', true );
?>
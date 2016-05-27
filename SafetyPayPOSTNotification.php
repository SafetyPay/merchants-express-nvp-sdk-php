<?php
/**
 * File: SafetyPayPOSTExpress.php
 * Author: SafetyPay Inc.
 * Description: Complete the notification process of paids orders.
 * @version 1.0
 * @package default
 * @license Open Software License (OSL 3.0)
 * Copyright 2012-2016 SafetyPay Inc. All rights reserved.
*******************************************************************************/

/*
 * Recommend put this file with public access.
 * This script will be executed by Express service.
 * Remember register the public URL where into the MMS application.
 */

ini_set('display_errors', 0);

// SafetyPay Proxy Class
require_once("class/SafetyPayProxyExpress.php");

$proxy = new SafetyPayProxyExpress();

// IMPORTANT!
// Debug enabled only Sandbox environment.
// For reason of security, delete the function "write_log"
$logg = $proxy->conf['Environment'];

// Confirm New Paid Orders
function ConfirmNewPaidOrders( $pRequest )
{
    global $proxy;
    
    $iError = 0;
    
    // HERE YOUR CODE TO UPGRADE YOUR ORDER NUMBER FROM A PENDING STATUS TO 
    // COMPLETED OR PAID STATUS.
    // OPTIONALLY CAN SEND A CONFIRMATION EMAIL AT SHOPPER.
    // USE $pRequest['MerchantSalesID'] TO SELECT YOUR REFERENCE ORDER.
    // 
    // IF YOUR "ORDER NUMBER" IS EQUAL TO "MERCHANT SALES ID"
    $sMerchantOrderNo = $pRequest['MerchantSalesID'];
    // ELSE
    //	$sMerchantOrderNo = 'SET YOUR FINAL ORDER NUMBER';
    
    $respItems = array(
        'ErrorNumber' => $iError,
        'RequestDateTime' => $proxy->conf['RequestDateTime'],
        'MerchantSalesID' => $pRequest['MerchantSalesID'],
        'ReferenceNo' => $pRequest['ReferenceNo'],
        'CreationDateTime' => $pRequest['CreationDateTime'],
        'Amount' => $pRequest['Amount'],
        'CurrencyID' => $pRequest['CurrencyID'],
        'PaymentReferenceNo' => $pRequest['PaymentReferenceNo'],
        'Status' => $pRequest['Status'],
        'MerchantOrderNo' => $sMerchantOrderNo
    );
    
    $respItems['Signature'] = $proxy->GetSignature( $respItems, 
                                'MerchantSalesID, ReferenceNo, CreationDateTime, '
                                . 'Amount, CurrencyID, PaymentReferenceNo, '
                                . 'Status, MerchantOrderNo');
    
    write_log('2. Response to SafetyPay:' . implode('|', $respItems), $logg);
    $pDataResponse = implode(',', $respItems);
    
    return $pDataResponse;
}

function write_log($message, $logger_active = '0')
{
    global $proxy;
    
    $lfile = 'log/' . $proxy->GetSignature(array('service' => STP_SERVICE, 
                                                 'sdk' => STP_SDK, 
                                                 '_date' => date("Ymd", time())), 
                                                 'module, version, _date', true
                                            );
    
    if( ($time = $_SERVER['REQUEST_TIME']) == '')
    $time = time();
    
    if( ($remote_addr = $_SERVER['REMOTE_ADDR']) == '')
    $remote_addr = "REMOTE_ADDR_UNKNOWN";
    
    if( ($request_uri = $_SERVER['REQUEST_URI']) == '')
    $request_uri = "REQUEST_URI_UNKNOWN";
    
    $datet = date("Y-m-d H:i:s", $time);
    $fd = @fopen($lfile, "a");
    if ($fd)
    {
        $toFile = "$datet,$remote_addr,$message \n";
        $result = @fwrite($fd, "$toFile");
    }
    fclose($fd);
}

// ***************************************************************************
// 1. Start getting information POST from SafetyPay **************************
// ***************************************************************************
$reqItems = array(
    'ApiKey' => strip_tags($_POST['ApiKey']),
    'RequestDateTime' => strip_tags($_POST['RequestDateTime']),
    'MerchantSalesID' => strip_tags($_POST['MerchantSalesID']),
    'ReferenceNo' => strip_tags($_POST['ReferenceNo']),
    'CreationDateTime' => strip_tags($_POST['CreationDateTime']),
    'Amount' => strip_tags($_POST['Amount']),
    'CurrencyID' => strip_tags($_POST['CurrencyID']),
    'PaymentReferenceNo' => strip_tags($_POST['PaymentReferenceNo']),
    'Status' => strip_tags($_POST['Status']),
    'Signature' => strip_tags($_POST['Signature'])
);

write_log('*********************************************************', $logg);
write_log('0. Configuration Parameters:' . implode('|', $proxy->conf), $logg);
write_log('1. SafetyPay Elements      :' . implode('|', $reqItems), $logg);

$iError = 0;
$Signature = $proxy->GetSignature(  $reqItems, 
                                    'RequestDateTime, MerchantSalesID, '
                                    . 'ReferenceNo, CreationDateTime, Amount, '
                                    . 'CurrencyID, PaymentReferenceNo, Status');

// ***************************************************************************
// 2. Comparing Signature received with local calculated *********************
// ***************************************************************************
if (strtoupper($reqItems['ApiKey']) != strtoupper($proxy->conf['ApiKey']))
    $sDataResponse = 'Error (1) in ApiKey.';
elseif (strtoupper($reqItems['Signature']) != strtoupper($Signature))
    $sDataResponse = 'Error (2) in Signature';
else
    $sDataResponse = ConfirmNewPaidOrders( $reqItems );

write_log('3. Response End: ' . $sDataResponse, $logg);

// Write response to SafetyPay
echo $sDataResponse;
?>
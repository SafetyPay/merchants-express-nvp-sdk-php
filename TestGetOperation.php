<?php
/**
 * File: TestGetOperation.php
 * Author: SafetyPay Inc.
 * Description: Checking details of an operation in your store.
 * @version 1.0
 * @package default
 * @license Open Software License (OSL 3.0)
 * Copyright 2012-2016 SafetyPay Inc. All rights reserved.
*******************************************************************************/
error_reporting(E_ALL & ~(E_WARNING|E_NOTICE));
require_once 'class/SafetyPayProxyExpress.php';

$proxy = new SafetyPayProxyExpress();

$MerchantSalesID = $_REQUEST['txtMerchantSalesID'];

// Values Setting: Required
$proxy->conf['MerchantSalesID'] = $MerchantSalesID;

if ( (strip_tags($_REQUEST['Submit']) == 'Check Status')
        && ($MerchantSalesID != '')
    )
{
    // Get Operation
    $Result = $proxy->GetOperation();
    if ( $Result['ErrorManager']['ErrorNumber']['@content'] == '0' )
        $errorNo = '<span style="color:black;">'
            . current(@@$Result['ErrorManager']['ErrorNumber']) . ', '
            . current(@@$Result['ErrorManager']['Description']) . '. Severity: '
            . current(@@$Result['ErrorManager']['Severity']) . '</span>';
    else
        if (is_array($Result['ErrorManager']['ErrorNumber']))
            $errorNo = '<span style="color:red;">'
                . current(@@$Result['ErrorManager']['ErrorNumber']) . ', '
                . current(@@$Result['ErrorManager']['Description']) . '. Severity: '
                . current(@@$Result['ErrorManager']['Severity']) . '</span>';
        else
            $errorNo = '<span style="color:red;">'
                . @@$Result['ErrorManager']['ErrorNumber'] . ', '
                . @@$Result['ErrorManager']['Description'] . '. Severity: '
                . @@$Result['ErrorManager']['Severity'] . '</span>';
}
?>
<html>
<head>
<title>SafetyPay SDK PHP - Get Operations</title>
<style type="text/css">
    *           {   font-family: Verdana, Arial, Helvetica, sans-serif; font-size:11px; }
    BODY        {   font-family: Verdana, Arial, Helvetica, sans-serif; font-size:11px; }
    FORM        {   margin:0px; padding:0px; }
    TD          {   padding-bottom:1px; padding-top:1px; }
    A           {   color:#0000FF; }
    A:hover     {   color:#0000FF; }
    A:active    {   color:#0000FF; }
    A:visited   {   color:#0000FF; }
    .error      {   color:#FF0000; }
    .container  {   float:left; width:580px; vertical-align:top;
                    border:#036 1px solid; margin:0px;                   }
    .subcontain {   float:left; vertical-align:middle;
                    border:#036 1px solid; margin:0px; padding: 5px;     }
    .subtitle   {   background-color:#036; color:#FFFFFF; text-align:left;
                    font-weight:bold; padding-bottom:4px; padding-top:4px; }
</style>
</head>
<body>
<div class="container">
<form method="POST" name="frmSubmit" id="frmSubmit">
    <table width="100%" border="0" cellpadding="3" cellspacing="3">
    <tr><td colspan="4" nowrap>
            <div style="text-align:right;">
                <div style="float:left;text-align:left;width:50%;">
                    <img src="images/safetypay_logo.png" border="0" />
                </div>
                <br /><?php echo STP_SDK_NAME . ' ' . STP_SDK_VERSION; ?><br />
                        <?php echo STP_SERVICE_NAME . ' ' . STP_SERVICE_VERSION; ?>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td class="subtitle" colspan="4">Get Operation</td>
    </tr>
    <tr>
        <td width="2%">&nbsp;</td>
        <td colspan="3">Retrieve all activities for a specific operation.<br />
            Results shown here are response data only if operation exist. Each
            response is call at <i>GetOperation</i> method.<br />
            Note: All Status are related only to the Purchase Process.<br />
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Merchant Sales ID</td>
        <td colspan="2"><input name="txtMerchantSalesID" maxlength="50" type="text" size="40" 
                               value="<?php echo $MerchantSalesID; ?>" /> *
                        <br />(max 50 characters)
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="2"><input name="Submit" type="Submit"
                               value="Check Status" />
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="3">Field with an "*" are required.</td>
    </tr>
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <th colspan="4" class="subtitle">Response Result</th>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="3"><?php
        if ( $Result['ErrorManager']['ErrorNumber']['@content'] == '0' )
        {
            if ( $proxy->conf['ResponseFormat'] == 'XML' )
            {
                // Getting of main node to read all operations
                unset($Result['ListOfOperations']["@attributes"]);
                if ( isset($Result['ListOfOperations']['Operation']['OperationID']) )
                    $oResult = $Result['ListOfOperations'];
                else
                    $oResult = $Result['ListOfOperations']['Operation'];
                
                // Count all operations and start show it
                if ( count($oResult) )
                {
                    echo 'Found <strong>' . count($oResult) 
                            . ' operations</strong> related to '
                            . '<strong>Merchant Order ID: ' . $MerchantSalesID
                            . '</strong><br /><br />';
                    
                    foreach( $oResult as $k => $e )
                    {
                        echo "<br /><strong>OperationID:</strong> $e[OperationID]<br />";
                        echo "<strong>MerchantSalesID:</strong> $e[MerchantSalesID]<br />";
                        echo "================================<br />";
                        echo "<strong>CreationDateTime:</strong> $e[CreationDateTime]<br />";
                        echo "<strong>Amount:</strong> $e[Amount]<br />";
                        echo "<strong>CurrencyID:</strong> $e[CurrencyID]<br />";
                        echo "<strong>ShopperAmount:</strong> $e[ShopperAmount]<br />";
                        echo "<strong>ShopperCurrencyID:</strong> $e[ShopperCurrencyID]<br />";

                        if (isset($e['OperationActivities']['OperationActivity']['Status']))
                            $oActivities = $e['OperationActivities'];
                        else
                            $oActivities = $e['OperationActivities']['OperationActivity'];

                        $onRedund = false;

                        // Activities Iteration
                        foreach( $oActivities as $c => $v )
                        {
                            echo "<strong>($c).Status/StatusCode:</strong> "
                                        . $v['Status']['StatusCode']."<br />";
                            echo "<strong>($c).Status/Description:</strong> "
                                        . $v['Status']['Description']."<br />";
                            if ( ((int)$v['Status']['StatusCode'] >= 102)
                                     && ($onRedund == false)
                                    )
                                $onRedund = true;
                        }
                        
                        if ($onRedund)
                            echo 'Request Refund for this transaction?'
                                    . ' <a href="TestCreateRefund.php?'
                                    . 'txtSalesOperationID='.$e['OperationID']
                                    . '&txtAmountToRefund='.$e['Amount']
                                    . '&txtMerchantSalesID='.$e['MerchantSalesID']
                                    . '" target="_new">Click Here</a><br /><br />';
                    }
                }
                elseif ($MerchantSalesID != '')
                    echo 'Not found operations related to '
                            . '<strong>Merchant Order ID: ' . $MerchantSalesID
                            . '</strong>';
            }
            elseif ( $proxy->conf['ResponseFormat'] == 'CSV' )
            {
                // $aItems: Get all records between operation and activities.
                // 
                //    Error Number, Response DateTime, Signature, 
                //    Operation CreationDateTime, OperationID, MerchantSalesID, 
                //    Merchant OrderID, Amount, CurrencyID, ShopperAmount, 
                //    ShopperCurrencyID, AuthorizacionCode, 
                //    PaymentReferencesNumber, State Operation CreationDateTime,
                //    StatusCode, Status Description
                $aItems = explode( "\n", $Result[0] );
                $sPop = array_pop( $aItems );           // Remove empty element
                
                // Getting of main node to read all operations
                $iOperation = -1;
                $sBroken = '';             
                foreach( $aItems as $k => $v )
                {
                    $aLine = explode( ',', $v );
                    $sBrokenCurr = $aLine[3] . $aLine[4];
                    
                    if ( $sBroken != $sBrokenCurr )
                    {
                        $iOperation++;
                        // Getting the other operations
                        $oResult[$iOperation] = array(
                                    'CreationDateTime' => $aLine[3],
                                    'OperationID' => $aLine[4],
                                    'MerchantSalesID' => $aLine[5],
                                    'MerchantOrderID' => $aLine[6],
                                    'Amount' => $aLine[7],
                                    'CurrencyID' => $aLine[8],
                                    'ShopperAmount' => $aLine[9],
                                    'ShopperCurrencyID' => $aLine[10],
                                    'OperationActivities' => array()
                                );

                        // Getting activities of operation
                        $oResult[$iOperation]['OperationActivities'] = array(
                                    'OperationActivity' => 
                                        array(array(
                                            'CreationDateTime' => $aLine[13],
                                            'Status' => array(
                                                'StatusCode' => $aLine[14],
                                                'Description' => $aLine[15]
                                                ),
                                            ))
                                );
                    }
                    else
                    {
                        // Getting only activities
                        $oResult[$iOperation]['OperationActivities']['OperationActivity'][] =
                                        array(
                                            'CreationDateTime' => $aLine[13],
                                            'Status' => array(
                                                'StatusCode' => $aLine[14],
                                                'Description' => $aLine[15]
                                                ),
                                            );
                    }
                    $sBroken = $sBrokenCurr;
                }
                
                // Count all operations and start show it
                if ( count($oResult) )
                {
                    echo 'Found <strong>' . count($oResult) 
                            . ' operations</strong> related to '
                            . '<strong>Merchant Order ID: ' . $MerchantSalesID
                            . '</strong><br /><br />';

                    foreach( $oResult as $k => $e )
                    {
                        echo "<br /><strong>OperationID:</strong> $e[OperationID]<br />";
                        echo "<strong>MerchantSalesID:</strong> $e[MerchantSalesID]<br />";
                        echo "================================<br />";
                        echo "<strong>CreationDateTime:</strong> $e[CreationDateTime]<br />";
                        echo "<strong>Amount:</strong> $e[Amount]<br />";
                        echo "<strong>CurrencyID:</strong> $e[CurrencyID]<br />";
                        echo "<strong>ShopperAmount:</strong> $e[ShopperAmount]<br />";
                        echo "<strong>ShopperCurrencyID:</strong> $e[ShopperCurrencyID]<br />";

                        if (isset($e['OperationActivities']['OperationActivity']['Status']))
                            $oActivities = $e['OperationActivities'];
                        else
                            $oActivities = $e['OperationActivities']['OperationActivity'];

                        $onRedund = false;

                        // Activities Iteration
                        foreach( $oActivities as $c => $v )
                        {
                            echo "<strong>($c).Status/StatusCode:</strong> "
                                        . $v['Status']['StatusCode']."<br />";
                            echo "<strong>($c).Status/Description:</strong> "
                                        . $v['Status']['Description']."<br />";
                            if ( ((int)$v['Status']['StatusCode'] >= 102)
                                     && ($onRedund == false)
                                    )
                                $onRedund = true;
                        }
                        
                        if ($onRedund)
                            echo 'Request Refund for this transaction?'
                                    . ' <a href="TestCreateRefund.php?'
                                    . 'txtSalesOperationID='.$e['OperationID']
                                    . '&txtAmountToRefund='.$e['Amount']
                                    . '&txtMerchantSalesID='.$e['MerchantSalesID']
                                    . '" target="_new">Click Here</a><br /><br />';
                    }
                }
                elseif ($MerchantSalesID != '')
                    echo 'Not found operations related to '
                            . '<strong>Merchant Order ID: ' . $MerchantSalesID
                            . '</strong>';
            }
        }

        echo '<br /><br /><strong>Error:</strong> ' . $errorNo;
        ?></td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>
    </table>
</form>
</div>
</body>
</html>
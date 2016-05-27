<?php
/**
 * File: TestCreateRefund.php
 * Author: SafetyPay Inc.
 * Description: Start the refund process with Safetypay.
 * @version 1.0
 * @package default
 * @license Open Software License (OSL 3.0)
 * Copyright 2012-2016 SafetyPay Inc. All rights reserved.
*******************************************************************************/
error_reporting(E_ALL & ~(E_WARNING|E_NOTICE));
require_once 'class/SafetyPayProxyExpress.php';

$proxy = new SafetyPayProxyExpress();

$SalesOperationID = $_REQUEST['txtSalesOperationID'];
$AmountToRefund = $_REQUEST['txtAmountToRefund'];
$MerchantRefundID = $_REQUEST['MerchantRefundID'];
$CustomerinfoPhone = $_REQUEST['Customerinfo_phone'];
$CustomerinfoEmail = $_REQUEST['Customerinfo_email'];

$proxy->conf['Information']['ShopperInformation_email'] = $CustomerinfoEmail;
$proxy->conf['Information']['ShopperInformation_phone'] = $CustomerinfoPhone;

// Values Setting: Required
$params = array(    'SalesOperationID' => $SalesOperationID,
                    'AmountToRefund' => $AmountToRefund,
                    'TotalPartial' => $_REQUEST['radTotalPartial'],
					'MerchantRefundID' => $MerchantRefundID,
                    'Reason' => $_REQUEST['slcReason'],
                    'Comments' => $_REQUEST['txtComments']
                );

$MerchantSalesID = $_REQUEST['txtMerchantSalesID'];

if ( strip_tags($_REQUEST['Submit']) == 'Confirm' )
{
    // Create Refund
    $Result = $proxy->CreateRefund( $params );
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
<title>SafetyPay SDK PHP - Request Refund</title>
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
        <td colspan="3">This form allow the merchant start a Refund
            process to a customer:<br />
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Sales Operation ID</td>
        <td colspan="2">
            <input name="txtSalesOperationID" type="text"
                   value="<?php echo $SalesOperationID; ?>" /> * (16 characters)
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Amount to Refund</td>
        <td colspan="2">
            <input name="txtAmountToRefund" type="text"
                   value="<?php echo $AmountToRefund; ?>" /> *
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Total or Partial</td>
        <td colspan="2">
            <input type="radio" name="radTotalPartial" value="1" checked> Total
            <input type="radio" name="radTotalPartial" value="2"> Partial
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Reason</td>
        <td colspan="2">
            <select name="slcReason">
            	<option selected="selected" value="0">Select a Reason</option>
            	<option value="1">Product damaged on transit</option>
            	<option value="2">Transaction cancelled by shopper</option>
            	<option value="3">Shopper returned product</option>
            	<option value="4">Shopper not satisfied with service</option>
            	<option value="5">Shopper not satisfied with product</option>
				<option value="6">Other</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Merchant Refund ID</td>
        <td colspan="2">
            <input name="MerchantRefundID" type="text"
                   value="<?php echo $MerchantRefundID; ?>" /> *
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Contact e-mail</td>
        <td colspan="2">
            <input name="Customerinfo_email" type="text"
                   value="<?php echo $CustomerinfoEmail; ?>" /> *
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Contact Phone</td>
        <td colspan="2">
            <input name="Customerinfo_phone" type="text"
                   value="<?php echo $CustomerinfoPhone; ?>" /> *
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>Comments</td>
        <td colspan="2">
            <textarea name="txtComments" rows="6" cols="20"></textarea>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td colspan="2"><input name="Submit" type="Submit"
                               value="Confirm" />
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
                unset($Result['Refund']["@attributes"]);
                
                // Getting of main node to read all elements
                $oResult = $Result['Refund'];
            }
            elseif ( $proxy->conf['ResponseFormat'] == 'CSV' )
            {
                $aItems = $Result[0];
                $aLine = explode( ',', $aItems );
                
                // Getting of main node to read all elements
                $oResult = $Result = array(
                        'OperationID' => array( '@content' => $SalesOperationID ),
                        'CreationDateTime' => array( '@content' => $aLine[2] ),
                        'SalesOperationID' => array( '@content' => $aLine[1] ),
                        'AmountToRefund' => array( '@content' => $aLine[3] ),
                        'CurrencyIDToRefund' => array( '@content' => '' ),
                        'TotalPartial' => array( '@content' => $aLine[4] ),
                        'Reason' => array( '@content' => $aLine[5] ),
                        'Comments' => array( '@content' => $aLine[6] ),
                        );
            }
			
            // New Operation's Detail
            echo '<br /><strong>OperationID:</strong> '
                            . $oResult['OperationID']['@content'];
            echo '<br /><strong>Creation Date Time:</strong> '
                            . $oResult['CreationDateTime']['@content'];
            echo '<br /><strong>Sales Operation ID:</strong> '
                            . $oResult['SalesOperationID']['@content'];
            echo '<br /><strong>Amount To Refund:</strong> '
                            . $oResult['AmountToRefund']['@content'];
            echo '<br /><strong>Currency ID To Refund:</strong> '
                            . $oResult['CurrencyIDToRefund']['@content'];
            echo '<br /><strong>Total or Partial:</strong> '
                            . $oResult['TotalPartial']['@content'];
            echo '<br /><strong>Reason:</strong> '
                            . $oResult['Reason']['@content'];
            echo '<br /><strong>Comments:</strong> '
                            . $oResult['Comments']['@content'];
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
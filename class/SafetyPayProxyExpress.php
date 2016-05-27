<?php
/**
 * File: SafetyPayProxyExpress.php
 * Author: SafetyPay Inc.
 * Description: Configuration Code
 * @version 2.0
 * @package class
 * @license Open Software License (OSL 3.0)
 * Copyright 2012-2016 SafetyPay Inc. All rights reserved.
*******************************************************************************/

if(!@include("../lib/nanolink-sha256.inc.php"))
	require_once './lib/nanolink-sha256.inc.php';
if(!@include("../lib/simplexml.class.php"))
	require_once './lib/simplexml.class.php';

define('STP_SDK_NAME',          'POST PHP');
define('STP_SDK_VERSION',       '4.1.0.0');
define('STP_SERVICE_NAME',      'POST Express');
define('STP_SERVICE_VERSION',   '3.0');

/**
 * SafetyPay Proxy Class
 *
 * @author   	SafetyPay IT Team
 * @version   	1.0
 * @package   	class
 */
class SafetyPayProxyExpress
{
    var $conf = array();

    function __construct()
    {
        /**
         * API and Signature Key
         * Set your Sandbox/Prod Credential.
         * Generate your own keys in the MMS, option: Profile > Credentials
         */
        $this->conf['ApiKey'] = 'de9236b1b6743bebf3ae16b5e0f7134c';
        $this->conf['SignatureKey'] = 'fd41ed6009449598c721035c549c6ae8';

        /**
         * 1: For Sandbox (Test);
         * 0: For Production
         */
        $this->conf['Environment'] = 1;

        /**
         * Total Amount
         */
        $this->conf['Amount'] = '346.50';

        /**
         * Currency Code
         * Samples: USD, PEN, MXN, EUR.
         * Register your Default Currency market products, this must match your
         * Bank Account Affiliate. MMS, option: Profile > Bank Accounts.
         */
        $this->conf['CurrencyCode'] = 'USD';

        /**
         * ISO Code Language
         * Samples: EN, ES, DE, PT.
         */
        $this->conf['Language'] = 'EN';

        /**
         * Merchant Reference No.
         * Required in purchase process.
         */
        $this->conf['MerchantSalesID'] = 'ORDER_NO-12345';

        /**
         * Tracking Code.
         * Leave blank
         */
        $this->conf['TrackingCode'] = '';

        /**
         * Communication Protocol
         * 'http' or 'https'
         * Check also related parameter: 'port_ssl'.
         */
        $this->conf['Protocol'] = 'https';

        /**
         * URL Token Expiration Time
         * In minutes. Default: 120 minutes.
         */
        $this->conf['ExpirationTime'] = 120;

        /**
         * Filter By
         * Filter options in screen Express service as Countries, Banks
         * or Currencies. Leave blank. Optional.
         * Samples:
         * COUNTRY(PER)CURRENCY(USD): Show only to Peru and pay with US Dollar
         * BANK(1011,1019)COUNTRY(ESP): Shown only to Spain and banks selected.
         */
        $this->conf['FilterBy'] = '';

        /**
         * Choose a URL for Return at message of sucess or fail paid process.
         */
        $this->conf['TransactionOkURL'] = 'http://demostore.safetypay.com';
        $this->conf['TransactionErrorURL'] = 'http://demostore.safetypay.com/contacts/';

        /**
         * Port number to connections SSL. Related at "Environment" parameter.
         */
        $this->conf['port_ssl'] = 443;

        /**
         * Request Date Time
         */
        $this->conf['RequestDateTime'] = $this->getDateIso8601(time());

        /**
         * Data Responde Format
         * Options: XML, CSV (Default)
         */
        $this->conf['ResponseFormat'] = 'XML';
    }

    function setConf( $conf )
    {
        foreach( $conf as $k => $v )
            $this->conf[$k] = $v;
    }

    /**
     * Setting correctly the Service URL
     */
    function setAccessPoint()
    {
    	$_env = '';
    	$domain_srv = 'mws2.safetypay.com';

        if ( $this->conf['Environment'] )
        	$_env = '/sandbox';

        $this->conf['CreateExpressToken'] = strtolower( $this->conf['Protocol'] )
                . '://' . $domain_srv
                . "$_env/express/ws/v.3.0/Post/CreateExpressToken";
        $this->conf['CreateRefund'] = strtolower( $this->conf['Protocol'] )
                . '://' . $domain_srv
                . "$_env/express/ws/v.3.0/Post/CreateRefundProcess";
        $this->conf['GetOperation'] = strtolower( $this->conf['Protocol'] )
                . '://' . $domain_srv
                . "$_env/express/ws/v.3.0/Post/GetOperation";
    }

    function getDateIso8601($int_date)
    {
        $date_mod = date('Y-m-d\TH:i:s', $int_date);
        $pre_timezone = date('O', $int_date);
        $time_zone = substr($pre_timezone, 0, 3) . ':'
                                . substr($pre_timezone, 3, 2);
        $pos = strpos($time_zone, "-");
        if (PHP_VERSION >= '4.0')
            if ($pos === false) {
            	// nothing
            }
            else
                if ($pos != 0)
                    $date_mod = $time_zone;
                else
                    if (is_string($pos) && !$pos) {
                    // nothing
                    }
                    else
                        if ($pos != 0)
                            $date_mod = $time_zone;

        return $date_mod;
    }

    /**
     * Interpret an XML document into an array object
     * @param   string  $data Response of Web Service in string format
     * @return  array
     */
    function xml2object( $data )
    {
        $sxml = new simplexml;
        return $sxml->xml_load_file( '', $data );
    }

    /**
     * Get Signature
     */
    function GetSignature( $aparams, $slist = '' )
    {
        $allparams = '';
        $alist = explode( ',', $slist );
        if ( !isset($aparams[0]) )
            foreach( $alist as $k => $v )
                $allparams .= $aparams[rtrim(ltrim($v))];
        else
            foreach( $aparams as $k => $v )
                foreach( $alist as $x => $z )
                    $allparams .= $v[rtrim(ltrim($z))];

        if ( preg_match('/RequestDateTime/', $slist) )
            $this->conf['Signature'] = sha256( $allparams
                                                . $this->conf['SignatureKey'] );
        else
            $this->conf['Signature'] = sha256( $this->conf['RequestDateTime']
                                                . $allparams
                                                . $this->conf['SignatureKey']);

        return $this->conf['Signature'];
    }

    /**
     * To create a Token URL in order to request money, it can be send by email
     * by an automatic system, or any other method.
     * With this method you can implement "SafetyPay Express" Mode.
     */
    function CreateExpressToken()
    {
        $p = array(
                'ApiKey' => $this->conf['ApiKey'],
                'RequestDateTime' => $this->conf['RequestDateTime'],
                'CurrencyCode' => $this->conf['CurrencyCode'],
                'Amount' => $this->conf['Amount'],
                'MerchantSalesID' => $this->conf['MerchantSalesID'],
                'Language' => $this->conf['Language'],
                'TrackingCode' => $this->conf['TrackingCode'],
                'ExpirationTime' => $this->conf['ExpirationTime'],
                'FilterBy' => $this->conf['FilterBy'],
                'TransactionOkURL' => $this->conf['TransactionOkURL'],
                'TransactionErrorURL' => $this->conf['TransactionErrorURL']
                );

		foreach( $this->conf['Information'] as $k => $v )
		{
			if ($k !== NULL && $v !== NULL && $v!=='')	 { $p[$k] = $v; }
		}
		
        $p['Signature'] = $this->GetSignature(
                                    $this->conf,
                                    'CurrencyCode, Amount, MerchantSalesID,'
                                    . 'Language, TrackingCode, ExpirationTime,'
                                    . 'TransactionOkURL, TransactionErrorURL'
                                    );

        $Result = $this->callOperation( 'CreateExpressToken',
                                'ExpressTokenResponse', $p );

        return $Result;
    }

    /**
     * Retrieve all operation activity for a specific operation.
     */
    function GetOperation()
    {
        $p = array( 'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'MerchantSalesID' => $this->conf['MerchantSalesID']
                    );

        $p['Signature'] = $this->GetSignature( $this->conf, 'MerchantSalesID' );

        $Result = $this->callOperation( 'GetOperation',
                                'OperationResponse', $p );

        return $Result;
    }

    /**
     * To amount refund to specific Sales Operation ID
     */
    function CreateRefund( $params )
    {
        $p = array( 'ApiKey' => $this->conf['ApiKey'],
                    'RequestDateTime' => $this->conf['RequestDateTime'],
                    'SalesOperationID' => $params['SalesOperationID'],
                    'AmountToRefund' => $params['AmountToRefund'],
                    'TotalPartial' => $params['TotalPartial'],
					'MerchantRefundID' => $params['MerchantRefundID'],
                    'Reason' => $params['Reason'],
                    'Comments' => $params['Comments']
                    );
		foreach( $this->conf['Information'] as $k => $v )
		{
			if ($k !== NULL && $v !== NULL && $v!=='')	 { $p[$k] = $v; }
		}
		
        $p['Signature'] = $this->GetSignature(
                                    $params,
                                    'SalesOperationID, AmountToRefund, '
                                    . 'TotalPartial, Reason'
                                    );

        $Result = $this->callOperation( 'CreateRefund',
                                'RefundProcessResponse', $p );

        return $Result;
    }
	
	function GetNewTokenID()
	{
		$tokenURL = '';
		$this->conf['CurrencyCode'] = 'USD';
		$this->conf['Amount'] = '250.51';
		$this->conf['MerchantSalesID'] = 'ORDER_NO-98765';
		$this->conf['ExpirationTime'] = 240;
		//$this->conf['FilterBy'] = 'COUNTR(BRA)';
		$this->conf['ProductID'] = '1';
		$responseFormat = $this->conf['ResponseFormat'] = 'CSV';
		$tokenID = $this->CreateExpressToken();
		if ($tokenID['ErrorManager']['ErrorNumber']['@content'] == '0')
		{
			if ( $responseFormat == 'XML' )
			{
				$tokenURL = $tokenID['ShopperRedirectURL']['@content'];
			}
			elseif ( $responseFormat == 'CSV' )
			{
				$aItems = $tokenID[0];
				$aLine = explode( ',', $aItems );
				$tokenURL = $aLine[2];
			}
		}
		else
		{
			$tokenURL = $this->conf['TransactionErrorURL'];
		}
		
		return $tokenURL;
	}

    /**
     * To create a Token URL in order to request money, it can be send by email
     * by an automatic system, or any other method.
     * With this method you can implement "SafetyPay Express" Mode.
     */
    function callOperation( $process, $retNode, $_data )
    {
        $this->setAccessPoint();

        $data = array();
        while( list($n,$v) = each($_data) )
            $data[] = "$n=" . urlencode($v);

        $data[] = 'ResponseFormat=' . $this->conf['ResponseFormat'];
        $data = implode( '&', $data );
        $url = parse_url( $this->conf[$process] );
        $scheme = $url['scheme'];
        $host = $url['host'];
        $path = $url['path'];
        $fp = fsockopen( ($scheme == 'https'? 'ssl://'.$host:$host),
                         ($scheme == 'https'? $this->conf['port_ssl']:80)
                       );
        fputs($fp, "POST $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host\r\n");
        fputs($fp, "Content-Type: application/x-www-form-urlencoded;\r\n");
        fputs($fp, "Content-Length: " . strlen($data) . "\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $data);

        $result = '';
        while( !feof($fp) )
            $result .= fgets( $fp, 1024 );

        fclose($fp);

        $result = explode( "\r\n\r\n", $result, 2 );

        $pos1 = strpos(strtoupper($result[0]), '1.1 500');
        $pos2 = strpos(strtoupper($result[0]), '1.1 404');
        if ( ($pos1 === false) && ($pos2 === false) )
        {
            if ( $this->conf['ResponseFormat'] == 'XML' )
            {
            	$oReturn = $this->xml2object($result[1]);

                return $oReturn[$retNode];
            }
            elseif ( $this->conf['ResponseFormat'] == 'CSV' )
            {
                $oReturn = array();
                $errorCode = explode( ",", $result[1], 3 );

                $oReturn[$retNode][] = $result[1];
                $oReturn[$retNode]['ErrorManager'] =
                                        array( 'ErrorNumber' =>
                                        array( '@content' => $errorCode[0] ),
                                            'Description' =>
                                        array( '@content' =>
                                            ($errorCode[0]!=0?$errorCode[1]:'')
                                              ),
                                            'Severity' =>
                                        array('@content' =>
                                            ($errorCode[0]!=0?$errorCode[2]:'')
                                              )
                                                );

                return $oReturn[$retNode];
            }
        }
        else
        {
            $error_page = explode( "\n", $result[0] );

            $oReturn[$retNode]['ErrorManager'] =
                                        array( 'ErrorNumber' =>
                                        array( '@content' => 99 ),
                                        'Description' =>
                                        array( '@content' => $error_page[0]
                                                . '- Without Communication '
                                                . 'at Service.' ),
                                        'Severity' =>
                                        array('@content' => '')
                                            );

            return $oReturn[$retNode];
        }
    }
}
?>
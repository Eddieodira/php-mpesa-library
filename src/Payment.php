<?php

namespace Eddieodira\Mpesa;

use Eddieodira\Mpesa\Constant\Constants;
use Eddieodira\Mpesa\Exception\MpesaException;

class Payment
{
    protected $consumerKey;
    protected $consumerSecret; 
    protected $credentials;  
    protected $url;
    protected $tokenUrl;
    protected $token;
    protected $curlData = [];
    protected $data;
    protected $configuration;

    public function __construct($mpesaConfig) {
        $this->configuration = (object) $mpesaConfig;
        $this->consumerKey = $this->configuration->consumerKey;
        $this->consumerSecret = $this->configuration->consumerSecret;
        $this->appStatus = $this->configuration->appStatus;
    }

    public function generateToken(){
        if(!isset($this->consumerKey)||!isset($this->consumerSecret)){
            throw new MpesaException("Please declare the Consumer Key and Consumer Secret as defined in the documentation");
        }

        if($this->appStatus == 'live'){
            $this->tokenUrl = Constants::MPESA['TOKEN_API_URL'];
        }elseif($this->appStatus == 'sandbox'){
            $this->tokenUrl = Constants::MPESA['TOKEN_SANDBOX_URL'];
        } else{
            throw new MpesaException("Invalid application status");
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->tokenUrl);
        $this->credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $this->credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $curl_response = curl_exec($curl);
        return json_decode($curl_response)->access_token;
    }

    public static function c2b($shortCode, $commandID, $amount, $msisdn, $billRefNumber ){

        if($this->appStatus == 'live'){
            $this->url = Constants::MPESA['C2B_API_URL'];
        }elseif($this->appStatus == 'sandbox'){
            $this->url = Constants::MPESA['C2B_SANDBOX_URL'];
        } else{
            throw new MpesaException("Invalid application status");
        }

        $this->token = self::generateToken();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer ' . $this->token));

        $this->curlData[Constants::C2B['SHORTCODE']] = $shortCode;
        $this->curlData[Constants::C2B['COMMAND_ID']] = $commandID;
        $this->curlData[Constants::C2B['AMOUNT']] = $amount;
        $this->curlData[Constants::C2B['MSISDN']] = $msisdn;
        $this->curlData[Constants::C2B['BILL_REF_NUMBER']] = $billRefNumber;
        $this->data = json_encode($this->curlPostData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        $getHTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curl_response === false) {
            throw new MpesaException('Unable to connect to Safaricom Mpesa API: ' . $curlError);
        } elseif ($getHTTPCode != 200) {
            throw new MpesaException('Bad response from Safaricom Daraja Mpesa API: HTTP code ' . $getHTTPCode);
        }
        return $curl_response;
    }

    public function stkPush($businessShortCode, $lipaNaMpesaPasskey, $transactionType, $amount, $partyA, $partyB, $phoneNumber, $callBackURL, $accountReference, $transactionDesc){

        if($this->appStatus == 'live'){
            $this->url = Constants::MPESA['STKPUSH_API_URL'];
        }elseif($this->appStatus == 'sandbox'){
            $this->url = Constants::MPESA['STKPUSH_SANDBOX_URL'];
        } else{
            throw new MpesaException("Invalid application status");
        }

        $this->token = $this->generateToken();

        $timestamp = '20' . date("ymdhis");
        $password = base64_encode($businessShortCode.$lipaNaMpesaPasskey.$timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer ' . $this->token));

        $this->curlData[Constants::STK['SHORTCODE']] = $businessShortCode;
        $this->curlData[Constants::STK['PASSWORD']] = $password;
        $this->curlData[Constants::STK['TIMESTAMP']] = $timestamp;
        $this->curlData[Constants::STK['TRANS_TYPE']] = $transactionType;
        $this->curlData[Constants::STK['AMOUNT']] = $amount;
        $this->curlData[Constants::STK['PARTY_A']] = $partyA;
        $this->curlData[Constants::STK['PARTY_B']] = $partyB;
        $this->curlData[Constants::STK['PHONE_NUMBER']] = $phoneNumber;
        $this->curlData[Constants::STK['CALLBACK_URL']] = $callBackURL;
        $this->curlData[Constants::STK['ACCOUNT_REF']] = $accountReference;
        $this->curlData[Constants::STK['TRANS_DESC']] = $transactionType;

        $this->data = json_encode($this->curlData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);

        $getHTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curl_response === false) {
            throw new MpesaException('Unable to connect to Safaricom Mpesa API: ' . $curlError);
        } elseif ($getHTTPCode != 200) {
            throw new MpesaException('Bad response from Safaricom Daraja Mpesa API: HTTP code ' . $getHTTPCode);
        }
        return $curl_response;
    }

    public function transactionStatus($initiator, $securityCredential, $commandID, $transactionID, $partyA, $identifierType, $resultURL, $queueTimeOutURL, $remarks, $occasion){

        if($this->appStatus == 'live'){
            $this->url = Constants::MPESA['QUERY_API_URL'];
        }elseif($this->appStatus == 'sandbox'){
            $this->url = Constants::MPESA['QUERY_SANDBOX_URL'];
        } else{
            throw new MpesaException("Invalid application status");
        }

        $this->token = self::generateToken();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer ' . $this->token));

        $this->curlData[Constants::QUERY['INITIATOR']] = $initiator;
        $this->curlData[Constants::QUERY['SEC_CRED']] = $securityCredential;
        $this->curlData[Constants::QUERY['COMMAND_ID']] = $commandID;
        $this->curlData[Constants::QUERY['TRANS_ID']] = $transactionID;
        $this->curlData[Constants::QUERY['PARTY_A']] = $partyA;
        $this->curlData[Constants::QUERY['ID_TYPE']] = $identifierType;
        $this->curlData[Constants::QUERY['RESULT_URL']] = $resultURL;
        $this->curlData[Constants::QUERY['TIME_OUT_URL']] = $queueTimeOutURL;
        $this->curlData[Constants::QUERY['REMARKS']] = $remarks;
        $this->curlData[Constants::QUERY['OCCASION']] = $occasion;

        $this->data = json_encode($this->curlData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        $getHTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curl_response === false) {
            throw new MpesaException('Unable to connect to Safaricom Mpesa API: ' . $curlError);
        } elseif ($getHTTPCode != 200) {
            throw new MpesaException('Bad response from Safaricom Daraja Mpesa API: HTTP code ' . $getHTTPCode);
        }
        return $curl_response;
    }

    public static function stkPushQuery($environment, $checkoutRequestID, $businessShortCode, $password, $timestamp){
        $this->token = self::generateToken();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer ' . $this->token));

        $this->curlData[Constants::P_QUERY['SHORTCODE']] = $businessShortCode;
        $this->curlData[Constants::P_QUERY['PASSWORD']] = $password;
        $this->curlData[Constants::P_QUERY['TIMESTAMP']] = $timestamp;
        $this->curlData[Constants::P_QUERY['CHECKOUT_RID']] = $checkoutRequestID;

        $this->data = json_encode($this->curlData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);
        $getHTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curl_response === false) {
            throw new MpesaException('Unable to connect to Safaricom Mpesa API: ' . $curlError);
        } elseif ($getHTTPCode != 200) {
            throw new MpesaException('Bad response from Safaricom Daraja Mpesa API: HTTP code ' . $getHTTPCode);
        }
        return $curl_response;
    }

    public function completeTransaction($status = true)
    {
        if ($status === true) {
            $this->curlData[Constants::COMPLETE['RESULT_DESC']] = "Confirmation Service request accepted successfully";
            $this->curlData[Constants::COMPLETE['RESULT_CODE']] = "0";
        } else {
            $this->curlData[Constants::COMPLETE['RESULT_DESC']] = "Confirmation Service not accepted";
            $this->curlData[Constants::COMPLETE['RESULT_CODE']] = "1";
        }
        header('Content-Type: application/json');
        echo json_encode($this->curlData);
    }
}

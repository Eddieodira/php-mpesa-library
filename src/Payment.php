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
    protected $param = [];
    protected $data;
    protected $response;
    protected $configuration;

    public function __construct($mpesaConfig) {
        $this->configuration = (object) $mpesaConfig;
        $this->consumerKey = $this->configuration->consumerKey;
        $this->consumerSecret = $this->configuration->consumerSecret;
        $this->appStatus = $this->configuration->appStatus;
    }

    public function getResponse() {
        return $this->response;
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

        $this->param[Constants::C2B['SHORTCODE']] = $shortCode;
        $this->param[Constants::C2B['COMMAND_ID']] = $commandID;
        $this->param[Constants::C2B['AMOUNT']] = $amount;
        $this->param[Constants::C2B['MSISDN']] = $msisdn;
        $this->param[Constants::C2B['BILL_REF_NUMBER']] = $billRefNumber;
        return $this->response = $this->curlRequest($this->url, $this->param, $this->token);
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
        $this->param[Constants::STK['SHORTCODE']] = $businessShortCode;
        $this->param[Constants::STK['PASSWORD']] = $password;
        $this->param[Constants::STK['TIMESTAMP']] = $timestamp;
        $this->param[Constants::STK['TRANS_TYPE']] = $transactionType;
        $this->param[Constants::STK['AMOUNT']] = $amount;
        $this->param[Constants::STK['PARTY_A']] = $partyA;
        $this->param[Constants::STK['PARTY_B']] = $partyB;
        $this->param[Constants::STK['PHONE_NUMBER']] = $phoneNumber;
        $this->param[Constants::STK['CALLBACK_URL']] = $callBackURL;
        $this->param[Constants::STK['ACCOUNT_REF']] = $accountReference;
        $this->param[Constants::STK['TRANS_DESC']] = $transactionType;
        return $this->response = $this->curlRequest($this->url, $this->param, $this->token);
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

        $this->param[Constants::QUERY['INITIATOR']] = $initiator;
        $this->param[Constants::QUERY['SEC_CRED']] = $securityCredential;
        $this->param[Constants::QUERY['COMMAND_ID']] = $commandID;
        $this->param[Constants::QUERY['TRANS_ID']] = $transactionID;
        $this->param[Constants::QUERY['PARTY_A']] = $partyA;
        $this->param[Constants::QUERY['ID_TYPE']] = $identifierType;
        $this->param[Constants::QUERY['RESULT_URL']] = $resultURL;
        $this->param[Constants::QUERY['TIME_OUT_URL']] = $queueTimeOutURL;
        $this->param[Constants::QUERY['REMARKS']] = $remarks;
        $this->param[Constants::QUERY['OCCASION']] = $occasion;
        return $this->response = $this->curlRequest($this->url, $this->param, $this->token);
    }

    public static function stkPushQuery($environment, $checkoutRequestID, $businessShortCode, $password, $timestamp){
        $this->token = self::generateToken();
        if($this->appStatus == 'live'){
            $this->url = Constants::MPESA['STKQUERY_API_URL'];
        }elseif($this->appStatus == 'sandbox'){
            $this->url = Constants::MPESA['STKQUERY_SANDBOX_URL'];
        } else{
            throw new MpesaException("Invalid application status");
        }

        $this->param[Constants::P_QUERY['SHORTCODE']] = $businessShortCode;
        $this->param[Constants::P_QUERY['PASSWORD']] = $password;
        $this->param[Constants::P_QUERY['TIMESTAMP']] = $timestamp;
        $this->param[Constants::P_QUERY['CHECKOUT_RID']] = $checkoutRequestID;
        return $this->response = $this->curlRequest($this->url, $this->param, $this->token);
    }

    public function completeTransaction($status = true)
    {
        if ($status === true) {
            $this->param[Constants::COMPLETE['RESULT_DESC']] = "Confirmation Service request accepted successfully";
            $this->param[Constants::COMPLETE['RESULT_CODE']] = "0";
        } else {
            $this->param[Constants::COMPLETE['RESULT_DESC']] = "Confirmation Service not accepted";
            $this->param[Constants::COMPLETE['RESULT_CODE']] = "1";
        }
        header('Content-Type: application/json');
        echo json_encode($this->param);
    }

    private function curlRequest($url, $param, $token)
    {
        $data = json_encode($param);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type:application/json', 
            'Authorization:Bearer ' . $token
        ]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $curl_response = curl_exec($curl);
        $getHTTPCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        if ($curl_response === false) {
            throw new MpesaException('Unable to connect to Safaricom Mpesa API: ' . $curlError);
        } elseif ($getHTTPCode != 200) {
            $error = json_decode($curl_response, true);
            throw new MpesaException('Bad request from Safaricom Daraja Mpesa API: HTTP code ' . $getHTTPCode ." (". $error['errorMessage'] . ")");
        }
        return $curl_response;
    }
}

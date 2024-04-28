<?php

namespace Eddieodira\Mpesa\Constant;

class Constants
{
    const MPESA = [
        //TOKEN GENERATION ENDPONTS
        'TOKEN_SANDBOX_URL' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
        'TOKEN_API_URL' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',

        //C2B ENDPONTS
        'C2B_SANDBOX_URL' => 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/simulate',
        'C2B_API_URL' => 'https://api.safaricom.co.ke/mpesa/c2b/v1/simulate',

        //STKPUSH ENDPONTS
        'STKPUSH_SANDBOX_URL' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
        'STKPUSH_API_URL' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',

        //TRANSACTION STATUS QUERY ENDPONTS
        'QUERY_SANDBOX_URL' => 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
        'QUERY_API_URL' => 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query'
    ];

    const C2B = [
        'SHORTCODE' => 'ShortCode',
        'COMMAND_ID' => 'CommandID',
        'AMOUNT' => 'Amount',
        'MSISDN' => 'Msisdn',
        'BILL_REF_NUMBER' => 'BillRefNumber'
    ];

    const STK = [
        'SHORTCODE' => 'BusinessShortCode',
        'PASSWORD' => 'Password',
        'TIMESTAMP' => 'Timestamp',
        'TRANS_TYPE' => 'TransactionType',
        'AMOUNT' =>  'Amount',
        'PARTY_A' => 'PartyA',
        'PARTY_B' => 'PartyB',
        'PHONE_NUMBER' => 'PhoneNumber',
        'CALLBACK_URL' => 'CallBackURL',
        'ACCOUNT_REF' => 'AccountReference',
        'TRANS_DESC' => 'TransactionDesc'
    ];

    const QUERY = [
        'INITIATOR' => 'Initiator',
        'SEC_CRED' => 'SecurityCredential',
        'COMMAND_ID' => 'CommandID',
        'TRANS_ID' => 'TransactionID',
        'PARTY_A' => 'PartyA',
        'ID_TYPE' => 'IdentifierType',
        'RESULT_URL' => 'ResultURL',
        'TIME_OUT_URL' => 'QueueTimeOutURL',
        'REMARKS' => 'Remarks',
        'OCCASION' => 'Occasion'
    ];

    const P_QUERY = [
        'SHORTCODE' => 'BusinessShortCode',
        'PASSWORD' => 'Password',
        'TIMESTAMP' => 'Timestamp',
        'CHECKOUT_RID' => 'CheckoutRequestID'
    ];

    const COMPLETE = [
        'RESULT_DESC' =>  'ResultDesc',
        'RESULT_CODE' => 'ResultCode'
    ];
    
    const PARAM_PATTERNS = [
        'PHONE_NUMBER' => '/(\+254|254)([7][0-9]|[1][0-1]){1}[0-9]{1}[0-9]{6}/',
    ];
}

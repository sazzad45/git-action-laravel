<?php

namespace App\Domain\FastPay\Constant;


class APIEndPoints
{
    const LOGIN                             = "https://secure.fast-pay.cash/api/v3/signin/step1";

    const BALANCE_INFO                      = "https://secure.fast-pay.cash/api/v3/user-balance";

    const CASH_IN_CONFIRMATION              = "https://secure.fast-pay.cash/api/v3/deposit/cash-in";
    const CASH_IN_EXECUTE                   = "https://secure.fast-pay.cash/api/v3/deposit/cash-in";

    const BUNDLE_OPERATOR                   = "https://secure.fast-pay.cash/api/v3/operators";
    const BUNDLE_BUNDLE_LIST                = "https://secure.fast-pay.cash/api/v3/bundles" ;
    const BUNDLE_SUMMARY                    = "https://secure.fast-pay.cash/api/v3/process-purchase";
    const BUNDLE_PURCHASE                   = "https://secure.fast-pay.cash/api/v3/process-purchase";

    const REQUEST_MONEY_HISTORY             = "https://secure.fast-pay.cash/api/v3/reseller/money-requests";
    const REQUEST_MONEY_CONFIRMATION        = "https://secure.fast-pay.cash/api/v3/reseller/request-money-from-sales-rep";
    const REQUEST_MONEY_EXECUTE             = "https://secure.fast-pay.cash/api/v3/reseller/request-money-from-sales-rep";

    const TRANSACTION_HISTORY               = "https://secure.fast-pay.cash/api/v3/transaction-history";
    const INVOICE                           = "https://secure.fast-pay.cash/api/v3/invoice";
    const SALES_REPRESENTATIVE              = "https://secure.fast-pay.cash/api/v3/reseller/sales-reps";
    const NOTIFICATION                      = "https://secure.fast-pay.cash/api/v3/notifications";

    const FCM_TOKEN_UPDATE                  = "https://secure.fast-pay.cash/api/v3/fcm-key-update";
    const PIN_SET                           = "https://secure.fast-pay.cash/api/v3/set-4digit-pin";
    const PASSWORD_CHANGE                   = "https://secure.fast-pay.cash/api/v3/update-password";
    const RESET_PASSWORD_FOR_NEW            = "https://secure.fast-pay.cash/api/v3/password/reset-for-new";


}

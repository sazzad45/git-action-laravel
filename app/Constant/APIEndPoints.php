<?php


namespace App\Constant;


class APIEndPoints
{
    const VERSION = "/api/v1/version";

    const V1_LOGIN_SIGN_IN = "/api/v1/auth/signin";

    const V1_FIRST_TIME_PASSWORD_CHANGE = "/api/v1/auth/first-time-change-password";

    const V1_USER_BASIC_INFORMATION_GET = "/api/v1/private/user/basic-information";

    const V1_SYSTEM_PROMOTIONAL_OFFER_TOP_5 = "/api/v1/private/promotional-offers/top-5";
    const V1_SYSTEM_PROMOTIONAL_OFFER_ALL = "/api/v1/private/promotional-offers/show-all";

    const V1_SYSTEM_COMMON_COUNTRY_LIST = "/api/v1/private/common/show-country-list";
    const V1_SYSTEM_COMMON_CITY_LIST = "/api/v1/private/common/show-city-list";
    const V1_SYSTEM_COMMON_STATE_LIST_BY_COUNTRY_ID = "/api/v1/private/common/country/{id}";

    const V1_SYSTEM_COMMON_STATES = "/api/v1/private/common/state-list";
    const V1_SYSTEM_COMMON_CITIES = "/api/v1/private/common/city-list";

    const V1_SYSTEM_COMMON_OCCUPATIONS = "/api/v1/private/common/occupation-list";

    const V1_FORGOT_PASS_SEND_OTP = "/api/v1/auth/forgot-password/send-otp";
    const V1_FORGOT_PASS_VERIFY_OTP = "/api/v1/auth/forgot-password/verify-otp";
    const V1_FORGOT_PASS_RESET = "/api/v1/auth/forgot-password/reset";

    const V1_FORGOT_PIN_SEND_OTP = "/api/v1/auth/forgot-pin/send-otp";
    const V1_FORGOT_PIN_VERIFY_OTP = "/api/v1/auth/forgot-pin/verify-otp";
    const V1_FORGOT_PIN_RESET = "/api/v1/auth/forgot-pin/reset";

    const V1_USER_KYC_UPDATE_GEO_LOCATION = "/api/v1/private/kyc/update-geo-location-information";
    const V1_USER_KYC_UPLOAD_PHOTO = "/api/v1/private/kyc/upload-photo";
    const V1_USER_KYC_SET_SECURITY_PIN = "/api/v1/private/kyc/set-security-pin";

    const V1_CUSTOMER_KYC_OTP_GENERATE = "/api/v1/private/customer/kyc/otp-generate";
    const V1_CUSTOMER_KYC_OTP_VERIFY = "/api/v1/private/customer/kyc/otp-verify";

    const V1_CUSTOMER_KYC_VERIFICATION_DOC_TYPE_LIST = "/api/v1/private/customer/kyc/verification-doc-type-list";
    const V1_CUSTOMER_KYC_VERIFICATION_DOC_SUBMIT = "/api/v1/private/customer/kyc/document-submit";

    const V1_USER_TRANSACTIONAL_LIMIT = "/api/v1/private/user/transactional-limits";

    const V1_USER_PROFILE = "/api/v1/private/user/profile";
    const V1_USER_PROFILE_UPDATE = "/api/v1/private/user/profile-update";

    const V1_USER_PIN_CHANGE_STEP_1 = "/api/v1/auth/pin-change/step-1";
    const V1_USER_PIN_CHANGE_STEP_2 = "/api/v1/auth/pin-change/step-2";
    const V1_USER_PIN_CHANGE_WITHOUT_OTP = "/api/v1/auth/pin-change/without-pin";

    const V1_USER_PASSWORD_CHANGE_STEP_1 = "/api/v1/auth/change-password/step-1";
    const V1_USER_PASSWORD_CHANGE_STEP_2 = "/api/v1/auth/change-password/step-2";

    const V1_USER_LOGOUT = "/api/v1/auth/logout";

    const V1_USER_FIREBASE_TOKEN_UPDATE = "/api/v1/private/user/firebase-token-update";

    const V1_USER_REFER_A_FRIEND = "/api/v1/private/user/refer-a-friend";
    const V1_USER_APPLY_REFERRAL_CODE = "/api/v1/private/user/apply-referral-code";

    const V1_SUPPORT_CONTENT = "/api/v1/private/support-content";

    const V1_USER_TRANSACTION_HISTORY = "/api/v1/private/user/transaction/history";
    const V1_USER_TRANSACTION_INVOICE = "/api/v1/private/user/transaction/invoice";
    const V1_USER_TRANSACTION_SUMMARY = "/api/v1/private/user/transaction/summary";

    const V1_NOTIFICATIONS = "/api/v1/private/user/notifications";
    const V1_NOTIFICATION_READ = "/api/v1/private/user/notification";
    const V1_NOTIFICATION_READ_ALL = "/api/v1/private/user/notification/read-all";

    const V1_WALLET_TRANSACTION_SEND_MONEY_RECENT_RECIPIENT = "/api/v1/private/transaction/send-money/recent-recipients";

    const V1_WALLET_TRANSACTION_CASH_IN_STEP1 = "/api/v1/private/transaction/cash-in/summary";
    const V1_WALLET_TRANSACTION_CASH_IN_STEP2 = "/api/v1/private/transaction/cash-in/execute";

    const V1_WALLET_TRANSACTION_B2B_TRANSFER_RECENT_RECIPIENT = "/api/v1/private/transaction/b2b-transfer/recent-recipients";
    const V1_WALLET_TRANSACTION_B2B_TRANSFER_STEP1 = "/api/v1/private/transaction/b2b-transfer/summary";
    const V1_WALLET_TRANSACTION_B2B_TRANSFER_STEP2 = "/api/v1/private/transaction/b2b-transfer/execute";

    const V1_WALLET_TRANSACTION_CASH_OUT_STEP1 = "/api/v1/private/transaction/cash-out/summary";
    const V1_WALLET_TRANSACTION_CASH_OUT_STEP2 = "/api/v1/private/transaction/cash-out/execute";

    const V1_WALLET_TRANSACTION_RECEIVE_PAYMENT_QR = "/api/v1/private/transaction/receive-payment/qr";

    const V1_WALLET_TRANSACTION_DEPOSIT_MONEY_VIA_FAST_LINK = "/api/v1/private/transaction/deposit/via-fastlink/execute";

    const V1_WALLET_TRANSACTION_REQUEST_MONEY_RECENT_RECIPIENTS = "/api/v1/private/transaction/request-money/recent-recipients";
    const V1_WALLET_TRANSACTION_REQUEST_MONEY_STEP1 = "/api/v1/private/transaction/request-money/summary";
    const V1_WALLET_TRANSACTION_REQUEST_MONEY_STEP2 = "/api/v1/private/transaction/request-money/execute";
    const V1_WALLET_TRANSACTION_REQUEST_MONEY_HISTORY = "/api/v1/private/transaction/request-money/history";

    const V1_WALLET_TRANSACTION_QR_DETAILS = "/api/v1/private/transaction/qr-payment/summary";
    const V1_WALLET_TRANSACTION_QR_CONFRIMATION = "/api/v1/private/transaction/qr-payment/confirmation";
    const V1_WALLET_TRANSACTION_QR_EXECUTE = "/api/v1/private/transaction/qr-payment/execute";


    const V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_OPERATORS = "/api/v1/private/transaction/bundle-purchase/operators";
    const V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_BUNDLES = "/api/v1/private/transaction/bundle-purchase/bundles";
    const V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_SUMMARY = "/api/v1/private/transaction/bundle-purchase/summary";
    const V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_EXECUTE = "/api/v1/private/transaction/bundle-purchase/execute";

    const V1_PROMOTIONAL_BUNDLE_OFFERS_GET = "/api/v1/private/promotional-bundle-offers";

    const V1_CLIENT_SCREEN_COMPONENT_VISIBILITY_API = "/api/v1/private/client-screen-component-visible";

}

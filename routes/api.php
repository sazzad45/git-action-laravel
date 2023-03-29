<?php

use App\Constant\APIEndPoints as EP;
use App\Constant\BalanceType;
use App\Constant\Security\ApplicationFeature;
use App\Constant\Security\BlockReason;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'API\V1'], function () {

    Route::get(EP::VERSION, 'VersionController@index');

    Route::group(['middleware' => 'ddp'], function () {

        Route::group(['namespace' => 'Auth\ForgotPassword'], function () {
            Route::post(EP::V1_FORGOT_PASS_SEND_OTP, 'ForgetPasswordStep1Controller@sendOTP')
                ->middleware('is_blocked:' . BlockReason::Too_Many_OTP_Generated);
            Route::post(EP::V1_FORGOT_PASS_VERIFY_OTP, 'ForgetPasswordStep2Controller@verifyOTP')
                ->middleware('is_blocked:' . BlockReason::Too_Many_OTP_Generated);
            Route::post(EP::V1_FORGOT_PASS_RESET, 'ForgetPasswordStep3Controller@setPassword')
                ->middleware('is_blocked:' . BlockReason::Too_Many_OTP_Generated);
        });

        Route::group(['namespace' => 'Auth\SignIn'], function () {
            Route::post(EP::V1_LOGIN_SIGN_IN, 'LoginController@login')
                ->middleware('is_blocked:' . BlockReason::Multiple_Failed_Login_Attempt)
                ->middleware('throttle:40,1');
        });

        Route::group([
            'middleware' => [
                'auth:api_auth_passport',
                'isAgent',
                'has_access'
            ]
        ], function () {


            Route::group(['namespace' => 'Auth\Logout',], function () {
                Route::post(EP::V1_USER_LOGOUT, 'LogoutController@logout');
            });

            Route::group(['namespace' => 'System\PromotionalOffer'], function () {
                Route::get(EP::V1_SYSTEM_PROMOTIONAL_OFFER_TOP_5, 'TopFivePromotionalOfferController@show');
                Route::get(EP::V1_SYSTEM_PROMOTIONAL_OFFER_ALL, 'AllPromotionalOfferController@show');
                Route::get(EP::V1_PROMOTIONAL_BUNDLE_OFFERS_GET, 'PromotionalBundleOfferController@show');
            });

            Route::group(['namespace' => 'System\Common'], function () {
                Route::get(EP::V1_SYSTEM_COMMON_COUNTRY_LIST, 'CountryController@index');
                Route::get(EP::V1_SYSTEM_COMMON_STATE_LIST_BY_COUNTRY_ID, 'StateController@index')
                    ->where('id', '[0-9]+');
                Route::post(EP::V1_SYSTEM_COMMON_CITY_LIST, 'CityController@index');

                Route::get(EP::V1_SYSTEM_COMMON_STATES, 'StateListController@index');
                Route::get(EP::V1_SYSTEM_COMMON_CITIES, 'CityListController@index');

                Route::get(EP::V1_SYSTEM_COMMON_OCCUPATIONS, 'OccupationListController@index');
            });

            Route::group(['namespace' => 'User\KYC'], function () {
                Route::post(EP::V1_USER_KYC_UPDATE_GEO_LOCATION, 'GEOInfoUpdateController@update');
                Route::post(EP::V1_USER_KYC_UPLOAD_PHOTO, 'UploadPhotoController@upload');
                Route::post(EP::V1_USER_KYC_SET_SECURITY_PIN, 'SetSecurityPinController@store');
            });

            Route::group(['namespace' => 'Customer\Kyc', 'middleware' => 'authorizedToDoCustomerKyc'], function () {
                Route::group(['namespace' => 'Otp'], function () {
                    Route::post(EP::V1_CUSTOMER_KYC_OTP_GENERATE, 'OtpGenerateController@sendOtp');
                    Route::post(EP::V1_CUSTOMER_KYC_OTP_VERIFY, 'OtpVerifyController@verifyOtp');
                });

                Route::group(['namespace' => 'VerificationDoc'], function () {
                    Route::get(EP::V1_CUSTOMER_KYC_VERIFICATION_DOC_TYPE_LIST, 'VerificationDocTypeListController@index');
                    Route::post(EP::V1_CUSTOMER_KYC_VERIFICATION_DOC_SUBMIT, 'VerificationDocSubmitController@submit');
                    // Route::post('/api/v1/private/customer/kyc/reset', 'VerificationDocResetController@reset');
                });
            });

            Route::group(['namespace' => 'Auth'], function () {
                Route::post(EP::V1_USER_PIN_CHANGE_STEP_1, 'PinChange\PinChangeStep1Controller@sendOTP')
                    ->middleware('is_blocked:' . BlockReason::Too_Many_OTP_Generated);
                Route::post(EP::V1_USER_PIN_CHANGE_STEP_2, 'PinChange\PinChangeStep2Controller@update');
                Route::post(EP::V1_USER_PIN_CHANGE_WITHOUT_OTP, 'PinChange\PinChangeWithoutOTPController@update');

                Route::post(EP::V1_FIRST_TIME_PASSWORD_CHANGE, 'PasswordChange\FirstLoginPasswordChangeController@change');

                Route::post(EP::V1_USER_PASSWORD_CHANGE_STEP_1, 'PasswordChange\PasswordChangeStep1Controller@sendOTP');
                Route::post(EP::V1_USER_PASSWORD_CHANGE_STEP_2, 'PasswordChange\PasswordChangeStep2Controller@update');
            });

            Route::group(['namespace' => 'User'], function () {
                Route::get(EP::V1_USER_TRANSACTIONAL_LIMIT, 'Limit\LimitController@index');

                Route::get(EP::V1_USER_PROFILE, 'Profile\ProfileController@get');
                Route::post(EP::V1_USER_PROFILE_UPDATE, 'Profile\ProfileUpdateController@update');

                Route::get(EP::V1_USER_BASIC_INFORMATION_GET, 'BasicInfoController@show');

                Route::post(EP::V1_USER_FIREBASE_TOKEN_UPDATE, 'Firebase\FirebaseTokenUpdateController@update');

                Route::get(EP::V1_USER_REFER_A_FRIEND, 'Referral\ReferralController@get');
                Route::post(EP::V1_USER_APPLY_REFERRAL_CODE, 'Referral\ReferralCodeSubmitController@submit');
            });


            Route::group(['namespace' => 'Support'], function () {
                Route::get(EP::V1_SUPPORT_CONTENT, 'SupportController@index');
            });


            Route::group(['namespace' => 'Notification'], function () {
                Route::get(EP::V1_NOTIFICATIONS, 'NotificationController@index');
                Route::post(EP::V1_NOTIFICATION_READ, 'NotificationReadController@update');
                Route::post(EP::V1_NOTIFICATION_READ_ALL, 'ReadAllNotificationController@updateAll');
            });


            Route::group(['namespace' => 'Wallet'], function () {

                Route::get(EP::V1_USER_TRANSACTION_HISTORY, 'History\TransactionHistoryController@index');
                Route::get(EP::V1_USER_TRANSACTION_INVOICE, 'History\InvoiceController@show');
                Route::get(EP::V1_USER_TRANSACTION_SUMMARY, 'History\MonthwiseSummaryController@index');

                Route::post(EP::V1_WALLET_TRANSACTION_RECEIVE_PAYMENT_QR, 'Transaction\ReceivePayment\QrGeneratorController@generateQr');

                Route::post(EP::V1_WALLET_TRANSACTION_QR_DETAILS, 'QrPayment\DecryptQrController@decrypt');
                Route::post(EP::V1_WALLET_TRANSACTION_QR_CONFRIMATION, 'QrPayment\ConfirmationController@summary');
                Route::post(EP::V1_WALLET_TRANSACTION_QR_EXECUTE, 'QrPayment\PayController@execute');

                Route::get(EP::V1_WALLET_TRANSACTION_SEND_MONEY_RECENT_RECIPIENT, 'Recipient\RecentRecipientController@index');

                Route::post(EP::V1_WALLET_TRANSACTION_CASH_IN_STEP1, 'Transaction\CashIn\Step1Controller@summary')
                    ->middleware('is_blocked:' . ApplicationFeature::CASH_IN);
                Route::post(EP::V1_WALLET_TRANSACTION_CASH_IN_STEP2, 'Transaction\CashIn\Step2Controller@execute')
                    ->middleware('is_blocked:' . BlockReason::Multiple_Failed_PIN_Verification_Attempt)
                    ->middleware('is_blocked:' . ApplicationFeature::CASH_IN);

                Route::group(['middleware' => 'is_parent_main_agent'], function () {
                    Route::get(EP::V1_WALLET_TRANSACTION_B2B_TRANSFER_RECENT_RECIPIENT, 'Transaction\B2BTransfer\RecentRecipientController@index');
                    Route::post(EP::V1_WALLET_TRANSACTION_B2B_TRANSFER_STEP1, 'Transaction\B2BTransfer\Step1Controller@summary');
                    Route::post(EP::V1_WALLET_TRANSACTION_B2B_TRANSFER_STEP2, 'Transaction\B2BTransfer\Step2Controller@execute')
                        ->middleware('is_blocked:' . BlockReason::Multiple_Failed_PIN_Verification_Attempt);

                    Route::post(EP::V1_WALLET_TRANSACTION_CASH_OUT_STEP1, 'Transaction\CashOut\Step1Controller@summary');
                    Route::post(EP::V1_WALLET_TRANSACTION_CASH_OUT_STEP2, 'Transaction\CashOut\Step2Controller@execute')
                        ->middleware('is_blocked:' . BlockReason::Multiple_Failed_PIN_Verification_Attempt);
                });

                Route::get(EP::V1_WALLET_TRANSACTION_REQUEST_MONEY_RECENT_RECIPIENTS, 'Transaction\RequestMoney\RecentRecipientController@index');
                Route::post(EP::V1_WALLET_TRANSACTION_REQUEST_MONEY_STEP1, 'Transaction\RequestMoney\Step1Controller@requestMoney');
                Route::post(EP::V1_WALLET_TRANSACTION_REQUEST_MONEY_STEP2, 'Transaction\RequestMoney\Step2Controller@verifyPinForRequestMoney');
                Route::post(EP::V1_WALLET_TRANSACTION_REQUEST_MONEY_HISTORY, 'Transaction\RequestMoney\HistoryController@index');

                // Route::group(['middleware' => ['hasBalanceType:' . BalanceType::CARD]], function () {
                //     Route::get(EP::V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_OPERATORS, 'Transaction\BundlePurchase\OperatorController@index');
                //     Route::get(EP::V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_BUNDLES, 'Transaction\BundlePurchase\BundleController@index');
                //     Route::post(EP::V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_SUMMARY, 'Transaction\BundlePurchase\SummaryController@summary');
                //     Route::post(EP::V1_WALLET_TRANSACTION_BUNDLE_PURCHASE_EXECUTE, 'Transaction\BundlePurchase\PurchaseController@execute')
                //         ->middleware('is_blocked:' . BlockReason::Multiple_Failed_PIN_Verification_Attempt);
                // });
            });

            Route::group(['namespace' => 'System\Settings'], function () {
                Route::get(EP::V1_CLIENT_SCREEN_COMPONENT_VISIBILITY_API, 'VisibleComponentController@index');
            });
        });
    });
});

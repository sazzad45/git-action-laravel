<?php


namespace App\Constant\Security;


class BlockReason
{
    const Multiple_Failed_Login_Attempt = "Multiple Failed Login Attempt";
    const Multiple_Failed_PIN_Verification_Attempt = "PIN Verification Failed";
    const Too_Many_OTP_Generated = "Too Many OTP Generated";
}

<?php


namespace App\Domain\API\Utility;


class OTPPurpose
{
    const CHANGE_MSISDN = "Change MSISDN";
    const CHANGE_EMAIL = "Change Email";
    const VERIFY_EMAIL = "Verify Email";

    const PASSWORD_RESET = "Password Reset";
    const PASSWORD_CHANGE = "Password Change";
    const PIN_CHANGE = "PIN Change";

    const SEND_MONEY = "Send Money";
    const PIN_RESET = "Pin Reset";

    const KYC_VERIFICATION = "KYC Verification";
}

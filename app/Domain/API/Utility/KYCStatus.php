<?php


namespace App\Domain\API\Utility;


class KYCStatus
{
    const UNVERIFIED = 0;
    const VERIFIED = 1;
    const AWAITING_APPROVAL = 2;
    const DECLINATION = 9;
}

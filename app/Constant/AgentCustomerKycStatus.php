<?php


namespace App\Constant;


class AgentCustomerKycStatus
{
    const PENDING_ID = 0;
    const VERIFIED_ID  = 1;
    const DECLINED_ID = 9;

    const PENDING_TEXT = 'Pending';
    const VERIFIED_TEXT  = 'Verified';
    const DECLINED_TEXT = 'Declined';
}

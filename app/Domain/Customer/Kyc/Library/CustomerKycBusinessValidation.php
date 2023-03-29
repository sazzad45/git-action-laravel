<?php


namespace App\Domain\Customer\Kyc\Library;

use App\Constant\LevelConst;
use App\Domain\API\Utility\KYCStatus;
use App\Traits\ApiResponseTrait;

class CustomerKycBusinessValidation
{
    use ApiResponseTrait;

    private $user;
    private $latestKyc;

    /**
     * CustomerKycBusinessValidation constructor.
     * @param $user
     * @param $latestKyc
     */
    public function __construct($user, $latestKyc)
    {
        $this->user = $user;
        $this->latestKyc = $latestKyc;
    }

    public function validate()
    {
        if ( ! $this->user ) {
            return $this->invalidResponse([trans('messages.user_not_found')]);
        }

        if ($this->user->is_kyc_verified != 1) {
            return $this->invalidResponse([trans('messages.customer_kyc_is_not_verified')]);
        }

        if ($this->user->agent_kyc_verified == 1) {
            return $this->invalidResponse([trans('messages.customer_agent_kyc_is_already_verified')]);
        }

        if ( ! $this->latestKyc ) {
            return $this->invalidResponse([trans('messages.no_verified_kyc_document_found')]);
        }

        if ($this->latestKyc->status != KYCStatus::VERIFIED) {
            return $this->invalidResponse([trans('messages.no_verified_kyc_document_found')]);
        }

        $level = $this->user->levels->first();
        if (!empty($level) && $level->id == LevelConst::TEMPORARY_ACCOUNT) {
            return $this->invalidResponse([trans('messages.your_kyc_is_not_fully_verified_from_backoffice')]);
        }

        return false;
    }
}

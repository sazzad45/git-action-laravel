<?php

namespace App\Http\Controllers\API\V1\Customer\Kyc\Otp;

use App\Domain\API\Utility\OTPPurpose;
use App\Domain\Customer\Kyc\Library\CustomerKycBusinessValidation;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use App\Http\Controllers\APIBaseController;
use App\Domain\Independent\Models\OTP;
use App\Http\Requests\API\Customer\Kyc\Otp\VerifyRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OtpVerifyController extends APIBaseController
{
    public function verifyOtp(VerifyRequest $request)
    {
        try {
            $user = User::where('mobile_no', $request->mobile_number)->first();
            $latestKyc = UserVerificationDoc::where('user_id', $user->id)->latest('id')->first();
            if ( $error = (new CustomerKycBusinessValidation($user, $latestKyc))->validate() ) {
                return $error;
            }
            
            $otp = OTP::where('identity', $user->mobile_no)
                ->where('otp', $request->otp)
                ->where('purpose', OTPPurpose::KYC_VERIFICATION)
                ->where('status', 0)
                ->where('created_at', ">=", Carbon::now()->subMinutes(5)->format('Y-m-d H:i:s'))
                ->first();

            if ($otp) {
                $otp->status = 1;
                $otp->save();
                
                $this->logActivity('Customer kyc verfication otp verified. Customer mobile number# ' . $user->mobile_no, $user, auth()->user());

                return $this->respondInJSON(200, [], ['kycDoc' => $this->getKycDocData($latestKyc)]);
            }

            return $this->respondInJSON(422, [trans('messages.otp_didnot_matched')]);
        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getKycDocData(UserVerificationDoc $latestKyc)
    {
        $docImages = [];
        if ($latestKyc->verification_doc_file) {
            $docImages[] = $latestKyc->verification_doc_file;
        }

        if ($latestKyc->verification_doc_file_2) {
            $docImages[] = $latestKyc->verification_doc_file_2;
        }

        return [
            'kyc_id' => $latestKyc->id,
            'document_type_id' => [
                'id' => $latestKyc->verification_docs_type_id,
                'name' => $latestKyc->verificationDocType->name,
            ],
            'document_number' => $latestKyc->verification_doc_id,
            'document_images' => $docImages,
            'country' => [
                'id' => $latestKyc->issuing_country_id,
                'name' => $latestKyc->issuingCountry->name,
            ],
            'state' => [
                'id' => $latestKyc->state_id ?? '',
                'name' => $latestKyc->state->name ?? '',
            ],
            'city' => [
                'id' => $latestKyc->city_id ?? '',
                'name' => $latestKyc->city->name ?? '',
            ],
            'full_name' => $latestKyc->full_name,
            'date_of_birth' => $latestKyc->date_of_birth ? $latestKyc->date_of_birth->format('Y-m-d') : '',
            'gender' => $latestKyc->gender,
            'issue_date' => $latestKyc->issue_date ? $latestKyc->issue_date->format('Y-m-d') : '',
            'expiry_date' => $latestKyc->expiry_date ? $latestKyc->expiry_date->format('Y-m-d') : '',
        ];
    }
}
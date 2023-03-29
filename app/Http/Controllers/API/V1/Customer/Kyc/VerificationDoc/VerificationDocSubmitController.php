<?php

namespace App\Http\Controllers\API\V1\Customer\Kyc\VerificationDoc;

use App\Channels\FcmPushChannel;
use App\Constant\AgentCustomerKycStatus;
use App\Domain\Customer\Kyc\Library\CustomerKycBusinessValidation;
use App\Domain\Level\Models\KycLevelMapping;
use App\Domain\Level\Models\LevelUser;
use App\Domain\UserRelation\Models\AgentCustomerVerificationDoc;
use App\Domain\UserRelation\Models\AgentCustomerVerificationDocImage;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Customer\Kyc\VerificationDoc\DocSubmitRequest;
use App\Domain\UserRelation\Models\User;
use App\Domain\UserRelation\Models\UserVerificationDoc;
use App\Notifications\Kyc\VerificationDocSubmitted;
use App\Traits\FileHandlerTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationDocSubmitController extends APIBaseController
{
    use FileHandlerTrait;

    public function submit(DocSubmitRequest $request)
    {
        try {
            $latestKyc = UserVerificationDoc::find($request->kyc_id);
            $user = User::find($latestKyc->user_id ?? 0);
            if ( $error = (new CustomerKycBusinessValidation($user, $latestKyc))->validate() ) {
                return $error;
            }

            if ( ! ( $request->has('documnet_type_ids') && !empty($request->documnet_type_ids) ) ) {
                return $this->invalidResponse([trans('messages.customer_agent_kyc_document_ids_required')]);
            }

            if ( ! ( $request->has('document_images') && !empty($request->document_images) ) ) {
                return $this->invalidResponse([trans('messages.customer_agent_kyc_document_images_required')]);
            }

            DB::beginTransaction();

            $customerVerificationDoc = $this->storeCustomerVerificationDoc($request, $user);

            $this->storeDocImages($customerVerificationDoc, $request);

            $this->makeTheUserAgentKycVerifed($user);

            $this->updateUserLevelUsingKycMapping($user);

            $user->notify(new VerificationDocSubmitted(['mail', 'database', FcmPushChannel::class], $this->getKycDocSubmitTitle(), $this->getKycDocSubmitMessage()));

            $this->logActivity('Customer kyc verfication document submitted. Customer mobile number# ' . $user->mobile_no, $user, auth()->user(), $customerVerificationDoc->toArray());

            DB::commit();

            return $this->respondInJSON(200, [trans('messages.customer_verification_doc_submitted_successfully')]);
        } catch(\Exception $e) {
            DB::rollBack();
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function storeCustomerVerificationDoc(DocSubmitRequest $request, User $user)
    {
        return AgentCustomerVerificationDoc::create([
            'submitted_by_id' => auth()->user()->id,
            'customer_id' => $user->id,
            'kyc_id' => $request->kyc_id,
            'doc_type_id' => $request->doc_type_id,
            'doc_number' => $request->doc_number,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'full_name' => $request->full_name,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'monthly_income' => $request->monthly_income,
            'status' => AgentCustomerKycStatus::VERIFIED_ID,
        ]);
    }

    private function storeDocImages(AgentCustomerVerificationDoc $customerVerificationDoc, $request)
    {
        if ($request->has('document_images')) {
            if ( ! empty($request->document_images) ) {
                $i = 0;
                foreach ($request->document_images as $attachmentKey => $attachment) {
                    $filePath = $this->uploadCustomerVerificationDocFile($request, "document_images.$attachmentKey");
                    if ($filePath) {
                        $docTypeId = $request->documnet_type_ids[$i];

                        AgentCustomerVerificationDocImage::create([
                            'acvd_id' => $customerVerificationDoc->id,
                            'doc_type_id' => $docTypeId,
                            'image_path' => $filePath,
                        ]);

                        $i++;
                    } else {
                        throw new \Exception("Sorry! Can not upload the doc images");
                    }
                }
            }
        }
    }

    private function makeTheUserAgentKycVerifed(User $user)
    {
        $user->agent_kyc_verified = 1;
        $user->save();
    }

    private function updateUserLevelUsingKycMapping(User $user)
    {
        $level = $user->levels->first();
        if ($level) {
            $map = KycLevelMapping::where('primary_level_id', $level->id)->first();
            if ($map) {
                LevelUser::where('user_id', $user->id)->update([
                    'level_id' => $map->secondary_level_id
                ]);
            }
        }
    }

    private function getKycDocSubmitTitle()
    {
        return 'KYC Verified';
    }

    private function getKycDocSubmitMessage()
    {
        return 'Congratulations! Your KYC is now verified. Thank you for using FastPay.';
    }
}

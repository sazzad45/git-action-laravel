<?php

namespace App\Http\Controllers\API\V1\Wallet\QrPayment;

use App\Constant\Security\ApplicationFeature;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Wallet\Transaction\CashIn\VerifyRequest;
use App\Http\Requests\API\Wallet\Transaction\QrPayment\PayRequest;
use App\QrWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayController extends APIBaseController
{
    public function execute(PayRequest $request)
    {
        try {
            $qr = QrWarehouse::where('uuid', '=', $this->_decrypt_string($request->input('qr_text')))->first();

            if($qr == ""){
                return $this->invalidResponse([trans('messages.invalid_qr_code')]);
            }

            $payload = json_decode($qr->payload, true);

            $receiver = User::with('userType')->where('mobile_no', '=', $payload['receiver']['msisdn'])->firstOrFail();

            if ($qr->type == "Profile" && $receiver->userType->name == "Personal") {
                if ($block = $this->isBlocked(auth()->user()->mobile_no, ApplicationFeature::CASH_IN)) {
                    return $this->blockedPublicApiResponse($request, $block->remarks);
                }
                
                return $this->processAsCashInTransaction($qr, $payload, $receiver, $request);
            }

            return $this->invalidResponse([trans('messages.undefined_qr_journey')]);

        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getResponse(PayRequest $request)
    {
        $recipient = User::where('mobile_no', $request->user()->mobile_no)->first();

        return [
            "summary" => [
                "recipient" => [
                    "name" => $recipient->name,
                    "mobile_number" => $recipient->mobile_no,
                    "avatar" => secure_asset("image/revamp.jpg")
                ],
                "invoice_id" => "WQPGDR" . mt_rand(1000, 9999)
            ]
        ];
    }

    private function fetchUserObject(string $mobileNumber)
    {
        return User::where('mobile_no', '=', $mobileNumber)
            ->with('userType', 'userStatus', 'accounts.accountBalances.currency', 'accounts.userAccountType')
            ->first();
    }

    private function processAsCashInTransaction(QrWarehouse $qr, array $payload, User $receiver, PayRequest $request)
    {
        $verifyRequest = new VerifyRequest();
        $verifyRequest->merge([
            'receiver_mobile_number' => $receiver->mobile_no,
            'amount' => $request->input('amount'),
            'pin' => $request->input('pin')
        ]);

        $cashInObject = new \App\Http\Controllers\API\V1\Wallet\Transaction\CashIn\Step2Controller();
        return $cashInObject->execute($verifyRequest);
    }
}

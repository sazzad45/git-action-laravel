<?php

namespace App\Http\Controllers\API\V1\Wallet\QrPayment;

use App\Constant\Security\ApplicationFeature;
use App\Http\Controllers\API\V1\Wallet\Transaction\CashIn\Step1Controller;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\QrPayment\SummaryRequest;
use App\QrWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfirmationController extends APIBaseController
{
    public function summary(SummaryRequest $request)
    {
        try {
            $qr = QrWarehouse::where('uuid', '=', $this->_decrypt_string($request->input('qr_text')))->first();

            if($qr == ""){
                return $this->invalidResponse([trans('messages.invalid_qr_code')]);
            }

            $qrPayload = json_decode($qr->payload, true);

            return $this->generateSummary($request, $qrPayload);

           // return $this->respondInJSON(200, [], $responseData);

        } catch (\Exception $e) {

            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function generateSummary(SummaryRequest $request, $qrPayload)
    {
        if ($block = $this->isBlocked(auth()->user()->mobile_no, ApplicationFeature::CASH_IN)) {
            return $this->blockedPublicApiResponse($request, $block->remarks);
        }
        
        $summaryRequest = new \App\Http\Requests\API\Wallet\Transaction\CashIn\SummaryRequest();
        $summaryRequest->merge([
            'receiver_mobile_number' => $qrPayload['receiver']['msisdn'],
            'amount' => $request->amount
        ]);

        $cashInObject = new Step1Controller();
        return $cashInObject->summary($summaryRequest);
    }
}

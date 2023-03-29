<?php

namespace App\Http\Controllers\API\V1\Wallet\QrPayment;

use App\Http\Controllers\APIBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Wallet\Transaction\QrPayment\DecryptQrRequest;
use App\QrWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DecryptQrController extends APIBaseController
{
    public function decrypt(DecryptQrRequest $request)
    {
        try {
            $qr = QrWarehouse::where('uuid', '=', $this->_decrypt_string($request->input('qr_text')))->first();

            if($qr == ""){
                return $this->invalidResponse([trans('messages.invalid_qr_code')]);
            }

            return $this->successResponse([], json_decode($qr->payload, true));

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

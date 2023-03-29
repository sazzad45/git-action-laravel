<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\ReceivePayment;

use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\ReceivePayment\QrGeneratorRequest;
use App\QrWarehouse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QrGeneratorController extends APIBaseController
{
    public function generateQr(QrGeneratorRequest $request)
    {
        try {

            return $this->respondInJSON(200, [], ['qrText' => $this->generateQrAndRespondWithQrText($request)]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function generateQrAndRespondWithQrText(QrGeneratorRequest $request): string
    {
        $qrData = [
            'receiver' => [
                'name' => $request->user()->original_name,
                'msisdn' => $request->user()->mobile_no,
                "thumbnail" => $request->user()->avatar
            ],
            'params' => [
                [
                    'field_type' => 'numeric',
                    'label' => 'Amount',
                    'key' => 'amount',
                    'value' => (string) $request->input('amount'),
                    'placeholder' => "IQD",
                    'input' => true,
                    'type' => 'numeric',
                    'required' => true,
                    'is_read_only' => false
                ],
                [
                    'field_type' => 'textarea',
                    'label' => 'Write a note (optional)',
                    'key' => 'note',
                    'value' => "",
                    'placeholder' => '',
                    'input' => true,
                    'type' => 'alphanumeric',
                    'required' => false,
                    'is_read_only' => false
                ]
            ]
        ];

        $qr = QrWarehouse::create([
            'user_id' => $request->user()->id,
            'type' => 'PaymentQR',
            'uuid' => (string) Str::uuid(),
            'payload' => json_encode($qrData),
            'status' => false
        ]);

        $this->logActivity('QR generated for buy balance', $request->user(), $request->user(), $qr->toArray());

        return $this->_encrypt_string_with_prefix(
            $this->_encrypt_string(
                $qr->uuid
            )
        );
    }
}

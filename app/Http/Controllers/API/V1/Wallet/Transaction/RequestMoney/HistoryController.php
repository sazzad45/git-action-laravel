<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\RequestMoney;

use App\Domain\Wallet\Models\MoneyRequest;
use App\Http\Controllers\APIBaseController;
use App\QrWarehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HistoryController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            return $this->respondInJSON(200, [], [
                'history' => $this->fetchMoneyRequests($request)
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }


    private function fetchMoneyRequests(Request $request)
    {
        $user = $request->user();

        $moneyRequests = MoneyRequest::with('requestor.profile', 'requestee.profile')
            ->has('requestee')
            ->has('requestor')
            ->where(function ($query) use ($request) {
                $query->where('requestor_id', $request->user()->id)
                    ->orWhere('requestee_id', $request->user()->id);
            })->latest()->limit(50)->get();

        return $moneyRequests->map(function ($record) use ($user) {
            return [
                "request_id" => $record->id,
                "icon" => secure_asset("image/icons/request_money.png"),
                "title" => [
                    "text" => ($record->requestor_id == $user->id) ? "Money request to" : "Money request from",
                    "color" => "#03EBA3"
                ],
                "identity" => [
                    "name" => ($record->requestor_id == $user->id) ? $record->requestee->original_name : $record->requestor->original_name,
                    "mobile_number" => ($record->requestor_id == $user->id) ? $record->requestee->mobile_no : $record->requestor->mobile_no,
                    "color" => "#03EBA3"
                ],
                "amount" => [
                    "text" => (string)$record->amount,
                    "currency" => config('fastpay.currency_text'),
                    "color" => "#03EBA3"
                ],
                'qr_text' => $this->getEquivalentQrText($record),
                "created_at" => $record->created_at->format('Y-m-d H:i:s'),
                "date" => $record->created_at->format('j F Y H:i A'),
                'type' => 'FastPay Balance',
                "transaction-type" => "Money Request",
                "transaction_type" => "Money Request",
                "status" => $this->getRequestStatus($record),
                "is_actionable" => $this->defineActionable($record, $user)
            ];
        });
    }

    private function getRequestStatus($record): array
    {
        return [
            "icon" => $this->getIcon($record->status),
            "text" => $this->getStatusText($record->status),
            "color" => $this->getColor($record->status)
        ];
    }

    private function getIcon($status)
    {
        if ($status == 1) return secure_asset("image/icons/money-requests/accepted.png");

        if ($status == 2) return secure_asset("image/icons/money-requests/declined.png");

        if ($status == 9) return secure_asset("image/icons/money-requests/accepted.png");

        return secure_asset("image/icons/money-requests/pending.png");


    }

    private function getStatusText($status)
    {
        if ($status == 1) return "ACCEPTED";

        if ($status == 2) return "DECLINED";

        if ($status == 9) return "COMPLETED";

        return "PENDING";
    }

    private function getColor($status)
    {
        if ($status == 1) return "#03EBA3";

        if ($status == 2) return "#FFFFFF";

        if ($status == 9) return "#2B335E";

        return "#9294A7";
    }

    private function defineActionable($record, $user): bool
    {
        if (($record->requestor_id == $user->id)) {
            return false;
        } else {
            if ($record->status == 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function getEquivalentQrText($moneyRequest)
    {
        $qrData = [
            'receiver' => [
                'name' => $moneyRequest->requestor->original_name,
                'msisdn' => $moneyRequest->requestor->mobile_no,
                "thumbnail" => $moneyRequest->requestor->avatar
            ],
            'params' => [
                [
                    'field_type' => 'numeric',
                    'label' => 'Amount',
                    'key' => 'amount',
                    'value' => (int)$moneyRequest->amount,
                    'placeholder' => "IQD",
                    'input' => true,
                    'type' => 'numeric',
                    'required' => true,
                    'is_read_only' => true
                ],
                [
                    'field_type' => 'text',
                    'label' => 'Money Request ID',
                    'key' => 'money_request_id',
                    'value' => "{$moneyRequest->id}",
                    'placeholder' => "",
                    'input' => false,
                    'type' => 'alphanumeric',
                    'required' => true,
                    'is_read_only' => true
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

        $qr = QrWarehouse::firstOrCreate(
            ['user_id' => $moneyRequest->requestor->id, 'type' => "MoneyRequest{$moneyRequest->id}"],
            [
                'uuid' => (string)Str::uuid(),
                'payload' => json_encode($qrData),
                'status' => false
            ]
        );

        return $this->_encrypt_string_with_prefix(
            $this->_encrypt_string(
                $qr->uuid
            )
        );
    }
}

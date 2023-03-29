<?php

namespace App\Http\Controllers\API\V1\Wallet\Transaction\RequestMoney;

use App\Constant\DeepLinkMapping;
use App\Constant\TransactionTypeCode;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\MoneyRequest;
use App\Http\Controllers\APIBaseController;
use App\Http\Requests\API\Wallet\Transaction\RequestMoney\ExecuteRequest;
use App\Traits\TransactionTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Domain\UserRelation\Models\UserDevice;

class Step2Controller extends APIBaseController
{
    use TransactionTrait;
    public function verifyPinForRequestMoney(ExecuteRequest $request)
    {
        try {
            $recipient = User::where('mobile_no', $request->input('requestee_mobile_number'))->first();

            $moneyRequest = new MoneyRequest();
            $moneyRequest->id = (string) Str::uuid();
            $moneyRequest->requestor_id = auth()->user()->id;
            $moneyRequest->requestee_id = $recipient->id;
            $moneyRequest->amount = $request->input('amount');
            $moneyRequest->currency_id = config('fastpay.currency_id');
            $mr = $moneyRequest->save();

            if($mr){
                $deep_link_url = DeepLinkMapping::REQUEST_MONEY;
                $userDevice = UserDevice::where('user_id', $recipient->id)->orderBy('id', 'DESC')->first();
                if ($userDevice && strtolower($userDevice->push_os) == "ios"){
                    $deep_link_url = DeepLinkMapping::IOS_REQUEST_MONEY;
                }

                $action_url = $this->build_action_url($request,$moneyRequest,$deep_link_url);

                $this->notify(TransactionTypeCode::REQUEST_MONEY,null, $recipient, null,$action_url);
            }

            $logMessage = auth()->user()->name . '(' . auth()->user()->mobile_no . ')' . ' has requested money amount of: ' . $request->input('amount') . ' ' . config('fastpay.currency_text') . ' to ' . $recipient->name . '(' . $recipient->mobile_no . ').' . ' Money Request ID# ' . $moneyRequest->id;
            $this->logActivity($logMessage, auth()->user(), auth()->user(), $moneyRequest->toArray());

            return $this->respondInJSON(
                200,
                [trans('messages.request_has_been_sent')],
                $this->getResponse($recipient)
            );
        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getResponse(User $recipient )
    {
        return [
            "summary" => [
                "show_recipient_block" => false,
                "recipient" => [
                    "name" => $recipient->name,
                    "mobile_number" => $recipient->mobile_no,
                    "avatar" => $recipient->avatar
                ],
                "invoice_id" => ""
            ]
        ];
    }

    private function build_action_url($request, $mr, $deep_link_url = DeepLinkMapping::REQUEST_MONEY) : string
    {
        $query_params = [
            'requestId' => $mr->id,
            'title' => 'New Money Request',
            'subtitle' => 'Amount: ' . $request->input('amount') . ' ' . config('fastpay.currency_text'),
            'positiveBtnText' => 'Accept',
            'negetiveBtnText' => 'Not now',
            // 'note' => $request->has('note') ? $request->input('note') : '',
            'details' => 'From: ' . auth()->user()->original_name . PHP_EOL . 'Mobile: ' . auth()->user()->mobile_no,
            'amount' =>  $request->input('amount'),
            'mobileNumber' => auth()->user()->mobile_no,
            'banner' =>  auth()->user()->avatar,
            'name' =>  auth()->user()->original_name,
            'type' =>  'RequestMoney',
        ];
        return $deep_link_url . http_build_query($query_params);
    }
}

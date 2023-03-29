<?php

namespace App\Http\Controllers\API\V1\Wallet\History;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIBaseController;
use App\Domain\Transaction\Models\Transaction;
use App\Http\Requests\API\Wallet\Transaction\InvoiceRequest;

class InvoiceController extends APIBaseController
{
    public function show(InvoiceRequest $request)
    {
        try {
            $transaction = Transaction::has('sender.user')->has('receiver.user')
                ->with('sender.user', 'receiver.user', 'transactionType')
                ->where('tx_unique_id', $request->input('invoice_id'))
                ->first();

            if( ! $transaction ){
                return $this->invalidResponse(['Sorry! [404] Invoice Not Found.']);
            }

            if(
                ($transaction->sender->user->id != auth()->user()->id) &&
                ($transaction->receiver->user->id != auth()->user()->id)
            ){
                return $this->invalidResponse(['Sorry! [401] Invoice is not yours.']);
            }

            return $this->respondInJSON(200, [], $this->getDetails($request, $transaction));

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    private function getDetails(InvoiceRequest $request, Transaction $transaction): array
    {
        $total = (int)($transaction->amount + $transaction->sender_commission);
        $charge = (int)$transaction->sender_commission;
        if($transaction->receiver->user->id == auth()->user()->id)
        {
            $total = (int) $transaction->amount;
            $charge = 0;
        }

        return [
            'date' => $transaction->created_at->format('F j, Y H:i:s A'),
            'transaction_id' => $transaction->tx_unique_id,
            'transaction_type' => $transaction->transactionType->name,

            'transaction_amount' => number_format((int)$transaction->amount)." ".config('fastpay.currency_text'),
            'transaction_fee' => number_format($charge)." ".config('fastpay.currency_text'),
            'total_deduction' => number_format($total)." ".config('fastpay.currency_text'),
            'nature_of_transaction' => $transaction->transactionType->nature,

            'recipient' => $this->getRecipientDetails($transaction, $transaction->transactionType->nature, $request->user()),
            'bar_code' => uniqid(),
            'card' => $this->getCardPurchaseDetails($transaction, $transaction->transactionType->nature, $request->user()),
            'is_credit' => $this->checkTransactionIsCredit($transaction)
        ];
    }

    private function checkTransactionIsCredit(Transaction $transaction)
    {
        if($transaction->receiver->user->id == auth()->user()->id)
        {
            return true;
        }
        return false;
    }

    private function getRecipientDetails($transaction, $nature, $user)
    {
        if ($nature == "Card")
            return null;

        if ($transaction->sender->user_id == $user->id) {
            return [
                'title' => 'Sent To:',
                'name' => $transaction->receiver->user->original_name,
                'msisdn' => $transaction->receiver->user->mobile_no,
                "avatar" => $transaction->receiver->user->avatar,
            ];
        } else {
            return [
                'title' => 'Received From:',
                'name' => $transaction->sender->user->original_name,
                'msisdn' => $transaction->sender->user->mobile_no,
                "avatar" => $transaction->sender->user->avatar,
            ];
        }
    }

    private function getCardPurchaseDetails($transaction, $nature, $user)
    {
        if ($nature == 'Transfer')
            return null;

        $card = $this->getCardDetails($transaction->tx_unique_id, $transaction->order_id);

        return [
            'category' => $card['data']['info']['category'],
            'type' => $card['data']['info']['type'],
            'bundle_id' => $card['data']['info']['bundle_id'],
            'thumbnail' => $card['data']['info']['thumbnail'],
            'display_params' => $this->getDataPayload($card),
            'how_to' => $card['data']['info']['how_to']['en'] ?? ""
        ];
    }

    private function getCardDetails($tx_unique_id, $order_id)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => config('internal_services.thirdparty.base_url')."/api/card/get-card-info/{$order_id}/{$tx_unique_id}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $responseArray = json_decode($response, true);

        if ($responseArray['code'] != 200)
            throw new \Exception("NO CARD RELATED INFORMATION FOUND FROM OUTER MICROSERVICE.");

        return $responseArray;
    }

    private function getDataPayload(array $card): array
    {
        $payload = [];

        foreach ($card['data']['payload'] as $key => $value) {
            array_push($payload, [
                'key' => ucwords($key),
                'value' => (string) $value
            ]);
        }

        return $payload;
    }
}

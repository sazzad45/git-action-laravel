<?php


namespace App\Http\Controllers\API\V1\Wallet\Transaction\RequestMoney;

use App\Constant\UserStatusId;
use App\Domain\Distribution\Models\DistributionChannel;
use App\Domain\FastPay\Constant\APIEndPoints;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RecentRecipientController extends APIBaseController
{
    public function index(Request $request)
    {
        try {
            $recipients = $this->generateRecipients();

            return $this->respondInJSON(200, [], [
                'recipients' => $recipients
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }

    public function bring_recepients($request)
    {
        $response = Http::withToken(Cache::get(auth()->user()->mobile_no . '_old_token'))->get(APIEndPoints::SALES_REPRESENTATIVE);
        if ($response->failed() || !isset($response->json()['data']['sales-reps'])) {
            throw ValidationException::withMessages(['error' => trans('internal_server_error')]);
        }
        return $this->respondInJSON($response->json()['code'], $response->json()['messages'], ['recipients' => $response->json()['data']['sales-reps']]);
    }

    private function generateRecipients()
    {
        $dc = DistributionChannel::with('salesRep.profile')
            ->where('agent_id', auth()->user()->id)
            ->where('status', 1)
            ->whereNotNull('sales_rep_id')
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($dc->isEmpty()) return [];

        $list = [];
        foreach ($dc as $item) {
            $user = $item->salesRep;

            if (! $user ) continue;

            if ($item->salesRep->user_status_id != UserStatusId::APPROVED) continue;

            $list[] = [
                "name" => $item->salesRep->original_name,
                "mobile_number" => $item->salesRep->mobile_no ?? "",
                "avatar" => $item->salesRep->avatar
            ];

        }

        return $list;
    }
}

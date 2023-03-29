<?php

namespace App\Http\Controllers\API\V1\Wallet\History;

use App\Domain\Wallet\Library\TransactionSummaryGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIBaseController;
use App\Domain\Accounting\Models\UserAccount;
use App\Domain\Transaction\Models\TransactionType;

use App\Http\Requests\API\Wallet\Transaction\MonthwiseSummaryRequest;



class MonthwiseSummaryController extends APIBaseController
{
    public function index(MonthwiseSummaryRequest $request)
    {
        try {
            $user = auth()->user();
            $date = $request->date;

            $summary = (new TransactionSummaryGenerator($user, $date))->getSummary();

            return $this->respondInJSON(200, [], [
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

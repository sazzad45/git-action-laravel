<?php


namespace App\Domain\Wallet\Library;

use App\Constant\TransactionType;
use App\Constant\TransactionTypeText;
use App\Constant\UserTypeId;
use App\Domain\Transaction\Models\Statement;
use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\Limit\UserUsage;
use App\Domain\Wallet\Models\Limit\UserUsageSummary;
use Carbon\Carbon;

class TransactionSummaryGenerator
{
    private User $user;
    private $date;
    private $reportForThisMonth;
    private $firstBalance;
    private $lastBalance;

    public function __construct(User $user, $date)
    {
        $this->user = $user;
        $this->date = $date;
        $this->reportForThisMonth = $this->isCurrentMonth();

        $this->calculateBalance();
    }

    private function isCurrentMonth()
    {
        if (
            (date('Y') == date('Y', strtotime($this->date)))
            &&
            (date('m') == date('m', strtotime($this->date)))
        ) {
            return true;
        }

        return false;
    }

    private function calculateBalance()
    {
        if ($this->reportForThisMonth == true) {
            $this->firstBalance = $this->getFirstBalance($this->date);
            $this->lastBalance = $this->getLastBalance($this->date);
        }
    }

    private function getFirstBalance($date)
    {
        $date = date('Y-m-01', strtotime($date)) . ' 00:00:00';

        $statement = Statement::where('created_at', '<', $date)
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', "DESC")
            ->first();

        if ($statement == "") {
            return 0;
        }

        return $statement->current_balance;
    }

    private function getLastBalance($date)
    {
        $date = date('Y-m-d', strtotime($date . ' +1 Month')) . ' 00:00:00';

        $statement = Statement::where('created_at', '<', $date)
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', "DESC")
            ->first();

        if ($statement == "") {
            return 0;
        }

        return $statement->current_balance;
    }

    public function getSummary()
    {
        $transactionTypes = $this->getTransactionType($this->user->user_type_id);

        $last_updated_at = date('H:i:s A');
        $result = [];

        if ($this->reportForThisMonth == true) {
            $userUsages = UserUsage::where('user_id', $this->user->id)
                ->whereIn('transaction_type_id', $transactionTypes)
                ->where('action_date', $this->date)
                ->orderBy('created_at', 'DESC')
                ->get();

            foreach ($transactionTypes as $tt) {
                $usage = $this->getTransactionUsage($tt, $userUsages);

                $result[] = [
                    'type' => (new TransactionTypeText())->getText($tt),
                    'amount' => number_format((int)$usage['amount']) . " IQD",
                    'occurrence' => number_format((int)$usage['count']) . " times"
                ];
            }

        } else {

            $usageSummary = UserUsageSummary::where('user_id', $this->user->id)
                ->where('year', date('Y', strtotime($this->date)))
                ->where('month', date('m', strtotime($this->date)))
                ->first();

            if ($usageSummary == "") {
                foreach ($transactionTypes as $tt) {
                    $result[] = [
                        'type' => (new TransactionTypeText())->getText($tt),
                        'amount' => '0 IQD',
                        'occurrence' => '0 times'
                    ];
                }

                $this->firstBalance = $this->getFirstBalance($this->date);
                $this->lastBalance = $this->getLastBalance($this->date);

                try {
                    $newUsageSummary = new UserUsageSummary();
                    $newUsageSummary->user_id = $this->user->id;
                    $newUsageSummary->year = date('Y', strtotime($this->date));
                    $newUsageSummary->month = date('m', strtotime($this->date));
                    $newUsageSummary->starting_balance = $this->firstBalance;
                    $newUsageSummary->ending_balance = $this->lastBalance;
                    $newUsageSummary->summary = json_encode($result);
                    $newUsageSummary->save();
                } catch (\Exception $e) {
                    \Log::error($e);
                }

            } else {
                $result = json_decode($usageSummary->summary);

                $this->firstBalance = $usageSummary->starting_balance;
                $this->lastBalance = $usageSummary->ending_balance;
                $last_updated_at = date('H:i:s A', strtotime($usageSummary->created_at));
            }
        }


        return [
            'month' => date('F Y', strtotime($this->date)),
            'last_updated_at' => $last_updated_at,
            'start_balance' => $this->firstBalance . ' IQD',
            'end_balance' => $this->lastBalance . ' IQD',
            'overview' => $result
        ];
    }

    private function getTransactionUsage($trx_type_id, $userUsages): array
    {
        $res = [
            'amount' => 0,
            'count' => 0
        ];

        foreach ($userUsages as $usage) {
            if ($usage->transaction_type_id == $trx_type_id) {
                $res['amount'] = $usage->amount;
                $res['count'] = $usage->tx_no;
                break;
            }
        }

        return $res;
    }

    private function getTransactionType($userType)
    {
        $list = [];
        switch ($userType) {
            case UserTypeId::AGENT :
                $list = [
                    TransactionType::DATA_BUNDLE, // bundle purchase
                    TransactionType::P2P_TRANSFER,

                ];
                break;

            case UserTypeId::SR :
                $list = [
                    TransactionType::MONEY_TRANSFER
                ];
                break;

            case UserTypeId::MERCHANT :
                $list = [
                    TransactionType::REFUND,
                    TransactionType::CASH_OUT
                ];
                break;

            case UserTypeId::PERSONAL :
                $list = [
                    \App\Constant\TransactionType::CASH_OUT, // withdraw money
                    \App\Constant\TransactionType::P2P_TRANSFER, // send money
                    \App\Constant\TransactionType::ONLINE_SHOPPING, // merchant payment
                    \App\Constant\TransactionType::DEPOSIT_CASH_CARD, // deposit money
                    \App\Constant\TransactionType::DATA_BUNDLE // bundle purchase
                ];
                break;
        }

        return $list;
    }
}

<?php


namespace App\Domain\Wallet\Models\Limit;

use Illuminate\Database\Eloquent\Model;

class LimitUpdater
{
    private LimitCheckerParam $param;

    public function __construct(LimitCheckerParam $param)
    {
        $this->param = $param;
    }

    public function update()
    {
        //\Log::info("Limit Updater Update ");
        if($this->param->dailyLimit != null){
            $this->updateDailyLimit($this->getUserLimitForDaily($this->param->dailyLimit->id));
        }
        if($this->param->monthlyLimit != null){
            $this->updateMonthlyLimit($this->getUserLimitForMonthly($this->param->monthlyLimit->id));
        }
    }


    private function getUserLimitForDaily($dailyLimitId)
    {
        //\Log::info("Limit Updater getUserLimitForDaily : ".$dailyLimitId);

        return UserLimit::where('user_id', $this->param->user->id)
            ->where('action_date', date('Y-m-d'))
            ->where('daily_limit_id', $dailyLimitId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    private function getUserLimitForMonthly($monthlyLimitId)
    {
        //\Log::info("Limit Updater getUserLimitForMonthly : ".$monthlyLimitId);

        $thisMonth = date('m');
        $thisYear = date('Y');

        return UserMonthlyLimit::where('user_id', $this->param->user->id)
            ->where('action_month', $thisMonth)
            ->where('action_year', $thisYear)
            ->where('monthly_limit_id', $monthlyLimitId)
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    private function updateDailyLimit($userLimit)
    {
        if($userLimit == ""){

            //\Log::info("Limit Updater updateDailyLimit : inside not null");

            $newUserLimit = new UserLimit();
            $newUserLimit->action_date = date('Y-m-d');
            $newUserLimit->user_id = $this->param->user->id;
            $newUserLimit->daily_limit_id = $this->param->dailyLimit->id ;

            $newUserLimit->number_of_tx = 1;
            $newUserLimit->tx_amount = $this->param->amount;

            $newUserLimit->created_at = date('Y-m-d H:i:s');
            $newUserLimit->updated_at = date('Y-m-d H:i:s');

            $newUserLimit->save();

            return;
        }
        //\Log::info("Limit Updater updateDailyLimit : just update");
        // user daily limit available so just update
        $userLimit->number_of_tx = $userLimit->number_of_tx + 1;
        $userLimit->tx_amount = $userLimit->tx_amount + $this->param->amount;
        $userLimit->update();
    }



    private function updateMonthlyLimit($userLimit)
    {

        $thisMonth = date('m');
        $thisYear = date('Y');


        if($userLimit == ""){

            //\Log::info("Limit Updater updateMonthlyLimit : inside not null");

            $newUserLimit = new UserMonthlyLimit();
            $newUserLimit->action_month = $thisMonth;
            $newUserLimit->action_year = $thisYear;
            $newUserLimit->user_id = $this->param->user->id;
            $newUserLimit->monthly_limit_id = $this->param->monthlyLimit->id;
            $newUserLimit->number_of_tx = 1;
            $newUserLimit->tx_amount = $this->param->amount;
            $newUserLimit->created_at = date('Y-m-d H:i:s');
            $newUserLimit->updated_at = date('Y-m-d H:i:s');
            $newUserLimit->save();

            return;
        }


        // monthly limit already available so just update the value

        //\Log::info("Limit Updater updateMonthlyLimit : just update");

        $userLimit->number_of_tx = $userLimit->number_of_tx+1;
        $userLimit->tx_amount = $userLimit->tx_amount + $this->param->amount;
        $userLimit->update();
    }
}

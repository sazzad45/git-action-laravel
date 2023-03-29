<?php

namespace App\Jobs;

use App\Domain\UserRelation\Models\User;
use App\Domain\Wallet\Models\Limit\UserUsage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransactionConsumeUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private User $user;
    private $date;
    private int $transactionTypeId;
    private $amount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, $date, $transactionTypeId, $amount)
    {
        $this->user = $user;
        $this->date = date('Y-m-d', strtotime($date));
        $this->transactionTypeId = $transactionTypeId;
        $this->amount = $amount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $userUsage = UserUsage::where('user_id', $this->user->id)
                        ->where('action_date', $this->date)
                        ->where('transaction_type_id', $this->transactionTypeId)
                        ->first();

            if($userUsage != ""){
                $userUsage->amount = $userUsage->amount + $this->amount;
                $userUsage->tx_no = $userUsage->tx_no + 1;
                $userUsage->update();

                return;
            }


            $newUserUsage = new UserUsage();
            $newUserUsage->user_id = $this->user->id;
            $newUserUsage->action_date = $this->date;
            $newUserUsage->transaction_type_id = $this->transactionTypeId;
            $newUserUsage->amount = $this->amount;
            $newUserUsage->tx_no = 1;
            $newUserUsage->save();

        }catch (\Exception $e){
            \Log::error($e);
        }
    }
}

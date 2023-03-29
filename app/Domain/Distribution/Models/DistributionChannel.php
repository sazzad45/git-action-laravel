<?php

namespace App\Domain\Distribution\Models;

use App\Domain\Independent\Models\Currency;
use App\Domain\Transaction\Models\Transaction;
use App\Domain\UserRelation\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionChannel extends Model
{
    use SoftDeletes;

    protected $table = "distribution_channels";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];


    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function subdistributor()
    {
        return $this->belongsTo(User::class, 'sub_distributor_id');
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}

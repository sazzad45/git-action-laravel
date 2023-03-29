<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentCustomerVerificationDoc extends Model
{
    use SoftDeletes;
    protected $table = "agent_customer_verification_docs";
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'date_of_birth', 'issue_date', 'expiry_date'];
    protected $guarded = ['id'];
}

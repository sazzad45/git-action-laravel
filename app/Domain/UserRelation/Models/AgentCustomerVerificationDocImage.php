<?php

namespace App\Domain\UserRelation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgentCustomerVerificationDocImage extends Model
{
    use SoftDeletes;
    protected $table = "agent_customer_verification_doc_images";
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function agentCustomerVerificationDoc()
    {
        return $this->belongsTo(AgentCustomerVerificationDoc::class, 'acvd_id');
    }
}

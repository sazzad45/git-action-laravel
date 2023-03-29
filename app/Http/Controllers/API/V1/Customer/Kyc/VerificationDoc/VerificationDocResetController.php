<?php

namespace App\Http\Controllers\API\V1\Customer\Kyc\VerificationDoc;

use App\Domain\UserRelation\Models\AgentCustomerVerificationDoc;
use App\Domain\UserRelation\Models\AgentCustomerVerificationDocImage;
use App\Domain\UserRelation\Models\User;
use App\Http\Controllers\APIBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationDocResetController extends APIBaseController
{
    public function reset(Request $request)
    {
        try {
            return $this->respondInJSON(200, ['Service Unavailable']);
            
            $user = User::where('mobile_no', $request->mobile_number)->first();
            
            DB::beginTransaction();

            AgentCustomerVerificationDocImage::whereHas('agentCustomerVerificationDoc', function($query) use ($user) {
                $query->where('customer_id', $user->id);
            })
            ->forceDelete();

            AgentCustomerVerificationDoc::where('customer_id', $user->id)->forceDelete();

            $user->agent_kyc_verified = 0;
            $user->save();

            DB::commit();

            return $this->respondInJSON(200, ['Success']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

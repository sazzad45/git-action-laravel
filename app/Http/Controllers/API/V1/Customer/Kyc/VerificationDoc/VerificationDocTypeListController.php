<?php

namespace App\Http\Controllers\API\V1\Customer\Kyc\VerificationDoc;

use App\Domain\UserRelation\Models\UserVerificationDocType;
use App\Http\Controllers\APIBaseController;
use Illuminate\Support\Facades\Log;

class VerificationDocTypeListController extends APIBaseController
{
    public function index()
    {
        try {
            $data = UserVerificationDocType::active()
                ->select('id', 'name', 'icon', 'is_frontpage_required', 'is_backpage_required')
                ->get();

            return $this->respondInJSON(200, ['Success'], ['document_types' => $data]);
        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

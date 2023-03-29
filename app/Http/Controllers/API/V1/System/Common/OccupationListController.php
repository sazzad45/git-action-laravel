<?php

namespace App\Http\Controllers\API\V1\System\Common;

use App\Domain\UserRelation\Models\Occupation;
use App\Http\Controllers\APIBaseController;
use Illuminate\Support\Facades\Log;

class OccupationListController extends APIBaseController
{
    public function index()
    {
        try {
            $occupations = Occupation::active()->cursor();

            $responsePayload = [];
            foreach($occupations as $occupation){
                $responsePayload[] = [
                    'id' => $occupation->id,
                    'name' => $occupation->name,
                    'icon' => $occupation->icon,
                ];
            }

            return $this->respondInJSON(200, [], ['occupations' => $responsePayload]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

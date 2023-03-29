<?php

namespace App\Http\Controllers\API\V1\System\PromotionalOffer;

use App\Domain\Finance\Models\PromotionalOffer;
use App\Domain\MarketingApp\MarketingAppEndPoints;
use App\Http\Controllers\APIBaseController;
use App\Http\Traits\MarketingApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TopFivePromotionalOfferController extends APIBaseController
{
    use MarketingApp;
    public function show()
    {
        try {
            $params['limit'] = 5;
            $params['app_for'] = config('basic_settings.app_name');
            $data = $this->makeMarketingAppGetRequest(MarketingAppEndPoints::PROMOTIONAL_OFFERS_URL, $params);

            $response = $data->json();
            if ($data->failed()) {
                return $this->invalidResponse($response['messages']);
            }

            return $this->respondInJSON(200, [], $response['data']);
            
            $promotionalOffers = PromotionalOffer::where('status', 1)
                ->where('is_bundle_offer', 0)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $responsePayload = [];
            foreach ($promotionalOffers as $promotionalOffer)
            {
                $responsePayload[] = [
                    'promotion_id' => $promotionalOffer->id,
                    'promotion_title' => $promotionalOffer->title,
                    'promotional_banner' => $promotionalOffer->banner,
                    'promotion_details' => $promotionalOffer->details
                ];
            }

            return $this->respondInJSON(200, [], ['offers' => $responsePayload]);

        } catch(\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

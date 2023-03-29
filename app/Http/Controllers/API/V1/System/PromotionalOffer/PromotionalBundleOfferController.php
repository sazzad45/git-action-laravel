<?php

namespace App\Http\Controllers\API\V1\System\PromotionalOffer;

use App\Domain\Finance\Models\PromotionalOffer;
use App\Domain\MarketingApp\MarketingAppEndPoints;
use App\Http\Controllers\APIBaseController;
use App\Http\Traits\MarketingApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromotionalBundleOfferController extends APIBaseController
{
    use MarketingApp;
    public function show(Request $request)
    {
        try {
            $params['bundle_offer'] = 1;
            $params['app_for'] = config('basic_settings.app_name');
            $data = $this->makeMarketingAppGetRequest(MarketingAppEndPoints::PROMOTIONAL_OFFERS_URL, $params);

            $response = $data->json();
            if ($data->failed()) {
                return $this->invalidResponse($response['messages']);
            }

            return $this->respondInJSON(200, [], $response['data']);

            $promotionalOffers = PromotionalOffer::where('status', 1)
                ->where('is_bundle_offer', 1)
                ->orderBy('created_at', 'desc')
                ->get();

            $responsePayload = [];
            foreach ($promotionalOffers as $promotionalOffer)
            {
                $responsePayload[] = [
                    'promotion_id' => $promotionalOffer->id,
                    'promotion_title' => $promotionalOffer->title,
                    'promotional_banner' => $promotionalOffer->banner,
                    'link' => $promotionalOffer->link ?? '',
                    'btn_text' => $promotionalOffer->btn_text ?? "",
                    'btn_color' => $promotionalOffer->btn_color ?? "",
                    'operator_id' => $promotionalOffer->operator_id ?? "",
                    'bundle_id' => $promotionalOffer->bundle_id ?? ""
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

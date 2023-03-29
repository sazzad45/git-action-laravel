<?php


namespace App\Http\Controllers\API\V1\Support;


use App\Domain\MarketingApp\MarketingAppEndPoints;
use App\Http\Controllers\APIBaseController;
use App\Http\Traits\MarketingApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupportController extends APIBaseController
{
    use MarketingApp;
    public function index(Request $request)
    {
        try {
            $params['lang'] = strtolower(\App::getLocale() ?? 'en');
            $params['app_for'] = config('basic_settings.app_name');
            $data = $this->makeMarketingAppGetRequest(MarketingAppEndPoints::FAQ_CONTENT_URL, $params);

            $response = $data->json();
            if ($data->failed()) {
                return $this->invalidResponse($response['messages']);
            }

            $faqs = $response['data'];


            $contactUs = [
                'mobile' => config('basic_settings.company.mobile_no'),
                'email' => config('basic_settings.company.email'),
                'website' => config('basic_settings.company.website'),
                'address' => config('basic_settings.company.address'),
                'social_media' => [
                    'facebook' => config('basic_settings.company.facebook'),
                    'youtube' => config('basic_settings.company.youtube'),
                    'twitter' => config('basic_settings.company.twitter'),
                    'instragram' => config('basic_settings.company.instagram'),
                    'linkedin' => config('basic_settings.company.linkedin'),
                    'snapchat' => config('basic_settings.company.snapchat')
                ]
            ];

            return $this->respondInJSON(200, [], ['faqs' => $faqs, 'contact_us' => $contactUs]);

        } catch (\Exception $e) {
            Log::error($e);
            Log::error($e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage());

            return $this->respondInJSON(500, [trans('messages.internal_server_error')]);
        }
    }
}

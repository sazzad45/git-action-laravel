<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait MarketingApp {
    protected function makeMarketingAppGetRequest($url, $params=[]){
        
        $response = Http::withHeaders([
            'Store-Access-Token' => config('internal_services.marketing_panel.access_token.store_access_token')
        ])->get(config('internal_services.marketing_panel.base_url') . $url, $params);

        return $response;
    }
}